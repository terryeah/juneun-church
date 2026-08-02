<?php

namespace App\Filament\Resources\Users\Tables;

use App\Support\RoleLabel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Read-only listing of site accounts. Accounts are created and edited
 * from the linked roster record (성도), not here.
 */
class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('이름')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member.position.name')
                    ->label('직분')
                    ->placeholder('-'),
                TextColumn::make('email')
                    ->label('이메일')
                    ->searchable()
                    ->extraCellAttributes(['class' => 'stacked-span-full']),
                TextColumn::make('roles.name')
                    ->label('롤')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => RoleLabel::label($state)),
                TextColumn::make('app_authentication_secret')
                    ->label('2단계 인증')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => filled($state) ? '사용 중' : '미설정')
                    ->color(fn ($state): string => filled($state) ? 'success' : 'gray')
                    ->placeholder('미설정'),
                TextColumn::make('created_at')
                    ->label('생성일')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
