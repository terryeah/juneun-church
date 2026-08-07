<?php

namespace App\Filament\Resources\Cells\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Listing of cell small groups by leader and size.
 *
 * The stored name is not shown: it is derived from the leader, so the
 * two columns would repeat each other. Leaving it out also pairs 셀장
 * with 셀원 수 on one line in the stacked mobile layout, which lays
 * cells out two to a row in DOM order.
 */
class CellsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('leader.name')
                    ->label('셀장')
                    ->searchable()
                    ->sortable()
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
