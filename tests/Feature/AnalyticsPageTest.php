<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Access control tests for the developer-only analytics page.
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
     * Users without the developer role are refused.
     */
    public function test_non_developers_cannot_view_the_analytics_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get('/admin/analytics')
            ->assertStatus(403);
    }
}
