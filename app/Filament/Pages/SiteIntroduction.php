<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * A walkthrough of the site for church leadership.
 *
 * It explains what the public site holds, who may change what, and what
 * the church still needs to decide - the things a pastor asks about
 * rather than the things a developer does. Every class in its view is
 * prefixed and every rule scoped to .intro, so it renders as part of
 * the page rather than inside a frame of its own.
 */
class SiteIntroduction extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartBar;

    protected static ?string $navigationLabel = '인트로';

    protected static ?string $title = '인트로';

    protected static ?string $slug = 'intro';

    /**
     * Directly below 대시보드 in the sidebar.
     */
    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.site-introduction';

    /**
     * Administrators only: it names what the church still has to decide.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->isAdministrator() ?? false;
    }
}
