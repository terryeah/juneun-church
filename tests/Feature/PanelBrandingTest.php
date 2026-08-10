<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers how the panel names itself: in the browser tab, and at the top
 * of the sidebar.
 */
class PanelBrandingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An account that can open the panel.
     */
    private function staff(): User
    {
        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->syncRoles(['super_admin']);

        return $user;
    }

    /**
     * The tab reads the church first, then the page, as the public site
     * does - not Filament's own '{page} - {church}'.
     */
    public function test_the_tab_names_the_church_first(): void
    {
        $church = (string) config('app.name');

        $this->actingAs($this->staff())
            ->get('/admin/members')
            ->assertOk()
            ->assertSee('<title>'.e($church.' · 성도').'</title>', escape: false);
    }

    /**
     * The sign-in screen is titled the same way, and it is the one page
     * a visitor sees before anything else.
     */
    public function test_the_sign_in_screen_is_titled_the_same_way(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('<title>'.e((string) config('app.name').' · 로그인').'</title>', escape: false);
    }

    /**
     * A page whose title is only the church's name is left alone rather
     * than doubled up.
     */
    public function test_the_church_name_is_not_repeated(): void
    {
        $church = (string) config('app.name');

        $this->actingAs($this->staff())
            ->get('/admin/members')
            ->assertDontSee('<title>'.e($church.' · '.$church).'</title>', escape: false);
    }

    /**
     * The church mark sits beside the name at the top of the sidebar.
     *
     * The mark inherits its colour rather than carrying one, which is
     * what makes it white on the dark theme and near-black on the light
     * one, so that is what is checked rather than a fixed fill.
     */
    public function test_the_sidebar_carries_the_mark_beside_the_name(): void
    {
        $response = $this->actingAs($this->staff())->get('/admin');

        $response->assertOk()->assertSee((string) config('app.name'));

        $logo = str((string) $response->getContent())->after('fi-logo')->before('</div>');

        $this->assertStringContainsString('<svg', (string) $logo, '사이드바에 로고가 없습니다.');
        $this->assertStringContainsString('currentColor', (string) $logo);
    }
}
