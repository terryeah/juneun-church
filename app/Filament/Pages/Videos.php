<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * A signpost for 동영상, which is not built yet.
 *
 * It holds nothing and stores nothing: the sidebar entry exists so the
 * church can see where video will live once there is something to put
 * in it. Photographs stay under 사진 until then.
 */
class Videos extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFilm;

    protected static ?string $navigationLabel = '동영상';

    protected static ?string $title = '동영상';

    protected static ?string $slug = 'videos';

    /**
     * Directly below 사진 in the 미디어 group.
     */
    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.videos';

    /**
     * Administrators only: it names work the church has not decided on.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->isAdministrator() ?? false;
    }
}
