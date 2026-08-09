<?php

namespace App\Filament\Resources\Cells;

use App\Filament\Resources\Cells\Pages\CreateCell;
use App\Filament\Resources\Cells\Pages\EditCell;
use App\Filament\Resources\Cells\Pages\ListCells;
use App\Filament\Resources\Cells\RelationManagers\MembersRelationManager;
use App\Filament\Resources\Cells\Schemas\CellForm;
use App\Filament\Resources\Cells\Tables\CellsTable;
use App\Models\Cell;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Cell small groups (셀): each cell has a 셀장 and its 셀원 are the
 * roster members whose cell field points at it.
 */
class CellResource extends Resource
{
    protected static ?string $model = Cell::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = '셀';

    /**
     * Position of this item in the sidebar navigation.
     */
    protected static ?int $navigationSort = 13;

    protected static ?string $modelLabel = '셀';

    protected static ?string $pluralModelLabel = '셀';

    public static function form(Schema $schema): Schema
    {
        return CellForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CellsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCells::route('/'),
            'create' => CreateCell::route('/create'),
            'edit' => EditCell::route('/{record}/edit'),
        ];
    }
}
