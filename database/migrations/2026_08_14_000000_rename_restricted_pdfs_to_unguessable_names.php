<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Renames the 주보 and 문서 PDFs already on the CDN.
 *
 * 성도 전용 keeps a file out of the listing, not off the internet: the
 * bucket is public, so the file answers to anyone who knows its
 * address. The addresses were the upload's date and time to the second
 * - bulletins/bulletin-2026-08-02-143012.pdf - which is one day of
 * guesses for anyone who knows a 주보 goes up on a Sunday afternoon,
 * and a 주보 carries the cell lists, the rota and the offering record.
 *
 * New uploads are named with a UUID. These are the ones already up.
 *
 * A rename cannot be undone: the old address is what leaked, so
 * putting it back would be the point of the exercise in reverse.
 */
return new class extends Migration
{
    /**
     * Copy each file to a random name, repoint the row, drop the old.
     */
    public function up(): void
    {
        $disk = Storage::disk(config('filesystems.media'));

        foreach (['bulletins' => 'bulletins', 'documents' => 'documents'] as $table => $directory) {
            foreach (DB::table($table)->get(['id', 'file_path']) as $row) {
                $path = (string) $row->file_path;

                /** Already random, or nothing to move. */
                if ($path === '' || ! str_starts_with(basename($path), $directory === 'bulletins' ? 'bulletin-' : 'document-')) {
                    continue;
                }

                if (! $disk->exists($path)) {
                    continue;
                }

                $fresh = $directory.'/'.Str::uuid().'.pdf';

                $disk->put($fresh, (string) $disk->get($path), ['visibility' => 'public']);

                DB::table($table)->where('id', $row->id)->update(['file_path' => $fresh]);

                $disk->delete($path);
            }
        }
    }

    /**
     * Nothing to undo. The old names are the thing being retired.
     */
    public function down(): void
    {
        //
    }
};
