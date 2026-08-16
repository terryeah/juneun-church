<?php

namespace Tests\Feature;

use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers who gets counted in 방문자 통계.
 *
 * The figures come from Cloudflare, which counts a visit when a page
 * loads its beacon. Nothing can be subtracted afterwards - the numbers
 * arrive as daily totals with no addresses in them - so the only place
 * this can be decided is when the page is written.
 */
class AnalyticsBeaconTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);

        config([
            'services.cloudflare.web_analytics_token' => 'test-token',
            'services.cloudflare.analytics_ignored_ips' => ['203.0.113.7'],
        ]);
    }

    /**
     * An ordinary visitor is counted.
     */
    public function test_a_visitor_is_sent_the_beacon(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('static.cloudflareinsights.com/beacon.min.js', false);
    }

    /**
     * An ignored address is not.
     */
    public function test_an_ignored_address_is_not_sent_the_beacon(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])
            ->get('/')
            ->assertOk()
            ->assertDontSee('static.cloudflareinsights.com', false);
    }

    /**
     * With nothing configured the beacon still goes out, so an empty
     * setting cannot quietly switch the statistics off.
     */
    public function test_an_empty_ignore_list_counts_everybody(): void
    {
        config(['services.cloudflare.analytics_ignored_ips' => []]);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])
            ->get('/')
            ->assertOk()
            ->assertSee('static.cloudflareinsights.com/beacon.min.js', false);
    }
}
