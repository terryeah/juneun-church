<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Copies every local media file to the Cloudflare R2 bucket.
 *
 * Paths are preserved exactly, so the database needs no changes; once
 * the copy has finished, switching MEDIA_DISK to r2 makes the site read
 * and write straight from the bucket. The command is idempotent and
 * skips files that already exist remotely with the same size.
 */
class MigrateMediaToR2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:migrate-to-r2 {--dry-run : List what would be copied without uploading}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy all local media files to the Cloudflare R2 bucket, preserving paths';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (blank(config('filesystems.disks.r2.key')) || blank(config('filesystems.disks.r2.bucket'))) {
            $this->error('R2 is not configured. Set the CLOUDFLARE_R2_* variables first.');

            return self::FAILURE;
        }

        $local = Storage::disk('public');
        $remote = Storage::disk('r2');

        $files = collect($local->allFiles())
            ->reject(fn (string $path) => str_starts_with($path, '.'));

        $copied = $skipped = 0;

        foreach ($files as $path) {
            if ($remote->exists($path) && $remote->size($path) === $local->size($path)) {
                $skipped++;

                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("would copy: {$path}");
                $copied++;

                continue;
            }

            $remote->writeStream($path, $local->readStream($path));
            $copied++;
            $this->line("copied: {$path}");
        }

        $this->info(($this->option('dry-run') ? 'Would copy ' : 'Copied ')."{$copied} file(s), skipped {$skipped} already present.");

        return self::SUCCESS;
    }
}
