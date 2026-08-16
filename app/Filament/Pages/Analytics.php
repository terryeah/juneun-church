<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Computed;

/**
 * 방문자 통계, which is Umami's own dashboard on this page.
 *
 * It used to be Cloudflare's figures, drawn here as cards, a chart and
 * a table. Two things were wrong with them and neither could be fixed
 * from this side: Cloudflare injects its counting beacon at the edge,
 * so nobody could be left out of the numbers - the office and whoever
 * maintains the site were in every one of them - and what came back
 * was a daily total with nothing underneath it, no way to ask which
 * page anyone had actually opened.
 *
 * Umami answers both, and answers far more than this page ever drew:
 * pages, referrers, countries, devices, live visitors. Reading it
 * through the API needs a paid plan, so the dashboard itself is
 * embedded instead, through a share link created in Umami. The link is
 * public to anyone holding it, which is why it lives in the
 * environment and not in this repository.
 *
 * The Cloudflare snapshots are still collected and still in the
 * database. They are simply not drawn any more.
 */
class Analytics extends Page
{
    protected string $view = 'filament.pages.analytics';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = '방문자 통계';

    protected static ?string $title = '방문자 통계';

    protected static ?int $navigationSort = 21;

    /**
     * Administrators and developers may access this page.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['developer', 'admin', 'super_admin']) ?? false;
    }

    /**
     * The Umami dashboard to embed, or null when none is configured.
     */
    #[Computed]
    public function shareUrl(): ?string
    {
        return config('services.umami.share_url');
    }

    /**
     * A way out to the full dashboard, for anything the frame makes
     * awkward - a phone, mostly, where a dashboard inside a dashboard
     * is two sets of scrollbars.
     *
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('open')
                ->label('새 창에서 열기')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (): ?string => $this->shareUrl)
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->shareUrl)),
        ];
    }
}
