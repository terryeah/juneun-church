<?php

namespace App\Console\Commands;

use App\Models\AnalyticsSnapshot;
use App\Services\CloudflareAnalyticsService;
use Illuminate\Console\Command;

/**
 * Snapshots recent Cloudflare zone analytics into the local database.
 *
 * Runs daily from the scheduler. Re-fetching the last few days on each
 * run lets late-arriving data settle before the free-plan retention
 * window closes, and the unique date key makes the upsert idempotent.
 */
class SnapshotAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:snapshot {--days=3 : How many recent days to refresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store recent Cloudflare zone analytics in the local snapshot table';

    /**
     * Execute the console command.
     */
    public function handle(CloudflareAnalyticsService $cloudflare): int
    {
        if (! $cloudflare->isConfigured()) {
            $this->warn('Cloudflare analytics is not configured. Set CLOUDFLARE_API_TOKEN and CLOUDFLARE_ZONE_ID.');

            return self::SUCCESS;
        }

        $days = max(1, (int) $this->option('days'));
        $rows = $cloudflare->dailyStats(today()->subDays($days), today());

        /** Real-visitor numbers from Web Analytics override the bot-inflated zone counts */
        $realVisits = $cloudflare->dailyRealVisits(today()->subDays($days), today())->keyBy('date');

        foreach ($rows as $row) {
            if ($realVisits->has($row['date'])) {
                $row['unique_visitors'] = $realVisits[$row['date']]['visits'];
                $row['page_views'] = $realVisits[$row['date']]['page_views'];
            }

            AnalyticsSnapshot::query()->updateOrCreate(
                ['snapshot_date' => $row['date']],
                collect($row)->except('date')->all(),
            );
        }

        $this->info("Stored {$rows->count()} day(s) of analytics".($realVisits->isNotEmpty() ? ' with real-visitor data' : '').'.');

        return self::SUCCESS;
    }
}
