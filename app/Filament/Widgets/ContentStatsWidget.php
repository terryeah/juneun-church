<?php

namespace App\Filament\Widgets;

use App\Models\Album;
use App\Models\AnalyticsSnapshot;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Photo;
use App\Models\Sermon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

/**
 * Dashboard headline numbers: visitors and content at a glance.
 */
class ContentStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    /**
     * The numbers change slowly, so live polling is unnecessary.
     */
    protected ?string $pollingInterval = null;

    /**
     * Visitor and content figures are the church office's business, so
     * they stay hidden from anyone below an administrator rather than
     * greeting a 성도 with statistics they have no use for.
     */
    public static function canView(): bool
    {
        return auth()->user()?->isAdministrator() ?? false;
    }

    /**
     * Build the dashboard statistics.
     *
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $snapshots = AnalyticsSnapshot::query()
            ->where('snapshot_date', '>=', today()->subDays(30))
            ->orderBy('snapshot_date')
            ->get();

        return [
            Stat::make('실방문자 (30일)', Number::format($snapshots->sum('unique_visitors')))
                ->description('페이지뷰 '.Number::format($snapshots->sum('page_views')))
                ->chart($snapshots->pluck('unique_visitors')->all() ?: [0])
                ->color('primary'),
            Stat::make('다가오는 행사', Number::format(Event::query()->where('is_published', true)->whereDate('event_date', '>=', today())->count()))
                ->description('교회 행사 일정'),
            Stat::make('게시된 소식', Number::format(Announcement::query()->where('is_published', true)->count()))
                ->description('예배 영상 '.Number::format(Sermon::query()->where('is_published', true)->count()).'편'),
            Stat::make('갤러리 사진', Number::format(Photo::query()->count()))
                ->description('앨범 '.Number::format(Album::query()->where('is_published', true)->count()).'개'),
        ];
    }
}
