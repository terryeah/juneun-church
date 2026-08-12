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
     * The whole width of the dashboard.
     *
     * It is a row of buttons beside a label, not a card of content, so
     * in a half-width column the buttons wrapped onto a second line and
     * the row read as though it had been cut off.
     */
    protected int|string|array $columnSpan = 'full';

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
