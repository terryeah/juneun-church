<?php

namespace App\Filament\Resources\PersonalOfferings;

use App\Filament\Resources\PersonalOfferings\Pages\CreatePersonalOffering;
use App\Filament\Resources\PersonalOfferings\Pages\EditPersonalOffering;
use App\Filament\Resources\PersonalOfferings\Pages\ListPersonalOfferings;
use App\Filament\Resources\PersonalOfferings\Schemas\PersonalOfferingForm;
use App\Filament\Resources\PersonalOfferings\Tables\PersonalOfferingsTable;
use App\Models\PersonalOffering;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Individual giving records (개인 헌금), sitting under the weekly
 * offering totals in the sidebar.
 */
class PersonalOfferingResource extends Resource
{
    protected static ?string $model = PersonalOffering::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = '개인 헌금';

    /**
     * Position of this item in the sidebar navigation.
     */
    protected static ?int $navigationSort = 8;

    protected static ?string $modelLabel = '개인 헌금';

    protected static ?string $pluralModelLabel = '개인 헌금';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PersonalOfferingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PersonalOfferingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPersonalOfferings::route('/'),
            'create' => CreatePersonalOffering::route('/create'),
            'edit' => EditPersonalOffering::route('/{record}/edit'),
        ];
    }
}
