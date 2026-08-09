<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Public login (로그인) for 성도 accounts.
 *
 * Sign-in runs against the default `web` guard, which is the guard the
 * Filament panel uses too, so one session is recognised by the public
 * site and 관리자 페이지 alike.
 */
class LoginController extends Controller
{
    /**
     * The session key holding an account that has passed its password
     * check and is waiting on its second factor.
     *
     * The record is not a session: it carries no guard state, so the
     * `auth` middleware and every @auth block treat its holder as a
     * guest until the challenge is answered.
     */
    protected const PENDING_KEY = 'login.pending_two_factor';

    /**
     * How long a pending record stays usable, in seconds.
     */
    protected const PENDING_LIFETIME = 300;

    /**
     * Attempts allowed at the code step per minute, per account and
     * client address.
     */
    protected const CHALLENGE_ATTEMPTS = 5;

    /**
     * Attempts allowed at the code step per account per hour, whatever
     * address they come from.
     *
     * The per-address limit alone caps nobody who can change address,
     * and six digits is only a million guesses; this is what actually
     * bounds a stolen password.
     */
    protected const CHALLENGE_ATTEMPTS_PER_ACCOUNT = 20;

    /**
     * Show the login form.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Verify the credentials and start a session.
     *
     * An account with an authenticator app registered is not signed in
     * here: it is parked in a pending record and sent to the code step,
     * so a password alone never undoes the 2단계 인증 the admin panel
     * requires. Everything else signs in as it always has.
     */
    public function store(Request $request): RedirectResponse
    {
        /** A fresh password attempt supersedes any half-finished one. */
        $request->session()->forget(self::PENDING_KEY);

        $credentials = $request->validate(
            [
                'email' => ['required', 'string', 'email', 'max:255'],
                'password' => ['required', 'string'],
            ],
            [
                'required' => ':attribute 항목을 입력해주세요.',
                'email' => '올바른 이메일 주소를 입력해주세요.',
                'max' => ':attribute 항목이 너무 깁니다.',
            ],
            [
                'email' => '이메일',
                'password' => '비밀번호',
            ],
        );

        /**
         * Credentials are checked before the two-factor branch, so the
         * page never tells a stranger which addresses hold an account.
         */
        if (! Auth::validate($credentials)) {
            throw ValidationException::withMessages([
                'email' => '이메일 또는 비밀번호가 올바르지 않습니다.',
            ]);
        }

        /** @var User $user */
        $user = Auth::getLastAttempted();

        if (filled($user->getAppAuthenticationSecret())) {
            $request->session()->put(self::PENDING_KEY, [
                'id' => $user->getKey(),
                'remember' => $request->boolean('remember'),
                'at' => now()->timestamp,
            ]);

            return redirect()->route('login.challenge');
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('giving'));
    }

    /**
     * Ask the pending account for its authenticator code.
     */
    public function challenge(Request $request): View|RedirectResponse
    {
        return $this->pendingUser($request) === null
            ? $this->restart($request)
            : view('auth.two-factor-challenge');
    }

    /**
     * Check the second factor and, if it holds, start the session.
     *
     * Either an authenticator code or one of the recovery codes kept
     * for a lost phone is accepted. The code is verified with reuse
     * prevention on, and a recovery code is consumed by the provider,
     * so neither works twice.
     */
    public function challengeStore(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if ($user === null) {
            return $this->restart($request);
        }

        $data = $request->validate(
            [
                'code' => ['nullable', 'string', 'max:6'],
                'recovery_code' => ['nullable', 'string', 'max:255'],
            ],
            ['max' => ':attribute 항목이 너무 깁니다.'],
            ['code' => '인증 코드', 'recovery_code' => '복구 코드'],
        );

        $code = $data['code'] ?? null;
        $recoveryCode = $data['recovery_code'] ?? null;

        if (blank($code) && blank($recoveryCode)) {
            throw ValidationException::withMessages([
                'code' => '인증 앱의 코드 또는 복구 코드를 입력해주세요.',
            ]);
        }

        /**
         * Guessing is capped per account and client address, so a stolen
         * password buys only a handful of tries at the six digits.
         */
        $throttleKey = 'login.challenge:'.$user->getKey().'|'.$request->ip();
        $accountKey = 'login.challenge.account:'.$user->getKey();

        foreach ([[$throttleKey, self::CHALLENGE_ATTEMPTS], [$accountKey, self::CHALLENGE_ATTEMPTS_PER_ACCOUNT]] as [$key, $limit]) {
            if (RateLimiter::tooManyAttempts($key, $limit)) {
                throw ValidationException::withMessages([
                    'code' => '인증 시도가 너무 많습니다. '.RateLimiter::availableIn($key).'초 후에 다시 시도해주세요.',
                ]);
            }
        }

        RateLimiter::hit($throttleKey);
        RateLimiter::hit($accountKey, 3600);

        /** @var AppAuthentication $provider */
        $provider = Filament::getMultiFactorAuthenticationProviders()['app'];

        $isVerified = (filled($code) && $provider->verifyCode($code, $user->getAppAuthenticationSecret(), shouldPreventCodeReuse: true))
            || (filled($recoveryCode) && filled($user->getAppAuthenticationRecoveryCodes()) && $provider->verifyRecoveryCode($recoveryCode, $user));

        if (! $isVerified) {
            /** The pending record is left in place so the code can be retyped. */
            throw ValidationException::withMessages([
                'code' => '인증 코드가 올바르지 않습니다. 다시 확인해주세요.',
            ]);
        }

        $remember = (bool) $request->session()->get(self::PENDING_KEY.'.remember', false);

        RateLimiter::clear($throttleKey);
        RateLimiter::clear($accountKey);

        $request->session()->forget(self::PENDING_KEY);

        Auth::login($user, $remember);

        $request->session()->regenerate();

        return redirect()->intended(route('giving'));
    }

    /**
     * The account waiting on its second factor, or null when there is
     * no usable pending record.
     *
     * Only the stored id is taken at face value: the account is read
     * back from the database and must still carry a secret, and a
     * record older than five minutes is dropped.
     */
    protected function pendingUser(Request $request): ?User
    {
        $pending = $request->session()->get(self::PENDING_KEY);

        if (! is_array($pending)) {
            return null;
        }

        $user = User::find($pending['id'] ?? null);

        if (
            ! $user instanceof User
            || blank($user->getAppAuthenticationSecret())
            || now()->timestamp - (int) ($pending['at'] ?? 0) > self::PENDING_LIFETIME
        ) {
            $request->session()->forget(self::PENDING_KEY);

            return null;
        }

        return $user;
    }

    /**
     * Send someone whose pending record has expired, or who never had
     * one, back to the password step.
     */
    protected function restart(Request $request): RedirectResponse
    {
        $request->session()->forget(self::PENDING_KEY);

        return redirect()->route('login')->withErrors([
            'email' => '인증 시간이 지났거나 확인할 로그인 요청이 없습니다. 처음부터 다시 로그인해 주세요.',
        ]);
    }

    /**
     * End the session and return to the home page.
     *
     * The public site holds 성도 전용 pages now, so someone reading the
     * 주보 on a shared phone needs a way out that is not the admin panel.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
