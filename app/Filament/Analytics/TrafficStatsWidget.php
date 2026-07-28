<?php

namespace App\Filament\Analytics;

use App\Models\AnalyticsSnapshot;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

/**
 * Thirty-day traffic totals from the local analytics snapshots.
 */
class TrafficStatsWidget extends StatsOverviewWidget
{
    /**
     * Build the four headline statistics.
     *
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $snapshots = AnalyticsSnapshot::query()
            ->where('snapshot_date', '>=', today()->subDays(30))
            ->get();

        $cacheRatio = $snapshots->sum('requests') > 0
            ? round($snapshots->sum('cached_requests') / $snapshots->sum('requests') * 100)
            : 0;

        return [
            Stat::make('방문자 (30일)', Number::format($snapshots->sum('unique_visitors'))),
            Stat::make('페이지뷰 (30일)', Number::format($snapshots->sum('page_views'))),
            Stat::make('요청 수 (30일)', Number::format($snapshots->sum('requests')))
                ->description("캐시 적중 {$cacheRatio}%"),
            Stat::make('전송량 (30일)', Number::fileSize($snapshots->sum('bytes')))
                ->description('차단된 위협 '.Number::format($snapshots->sum('threats'))),
        ];
    }
}
