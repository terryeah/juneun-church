<?php

namespace App\Filament\Pages;

use App\Filament\Analytics\TrafficChartWidget;
use App\Filament\Analytics\TrafficStatsWidget;
use App\Services\CloudflareAnalyticsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Computed;

/**
 * Developer-only dashboard showing Cloudflare zone analytics.
 *
 * Charts read from the local analytics_snapshots table, which a daily
 * scheduled command fills from Cloudflare's GraphQL API, so history is
 * kept beyond the free-plan retention window.
 */
class Analytics extends Page
{
    protected string $view = 'filament.pages.analytics';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = 90;

    /**
     * Only users holding the developer role may access this page.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('developer') ?? false;
    }

    /**
     * Whether the Cloudflare credentials are configured.
     */
    #[Computed]
    public function isConfigured(): bool
    {
        return app(CloudflareAnalyticsService::class)->isConfigured();
    }

    /**
     * Widgets rendered above the page content.
     *
     * @return array<class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            TrafficStatsWidget::class,
            TrafficChartWidget::class,
        ];
    }

    /**
     * Header action that refreshes the snapshots on demand.
     *
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync')
                ->label('지금 동기화')
                ->icon(Heroicon::OutlinedArrowPath)
                ->action(function (): void {
                    Artisan::call('analytics:snapshot');

                    Notification::make()
                        ->title(trim(Artisan::output()) ?: 'Snapshot complete')
                        ->success()
                        ->send();
                }),
        ];
    }
}
