<?php

namespace App\Filament\Resources\MembershipRequests\Pages;

use App\Filament\Resources\MembershipRequests\MembershipRequestResource;
use App\Filament\Resources\MembershipRequests\Schemas\MembershipRequestInfolist;
use App\Models\Member;
use App\Models\MembershipRequest;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

class ViewMembershipRequest extends ViewRecord
{
    protected static string $resource = MembershipRequestResource::class;

    /**
     * 승인 creates the login and links it to a roster record; 거절
     * closes the request without creating anything. Both are gated by
     * MembershipRequestPolicy and only offered while the request is
     * still 대기.
     *
     * 승인 also demands a 확인 방법 on both paths - linking an existing
     * 성도 and registering a new one - because the candidate list only
     * shows that the applicant's own claims are self-consistent, never
     * that the applicant is the person named.
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
                ->modalDescription('승인하면 로그인이 만들어지고, 그 계정은 헌금 내역까지 볼 수 있습니다. 신청자가 본인이 맞는지 확인한 뒤 진행하세요.')
                ->schema([
                    Select::make('member_id')
                        ->label('교적부 연결')
                        ->options(fn (MembershipRequest $record): array => $record->candidates()
                            ->mapWithKeys(fn (array $candidate): array => [
                                $candidate['member']->getKey() => MembershipRequestInfolist::candidateLine($record, $candidate['member']),
                            ])
                            ->all())
                        ->placeholder('새 성도로 등록')
                        ->helperText('비워두면 신청 내용으로 새 성도를 등록합니다.'),
                    Select::make('verification_method')
                        ->label('확인 방법')
                        ->options(MembershipRequest::VERIFICATION_METHODS)
                        ->required()
                        ->live()
                        ->helperText('본인이 맞는지 어떻게 확인했는지 남겨 주세요. 새 성도로 등록할 때도 확인이 필요합니다.'),
                    Textarea::make('verification_note')
                        ->label('확인 메모')
                        ->rows(3)
                        ->placeholder('누가 언제 어떻게 확인했는지 적어 두면 나중에 확인할 수 있습니다.')
                        ->required(fn (Get $get): bool => $get('verification_method') === '기타'),
                ])
                ->action(function (MembershipRequest $record, array $data): void {
                    $record->approve(
                        filled($data['member_id'] ?? null) ? Member::query()->find($data['member_id']) : null,
                        auth()->user(),
                        $data['verification_method'],
                        $data['verification_note'] ?? null,
                    );

                    Notification::make()->success()->title('가입 신청을 승인했습니다.')->send();
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
