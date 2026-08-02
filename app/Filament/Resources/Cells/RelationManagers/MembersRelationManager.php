<?php

namespace App\Filament\Resources\Cells\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\DissociateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Lists the 셀원 of a cell; associating a member sets their cell_id.
 */
class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    protected static ?string $title = '셀원';

    protected static ?string $modelLabel = '셀원';

    protected static ?string $pluralModelLabel = '셀원';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('이름')
                    ->searchable(),
                TextColumn::make('position.name')
                    ->label('직분')
                    ->placeholder('-'),
                TextColumn::make('phone')
                    ->label('전화번호')
                    ->placeholder('-'),
            ])
            ->headerActions([
                AssociateAction::make()
                    ->recordSelectSearchColumns(['name']),
            ])
            ->recordActions([
                DissociateAction::make(),
            ]);
    }
}
