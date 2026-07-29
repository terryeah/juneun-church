<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

/**
 * Fetches zone analytics from the Cloudflare GraphQL Analytics API.
 *
 * Requires an API token with Analytics:Read on the zone and the zone
 * identifier, both provided through config/services.php. All methods
 * degrade gracefully when the integration is not configured yet.
 */
class CloudflareAnalyticsService
{
    /**
     * Whether the Cloudflare credentials have been configured.
     */
    public function isConfigured(): bool
    {
        return filled(config('services.cloudflare.api_token'))
            && filled(config('services.cloudflare.zone_id'));
    }

    /**
     * Daily zone totals for the given inclusive date range.
     *
     * @return Collection<int, array{date: string, requests: int, page_views: int, unique_visitors: int, bytes: int, cached_requests: int, threats: int}>
     */
    public function dailyStats(CarbonInterface $since, CarbonInterface $until): Collection
    {
        if (! $this->isConfigured()) {
            return collect();
        }

        $query = <<<'GRAPHQL'
        query ($zone: String!, $since: String!, $until: String!) {
            viewer {
                zones(filter: { zoneTag: $zone }) {
                    httpRequests1dGroups(
                        limit: 40
                        filter: { date_geq: $since, date_leq: $until }
                        orderBy: [date_ASC]
                    ) {
                        dimensions { date }
                        sum { requests bytes cachedRequests pageViews threats }
                        uniq { uniques }
                    }
                }
            }
        }
        GRAPHQL;

        $response = Http::withToken(config('services.cloudflare.api_token'))
            ->post('https://api.cloudflare.com/client/v4/graphql', [
                'query' => $query,
                'variables' => [
                    'zone' => config('services.cloudflare.zone_id'),
                    'since' => $since->toDateString(),
                    'until' => $until->toDateString(),
                ],
            ]);

        if (! $response->successful() || filled($response->json('errors'))) {
            report(new \RuntimeException('Cloudflare analytics query failed: '.$response->body()));

            return collect();
        }

        return collect($response->json('data.viewer.zones.0.httpRequests1dGroups', []))
            ->map(fn (array $group) => [
                'date' => $group['dimensions']['date'],
                'requests' => $group['sum']['requests'] ?? 0,
                'page_views' => $group['sum']['pageViews'] ?? 0,
                'unique_visitors' => $group['uniq']['uniques'] ?? 0,
                'bytes' => $group['sum']['bytes'] ?? 0,
                'cached_requests' => $group['sum']['cachedRequests'] ?? 0,
                'threats' => $group['sum']['threats'] ?? 0,
            ]);
    }

    /**
     * Whether the Web Analytics (RUM) credentials have been configured.
     *
     * RUM data comes from the browser beacon, so it counts real
     * visitors only and is unaffected by bots and crawlers.
     */
    public function isRumConfigured(): bool
    {
        return $this->isConfigured()
            && filled(config('services.cloudflare.account_id'))
            && filled(config('services.cloudflare.rum_site_tag'));
    }

    /**
     * Daily real-visitor totals from Web Analytics for the date range.
     *
     * @return Collection<int, array{date: string, visits: int, page_views: int}>
     */
    public function dailyRealVisits(CarbonInterface $since, CarbonInterface $until): Collection
    {
        if (! $this->isRumConfigured()) {
            return collect();
        }

        $query = <<<'GRAPHQL'
        query ($account: String!, $site: String!, $since: Date!, $until: Date!) {
            viewer {
                accounts(filter: { accountTag: $account }) {
                    rumPageloadEventsAdaptiveGroups(
                        limit: 40
                        filter: { date_geq: $since, date_leq: $until, siteTag: $site }
                        orderBy: [date_ASC]
                    ) {
                        dimensions { date }
                        sum { visits }
                        count
                    }
                }
            }
        }
        GRAPHQL;

        $response = Http::withToken(config('services.cloudflare.api_token'))
            ->post('https://api.cloudflare.com/client/v4/graphql', [
                'query' => $query,
                'variables' => [
                    'account' => config('services.cloudflare.account_id'),
                    'site' => config('services.cloudflare.rum_site_tag'),
                    'since' => $since->toDateString(),
                    'until' => $until->toDateString(),
                ],
            ]);

        if (! $response->successful() || filled($response->json('errors'))) {
            report(new \RuntimeException('Cloudflare RUM query failed: '.$response->body()));

            return collect();
        }

        return collect($response->json('data.viewer.accounts.0.rumPageloadEventsAdaptiveGroups', []))
            ->map(fn (array $group) => [
                'date' => $group['dimensions']['date'],
                'visits' => $group['sum']['visits'] ?? 0,
                'page_views' => $group['count'] ?? 0,
            ]);
    }
}
