<?php

namespace App\Filament\Resources\Bulletins;

use App\Filament\Resources\Bulletins\Pages\CreateBulletin;
use App\Filament\Resources\Bulletins\Pages\EditBulletin;
use App\Filament\Resources\Bulletins\Pages\ListBulletins;
use App\Filament\Resources\Bulletins\Schemas\BulletinForm;
use App\Filament\Resources\Bulletins\Tables\BulletinsTable;
use App\Models\Bulletin;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BulletinResource extends Resource
{
    protected static ?string $model = Bulletin::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = '주보';

    /**
     * Position of this item in the sidebar navigation.
     */
    protected static ?int $navigationSort = 8;

    protected static ?string $modelLabel = '주보';

    protected static ?string $pluralModelLabel = '주보';

    public static function form(Schema $schema): Schema
    {
        return BulletinForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BulletinsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBulletins::route('/'),
            'create' => CreateBulletin::route('/create'),
            'edit' => EditBulletin::route('/{record}/edit'),
        ];
    }
}
