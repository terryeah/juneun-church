<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Placeholder page for the congregation member directory (성도).
 *
 * The member management feature is planned but not yet built; this
 * page reserves its place in the navigation so the structure is ready.
 */
class Members extends Page
{
    protected string $view = 'filament.pages.members';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?string $navigationLabel = '성도';

    protected static ?string $title = '성도';

    protected static ?string $slug = 'members';

    /**
     * Only staff-level roles can see the future member directory.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['developer', 'admin', 'super_admin']) ?? false;
    }
}
