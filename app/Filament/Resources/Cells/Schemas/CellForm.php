<?php

namespace App\Filament\Resources\Cells\Schemas;

use App\Models\Member;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/**
 * Form schema for cell small groups (셀).
 *
 * The cell has no name field: its name is derived from the leader
 * (셀장 이름 + ' 셀') by the Cell model, and ordering is handled by
 * dragging rows on the list.
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
                /** Searched on the server so the page never carries the whole roster. */
                Select::make('leader_id')
                    ->label('셀장')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => Member::query()
                        ->whereLike('name', "%{$search}%")
                        ->orderBy('name')
                        ->limit(50)
                        ->pluck('name', 'id')
                        ->all())
                    ->getOptionLabelUsing(fn (?string $value): ?string => Member::query()->whereKey($value)->value('name'))
                    ->required(),
                TextInput::make('description')
                    ->label('설명')
                    ->maxLength(255),
            ]);
    }
}
