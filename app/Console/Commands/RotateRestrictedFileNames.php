<?php

namespace App\Console\Commands;

use App\Models\Bulletin;
use App\Models\Document;
use App\Services\CloudflareCachePurger;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Gives every 주보 and 문서 a new, private address.
 *
 * Uploads were written to the bucket public, and the bucket is served
 * by the CDN, so each file answered anyone holding its URL and was
 * cached at the edge for a year - the round trip through the
 * application, which asks who is asking, counted for nothing. Every
 * address that was ever on a page is still out there too, in browser
 * histories, in chat threads and in whatever crawled them.
 *
 * Rewriting each file under a fresh name with private visibility ends
 * both at once: the old public objects are deleted, and the new ones
 * answer nobody but this application's own credentials.
 *
 * Every row, not only the ones once ticked 성도 전용: 자료실 is closed
 * as a whole page, so every file behind it is restricted.
 *
 * Run it again if a link is ever thought to have escaped.
 */
class RotateRestrictedFileNames extends Command
{
    protected $signature = 'files:rotate-restricted';

    protected $description = '주보와 문서의 주소를 새로 바꾸고 비공개로 저장합니다';

    /**
     * Rotate every bulletin and document.
     */
    public function handle(): int
    {
        $rotated = 0;

        foreach ([Bulletin::class => 'bulletins', Document::class => 'documents'] as $model => $directory) {
            $model::query()->each(function (Model $record) use ($directory, &$rotated): void {
                if ($this->rotate($record, $directory)) {
                    $rotated++;
                }
            });
        }

        /**
         * Sent here rather than left to the deferred flush, because a
         * command is not a request: nothing terminates to trigger it.
         */
        CloudflareCachePurger::flush();

        $this->info($rotated.'개 파일의 주소를 바꿨습니다.');

        return self::SUCCESS;
    }

    /**
     * Copy one file to a fresh, private name and drop the old one.
     */
    private function rotate(Model $record, string $directory): bool
    {
        $disk = Storage::disk(config('filesystems.media'));
        $old = (string) $record->file_path;

        if ($old === '' || ! $disk->exists($old)) {
            $this->warn('건너뜀: '.$record->title.' (파일 없음)');

            return false;
        }

        $fresh = $directory.'/'.Str::uuid().'.pdf';

        $disk->put($fresh, (string) $disk->get($old), ['visibility' => 'private']);
        $record->forceFill(['file_path' => $fresh])->saveQuietly();
        $disk->delete($old);

        /**
         * Deleting the object is not enough on its own: the old address
         * is cached at the edge for a year, so Cloudflare would go on
         * answering every leaked link with the file long after the
         * bucket stopped holding it. The model's own purge hook does
         * not fire here, since the row is saved quietly.
         */
        CloudflareCachePurger::forget([$old]);

        return true;
    }
}
