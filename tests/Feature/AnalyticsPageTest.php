<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 방문자 통계: who may open it, and what it draws.
 *
 * The page is a frame around Umami's own dashboard. Reading those
 * figures through the API needs a paid plan, so the share link Umami
 * issues is embedded instead - which means the page has one job, and
 * one way to fail: no link configured.
 */
class AnalyticsPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the application roles.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    /**
     * Developers can open the analytics page.
     */
    public function test_developers_can_view_the_analytics_page(): void
    {
        $developer = User::factory()->create();
        $developer->assignRole('developer');

        $this->actingAs($developer)
            ->get('/admin/analytics')
            ->assertStatus(200);
    }

    /**
     * Administrators can open the analytics page.
     */
    public function test_admins_can_view_the_analytics_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get('/admin/analytics')
            ->assertStatus(200);
    }

    /**
     * The configured dashboard is what the page draws.
     */
    public function test_the_umami_dashboard_is_embedded(): void
    {
        config(['services.umami.share_url' => 'https://cloud.umami.is/share/test-share-id']);

        $developer = User::factory()->create();
        $developer->assignRole('developer');

        $this->actingAs($developer)
            ->get('/admin/analytics')
            ->assertOk()
            ->assertSee('https://cloud.umami.is/share/test-share-id', false)
            /** The frame, for a laptop. */
            ->assertSee('Umami 방문자 통계', false)
            /** And the way out, which is what a phone is shown instead. */
            ->assertSee('Umami에서 열기', false);
    }

    /**
     * With no link set the page says so rather than framing nothing.
     */
    public function test_an_unconfigured_page_explains_itself(): void
    {
        config(['services.umami.share_url' => null]);

        $developer = User::factory()->create();
        $developer->assignRole('developer');

        $this->actingAs($developer)
            ->get('/admin/analytics')
            ->assertOk()
            ->assertSee('UMAMI_SHARE_URL')
            ->assertDontSee('<iframe', false);
    }

    /**
     * Content editors are refused.
     */
    public function test_content_editors_cannot_view_the_analytics_page(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole('content_editor');

        $this->actingAs($editor)
            ->get('/admin/analytics')
            ->assertStatus(403);
    }
}
