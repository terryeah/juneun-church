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

        $this->get(route('site-introduction'))
            ->assertOk()
            ->assertSee('Brisbane Juneun Church', false)
            ->assertSee('교회의 결정이 필요한 것', false);
    }

    /**
     * Everyone else is refused, including a signed-in 성도 and a guest.
     */
    public function test_others_are_refused(): void
    {
        $this->seed(RoleSeeder::class);

        $this->get(route('site-introduction'))->assertRedirect();

        $member = User::factory()->create();
        $member->assignRole('member');

        $this->actingAs($member);

        $this->get(route('site-introduction'))->assertForbidden();
        $this->assertFalse(SiteIntroduction::canAccess());
    }
}
