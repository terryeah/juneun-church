<?php

namespace App\Filament\Resources\Members;

use App\Filament\Resources\Members\Pages\CreateMember;
use App\Filament\Resources\Members\Pages\EditMember;
use App\Filament\Resources\Members\Pages\ListMembers;
use App\Filament\Resources\Members\Schemas\MemberForm;
use App\Filament\Resources\Members\Tables\MembersTable;
use App\Models\Member;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?string $navigationLabel = '성도';

    /**
     * Position of this item in the sidebar navigation.
     */
    protected static ?int $navigationSort = 12;

    protected static ?string $modelLabel = '성도';

    protected static ?string $pluralModelLabel = '성도';

    /**
     * Member statuses selectable on the form and used as list tabs.
     *
     * @var array<string, string>
     */
    public const STATUSES = [
        '새가족' => '새가족',
        '재적' => '재적',
        '장기결석' => '장기결석',
        '별세' => '별세',
    ];

    public static function form(Schema $schema): Schema
    {
        return MemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MembersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembers::route('/'),
            'create' => CreateMember::route('/create'),
            'edit' => EditMember::route('/{record}/edit'),
        ];
    }
}
