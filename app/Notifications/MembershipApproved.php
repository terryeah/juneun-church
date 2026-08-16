<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells an applicant their 가입 신청 has been approved.
 *
 * Sent after the approval transaction has committed, so a mail server
 * having a bad afternoon cannot undo an approval an administrator has
 * already made. The 'deferred' connection runs the send in this same
 * process once the response is on its way: the server runs no queue
 * worker, so a job put on the database queue would sit there for ever,
 * and sending inline would hold the panel open for as long as the mail
 * host takes.
 *
 * The applicant chose their own password when they applied, so this is
 * not a "set your password" mail and must not read like one. Saying so
 * plainly is the difference between somebody signing in and somebody
 * writing to the office asking where their password is.
 *
 * Reply-To is not set here. MailManager reads config('mail.reply_to')
 * and hands it to every message the application sends, so naming it
 * again put the address in the header twice.
 */
class MembershipApproved extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  bool  $onRoster  Whether the approval put the applicant on the 교적, which is what opens 성도 전용 content - an account alone does not.
     */
    public function __construct(private bool $onRoster)
    {
        $this->onConnection('deferred');
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the approval mail.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('가입 신청이 승인되었습니다 · 브리즈번 주는교회')
            ->greeting($notifiable->name.' 님, 안녕하세요.')
            ->line('홈페이지 가입 신청이 승인되었습니다. 이제 로그인하실 수 있습니다.')
            /**
             * The one line this mail exists to carry. Everything else is
             * courtesy; without this the reader looks for a password
             * that was never sent.
             */
            ->line('비밀번호는 **가입 신청하실 때 직접 정하신 비밀번호**입니다. 따로 발급해 드리지 않습니다.')
            ->action('로그인하기', route('login'));

        /**
         * An approval does not always put somebody on the 교적 - the
         * office can open an account for a visitor and leave them off
         * it - and it is the 교적 record, not the account, that opens
         * 성도 전용 content. Promising it to everyone would be a
         * promise the site then breaks.
         */
        $message->line($this->onRoster
            ? '성도 전용 자료와 헌금 내역도 함께 보실 수 있습니다.'
            : '성도 전용 자료는 교적에 등록된 뒤에 보실 수 있습니다. 교회 사무실로 문의해 주세요.');

        return $message
            ->line('비밀번호가 기억나지 않으시면 로그인 화면에서 재설정하실 수 있습니다.');
    }
}
