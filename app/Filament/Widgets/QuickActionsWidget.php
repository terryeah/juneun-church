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
     * Anybody who may actually use the shortcuts.
     *
     * This was administrators only, on the stated grounds that only the
     * office may submit these forms - which is not so: an editor holds
     * 소식, 주보 and 사진, the three the card links to. The one role
     * whose whole job is those screens was the only staff role that
     * could not see them.
     */
    public static function canView(): bool
    {
        return ! (auth()->user()?->isGeneralMember() ?? true);
    }
}
