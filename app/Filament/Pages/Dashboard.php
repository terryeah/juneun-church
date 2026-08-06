<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ContentStatsWidget;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\UpcomingEventsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

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
     * The dashboard summarises work an ordinary 성도 has no part in,
     * and every widget on it is now closed to them anyway, so the page
     * itself is closed too. Panel auth middleware turns that into a
     * redirect to their profile rather than a refusal, and it keeps
     * 대시보드 out of their navigation.
     */
    public static function canAccess(): bool
    {
        return ! (auth()->user()?->isMemberOnly() ?? false);
    }

    /**
     * Widgets rendered on the dashboard, in display order.
     *
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        return [
            QuickActionsWidget::class,
            ContentStatsWidget::class,
            UpcomingEventsWidget::class,
            RecentActivityWidget::class,
        ];
    }
}
