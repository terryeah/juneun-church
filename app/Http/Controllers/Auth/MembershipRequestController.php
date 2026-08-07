<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MembershipRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Public membership sign-up (가입 신청).
 *
 * A submission only queues a request for an administrator to review:
 * no account is created and nobody is signed in here.
 */
class MembershipRequestController extends Controller
{
    /**
     * Show the sign-up form, or the confirmation screen once a request
     * has just been submitted.
     */
    public function create(): View
    {
        return view('auth.signup', ['submitted' => (bool) session('signup_submitted')]);
    }

    /**
     * Record a sign-up request.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'birth_date' => ['required', 'date', 'before:today'],
                'phone' => ['required', 'string', 'max:10'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'password' => ['required', 'confirmed', Password::defaults()],
                'note' => ['nullable', 'string', 'max:1000'],
            ],
            [
                'required' => ':attribute 항목을 입력해주세요.',
                'string' => ':attribute 항목을 다시 확인해주세요.',
                'date' => '올바른 날짜를 입력해주세요.',
                'before' => '생년월일은 오늘보다 이전이어야 합니다.',
                'email' => '올바른 이메일 주소를 입력해주세요.',
                'max' => ':attribute 항목이 너무 깁니다.',
                'confirmed' => '비밀번호 확인이 일치하지 않습니다.',
                'password.min' => '비밀번호는 최소 :min자 이상이어야 합니다.',
            ],
            [
                'name' => '이름',
                'birth_date' => '생년월일',
                'phone' => '전화번호',
                'email' => '이메일',
                'password' => '비밀번호',
                'note' => '남기실 말씀',
            ],
        );

        /**
         * The password is hashed here rather than being left to the
         * model's cast, so that a dropped submission costs the same
         * work as an accepted one.
         *
         * Hashing is by far the slowest thing this method does. Doing
         * it only on the path that stores a request would make an
         * address that already belongs to the church answer in a few
         * milliseconds while an unknown address took a few hundred,
         * and the difference is plain in the response time from
         * anywhere on the internet. Whoever attends this church is
         * exactly what the silent drop exists to keep private, so the
         * cost is paid on both paths. The 'hashed' cast leaves an
         * already-hashed value alone, so nothing is hashed twice.
         */
        $data['password'] = Hash::make($data['password']);

        /**
         * A duplicate is dropped silently rather than rejected with a
         * validation error: telling a visitor that an address is
         * already taken would confirm who belongs to the church. A
         * previously rejected request may be submitted again.
         *
         * The drop is recorded in the server log so a submission that
         * never reaches the review queue can be explained afterwards.
         * The address is reduced to a short digest: the log is enough
         * to match a complaint against a drop, without turning the log
         * file into a list of who attends.
         */
        $reason = $this->duplicateReason($data['email']);

        if ($reason === null) {
            MembershipRequest::create($data);
        } else {
            Log::warning('Sign-up request dropped as a duplicate.', [
                'email_digest' => substr(hash('sha256', $data['email']), 0, 12),
                'reason' => $reason,
            ]);
        }

        return redirect()->route('signup')->with('signup_submitted', true);
    }

    /**
     * Why the address cannot raise a new request, or null when it can.
     */
    private function duplicateReason(string $email): ?string
    {
        if (User::query()->where('email', $email)->exists()) {
            return 'account exists';
        }

        if (MembershipRequest::query()->where('email', $email)->where('status', '대기')->exists()) {
            return 'request awaiting review';
        }

        return null;
    }
}
