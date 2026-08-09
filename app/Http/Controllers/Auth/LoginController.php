<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * Show the login form.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Verify the credentials and start a session.
     *
     * Accounts with an authenticator app registered are turned away
     * rather than signed in: this form has no second factor, and
     * letting a password alone through here would quietly undo the
     * 2단계 인증 the admin panel requires. They are pointed at the
     * panel's own login, which runs the full challenge.
     */
    public function store(Request $request): RedirectResponse
    {
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
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'two_factor' => '이 계정은 2단계 인증이 등록되어 있습니다. 인증 앱의 코드까지 확인하는 관리자 로그인 화면에서 로그인해 주세요.',
                ]);
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('giving'));
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
