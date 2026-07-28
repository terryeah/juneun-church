<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Panel dashboard with a Korean navigation label.
 */
class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = '대시보드';

    protected static ?string $title = '대시보드';
}
