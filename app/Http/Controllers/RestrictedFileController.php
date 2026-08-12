<?php

namespace App\Http\Controllers;

use App\Models\Bulletin;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves a 성도 전용 file only to somebody who may have it.
 *
 * The media bucket is public, so a restricted PDF used to be protected
 * by nothing but a name nobody could guess. That is concealment, not
 * access control: one link forwarded out of a 단톡방 opens the file to
 * everyone who receives it, for ever, and because the bucket is served
 * by the CDN rather than by this application, nobody at the church
 * would ever know it had happened.
 *
 * A 주보 carries the cell lists, the rota and the offering record, so
 * it is worth the round trip through here. Open files keep going
 * straight from the CDN - there is nothing to check, and no reason to
 * put a 1 GB server in front of them.
 */
class RestrictedFileController extends Controller
{
    /**
     * Stream a 주보 to a 성도.
     */
    public function bulletin(Request $request, Bulletin $bulletin): StreamedResponse
    {
        abort_unless(Bulletin::query()->visible()->whereKey($bulletin->getKey())->exists(), 404);

        return $this->stream($bulletin->file_path, $bulletin->title);
    }

    /**
     * Stream a 문서 to a 성도.
     */
    public function document(Request $request, Document $document): StreamedResponse
    {
        abort_unless(Document::query()->visible()->whereKey($document->getKey())->exists(), 404);

        return $this->stream($document->file_path, $document->title);
    }

    /**
     * Hand the file over, named for what it is.
     *
     * Inline rather than as a download, because a 주보 is read on a
     * phone rather than filed. The no-store header keeps it out of any
     * shared cache between here and the reader - Cloudflare included,
     * which would otherwise be free to answer the next person with it.
     */
    private function stream(string $path, string $title): StreamedResponse
    {
        $disk = Storage::disk(config('filesystems.media'));

        abort_unless($disk->exists($path), 404);

        return $disk->response($path, $title.'.pdf', [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store, max-age=0',
        ], 'inline');
    }
}
