<?php

namespace Tests\Feature;

use App\Filament\Pages\Videos;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The 동영상 signpost belongs to administrators.
 */
class VideosTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An administrator reaches the page and is told plainly that there
     * is nothing in it yet.
     */
    public function test_an_administrator_reaches_the_signpost(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $this->assertTrue(Videos::canAccess());

        $this->get(Videos::getUrl())
            ->assertOk()
            ->assertSee('동영상은 아직 준비되지 않았습니다.', false);

        $this->assertStringEndsWith('/admin/videos', Videos::getUrl());
    }

    /**
     * Everyone else is kept out: a guest, a 성도, a content editor.
     */
    public function test_others_are_refused(): void
    {
        $this->seed(RoleSeeder::class);

        $this->get('/admin/videos')->assertRedirect();

        /** A 성도 is diverted to their profile before any page is reached. */
        $member = User::factory()->create();
        $member->assignRole('general_member');

        $this->actingAs($member);

        $this->assertFalse(Videos::canAccess());
        $this->get('/admin/videos')->assertRedirect();

        /** A content editor stays in the panel and is refused outright. */
        $editor = User::factory()->create();
        $editor->assignRole('content_editor');

        $this->actingAs($editor);

        $this->assertFalse(Videos::canAccess());
        $this->get('/admin/videos')->assertForbidden();
    }
}
