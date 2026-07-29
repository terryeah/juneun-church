<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * The next few church events, so the week ahead is always visible.
 */
class UpcomingEventsWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = '다가오는 행사';

    protected int|string|array $columnSpan = ['lg' => 2];

    /**
     * Configure the compact upcoming-events table.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Event::query()
                ->where('is_published', true)
                ->whereDate('event_date', '>=', today())
                ->orderBy('event_date'))
            ->defaultSort('event_date')
            ->paginated(false)
            ->columns([
                TextColumn::make('event_date')
                    ->label('행사일')
                    ->date('Y-m-d (D)'),
                TextColumn::make('event_time')
                    ->label('시작 시간')
                    ->time('H:i')
                    ->placeholder('-'),
                TextColumn::make('title')
                    ->label('행사명')
                    ->weight('medium'),
                TextColumn::make('location')
                    ->label('행사장'),
            ])
            ->emptyStateHeading('예정된 행사가 없습니다')
            ->emptyStateDescription('교회 행사에서 새 일정을 등록해 보세요.');
    }

    /**
     * Only the first five upcoming events are shown.
     */
    protected function getTableQuery(): ?Builder
    {
        return Event::query()
            ->where('is_published', true)
            ->whereDate('event_date', '>=', today())
            ->orderBy('event_date')
            ->limit(5);
    }
}
