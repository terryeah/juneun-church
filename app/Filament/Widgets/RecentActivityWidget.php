<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

/**
 * The latest admin activity, shown only to the developer role.
 */
class RecentActivityWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = '최근 활동';

    protected int|string|array $columnSpan = ['lg' => 2];

    /**
     * Mirrors the activity log's developer-only visibility.
     */
    public static function canView(): bool
    {
        return auth()->user()?->hasRole('developer') ?? false;
    }

    /**
     * Configure the compact recent-activity table.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Activity::query()->latest()->limit(8))
            ->paginated(false)
            ->columns([
                TextColumn::make('created_at')
                    ->label('일시')
                    ->dateTime(),
                TextColumn::make('causer.name')
                    ->label('사용자')
                    ->placeholder('시스템'),
                TextColumn::make('event')
                    ->label('동작')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'created', 'login' => 'success',
                        'updated' => 'warning',
                        'deleted', 'failed_login' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('subject_type')
                    ->label('대상')
                    ->formatStateUsing(fn (?string $state, Activity $record): string => $state
                        ? class_basename($state).' #'.$record->subject_id
                        : '-')
                    ->placeholder('-'),
            ]);
    }
}
