<?php

namespace App\Filament\Resources\MembershipRequests\Pages;

use App\Filament\Resources\MembershipRequests\MembershipRequestResource;
use App\Filament\Resources\MembershipRequests\Schemas\MembershipRequestInfolist;
use App\Models\Member;
use App\Models\MembershipRequest;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

class ViewMembershipRequest extends ViewRecord
{
    protected static string $resource = MembershipRequestResource::class;

    /** Link the applicant to a 성도 already on the 교적. */
    private const OUTCOME_LINK = 'link';

    /** Register the applicant on the 교적 as a new 성도. */
    private const OUTCOME_REGISTER = 'register';

    /** Give the applicant a login and leave them off the 교적. */
    private const OUTCOME_ACCOUNT_ONLY = 'account_only';

    /**
     * 승인 creates the login and decides whether the applicant goes on
     * the 교적; 거절 closes the request without creating anything. Both
     * are gated by MembershipRequestPolicy and only offered while the
     * request is still 대기.
     *
     * The 교적 question is asked outright rather than inferred from a
     * blank field, because it is the question that matters: anyone may
     * send a 가입 신청, and it is the 교적 record - not the approval -
     * that opens 성도 전용 content and 헌금 내역.
     *
     * 승인 also demands a 확인 방법 on every path, because the candidate
     * list only shows that the applicant's own claims are
     * self-consistent, never that the applicant is the person named.
     *
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('승인')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->authorize('approve')
                ->visible(fn (MembershipRequest $record): bool => $record->status === '대기')
                ->modalDescription('신청자가 본인이 맞는지 확인한 뒤 진행하세요. 교적에 올리면 그 계정은 헌금 내역과 성도 전용 자료까지 볼 수 있습니다.')
                ->schema([
                    /**
                     * Connecting to an existing 성도 is only offered
                     * when there is one to connect to, and is the
                     * default when there is, because that is the
                     * ordinary case: somebody the church already knows.
                     */
                    Radio::make('outcome')
                        ->label('교적 처리')
                        ->options(fn (MembershipRequest $record): array => array_filter([
                            self::OUTCOME_LINK => $record->candidates()->isNotEmpty() ? '교적에 있는 성도와 연결' : null,
                            self::OUTCOME_REGISTER => '새 성도로 교적에 등록',
                            self::OUTCOME_ACCOUNT_ONLY => '교적에 올리지 않고 계정만 (일반회원)',
                        ]))
                        ->descriptions([
                            self::OUTCOME_ACCOUNT_ONLY => '우리 교회 성도가 아닌 분입니다. 로그인은 되지만 성도 전용 자료와 헌금 내역은 보이지 않습니다.',
                        ])
                        ->default(fn (MembershipRequest $record): string => $record->candidates()->isNotEmpty()
                            ? self::OUTCOME_LINK
                            : self::OUTCOME_REGISTER)
                        ->required()
                        ->live(),
                    Select::make('member_id')
                        ->label('연결할 성도')
                        ->options(fn (MembershipRequest $record): array => $record->candidates()
                            ->mapWithKeys(fn (array $candidate): array => [
                                $candidate['member']->getKey() => MembershipRequestInfolist::candidateLine($record, $candidate['member']),
                            ])
                            ->all())
                        ->visible(fn (Get $get): bool => $get('outcome') === self::OUTCOME_LINK)
                        ->required(fn (Get $get): bool => $get('outcome') === self::OUTCOME_LINK)
                        ->helperText('신청 내용과 겹치는 교적 기록만 나옵니다. 없으면 위에서 새로 등록하세요.'),
                    Select::make('verification_method')
                        ->label('확인 방법')
                        ->options(MembershipRequest::VERIFICATION_METHODS)
                        ->required()
                        ->live()
                        ->helperText('본인이 맞는지 어떻게 확인했는지 남겨주세요. 새 성도로 등록할 때도 확인이 필요합니다.'),
                    Textarea::make('verification_note')
                        ->label('확인 메모')
                        ->rows(3)
                        ->placeholder('누가 언제 어떻게 확인했는지 적어두면 나중에 확인할 수 있습니다.')
                        ->required(fn (Get $get): bool => $get('verification_method') === '기타'),
                    /**
                     * On by default, because somebody who applied and
                     * heard nothing has no way to know it worked. Off
                     * for the case the office has already rung them, or
                     * where the address on the request is one they have
                     * reason not to write to.
                     */
                    Checkbox::make('notify')
                        ->label('승인 안내 메일 보내기')
                        ->helperText(fn (MembershipRequest $record): string => $record->email.' 로 발송됩니다. 가입할 때 정한 비밀번호로 로그인하라는 안내가 나갑니다.')
                        ->default(true),
                ])
                ->action(function (MembershipRequest $record, array $data): void {
                    $outcome = $data['outcome'] ?? self::OUTCOME_REGISTER;

                    $notify = (bool) ($data['notify'] ?? true);

                    $record->approve(
                        $outcome === self::OUTCOME_LINK ? Member::query()->find($data['member_id']) : null,
                        auth()->user(),
                        $data['verification_method'],
                        $data['verification_note'] ?? null,
                        registerOnRoster: $outcome !== self::OUTCOME_ACCOUNT_ONLY,
                        notify: $notify,
                    );

                    Notification::make()
                        ->success()
                        ->title('가입 신청을 승인했습니다.')
                        ->body(collect([
                            $outcome === self::OUTCOME_ACCOUNT_ONLY
                                ? '교적에 올리지 않았으므로 성도 전용 자료는 보이지 않습니다.'
                                : null,
                            $notify
                                ? $record->email.' 로 안내 메일을 보냈습니다.'
                                : '안내 메일은 보내지 않았습니다.',
                        ])->filter()->implode(' '))
                        ->send();
                }),
            Action::make('reject')
                ->label('거절')
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger')
                ->authorize('reject')
                ->visible(fn (MembershipRequest $record): bool => $record->status === '대기')
                ->requiresConfirmation()
                ->modalHeading('가입 신청 거절')
                ->modalDescription('계정은 만들어지지 않습니다. 계속하시겠습니까?')
                ->action(function (MembershipRequest $record): void {
                    $record->reject(auth()->user());

                    Notification::make()->success()->title('가입 신청을 거절했습니다.')->send();
                }),
        ];
    }
}
