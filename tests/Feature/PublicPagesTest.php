<?php

namespace Tests\Feature;

use Database\Seeders\PositionSeeder;
use Database\Seeders\ServiceTypeSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke tests covering every public page of the site.
 */
class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the reference data the layout and pages rely on.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PositionSeeder::class,
            ServiceTypeSeeder::class,
            SiteSettingSeeder::class,
        ]);
    }

    /**
     * Every public route responds successfully.
     */
    public function test_all_public_pages_return_a_successful_response(): void
    {
        $routes = [
            '/', '/worship', '/news', '/events', '/people',
            '/bulletins', '/gallery', '/giving', '/location',
        ];

        foreach ($routes as $route) {
            $this->get($route)->assertStatus(200);
        }
    }

    /**
     * Unpublished announcements are hidden from the public site.
     */
    public function test_unpublished_announcements_are_not_visible(): void
    {
        $announcement = \App\Models\Announcement::factory()->draft()->create();

        $this->get('/news/'.$announcement->slug)->assertStatus(404);
        $this->get('/news')->assertDontSee($announcement->title);
    }

    /**
     * Published announcements are visible on their detail page.
     */
    public function test_published_announcements_are_visible(): void
    {
        $announcement = \App\Models\Announcement::factory()->create();

        $this->get('/news/'.$announcement->slug)
            ->assertStatus(200)
            ->assertSee($announcement->title);
    }
}
