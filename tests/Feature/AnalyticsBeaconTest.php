<?php

namespace Tests\Feature;

use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers who gets counted in 방문자 통계.
 *
 * Google Analytics counts a visit when the page runs its tag, and
 * nothing can be subtracted afterwards - so the only place this can be
 * decided is when the page is written. Which is the whole reason the
 * tag is ours rather than injected at the edge, as Cloudflare's was.
 */
class AnalyticsBeaconTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);

        config([
            'analytics.measurement_id' => 'G-TESTTEST01',
            'services.cloudflare.analytics_ignored_ips' => ['203.0.113.7'],
        ]);
    }

    /**
     * An ordinary visitor is counted.
     */
    public function test_a_visitor_is_sent_the_tag(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('googletagmanager.com/gtag/js?id=G-TESTTEST01', false)
            ->assertSee("gtag('config', \"G-TESTTEST01\")", false);
    }

    /**
     * An ignored address is not.
     */
    public function test_an_ignored_address_is_not_sent_the_tag(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])
            ->get('/')
            ->assertOk()
            ->assertDontSee('googletagmanager', false);
    }

    /**
     * With nothing configured the tag still goes out, so an empty
     * setting cannot quietly switch the statistics off.
     */
    public function test_an_empty_ignore_list_counts_everybody(): void
    {
        config(['services.cloudflare.analytics_ignored_ips' => []]);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])
            ->get('/')
            ->assertOk()
            ->assertSee('googletagmanager.com/gtag/js', false);
    }

    /**
     * Without a measurement id nobody is sent anything.
     */
    public function test_no_measurement_id_means_no_tag(): void
    {
        config(['analytics.measurement_id' => null]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('googletagmanager', false);
    }
}
