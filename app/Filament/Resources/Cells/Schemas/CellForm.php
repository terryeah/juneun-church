<?php

namespace App\Filament\Resources\Cells\Schemas;

use App\Models\Member;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/**
 * Form schema for cell small groups (셀).
 */
class CellForm
{
    /**
     * Configure the cell form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('셀 이름')
                    ->required()
                    ->maxLength(255),
                Select::make('leader_id')
                    ->label('셀장')
                    ->options(fn (): array => Member::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),
                TextInput::make('description')
                    ->label('설명')
                    ->maxLength(255),
                TextInput::make('sort_order')
                    ->label('순서')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
