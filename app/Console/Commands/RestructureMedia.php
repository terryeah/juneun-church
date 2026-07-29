<?php

namespace App\Console\Commands;

use App\Models\Album;
use App\Models\Bulletin;
use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Reorganises the media disk into per-album folders.
 *
 * Gallery photographs move from the flat gallery folder into a folder
 * named after their album's slug, and bulletin PDFs move into a single
 * bulletin folder. Objects are copied to their new keys and database
 * paths updated; the old keys are only deleted when --cleanup is given,
 * so the command can run against a shared bucket before every
 * environment's database has been updated. Repeated runs are safe:
 * records already on their new path are skipped, and a missing source
 * with an existing destination results in a database update only.
 */
class RestructureMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:restructure {--cleanup : Delete the old objects after copying} {--dry-run : Report what would change without touching anything}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move gallery photos into albums/{slug} folders and bulletins into the bulletins folder';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $disk = Storage::disk(config('filesystems.media'));

        $moved = 0;

        foreach (Photo::query()->with('album')->get() as $photo) {
            if (! $photo->album) {
                continue;
            }

            $newPath = 'albums/'.$photo->album->slug.'/'.basename($photo->path);
            $moved += $this->relocate($disk, $photo, 'path', $newPath);

            if ($photo->thumbnail_path) {
                $newThumbnail = 'albums/'.$photo->album->slug.'/'.basename($photo->thumbnail_path);
                $moved += $this->relocate($disk, $photo, 'thumbnail_path', $newThumbnail);
            }
        }

        foreach (Album::query()->whereNotNull('cover_photo_path')->get() as $album) {
            $newCover = 'albums/'.$album->slug.'/'.basename($album->cover_photo_path);
            $moved += $this->relocate($disk, $album, 'cover_photo_path', $newCover);
        }

        foreach (Bulletin::query()->get() as $bulletin) {
            $newPath = 'bulletins/'.basename($bulletin->file_path);
            $moved += $this->relocate($disk, $bulletin, 'file_path', $newPath);
        }

        $this->info(($this->option('dry-run') ? 'Would relocate ' : 'Relocated ')."{$moved} file reference(s).");

        return self::SUCCESS;
    }

    /**
     * Copy one file to its new key and update the model attribute.
     *
     * @param  \Illuminate\Contracts\Filesystem\Filesystem  $disk
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return int 1 when the record changed, 0 when skipped
     */
    protected function relocate($disk, $model, string $attribute, string $newPath): int
    {
        $oldPath = $model->getAttribute($attribute);

        if ($oldPath === $newPath) {
            return 0;
        }

        if ($this->option('dry-run')) {
            $this->line("would move: {$oldPath} -> {$newPath}");

            return 1;
        }

        if (! $disk->exists($newPath)) {
            if (! $disk->exists($oldPath)) {
                $this->warn("missing on disk, left untouched: {$oldPath}");

                return 0;
            }

            $disk->writeStream($newPath, $disk->readStream($oldPath));
        }

        if ($this->option('cleanup') && $disk->exists($oldPath)) {
            $disk->delete($oldPath);
        }

        $model->forceFill([$attribute => $newPath])->saveQuietly();
        $this->line("moved: {$oldPath} -> {$newPath}");

        return 1;
    }
}
