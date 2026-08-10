<?php

namespace App\Filament\Resources\MembershipRequests\Tables;

use App\Models\MembershipRequest;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Sign-up request listing, newest first.
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
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
