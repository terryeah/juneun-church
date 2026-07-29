<?php

namespace App\Console\Commands;

use App\Filament\Support\SaveUploadsAsWebp;
use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Generates grid thumbnails for gallery photographs.
 *
 * Thumbnails live in a thumbs folder inside each album's folder and
 * are what the slider, gallery grids and featured images serve, so
 * mobile visitors never download full-size photographs. The command
 * is idempotent: an existing thumbnail object is reused and only the
 * database reference is filled in, which lets it run against the
 * shared bucket from every environment.
 */
class GenerateThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:thumbnails {--force : Regenerate thumbnails that already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate WebP grid thumbnails for every gallery photo';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $disk = Storage::disk(config('filesystems.media'));
        $generated = 0;

        foreach (Photo::query()->with('album')->get() as $photo) {
            if (! $photo->album) {
                continue;
            }

            $thumbnailPath = dirname($photo->path).'/thumbs/'.basename($photo->path);

            if (! $this->option('force') && $photo->thumbnail_path === $thumbnailPath && $disk->exists($thumbnailPath)) {
                continue;
            }

            if ($this->option('force') || ! $disk->exists($thumbnailPath)) {
                if (! $disk->exists($photo->path)) {
                    $this->warn("missing original, skipped: {$photo->path}");

                    continue;
                }

                $thumbnail = SaveUploadsAsWebp::thumbnail((string) $disk->get($photo->path));

                if ($thumbnail === null) {
                    $this->warn("could not thumbnail, skipped: {$photo->path}");

                    continue;
                }

                $disk->put($thumbnailPath, $thumbnail);
            }

            $photo->forceFill(['thumbnail_path' => $thumbnailPath])->saveQuietly();
            $this->line("thumbnail: {$thumbnailPath}");
            $generated++;
        }

        $this->info("Generated or linked {$generated} thumbnail(s).");

        return self::SUCCESS;
    }
}
