<?php

namespace App\Filament\Resources\Ministries;

use App\Filament\Resources\Ministries\Pages\CreateMinistry;
use App\Filament\Resources\Ministries\Pages\EditMinistry;
use App\Filament\Resources\Ministries\Pages\ListMinistries;
use App\Filament\Resources\Ministries\Schemas\MinistryForm;
use App\Filament\Resources\Ministries\Tables\MinistriesTable;
use App\Models\Ministry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MinistryResource extends Resource
{
    protected static ?string $model = Ministry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?string $navigationLabel = '부서';

    /**
     * Position of this item in the sidebar navigation.
     */
    protected static ?int $navigationSort = 9;

    protected static ?string $modelLabel = '부서';

    protected static ?string $pluralModelLabel = '부서';

    public static function form(Schema $schema): Schema
    {
        return MinistryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MinistriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMinistries::route('/'),
            'create' => CreateMinistry::route('/create'),
            'edit' => EditMinistry::route('/{record}/edit'),
        ];
    }
}
