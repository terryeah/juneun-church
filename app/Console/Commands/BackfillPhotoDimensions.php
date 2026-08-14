<?php

namespace App\Console\Commands;

use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Records the pixel size of photos stored without one.
 *
 * The upload page wrote a thumbnail but never the dimensions, so every
 * photo added through the panel has none. The lightbox uses them to lay
 * a photo out before its file has arrived; without them it takes its
 * size from whichever file loaded first, which is the thumbnail - so
 * the picture opens small and grows when the original lands.
 *
 * Reading the size means fetching the file, so this walks only the rows
 * that are missing it rather than the whole gallery.
 */
class BackfillPhotoDimensions extends Command
{
    protected $signature = 'photos:backfill-dimensions {--dry-run : Report what would change without writing}';

    protected $description = '크기가 비어 있는 사진의 가로·세로를 채웁니다';

    /**
     * Fill in every photo with no recorded size.
     */
    public function handle(): int
    {
        $disk = Storage::disk(config('filesystems.media'));
        $dryRun = (bool) $this->option('dry-run');

        $pending = Photo::query()
            ->where(fn ($query) => $query->whereNull('width')->orWhereNull('height'))
            ->get();

        if ($pending->isEmpty()) {
            $this->info('크기가 비어 있는 사진이 없습니다.');

            return self::SUCCESS;
        }

        $filled = 0;
        $failed = 0;

        foreach ($pending as $photo) {
            $size = @getimagesizefromstring((string) $disk->get($photo->path));

            if ($size === false) {
                $this->warn("#{$photo->id} 파일을 읽지 못했습니다: {$photo->path}");
                $failed++;

                continue;
            }

            if (! $dryRun) {
                $photo->forceFill(['width' => $size[0], 'height' => $size[1]])->saveQuietly();
            }

            $filled++;
        }

        $this->info(($dryRun ? '[미리보기] ' : '').$filled.'장의 크기를 채웠습니다.'
            .($failed > 0 ? " {$failed}장은 읽지 못했습니다." : ''));

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
