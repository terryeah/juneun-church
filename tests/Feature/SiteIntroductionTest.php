<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteIntroduction;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The leadership walkthrough belongs to administrators.
 */
class SiteIntroductionTest extends TestCase
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

        $this->assertTrue(SiteIntroduction::canAccess());

        $this->get(SiteIntroduction::getUrl())
            ->assertOk()
            ->assertSee('Brisbane Juneun Church', false)
            ->assertSee('교회의 결정이 필요한 것', false);

        $this->assertStringEndsWith('/admin/intro', SiteIntroduction::getUrl());
    }

    /**
     * Everyone else is kept out: a guest, a 성도, a content editor.
     */
    public function test_others_are_refused(): void
    {
        $this->seed(RoleSeeder::class);

        $this->get('/admin/intro')->assertRedirect();

        /** A 성도 is diverted to their profile before any page is reached. */
        $member = User::factory()->create();
        $member->assignRole('member');

        $this->actingAs($member);

        $this->assertFalse(SiteIntroduction::canAccess());
        $this->get('/admin/intro')->assertRedirect();

        /** A content editor stays in the panel and is refused outright. */
        $editor = User::factory()->create();
        $editor->assignRole('content_editor');

        $this->actingAs($editor);

        $this->assertFalse(SiteIntroduction::canAccess());
        $this->get('/admin/intro')->assertForbidden();
    }
}
