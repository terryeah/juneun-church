<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GoogleAnalyticsService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Access control and unconfigured-state tests for the developer-only
 * Google Analytics page.
 */
class GoogleAnalyticsPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the application roles and blank the integration, which is how
     * the page is deployed until the owner creates the GA4 property.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        config([
            'analytics.property_id' => null,
            'analytics.service_account_credentials_json' => storage_path('app/analytics/does-not-exist.json'),
        ]);
    }

    /**
     * With no property id and no credentials file, the page still renders
     * and explains what is missing rather than throwing.
     */
    public function test_developers_see_the_setup_instructions_when_nothing_is_configured(): void
    {
        $developer = User::factory()->create();
        $developer->assignRole('developer');

        $this->assertFalse(app(GoogleAnalyticsService::class)->isConfigured());
        $this->assertSame([], app(GoogleAnalyticsService::class)->report(today()->subDays(6), today()));

        $this->actingAs($developer)
            ->get('/admin/google-analytics')
            ->assertStatus(200)
            ->assertSee('구글 애널리틱스 연동이 아직 설정되지 않았습니다');
    }

    /**
     * A super admin without the developer role is refused, matching the
     * database graph.
     */
    public function test_super_admins_without_the_developer_role_are_refused(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin)
            ->get('/admin/google-analytics')
            ->assertStatus(403);
    }
}
