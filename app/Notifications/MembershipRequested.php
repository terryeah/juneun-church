<?php

namespace App\Notifications;

use App\Filament\Resources\MembershipRequests\MembershipRequestResource;
use App\Models\MembershipRequest;
use App\Providers\AppServiceProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells the office a 가입 신청 is waiting.
 *
 * Until this, nothing did. A request landed in the table and the only
 * sign of it was a badge in the sidebar, which somebody had to be
 * already signed in to see - so an applicant could wait days on a
 * request nobody knew about.
 *
 * It carries the applicant's name and nothing else about them. The
 * office opens the panel to see a birth date or a phone number, where
 * the 가입 신청 permission is checked and the reading is logged; an
 * address that forwards to a personal inbox is the wrong place to copy
 * somebody's details to, and a mail nobody can withdraw is the wrong
 * container for them.
 */
class MembershipRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private MembershipRequest $request)
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
     * Build the notice.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('새 가입 신청: '.$this->request->name.' 님')
            ->replyTo(config('mail.reply_to.address'), config('mail.reply_to.name'))
            ->greeting('가입 신청이 들어왔습니다.')
            ->line('**'.$this->request->name.'** 님이 '.$this->request->created_at->format(AppServiceProvider::DATE_TIME_FORMAT).' 에 신청하셨습니다.')
            ->line('교적부와 대조한 뒤 승인해 주세요. 승인하면 신청자에게 안내 메일이 나갑니다.')
            ->action('가입 신청 검토하기', MembershipRequestResource::getUrl('view', ['record' => $this->request]))
            ->line('신청자가 적은 생년월일과 연락처는 관리자 화면에서 확인하실 수 있습니다.');
    }
}
