<?php

namespace App\Console\Commands;

use App\Models\Bulletin;
use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Gives every 성도 전용 file a new address.
 *
 * The media bucket is public, so a file answers anyone who holds its
 * URL. The site no longer publishes those URLs - a restricted file is
 * linked through the application, which asks who is asking - but every
 * address that was on the page before that change is still out there,
 * in browser histories, in chat threads and in whatever crawled them.
 *
 * Rotating retires all of those at once. From here the address of a
 * restricted file is known only to the server, so the bucket being
 * public stops mattering for them.
 *
 * Run it again if a link is ever thought to have escaped.
 */
class RotateRestrictedFileNames extends Command
{
    protected $signature = 'files:rotate-restricted';

    protected $description = '성도 전용 파일의 주소를 새로 바꿉니다';

    /**
     * Rotate every restricted bulletin and document.
     */
    public function handle(): int
    {
        $rotated = 0;

        foreach ([Bulletin::class => 'bulletins', Document::class => 'documents'] as $model => $directory) {
            $model::query()->where('is_members_only', true)->each(function (Model $record) use ($directory, &$rotated): void {
                if ($this->rotate($record, $directory)) {
                    $rotated++;
                }
            });
        }

        $this->info($rotated.'개 파일의 주소를 바꿨습니다.');

        return self::SUCCESS;
    }

    /**
     * Copy one file to a fresh name and drop the old one.
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

        $disk->put($fresh, (string) $disk->get($old), ['visibility' => 'public']);
        $record->forceFill(['file_path' => $fresh])->saveQuietly();
        $disk->delete($old);

        return true;
    }
}
