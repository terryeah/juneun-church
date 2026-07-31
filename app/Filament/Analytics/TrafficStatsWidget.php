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
     * Snapshots change once a day, so live polling is unnecessary.
     */
    protected ?string $pollingInterval = null;

    /**
     * Build the three headline statistics: real visitors and page
     * views from Web Analytics, and total requests including bots
     * from the zone metrics for server-load context.
     *
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $snapshots = AnalyticsSnapshot::query()
            ->where('snapshot_date', '>=', today()->subDays(29))
            ->get();

        return [
            Stat::make('실방문자 (30일)', Number::format($snapshots->sum('unique_visitors'))),
            Stat::make('페이지뷰 (30일)', Number::format($snapshots->sum('page_views'))),
            Stat::make('총 요청 (30일)', Number::format($snapshots->sum('requests')))
                ->description('봇 포함'),
        ];
    }
}
