<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\GoogleAnalyticsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;

/**
 * Developer-only dashboard showing Google Analytics 4 figures, run
 * alongside the Cloudflare 방문자 통계 page so the two counts can be
 * compared. GA4 sees only browsers that execute its tag, so crawlers
 * that Cloudflare's beacon still counts should not appear here.
 *
 * Ranges are built from app-timezone dates (Australia/Brisbane), and the
 * GA4 property must be set to the same reporting timezone for a day here
 * to mean the same day there.
 */
class GoogleAnalytics extends Page
{
    protected string $view = 'filament.pages.google-analytics';

    protected static ?string $slug = 'google-analytics';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static ?string $navigationLabel = '구글 애널리틱스';

    protected static ?string $title = '구글 애널리틱스';

    protected static ?int $navigationSort = 22;

    /**
     * Restricted to the developer role while the integration is being
     * trialled, matching the database graph.
     */
    public static function canAccess(): bool
    {
        /**
         * Parked until the church registers the domain with Google.
         * That needs the pastor's agreement rather than a code change,
         * so the page stays built and tested but out of the panel;
         * dropping the property id check here brings it back.
         */
        if (blank(config('analytics.property_id'))) {
            return false;
        }

        return auth()->user()?->hasRole('developer') ?? false;
    }

    /**
     * Selected range for every figure on the page.
     */
    public string $range = '7d';

    /**
     * Range options offered above the figures.
     *
     * @var array<string, string>
     */
    public array $rangeOptions = [
        'today' => '오늘',
        '7d' => '최근 7일',
        '30d' => '최근 30일',
        '90d' => '최근 90일',
    ];

    /**
     * Whether the property id and service-account key are both present.
     */
    #[Computed]
    public function isConfigured(): bool
    {
        return app(GoogleAnalyticsService::class)->isConfigured();
    }

    /**
     * The whole report for the selected range, cached per range so
     * reopening the page costs nothing. Today's numbers still move, so
     * they are held for a quarter of an hour rather than an hour.
     *
     * @return array<string, mixed>
     */
    #[Computed]
    public function report(): array
    {
        $since = match ($this->range) {
            'today' => today(),
            '30d' => today()->subDays(29),
            '90d' => today()->subDays(89),
            default => today()->subDays(6),
        };

        return Cache::remember(
            "ga4-report-{$this->range}",
            $this->range === 'today' ? 900 : 3600,
            fn (): array => app(GoogleAnalyticsService::class)->report($since, today()),
        );
    }

    /**
     * Range totals, summed from the daily rows so the headline figures
     * cannot disagree with the table below them.
     *
     * @return array{visitors: int, page_views: int}
     */
    #[Computed]
    public function totals(): array
    {
        $daily = collect($this->report['daily'] ?? []);

        return [
            'visitors' => (int) $daily->sum('visitors'),
            'page_views' => (int) $daily->sum('page_views'),
        ];
    }

    /**
     * Header action that drops the cached reports.
     *
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('지금 새로고침')
                ->icon(Heroicon::OutlinedArrowPath)
                ->action(function (): void {
                    foreach (array_keys($this->rangeOptions) as $range) {
                        Cache::forget("ga4-report-{$range}");
                    }

                    unset($this->report, $this->totals);

                    Notification::make()
                        ->title('구글 애널리틱스 데이터를 다시 불러왔습니다.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
