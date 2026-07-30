<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

/**
 * Shortcut buttons beside the welcome card for everyday tasks.
 */
class QuickActionsWidget extends Widget
{
    protected string $view = 'filament.widgets.quick-actions';

    protected static ?int $sort = 0;
}
