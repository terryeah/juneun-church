<?php

namespace App\Filament\Resources\Devotions;

use App\Filament\Resources\Devotions\Pages\CreateDevotion;
use App\Filament\Resources\Devotions\Pages\EditDevotion;
use App\Filament\Resources\Devotions\Pages\ListDevotions;
use App\Filament\Resources\Devotions\Schemas\DevotionForm;
use App\Filament\Resources\Devotions\Tables\DevotionsTable;
use App\Models\Devotion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * 큐티 days. The 묵상 성도 write on them have no screen here at all.
 */
class DevotionResource extends Resource
{
    protected static ?string $model = Devotion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $navigationLabel = '큐티';

    protected static ?string $modelLabel = '큐티';

    protected static ?string $pluralModelLabel = '큐티';

    public static function form(Schema $schema): Schema
    {
        return DevotionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DevotionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDevotions::route('/'),
            'create' => CreateDevotion::route('/create'),
            'edit' => EditDevotion::route('/{record}/edit'),
        ];
    }
}
