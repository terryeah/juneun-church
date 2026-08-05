<?php

namespace App\Filament\Resources\MembershipRequests;

use App\Filament\Resources\MembershipRequests\Pages\ListMembershipRequests;
use App\Filament\Resources\MembershipRequests\Pages\ViewMembershipRequest;
use App\Filament\Resources\MembershipRequests\Schemas\MembershipRequestInfolist;
use App\Filament\Resources\MembershipRequests\Tables\MembershipRequestsTable;
use App\Models\MembershipRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MembershipRequestResource extends Resource
{
    protected static ?string $model = MembershipRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static ?string $navigationLabel = '가입 신청';

    /**
     * Position of this item in the sidebar navigation, directly below
     * 사이트 유저 in the 공동체 group.
     */
    protected static ?int $navigationSort = 12;

    protected static ?string $modelLabel = '가입 신청';

    protected static ?string $pluralModelLabel = '가입 신청';

    /**
     * Badge the sidebar with the number of requests still waiting.
     */
    public static function getNavigationBadge(): ?string
    {
        $waiting = MembershipRequest::query()->where('status', '대기')->count();

        return $waiting > 0 ? (string) $waiting : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return MembershipRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MembershipRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembershipRequests::route('/'),
            'view' => ViewMembershipRequest::route('/{record}'),
        ];
    }
}
