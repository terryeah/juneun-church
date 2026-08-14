<?php

namespace App\Filament\Resources\MembershipRequests\Schemas;

use App\Filament\Resources\MembershipRequests\Tables\MembershipRequestsTable;
use App\Filament\Support\Author;
use App\Models\Member;
use App\Models\MembershipRequest;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

/**
 * Review view for a sign-up request: what was submitted, plus the
 * ranked roster records that may already be the same person and a
 * field-by-field comparison showing how little that ranking proves.
 */
class MembershipRequestInfolist
{
    /**
     * A candidate rendered as one line for the reviewing administrator,
     * ending in what the roster record actually corroborates.
     */
    public static function candidateLine(MembershipRequest $request, Member $member): string
    {
        $details = array_filter([
            $member->birth_date?->format('Y-m-d'),
            $member->phone,
            $member->position?->name,
            $request->corroboration($member),
        ]);

        return $member->name.' · '.implode(' · ', $details);
    }

    /**
     * Every candidate's comparison flattened into one table, strongest
     * candidate first. The roster record is named on its first row only,
     * so each candidate reads as a block.
     *
     * @return list<array{member: ?string, field: string, submitted: ?string, held: ?string, verdict: string}>
     */
    public static function comparisonRows(MembershipRequest $request): array
    {
        return $request->candidates()
            ->flatMap(fn (array $candidate): array => collect($request->comparison($candidate['member']))
                ->map(fn (array $row, int $index): array => [
                    'member' => $index === 0 ? $candidate['member']->name : null,
                    ...$row,
                ])
                ->all())
            ->all();
    }

    /**
     * Badge colour for a comparison verdict: a 불일치 has to read as a
     * warning, and a 자기 신고 must not read as a confirmation.
     */
    public static function verdictColour(string $verdict): string
    {
        return match ($verdict) {
            MembershipRequest::VERDICT_MATCH => 'success',
            MembershipRequest::VERDICT_CONFLICT => 'danger',
            default => 'gray',
        };
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
                    ->dateTime(),
                TextEntry::make('note')
                    ->label('남기실 말씀')
                    ->placeholder('-')
                    ->columnSpanFull(),
                RepeatableEntry::make('candidate_comparison')
                    ->label('교적부 대조')
                    ->helperText('이름과 생년월일은 신청자가 직접 적은 내용입니다. 성도 이름은 홈페이지에 공개되어 있어서, 이름과 생년월일이 "일치"해도 본인이라는 증거가 되지 않습니다. 교회가 따로 기록해 둔 전화번호·이메일이 "일치"할 때만 확인에 도움이 되고, "불일치"가 있으면 승인하기 전에 반드시 확인하세요.')
                    ->state(fn (MembershipRequest $record): array => self::comparisonRows($record))
                    ->table([
                        TableColumn::make('성도'),
                        TableColumn::make('항목'),
                        TableColumn::make('신청서 내용'),
                        TableColumn::make('교적부 기록'),
                        TableColumn::make('확인'),
                    ])
                    /**
                     * Labels are set rather than hidden: below the
                     * repeatable's own breakpoint Filament drops the
                     * header row and stacks each row as a block,
                     * captioning every cell with its entry label - so
                     * hiddenLabel() leaves a phone showing five bare
                     * values, three of them names.
                     */
                    ->schema([
                        TextEntry::make('member')
                            ->label('성도')
                            ->weight(FontWeight::Medium)
                            ->placeholder(''),
                        TextEntry::make('field')
                            ->label('항목'),
                        TextEntry::make('submitted')
                            ->label('신청서 내용')
                            ->placeholder('-'),
                        TextEntry::make('held')
                            ->label('교적부 기록')
                            ->placeholder('교회 기록 없음'),
                        TextEntry::make('verdict')
                            ->label('확인')
                            ->badge()
                            ->color(fn (string $state): string => self::verdictColour($state)),
                    ])
                    ->placeholder('일치하는 성도가 없습니다. 승인하면 새 성도로 등록됩니다.')
                    ->columnSpanFull(),
                TextEntry::make('matchedMember.name')
                    ->label('연결된 성도')
                    ->placeholder('-'),
                TextEntry::make('reviewer.name')
                    ->label('처리자')
                    ->state(fn (MembershipRequest $record): ?string => $record->reviewer === null
                        ? null
                        : Author::name($record->reviewer))
                    ->color(Author::colour(...))
                    ->placeholder('-'),
                TextEntry::make('reviewed_at')
                    ->label('처리일')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('verification_method')
                    ->label('확인 방법')
                    ->placeholder('-'),
                TextEntry::make('verification_note')
                    ->label('확인 메모')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ]);
    }
}
