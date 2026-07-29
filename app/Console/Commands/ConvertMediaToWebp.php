<?php

namespace App\Console\Commands;

use App\Filament\Support\SaveUploadsAsWebp;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Converts existing gallery photographs on the media disk to WebP.
 *
 * Photos that are already WebP (or GIF, which is excluded from
 * conversion by design) are left alone. Converted files are written
 * next to the originals with a .webp extension and database paths are
 * updated; the original objects are only deleted with --cleanup, so
 * the command can run against the shared bucket before every
 * environment's database has been updated. Repeated runs are safe.
 */
class ConvertMediaToWebp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:convert-webp {--cleanup : Delete the original files after conversion} {--dry-run : Report what would change without touching anything}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert existing non-WebP gallery photos on the media disk to WebP';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $disk = Storage::disk(config('filesystems.media'));
        $converted = 0;

        foreach (Photo::query()->get() as $photo) {
            $extension = strtolower(pathinfo($photo->path, PATHINFO_EXTENSION));

            if (in_array($extension, ['webp', 'gif'], true)) {
                continue;
            }

            $newPath = preg_replace('/\.[^.]+$/', '.webp', $photo->path);

            if ($this->option('dry-run')) {
                $this->line("would convert: {$photo->path} -> {$newPath}");
                $converted++;

                continue;
            }

            if (! $disk->exists($newPath)) {
                if (! $disk->exists($photo->path)) {
                    $this->warn("missing on disk, left untouched: {$photo->path}");

                    continue;
                }

                $webp = SaveUploadsAsWebp::toWebp((string) $disk->get($photo->path));

                if ($webp === null) {
                    $this->warn("could not convert, left untouched: {$photo->path}");

                    continue;
                }

                $disk->put($newPath, $webp);
            }

            if ($this->option('cleanup') && $disk->exists($photo->path)) {
                $disk->delete($photo->path);
            }

            $photo->forceFill([
                'path' => $newPath,
                'filename' => basename($newPath),
                'file_size' => $disk->size($newPath),
            ])->saveQuietly();

            $this->line("converted: {$photo->path}");
            $converted++;
        }

        foreach (Album::query()->whereNotNull('cover_photo_path')->get() as $album) {
            $extension = strtolower(pathinfo($album->cover_photo_path, PATHINFO_EXTENSION));

            if (in_array($extension, ['webp', 'gif'], true)) {
                continue;
            }

            $newCover = preg_replace('/\.[^.]+$/', '.webp', $album->cover_photo_path);

            if (! $this->option('dry-run') && $disk->exists($newCover)) {
                $album->forceFill(['cover_photo_path' => $newCover])->saveQuietly();
            }
        }

        $this->info(($this->option('dry-run') ? 'Would convert ' : 'Converted ')."{$converted} photo(s).");

        return self::SUCCESS;
    }
}
