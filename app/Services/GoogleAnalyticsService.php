<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\CarbonInterface;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\OrderBy;
use Spatie\Analytics\Period;
use Throwable;

/**
 * Reads visitor figures from the Google Analytics 4 Data API through
 * spatie/laravel-analytics.
 *
 * The property id and the service-account key file are usually absent -
 * the integration is opt-in and the key never lives in the repository -
 * so every method degrades to empty output rather than throwing. The
 * package itself throws from its container binding the moment either is
 * missing, hence isConfigured() is checked before anything is resolved.
 *
 * Dates are handed to Google as plain Y-m-d strings taken from
 * app-timezone Carbon instances (Australia/Brisbane). Google buckets the
 * date dimension in the GA4 property's own reporting timezone, so that
 * property must also be set to Brisbane for the two to line up.
 */
class GoogleAnalyticsService
{
    /**
     * Whether the property id and service-account credentials are both
     * present. The credentials may be an inline array instead of a path.
     */
    public function isConfigured(): bool
    {
        $credentials = config('analytics.service_account_credentials_json');

        return filled(config('analytics.property_id'))
            && (is_array($credentials) || (is_string($credentials) && is_file($credentials)));
    }

    /**
     * Every figure the admin page shows, for one inclusive date range.
     *
     * Returns an empty array when the integration is not configured or
     * the API call fails, which the page renders as an empty state. The
     * breakdown keys match the shared breakdowns partial so the Google
     * page and the Cloudflare page read the same way.
     *
     * @return array{
     *     daily: array<int, array{date: string, visitors: int, page_views: int}>,
     *     path: array<int, array{label: string, count: int}>,
     *     country: array<int, array{label: string, count: int}>,
     *     referer: array<int, array{label: string, count: int}>
     * }|array{}
     */
    public function report(CarbonInterface $since, CarbonInterface $until): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        try {
            $period = Period::create($since->copy()->startOfDay(), $until->copy()->endOfDay());

            return [
                'daily' => $this->daily($period),
                'path' => $this->breakdown($period, 'pagePath'),
                'country' => $this->breakdown($period, 'country'),
                'referer' => $this->breakdown($period, 'pageReferrer'),
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [];
        }
    }

    /**
     * Daily active users and page views, newest first.
     *
     * @return array<int, array{date: string, visitors: int, page_views: int}>
     */
    private function daily(Period $period): array
    {
        return Analytics::get(
            period: $period,
            metrics: ['activeUsers', 'screenPageViews'],
            dimensions: ['date'],
            maxResults: 400,
            orderBy: [OrderBy::dimension('date', true)],
        )
            ->map(fn (array $row): array => [
                'date' => $row['date']->toDateString(),
                'visitors' => (int) ($row['activeUsers'] ?? 0),
                'page_views' => (int) ($row['screenPageViews'] ?? 0),
            ])
            ->all();
    }

    /**
     * The eight busiest values of one dimension, by page views.
     *
     * @return array<int, array{label: string, count: int}>
     */
    private function breakdown(Period $period, string $dimension): array
    {
        return Analytics::get(
            period: $period,
            metrics: ['screenPageViews'],
            dimensions: [$dimension],
            maxResults: 8,
            orderBy: [OrderBy::metric('screenPageViews', true)],
        )
            ->map(fn (array $row): array => [
                'label' => (string) ($row[$dimension] ?? '-'),
                'count' => (int) ($row['screenPageViews'] ?? 0),
            ])
            ->filter(fn (array $row): bool => $row['count'] > 0)
            ->values()
            ->all();
    }
}
