<?php

namespace App\Filament\Resources\MembershipRequests\Schemas;

use App\Filament\Resources\MembershipRequests\Tables\MembershipRequestsTable;
use App\Models\Member;
use App\Models\MembershipRequest;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

/**
 * Review view for a sign-up request: what was submitted, plus the
 * ranked roster records that may already be the same person.
 */
class MembershipRequestInfolist
{
    /**
     * A candidate rendered as one line for the reviewing administrator.
     */
    public static function candidateLine(Member $member, string $reason): string
    {
        $details = array_filter([
            $member->birth_date?->format('Y-m-d'),
            $member->phone,
            $member->position?->name,
        ]);

        return $member->name.' · '.$reason.($details === [] ? '' : ' · '.implode(' · ', $details));
    }

    /**
     * Configure the request detail view.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('이름'),
                TextEntry::make('birth_date')
                    ->label('생년월일')
                    ->date('Y-m-d'),
                TextEntry::make('phone')
                    ->label('전화번호')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('이메일'),
                TextEntry::make('status')
                    ->label('상태')
                    ->badge()
                    ->color(fn (string $state): string => MembershipRequestsTable::statusColour($state)),
                TextEntry::make('created_at')
                    ->label('신청일')
                    ->dateTime('Y-m-d H:i'),
                TextEntry::make('note')
                    ->label('남기실 말씀')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('candidate_members')
                    ->label('교적부 후보')
                    ->state(fn (MembershipRequest $record): array => $record->candidates()
                        ->map(fn (array $candidate): string => self::candidateLine($candidate['member'], $candidate['reason']))
                        ->all())
                    ->placeholder('일치하는 성도가 없습니다. 승인하면 새 성도로 등록됩니다.')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->columnSpanFull(),
                TextEntry::make('matchedMember.name')
                    ->label('연결된 성도')
                    ->placeholder('-'),
                TextEntry::make('reviewer.name')
                    ->label('처리자')
                    ->placeholder('-'),
                TextEntry::make('reviewed_at')
                    ->label('처리일')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('-'),
            ]);
    }
}
