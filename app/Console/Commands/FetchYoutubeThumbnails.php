<?php

namespace App\Console\Commands;

use App\Models\Sermon;
use App\Services\YoutubeThumbnail;
use Illuminate\Console\Command;

/**
 * Backfills high-quality thumbnails for every worship recording.
 */
class FetchYoutubeThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'youtube:thumbnails {--force : Refetch thumbnails that already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download high-quality YouTube thumbnails for all sermons onto the media disk';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $updated = 0;

        foreach (Sermon::query()->get() as $sermon) {
            if ($sermon->thumbnail_path && ! $this->option('force')) {
                continue;
            }

            $path = YoutubeThumbnail::store($sermon->youtube_video_id);

            if ($path === null) {
                $this->warn("could not fetch thumbnail: {$sermon->youtube_video_id}");

                continue;
            }

            $sermon->forceFill(['thumbnail_path' => $path])->saveQuietly();
            $this->line("thumbnail: {$sermon->title} -> {$path}");
            $updated++;
        }

        $this->info("Updated {$updated} sermon thumbnail(s).");

        return self::SUCCESS;
    }
}
