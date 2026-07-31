<?php

namespace App\Filament\Pages;

use App\Filament\Analytics\TrafficChartWidget;
use App\Filament\Analytics\TrafficStatsWidget;
use App\Models\AnalyticsSnapshot;
use App\Services\CloudflareAnalyticsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
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

    protected static ?string $navigationLabel = '방문자 통계';

    protected static ?string $title = '방문자 통계';

    protected static ?int $navigationSort = 12;

    /**
     * Administrators and developers may access this page.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['developer', 'admin', 'super_admin']) ?? false;
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
     * The last thirty daily snapshots, newest first, for the table.
     *
     * @return Collection<int, AnalyticsSnapshot>
     */
    #[Computed]
    public function dailySnapshots(): Collection
    {
        return AnalyticsSnapshot::query()
            ->where('snapshot_date', '>=', today()->subDays(30))
            ->orderByDesc('snapshot_date')
            ->get();
    }

    /**
     * Selected range for the real-visitor breakdowns: 24h, 7d, 30d or all.
     */
    public string $visitorRange = 'today';

    /**
     * Range options offered on the breakdown sections.
     *
     * @var array<string, string>
     */
    public array $rangeOptions = [
        'today' => '오늘',
        '24h' => '최근 24시간',
        '7d' => '최근 7일',
        '30d' => '최근 30일',
        'all' => '전체',
    ];

    /**
     * Web Analytics breakdowns for the selected range, cached per range.
     *
     * @return array<string, array<int, array{label: string, count: int}>>
     */
    #[Computed]
    public function breakdowns(): array
    {
        $since = match ($this->visitorRange) {
            'today' => today(),
            '24h' => today()->subDay(),
            '7d' => today()->subDays(7),
            'all' => today()->subMonths(6),
            default => today()->subDays(30),
        };

        return Cache::remember(
            "cf-rum-breakdowns-{$this->visitorRange}",
            3600,
            fn (): array => app(CloudflareAnalyticsService::class)->breakdowns($since, today()),
        );
    }

    /**
     * Bot-included request breakdowns, cached for an hour.
     *
     * @return array<string, array<int, array{label: string, count: int}>>
     */
    #[Computed]
    public function botBreakdowns(): array
    {
        return Cache::remember(
            'cf-zone-breakdowns',
            3600,
            fn (): array => app(CloudflareAnalyticsService::class)->botIncludedBreakdowns(),
        );
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
        ];
    }

    /**
     * The traffic chart renders below the page body, under the daily
     * detail table.
     *
     * @return array<class-string>
     */
    protected function getFooterWidgets(): array
    {
        return [
            TrafficChartWidget::class,
        ];
    }

    /**
     * Whether the signed-in user holds the developer role.
     */
    #[Computed]
    public function isDeveloper(): bool
    {
        return auth()->user()?->hasRole('developer') ?? false;
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
                    foreach (array_keys($this->rangeOptions) as $range) {
                        Cache::forget("cf-rum-breakdowns-{$range}");
                    }
                    Cache::forget('cf-zone-breakdowns');
                    Artisan::call('analytics:snapshot');

                    Notification::make()
                        ->title(trim(Artisan::output()) ?: 'Snapshot complete')
                        ->success()
                        ->send();
                }),
        ];
    }
}
