<?php

namespace App\Filament\Resources\MembershipRequests\Tables;

use App\Models\MembershipRequest;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Sign-up request listing: still waiting first, then newest.
 *
 * The office works this list by deciding which requests are still
 * waiting and ringing the people behind them, so a phone gets the name,
 * the phone number, the status and the day it came in - two tidy pairs
 * in the stacked layout. Everything used to verify a request against
 * the roster is opened through the view action anyway, so 생년월일,
 * 이메일 and 확인 방법 wait for a laptop.
 */
class MembershipRequestsTable
{
    /**
     * Badge colour for a request status.
     */
    public static function statusColour(string $status): string
    {
        return match ($status) {
            '승인' => 'success',
            '거절' => 'danger',
            default => 'warning',
        };
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('이름')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold),
                TextColumn::make('birth_date')
                    ->label('생년월일')
                    ->placeholder('-')
                    ->date('Y-m-d')
                    ->sortable()
                    ->visibleFrom('lg'),
                TextColumn::make('phone')
                    ->label('전화번호')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('이메일')
                    ->placeholder('-')
                    ->searchable()
                    ->visibleFrom('lg'),
                TextColumn::make('status')
                    ->label('상태')
                    ->badge()
                    ->color(fn (string $state): string => self::statusColour($state)),
                TextColumn::make('verification_method')
                    ->label('확인 방법')
                    ->placeholder('-')
                    ->tooltip(fn (MembershipRequest $record): ?string => $record->verification_note)
                    ->visibleFrom('lg'),
                TextColumn::make('created_at')
                    ->label('신청일')
                    ->placeholder('-')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            /**
             * Waiting requests first, then newest. A settled request is
             * a record; a waiting one is a job, and on a list sorted
             * purely by date the job sinks under the record the moment
             * a newer request is approved.
             *
             * A closure rather than a column so the two orderings can
             * be stated together. Filament applies whichever column the
             * reader clicked before this, so their sort still wins.
             */
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderByRaw('(status = ?) desc', ['대기'])
                ->orderByDesc('created_at'))
            /**
             * And the waiting ones are marked, so they are found by
             * looking rather than by reading every status badge.
             */
            ->recordClasses(fn (MembershipRequest $record): ?string => $record->status === '대기' ? 'record-pending' : null)
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
