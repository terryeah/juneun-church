<?php

namespace App\Filament\Resources\Activities;

use App\Filament\Resources\Activities\Pages\ListActivities;
use App\Filament\Resources\Activities\Tables\ActivitiesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

/**
 * Read-only browser for the user activity log (developer only).
 *
 * Access is enforced by ActivityPolicy; records are created by model
 * observers and auth listeners, and pruned after six months by the
 * scheduled activitylog:clean command.
 */
class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = '활동 기록';

    protected static ?string $modelLabel = '활동 기록';

    protected static ?string $slug = 'activity-log';

    protected static ?int $navigationSort = 91;

    public static function table(Table $table): Table
    {
        return ActivitiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
        ];
    }
}
