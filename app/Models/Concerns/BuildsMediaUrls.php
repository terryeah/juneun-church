<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Builds the public URL of a file on the media disk.
 *
 * Storage::disk('r2')->url() would do this, but resolving that disk
 * constructs an S3 client first: the AWS SDK's endpoint rules and API
 * description unpack into about 17 MB of request memory, on a box with
 * 1 GB and room for six PHP workers. The client is then never used,
 * because a disk with a `url` configured answers by joining two
 * strings - which is what this does instead.
 *
 * Uploading and deleting still go through Storage, where the client is
 * genuinely needed. This is only for reading a URL out of a path.
 */
trait BuildsMediaUrls
{
    /**
     * The public URL of a path on the media disk.
     */
    protected static function mediaUrl(string $path): string
    {
        $base = config('filesystems.disks.'.config('filesystems.media').'.url');

        /** A disk with no configured URL has to answer for itself. */
        if (blank($base)) {
            return Storage::disk(config('filesystems.media'))->url($path);
        }

        return rtrim((string) $base, '/').'/'.ltrim($path, '/');
    }
}
