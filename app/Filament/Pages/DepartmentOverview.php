<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Ministries\MinistryResource;
use App\Models\Ministry;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

/**
 * Read-only roster view of the 부서 (departments): each ministry with
 * its member head count. Clicking a row opens the member list filtered
 * to that department.
 */
class DepartmentOverview extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.department-overview';

    protected static ?string $slug = 'department-overview';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?string $navigationLabel = '부서 현황';

    protected static ?string $title = '부서 현황';

    /**
     * Visible to whoever may view the Ministries resource, mirroring
     * how the panel decides that resource's navigation visibility.
     */
    public static function canAccess(): bool
    {
        return MinistryResource::canViewAny();
    }

    /**
     * The departments with their member counts, in drag order.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(Ministry::query()->withCount('members'))
            ->columns([
                TextColumn::make('name')
                    ->label('부서')
                    ->searchable(),
                TextColumn::make('members_count')
                    ->label('인원'),
                TextColumn::make('description')
                    ->label('설명')
                    ->placeholder('-'),
            ])
            ->defaultSort('sort_order')
            ->recordUrl(fn (Ministry $record): string => MemberResource::getUrl('index', [
                'filters' => ['department' => ['value' => $record->name]],
            ]))
            ->paginated(false);
    }
}
