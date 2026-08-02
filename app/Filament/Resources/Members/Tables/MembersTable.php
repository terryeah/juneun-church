<?php

namespace App\Filament\Resources\Members\Tables;

use App\Models\Ministry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Roster listing. Column order matters: the mobile stylesheet lays the
 * cells out in a two-column grid following DOM order, pairing 이름/상태,
 * 성별/생년월일 and 직분/전화번호.
 */
class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('이름')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('상태')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '재적' => 'success',
                        '새가족' => 'info',
                        '장기결석' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('gender')
                    ->label('성별')
                    ->placeholder('-'),
                TextColumn::make('birth_date')
                    ->label('생년월일')
                    ->placeholder('-')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('position.name')
                    ->label('직분')
                    ->placeholder('-'),
                TextColumn::make('phone')
                    ->label('전화번호')
                    ->placeholder('-')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('position_id')
                    ->label('직분')
                    ->relationship('position', 'name'),
                SelectFilter::make('department')
                    ->label('부서')
                    ->options(fn (): array => Ministry::query()->orderBy('sort_order')->pluck('name', 'name')->all()),
                SelectFilter::make('gender')
                    ->label('성별')
                    ->options(['남' => '남', '여' => '여']),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
