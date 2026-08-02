<?php

namespace App\Filament\Resources\Cells\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Listing of cell small groups with their leader and member count.
 */
class CellsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('셀 이름')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('leader.name')
                    ->label('셀장')
                    ->placeholder('-'),
                TextColumn::make('members_count')
                    ->label('셀원 수')
                    ->counts('members'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
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
