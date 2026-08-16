<?php

namespace Tests\Feature;

use App\Models\MembershipRequest;
use App\Models\User;
use App\Notifications\MembershipApproved;
use App\Notifications\MembershipRequested;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Tests\TestCase;

/**
 * Covers what the church's letters carry below the last line.
 *
 * Laravel closes a notification with an English sign-off and, when the
 * mail has a button, an English paragraph repeating it as a pasteable
 * link. Neither belongs on a Korean letter, and the link was an admin
 * URL nobody outside the office can open.
 */
class NotificationLayoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The office notice carries a button and no English after it.
     */
    public function test_the_office_notice_ends_at_its_last_line(): void
    {
        $request = MembershipRequest::create([
            'name' => '김철수',
            'birth_date' => '1980-03-02',
            'phone' => '0411222333',
            'email' => 'kim@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ]);

        $body = (string) (new MembershipRequested($request))
            ->toMail(new AnonymousNotifiable)
            ->render();

        $this->assertStringContainsString('가입 신청 검토하기', $body);
        $this->assertStringNotContainsString('Regards', $body);
        $this->assertStringNotContainsString('having trouble clicking', $body);

        /**
         * The button still links to the request - that is its job. What
         * has gone is the address written out as a sentence for anyone
         * to read, under a heading in a language the letter is not in.
         */
        $this->assertSame(1, substr_count($body, '/admin/membership-requests/'));
    }

    /**
     * The approval letter ends the same way: no sign-off at all.
     */
    public function test_the_approval_letter_has_no_sign_off(): void
    {
        $body = (string) (new MembershipApproved(true))
            ->toMail(User::factory()->create())
            ->render();

        $this->assertStringContainsString('비밀번호가 기억나지 않으시면', $body);
        $this->assertStringNotContainsString('드림', $body);
        $this->assertStringNotContainsString('Regards', $body);
        $this->assertStringNotContainsString('having trouble clicking', $body);
    }

    /**
     * And nothing signs the page off underneath either. The header
     * already says who is writing.
     */
    public function test_no_letter_carries_a_footer(): void
    {
        $body = (string) (new MembershipApproved(true))
            ->toMail(User::factory()->create())
            ->render();

        $this->assertStringNotContainsString('All rights reserved', $body);
        $this->assertStringNotContainsString('©', $body);
    }
}
