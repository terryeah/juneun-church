<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Covers the public login form (로그인): the ordinary 성도 sign-in and
 * the diversion of accounts that carry a second factor.
 */
class LoginPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the settings the destination page renders. The whole class
     * is skipped until routes/web.php registers the login routes.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (! Route::has('login') || ! Route::has('login.store')) {
            $this->markTestSkipped('routes/web.php does not register the public login routes yet.');
        }

        $this->seed(SiteSettingSeeder::class);
    }

    /**
     * The form renders for a guest.
     */
    public function test_the_login_form_renders(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('로그인 상태 유지')
            ->assertSee('가입 신청하기');
    }

    /**
     * Correct credentials start a `web` guard session and land on 헌금.
     */
    public function test_valid_credentials_sign_the_member_in(): void
    {
        $user = User::factory()->create(['email' => 'kim@example.com', 'password' => 'correct-horse-battery']);

        $this->post(route('login.store'), [
            'email' => 'kim@example.com',
            'password' => 'correct-horse-battery',
        ])->assertRedirect(route('giving'));

        $this->assertAuthenticatedAs($user);
    }

    /**
     * A visitor sent to the login form by the auth middleware returns
     * to the page they asked for.
     */
    public function test_the_intended_url_wins_over_the_giving_page(): void
    {
        User::factory()->create(['email' => 'kim@example.com', 'password' => 'correct-horse-battery']);

        $this->withSession(['url.intended' => url('/bulletins')])
            ->post(route('login.store'), [
                'email' => 'kim@example.com',
                'password' => 'correct-horse-battery',
            ])
            ->assertRedirect(url('/bulletins'));
    }

    /**
     * A wrong password is refused without saying whether the address
     * is known to the church.
     */
    public function test_a_wrong_password_is_refused(): void
    {
        User::factory()->create(['email' => 'kim@example.com', 'password' => 'correct-horse-battery']);

        $this->from(route('login'))
            ->post(route('login.store'), ['email' => 'kim@example.com', 'password' => 'wrong'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /**
     * An account with an authenticator app registered is never signed
     * in here; it is sent to the panel login, which runs the second
     * factor, with the address kept for the next attempt.
     */
    public function test_a_two_factor_account_is_diverted_rather_than_signed_in(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com', 'password' => 'correct-horse-battery']);
        $user->saveAppAuthenticationSecret('JBSWY3DPEHPK3PXP');

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'admin@example.com',
                'password' => 'correct-horse-battery',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('two_factor')
            ->assertSessionHasInput('email', 'admin@example.com');

        $this->assertGuest();

        $this->followingRedirects()
            ->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'admin@example.com',
                'password' => 'correct-horse-battery',
            ])
            ->assertOk()
            ->assertSee('2단계 인증이 등록되어 있습니다', false)
            ->assertSee('/admin/login', false);
    }
}
