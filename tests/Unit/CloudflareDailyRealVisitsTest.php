<?php

namespace Tests\Unit;

use App\Services\CloudflareAnalyticsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Bucketing tests for the RUM daily real-visitor aggregation.
 *
 * Cloudflare returns UTC hourly buckets; the service must roll them
 * up into Australia/Brisbane days so evening traffic lands on the
 * day the visitor actually experienced.
 */
class CloudflareDailyRealVisitsTest extends TestCase
{
    /**
     * An hour after 14:00 UTC belongs to the next Brisbane day.
     */
    public function test_utc_evening_hours_land_on_the_next_brisbane_day(): void
    {
        $service = new CloudflareAnalyticsService;

        $days = $service->bucketHoursIntoLocalDays(
            [
                ['dimensions' => ['datetimeHour' => '2026-07-31T10:00:00Z'], 'sum' => ['visits' => 2], 'count' => 4],
                ['dimensions' => ['datetimeHour' => '2026-07-31T15:00:00Z'], 'sum' => ['visits' => 3], 'count' => 5],
            ],
            Carbon::parse('2026-07-31', 'Australia/Brisbane'),
            Carbon::parse('2026-08-01 23:59:59', 'Australia/Brisbane'),
        )->keyBy('date');

        $this->assertSame(2, $days['2026-07-31']['visits']);
        $this->assertSame(4, $days['2026-07-31']['page_views']);
        $this->assertSame(3, $days['2026-08-01']['visits']);
        $this->assertSame(5, $days['2026-08-01']['page_views']);
    }

    /**
     * Days with no beacon data are returned zero-filled so the
     * snapshot command never falls back to bot-inflated zone counts.
     */
    public function test_quiet_days_are_zero_filled(): void
    {
        $service = new CloudflareAnalyticsService;

        $days = $service->bucketHoursIntoLocalDays(
            [],
            Carbon::parse('2026-07-30', 'Australia/Brisbane'),
            Carbon::parse('2026-08-01 23:59:59', 'Australia/Brisbane'),
        );

        $this->assertSame(
            [
                ['date' => '2026-07-30', 'visits' => 0, 'page_views' => 0],
                ['date' => '2026-07-31', 'visits' => 0, 'page_views' => 0],
                ['date' => '2026-08-01', 'visits' => 0, 'page_views' => 0],
            ],
            $days->all(),
        );
    }

    /**
     * The GraphQL query filters by UTC datetimes covering the whole
     * Brisbane-day range, not by UTC date buckets.
     */
    public function test_daily_real_visits_queries_brisbane_day_bounds_in_utc(): void
    {
        config()->set('services.cloudflare', [
            'api_token' => 'token',
            'zone_id' => 'zone',
            'account_id' => 'account',
            'rum_site_tag' => 'site',
        ]);

        Http::fake([
            'api.cloudflare.com/*' => Http::response([
                'data' => ['viewer' => ['accounts' => [['rumPageloadEventsAdaptiveGroups' => [
                    ['dimensions' => ['datetimeHour' => '2026-07-31T15:00:00Z'], 'sum' => ['visits' => 7], 'count' => 9],
                ]]]]],
            ]),
        ]);

        $rows = (new CloudflareAnalyticsService)->dailyRealVisits(
            Carbon::parse('2026-07-31', 'Australia/Brisbane'),
            Carbon::parse('2026-08-01', 'Australia/Brisbane'),
        );

        Http::assertSent(function ($request): bool {
            return str_contains($request['query'], 'datetime_geq: "2026-07-30T14:00:00Z"')
                && str_contains($request['query'], 'datetime_leq: "2026-08-01T13:59:59Z"')
                && str_contains($request['query'], 'datetimeHour');
        });

        $this->assertSame(
            [
                ['date' => '2026-07-31', 'visits' => 0, 'page_views' => 0],
                ['date' => '2026-08-01', 'visits' => 7, 'page_views' => 9],
            ],
            $rows->all(),
        );
    }
}
