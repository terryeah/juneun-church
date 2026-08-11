<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * The church's handbook for its own website.
 *
 * It answers the questions a person actually arrives with - how do I
 * put up this week's 주보, why can nobody see the album I published,
 * who is allowed to touch 헌금 - rather than describing the software.
 * Every class in its view is prefixed and every rule scoped to .wiki,
 * so it renders as part of the panel rather than inside a frame of its
 * own.
 */
class Wiki extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $navigationLabel = '위키';

    protected static ?string $title = '위키';

    protected static ?string $slug = 'wiki';

    /**
     * Directly below 대시보드 in the sidebar.
     */
    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.wiki';

    /**
     * Anybody who can work in the panel at all.
     *
     * It is the instructions for using this site, so the person most
     * likely to need it is the one with the fewest menus - an editor
     * putting up a 주보 for the first time. Nothing in it is privileged:
     * it explains screens that each reader is separately allowed, or
     * not allowed, to open.
     */
    public static function canAccess(): bool
    {
        return ! (auth()->user()?->isGeneralMember() ?? true);
    }
}
