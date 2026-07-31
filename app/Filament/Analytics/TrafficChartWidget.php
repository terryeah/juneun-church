<?php

namespace App\Filament\Analytics;

use App\Models\AnalyticsSnapshot;
use Filament\Widgets\ChartWidget;

/**
 * Daily unique visitors and page views over the last thirty days.
 */
class TrafficChartWidget extends ChartWidget
{
    protected ?string $heading = '최근 30일 트래픽';

    protected int|string|array $columnSpan = 'full';

    /**
     * Snapshots change once a day, so live polling is unnecessary.
     */
    protected ?string $pollingInterval = null;

    /**
     * Chart dataset built from the local snapshots.
     *
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $snapshots = AnalyticsSnapshot::query()
            ->where('snapshot_date', '>=', today()->subDays(29))
            ->orderBy('snapshot_date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => '실방문자',
                    'data' => $snapshots->pluck('unique_visitors')->all(),
                    'borderColor' => '#004aad',
                    'backgroundColor' => 'rgba(0, 74, 173, 0.1)',
                    'fill' => true,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => '페이지뷰',
                    'data' => $snapshots->pluck('page_views')->all(),
                    'borderColor' => '#7688aa',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => '총 요청 (봇 포함)',
                    'data' => $snapshots->pluck('requests')->all(),
                    'borderColor' => '#c2cbdb',
                    'borderDash' => [6, 4],
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $snapshots->map(fn (AnalyticsSnapshot $s) => $s->snapshot_date->format('m/d'))->all(),
        ];
    }

    /**
     * Requests dwarf visitor counts, so they get their own axis on
     * the right while visitors and page views share the left one.
     *
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'position' => 'left',
                ],
                'y1' => [
                    'beginAtZero' => true,
                    'position' => 'right',
                    'grid' => ['drawOnChartArea' => false],
                ],
            ],
        ];
    }

    /**
     * Render as a line chart.
     */
    protected function getType(): string
    {
        return 'line';
    }
}
