<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SiteSettingSeeder;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Covers the second factor on the public login (로그인): an account with
 * an authenticator app registered answers a code before it is signed in,
 * and the record left waiting between the two steps is worth nothing on
 * its own.
 */
class PublicTwoFactorLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The session key holding the account waiting on its second factor.
     */
    protected const PENDING_KEY = 'login.pending_two_factor';

    /**
     * Seed the settings the destination pages render.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);
    }

    /**
     * The authenticator provider the panel is configured with.
     */
    protected function provider(): AppAuthentication
    {
        return Filament::getMultiFactorAuthenticationProviders()['app'];
    }

    /**
     * An account carrying a secret, with recovery codes when asked for.
     *
     * @param  array<string>|null  $recoveryCodes  filled by reference with the plain codes
     */
    protected function twoFactorUser(?array &$recoveryCodes = null): User
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'correct-horse-battery',
        ]);

        $user->saveAppAuthenticationSecret($this->provider()->generateSecret());

        $recoveryCodes = $this->provider()->generateRecoveryCodes();
        $this->provider()->saveRecoveryCodes($user, $recoveryCodes);

        return $user->refresh();
    }

    /**
     * Pass the password step and land on the code step.
     */
    protected function passPasswordStep(bool $remember = false): void
    {
        $this->post(route('login.store'), array_filter([
            'email' => 'admin@example.com',
            'password' => 'correct-horse-battery',
            'remember' => $remember ? '1' : null,
        ]))->assertRedirect(route('login.challenge'));
    }

    /**
     * An account without a second factor signs in on the password alone,
     * exactly as it did before the code step existed.
     */
    public function test_an_account_without_two_factor_signs_in_on_the_password_alone(): void
    {
        $user = User::factory()->create(['email' => 'kim@example.com', 'password' => 'correct-horse-battery']);

        $this->post(route('login.store'), [
            'email' => 'kim@example.com',
            'password' => 'correct-horse-battery',
        ])->assertRedirect(route('giving'));

        $this->assertAuthenticatedAs($user);
        $this->assertNull(session(self::PENDING_KEY));
    }

    /**
     * The password step never signs a two-factor account in; it only
     * parks it and asks for the code.
     */
    public function test_the_password_step_does_not_sign_a_two_factor_account_in(): void
    {
        $user = $this->twoFactorUser();

        $this->passPasswordStep();

        $this->assertGuest();
        $this->assertSame($user->getKey(), session(self::PENDING_KEY.'.id'));

        $this->get(route('login.challenge'))
            ->assertOk()
            ->assertSee('인증 코드 여섯 자리');
    }

    /**
     * The current code from the authenticator app completes the sign-in.
     */
    public function test_the_current_code_completes_the_sign_in(): void
    {
        $user = $this->twoFactorUser();

        $this->passPasswordStep();

        $this->post(route('login.challenge.store'), [
            'code' => $this->provider()->getCurrentCode($user),
        ])->assertRedirect(route('giving'));

        $this->assertAuthenticatedAs($user);
        $this->assertNull(session(self::PENDING_KEY));
    }

    /**
     * A wrong code leaves the visitor a guest, and the pending record
     * survives so the code can be retyped.
     */
    public function test_a_wrong_code_is_refused_and_the_pending_record_survives(): void
    {
        $user = $this->twoFactorUser();

        $this->passPasswordStep();

        $this->from(route('login.challenge'))
            ->post(route('login.challenge.store'), ['code' => '000000'])
            ->assertRedirect(route('login.challenge'))
            ->assertSessionHasErrors('code');

        $this->assertGuest();
        $this->assertSame($user->getKey(), session(self::PENDING_KEY.'.id'));
    }

    /**
     * A recovery code stands in for the app, and is spent doing so.
     */
    public function test_a_recovery_code_signs_in_once_only(): void
    {
        $user = $this->twoFactorUser($recoveryCodes);

        $this->passPasswordStep();

        $this->post(route('login.challenge.store'), [
            'recovery_code' => $recoveryCodes[0],
        ])->assertRedirect(route('giving'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('home'));

        $this->passPasswordStep();

        $this->from(route('login.challenge'))
            ->post(route('login.challenge.store'), ['recovery_code' => $recoveryCodes[0]])
            ->assertRedirect(route('login.challenge'))
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    /**
     * The code step is closed to anyone who has not passed a password.
     */
    public function test_the_code_step_without_a_pending_record_returns_to_the_password_step(): void
    {
        $this->twoFactorUser();

        $this->get(route('login.challenge'))->assertRedirect(route('login'));

        $this->post(route('login.challenge.store'), ['code' => '000000'])
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /**
     * The pending record is not a session: 성도 전용 content stays shut
     * while it is sitting there.
     */
    public function test_the_pending_record_alone_authenticates_nothing(): void
    {
        $this->twoFactorUser();

        $this->passPasswordStep();

        $this->assertGuest();

        $this->get(route('giving'))
            ->assertOk()
            ->assertDontSee('section-giving-records')
            ->assertSee('section-members-only');
    }

    /**
     * Five minutes after the password, the pending record is worthless.
     */
    public function test_an_expired_pending_record_returns_to_the_password_step(): void
    {
        $user = $this->twoFactorUser();

        $this->passPasswordStep();

        $this->travel(6)->minutes();

        $this->post(route('login.challenge.store'), [
            'code' => $this->provider()->getCurrentCode($user),
        ])->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertNull(session(self::PENDING_KEY));
    }

    /**
     * 로그인 상태 유지, ticked at the password step, still applies once
     * the code has been answered.
     */
    public function test_the_remember_choice_survives_the_code_step(): void
    {
        $user = $this->twoFactorUser();

        $this->passPasswordStep(remember: true);

        $this->post(route('login.challenge.store'), [
            'code' => $this->provider()->getCurrentCode($user),
        ])
            ->assertRedirect(route('giving'))
            ->assertCookie(Auth::guard()->getRecallerName());

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->refresh()->remember_token);
    }

    /**
     * Guessing the six digits is capped at five tries a minute.
     */
    public function test_the_code_step_is_throttled(): void
    {
        $this->twoFactorUser();

        $this->passPasswordStep();

        foreach (range(1, 5) as $ignored) {
            $this->from(route('login.challenge'))
                ->post(route('login.challenge.store'), ['code' => '000000'])
                ->assertSessionHasErrors(['code' => '인증 코드가 올바르지 않습니다. 다시 확인해주세요.']);
        }

        $this->followingRedirects()
            ->from(route('login.challenge'))
            ->post(route('login.challenge.store'), ['code' => '000000'])
            ->assertOk()
            ->assertSee('인증 시도가 너무 많습니다.');

        $this->assertGuest();
    }

    /**
     * Guessing is capped per account as well as per address.
     *
     * The per-address limit alone caps nobody who can change address,
     * and six digits is only a million guesses, so a stolen password
     * would otherwise buy an unbounded run at the second factor.
     */
    public function test_the_code_step_is_capped_for_the_account_across_addresses(): void
    {
        $this->twoFactorUser();

        /** Twenty guesses, each from a different address. */
        foreach (range(1, 20) as $attempt) {
            $this->passPasswordStep();

            $this->from(route('login.challenge'))
                ->withServerVariables(['REMOTE_ADDR' => '203.0.113.'.$attempt])
                ->post(route('login.challenge.store'), ['code' => '000000'])
                ->assertSessionHasErrors('code');
        }

        $this->passPasswordStep();

        $this->followingRedirects()
            ->from(route('login.challenge'))
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.254'])
            ->post(route('login.challenge.store'), ['code' => '000000'])
            ->assertOk()
            ->assertSee('인증 시도가 너무 많습니다.');

        $this->assertGuest();
    }
}
