<?php

namespace Tests\Feature;

use App\Filament\Pages\Wiki;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The leadership walkthrough belongs to administrators.
 */
class WikiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An administrator reaches both the panel page and the document it
     * frames, and the document carries the walkthrough.
     */
    public function test_an_administrator_reaches_the_walkthrough(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $this->assertTrue(Wiki::canAccess());

        $this->get(Wiki::getUrl())
            ->assertOk()
            ->assertSee('자주 하는 일', false)
            ->assertSee('누가 무엇을 보나', false)
            ->assertSee('막혔을 때', false);

        $this->assertStringEndsWith('/admin/wiki', Wiki::getUrl());
    }

    /**
     * An editor reads it too, and is the likeliest to need it.
     *
     * It is the instructions for the panel, and the person with the
     * fewest menus is the one putting up their first 주보. Nothing in
     * it is privileged: it describes screens each reader is separately
     * allowed, or not allowed, to open.
     */
    public function test_an_editor_may_read_it(): void
    {
        $this->seed(RoleSeeder::class);

        $editor = User::factory()->create();
        $editor->assignRole('content_editor');

        $this->actingAs($editor);

        $this->assertTrue(Wiki::canAccess());
        $this->get('/admin/wiki')->assertOk()->assertSee('자주 하는 일', false);
    }

    /**
     * Only somebody with no part in running the site is kept out: a
     * guest, and a 일반회원 who reaches no panel screen at all.
     */
    public function test_others_are_refused(): void
    {
        $this->seed(RoleSeeder::class);

        $this->get('/admin/wiki')->assertRedirect();

        /** A 일반회원 is diverted to their profile before any page is reached. */
        $member = User::factory()->create();
        $member->assignRole('general_member');

        $this->actingAs($member);

        $this->assertFalse(Wiki::canAccess());
        $this->get('/admin/wiki')->assertRedirect();
    }
}
