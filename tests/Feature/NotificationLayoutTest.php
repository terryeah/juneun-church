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
 * Laravel signs every notification off with "Regards," over the app
 * name, which was the one piece of English in the middle of a Korean
 * letter. That goes. What Laravel puts under it - the subcopy spelling
 * out the button as a link, and the footer - stays, because a button
 * that a mail client mangles leaves that address as the only way
 * through.
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

        /**
         * The subcopy stays: a button that a mail client mangles leaves
         * the address written underneath as the only way through.
         */
        $this->assertStringContainsString('having trouble clicking', $body);
        $this->assertStringContainsString('/admin/membership-requests/', $body);
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
    }

    /**
     * The footer under every letter is Laravel's and stays there.
     */
    public function test_the_footer_stays(): void
    {
        $body = (string) (new MembershipApproved(true))
            ->toMail(User::factory()->create())
            ->render();

        $this->assertStringContainsString('All rights reserved', $body);
        $this->assertStringContainsString('브리즈번 주는교회', $body);
    }
}
