<?php

namespace Tests\Feature;

use App\Filament\Pages\GoogleAnalytics;
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
     * Without a property id the page is parked: the church has not
     * registered the domain with Google yet, so it stays out of the
     * panel rather than offering a screen nobody can use.
     */
    public function test_the_page_is_hidden_until_a_property_is_configured(): void
    {
        $developer = User::factory()->create();
        $developer->assignRole('developer');

        $this->assertFalse(GoogleAnalytics::canAccess());

        $this->actingAs($developer)
            ->get('/admin/google-analytics')
            ->assertForbidden();
    }

    /**
     * Once a property id exists but the credentials file does not, the
     * page renders and explains what is missing rather than throwing.
     */
    public function test_developers_see_the_setup_instructions_when_credentials_are_missing(): void
    {
        config(['analytics.property_id' => '123456789']);

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
