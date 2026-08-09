<?php

namespace App\Filament\Resources\SiteSettings;

use App\Filament\Resources\SiteSettings\Pages\ManageSiteSettings;
use App\Filament\Resources\SiteSettings\Schemas\SiteSettingForm;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * 사이트 설정: the fixed set of values the public site reads by key.
 *
 * The resource keeps its model, its policy and its sidebar position,
 * but registers a single page instead of a list with create, edit and
 * delete actions - see {@see ManageSiteSettings} for why.
 */
class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = '사이트 설정';

    /**
     * Position of this item in the sidebar navigation.
     */
    protected static ?int $navigationSort = 17;

    protected static ?string $modelLabel = '설정';

    protected static ?string $pluralModelLabel = '설정';

    public static function form(Schema $schema): Schema
    {
        return SiteSettingForm::configure($schema);
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
            'index' => ManageSiteSettings::route('/'),
        ];
    }
}
