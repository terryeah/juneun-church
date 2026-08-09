<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * The account item at the end of the main menu.
 *
 * Now that 주보 and parts of 소식 are 성도 전용, the public site needs a
 * way in - and, for someone reading on a shared phone, a way out that
 * is not the admin panel.
 */
class HeaderAccountLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);
    }

    /**
     * A guest is offered 로그인, not 로그아웃.
     */
    public function test_a_guest_is_offered_the_login_link(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('로그인')
            ->assertDontSee('로그아웃');
    }

    /**
     * A signed-in 성도 is offered 로그아웃 instead, so the menu never
     * invites someone already signed in to sign in again.
     */
    public function test_a_signed_in_member_is_offered_logout(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertSee('로그아웃')
            ->assertSee(route('logout'));
    }

    /**
     * Signing out ends the session and returns to the home page.
     */
    public function test_signing_out_ends_the_session(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')
            ->assertRedirect(route('home'));

        $this->assertFalse(Auth::check());
    }

    /**
     * A guest cannot post to it.
     */
    public function test_a_guest_cannot_sign_out(): void
    {
        $this->post('/logout')->assertRedirect(route('login'));
    }
}
