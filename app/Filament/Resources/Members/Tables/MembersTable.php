<?php

namespace App\Filament\Resources\Members\Tables;

use App\Models\Ministry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                TextColumn::make('position.name')
                    ->label('직분')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('department')
                    ->label('부서 / 사역')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('전화번호')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('birth_date')
                    ->label('생년월일')
                    ->placeholder('-')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('head.name')
                    ->label('세대주')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('registered_at')
                    ->label('등록일')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('position_id')
                    ->label('직분')
                    ->relationship('position', 'name'),
                SelectFilter::make('department')
                    ->label('부서 / 사역')
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
