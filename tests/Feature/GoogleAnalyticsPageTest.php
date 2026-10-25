<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GoogleAnalyticsService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Access control and unconfigured-state tests for 방문자 통계, which
 * is Google Analytics read through the Data API.
 */
class GoogleAnalyticsPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the application roles and blank the integration, which is how
     * the page stands until the property and the key are in place.
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
     * Administrators and developers open it; before anything is
     * configured it explains what is missing rather than throwing.
     */
    public function test_administrators_see_the_setup_instructions_until_configured(): void
    {
        $this->assertFalse(app(GoogleAnalyticsService::class)->isConfigured());
        $this->assertSame([], app(GoogleAnalyticsService::class)->report(today()->subDays(6), today()));

        foreach (['admin', 'super_admin', 'developer'] as $role) {
            $user = User::factory()->create();
            $user->assignRole($role);

            $this->actingAs($user)
                ->get('/admin/analytics')
                ->assertOk()
                ->assertSee('구글 애널리틱스 연동이 아직 설정되지 않았습니다')
                ->assertSee('ANALYTICS_MEASUREMENT_ID');
        }
    }

    /**
     * A property id alone is not enough: the key file has to be there.
     */
    public function test_a_property_id_without_a_key_is_still_unconfigured(): void
    {
        config(['analytics.property_id' => '123456789']);

        $this->assertFalse(app(GoogleAnalyticsService::class)->isConfigured());
    }

    /**
     * Everyone else is refused.
     */
    public function test_editors_and_members_are_refused(): void
    {
        foreach (['content_editor', 'finance_officer'] as $role) {
            $user = User::factory()->create();
            $user->assignRole($role);

            $this->actingAs($user)->get('/admin/analytics')->assertForbidden();
        }
    }
}
