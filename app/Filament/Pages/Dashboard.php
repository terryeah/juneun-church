<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ContentStatsWidget;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\UpcomingEventsWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;

/**
 * Panel dashboard: headline numbers, the week ahead and, for the
 * developer role, the latest admin activity.
 */
class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = '대시보드';

    /**
     * Position of this item in the sidebar navigation.
     */
    protected static ?int $navigationSort = 1;

    protected static ?string $title = '대시보드';

    /**
     * Widgets rendered on the dashboard, in display order.
     *
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        return [
            AccountWidget::class,
            QuickActionsWidget::class,
            ContentStatsWidget::class,
            UpcomingEventsWidget::class,
            RecentActivityWidget::class,
        ];
    }
}
