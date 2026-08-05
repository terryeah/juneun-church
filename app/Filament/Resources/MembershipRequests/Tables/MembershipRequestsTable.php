<?php

namespace App\Filament\Resources\MembershipRequests\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Sign-up request listing, newest first.
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
                    ->sortable(),
                TextColumn::make('birth_date')
                    ->label('생년월일')
                    ->placeholder('-')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('전화번호')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('이메일')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('상태')
                    ->badge()
                    ->color(fn (string $state): string => self::statusColour($state)),
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
