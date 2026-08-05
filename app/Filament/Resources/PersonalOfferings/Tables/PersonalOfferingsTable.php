<?php

namespace App\Filament\Resources\PersonalOfferings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Listing of individual giving records (개인 헌금).
 */
class PersonalOfferingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('성함')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('구분')
                    ->placeholder('-'),
                TextColumn::make('amount')
                    ->label('금액')
                    ->placeholder('-')
                    ->formatStateUsing(fn (string $state): string => '$'.number_format((float) $state, 2))
                    ->sortable(),
                TextColumn::make('offering.sunday_date')
                    ->label('주일')
                    ->placeholder('-')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
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
