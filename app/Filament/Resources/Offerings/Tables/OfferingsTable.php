<?php

namespace App\Filament\Resources\Offerings\Tables;

use App\Models\Offering;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OfferingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sunday_date')
                    ->label('주일')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('items')
                    ->label('건수')
                    ->formatStateUsing(fn (Offering $record): string => count($record->items ?? []).'건'),
                TextColumn::make('total')
                    ->label('합계')
                    ->state(fn (Offering $record): string => '$'.number_format($record->total(), 2)),
                TextColumn::make('note')
                    ->label('비고')
                    ->placeholder('-'),
            ])
            ->defaultSort('sunday_date', 'desc')
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
