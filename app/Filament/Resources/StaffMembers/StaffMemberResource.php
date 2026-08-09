<?php

namespace App\Filament\Resources\StaffMembers;

use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\StaffMembers\Pages\ListStaffMembers;
use App\Models\Member;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Read-only view of the congregation members currently serving: any
 * roster record with a position or ministry appears here (and on the
 * public /people page) automatically. Rows link through to the roster
 * edit page. Column order matters: the mobile stylesheet pairs cells
 * in DOM order, giving 이름/성별 then 직분/부서.
 */
class StaffMemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = '섬김이';

    /**
     * Position of this item in the sidebar navigation.
     */
    protected static ?int $navigationSort = 14;

    protected static ?string $modelLabel = '섬김이';

    protected static ?string $pluralModelLabel = '섬김이';

    protected static ?string $slug = 'staff-members';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(fn (Builder $query) => $query->whereNotNull('position_id')->orWhereNotNull('department'))
            ->withoutLayPositions();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('이름')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label('성별')
                    ->placeholder('-'),
                TextColumn::make('position.name')
                    ->label('직분')
                    ->placeholder('-'),
                TextColumn::make('department')
                    ->label('부서')
                    ->placeholder('-'),
            ])
            ->recordUrl(fn (Member $record): string => MemberResource::getUrl('edit', ['record' => $record]))
            ->defaultSort('sort_order')
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaffMembers::route('/'),
        ];
    }
}
