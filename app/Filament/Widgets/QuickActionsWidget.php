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

    /**
     * Every shortcut here opens a create form only the church office
     * may submit, so the whole card is hidden from anyone below an
     * administrator rather than offering links that end in a 403.
     */
    public static function canView(): bool
    {
        return auth()->user()?->isAdministrator() ?? false;
    }
}
