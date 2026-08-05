<?php

namespace App\Filament\Resources\MembershipRequests\Pages;

use App\Filament\Resources\MembershipRequests\MembershipRequestResource;
use App\Filament\Resources\MembershipRequests\Schemas\MembershipRequestInfolist;
use App\Models\Member;
use App\Models\MembershipRequest;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
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
                ->schema([
                    Select::make('member_id')
                        ->label('교적부 연결')
                        ->options(fn (MembershipRequest $record): array => $record->candidates()
                            ->mapWithKeys(fn (array $candidate): array => [
                                $candidate['member']->getKey() => MembershipRequestInfolist::candidateLine($candidate['member'], $candidate['reason']),
                            ])
                            ->all())
                        ->placeholder('새 성도로 등록')
                        ->helperText('비워두면 신청 내용으로 새 성도를 등록합니다.'),
                ])
                ->action(function (MembershipRequest $record, array $data): void {
                    $record->approve(
                        filled($data['member_id'] ?? null) ? Member::query()->find($data['member_id']) : null,
                        auth()->user(),
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
