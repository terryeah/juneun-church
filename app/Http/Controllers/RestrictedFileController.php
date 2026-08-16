<?php

namespace App\Http\Controllers;

use App\Models\Bulletin;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\HeaderUtils;
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
     * Show a 주보 to a 성도 as a page.
     *
     * A raw PDF has no title for the browser to put in the tab, so it
     * showed the last part of the address - the record's id, "5". The
     * PDF is embedded in an ordinary page of the site instead, and the
     * file hangs off its own address below.
     *
     * This is a page, so a reader with no account is answered the way
     * every other 성도 전용 page answers one: with the sign-in notice.
     * A 주보 link forwarded into a 단톡방 is opened by 성도 whose session
     * has lapsed, and a bare 404 leaves them with nowhere to go.
     *
     * Nothing is looked up before that answer and the notice names no
     * 주보 and no date, so it is the same response at every address -
     * it says only that 주보 are 성도 전용, which the site says anyway.
     * A signed-in reader who is not on the 교적 keeps the 404, so
     * working through the addresses one by one still learns nothing.
     *
     * The bulletin is taken as a plain route parameter for that reason:
     * route model binding would resolve the record first and answer a
     * guest 404 or notice depending on whether it exists.
     */
    public function bulletin(string $bulletin): View
    {
        if (Auth::guest()) {
            return view('pages.members-only', [
                'kicker' => '주일 예배 · Bulletin',
                'title' => '주보',
                'body' => '주보에는 셀 편성과 섬김이 명단, 주일 헌금 내역이 담겨 있어 성도에게만 공개됩니다.',
            ]);
        }

        $record = Bulletin::query()->visible()->whereKey($bulletin)->first();

        /**
         * The file is checked here and not only on the stream below.
         * The whole page is the PDF, so without the object there is
         * nothing to show but an empty frame and a button that 404s -
         * a page that says a 주보 is there when it is not. A missing
         * object is an upload to redo in the admin panel rather than a
         * state worth a screen of its own.
         */
        abort_unless($record !== null && Storage::disk(config('filesystems.media'))->exists($record->file_path), 404);

        return view('pages.bulletin', ['bulletin' => $record]);
    }

    /**
     * Stream the 주보 PDF itself to a 성도.
     */
    public function bulletinPdf(Bulletin $bulletin, string $filename): StreamedResponse|RedirectResponse
    {
        abort_unless(Bulletin::query()->visible()->whereKey($bulletin->getKey())->exists(), 404);

        /**
         * The name in the address is made to match the record instead
         * of being decorative: any date-shaped name used to serve any
         * 주보, so one file had unlimited addresses, the CDN as many
         * cache entries, and a forwarded link could name the wrong
         * Sunday. A redirect rather than dropping the segment, because
         * the address is built in the model, which another change owns.
         */
        if ($filename !== $bulletin->downloadName()) {
            return redirect($bulletin->pdfUrl(), 301);
        }

        return $this->stream($bulletin->file_path, $bulletin->downloadName());
    }

    /**
     * Stream a 문서 to a 성도.
     *
     * The real title is the name worth having, and a header can carry
     * it; the date-based name is only what a browser too old for
     * filename* falls back to. Without this every Korean title landed
     * on that fallback, so two documents published the same day were
     * saved over each other.
     */
    public function document(Document $document): StreamedResponse
    {
        abort_unless(Document::query()->visible()->whereKey($document->getKey())->exists(), 404);

        return $this->stream($document->file_path, $document->title.'.pdf', $document->downloadName());
    }

    /**
     * Hand the file over, named for what it is.
     *
     * Inline rather than as a download, because a 주보 is read on a
     * phone rather than filed. The no-store header keeps it out of any
     * shared cache between here and the reader - Cloudflare included,
     * which would otherwise be free to answer the next person with it.
     */
    private function stream(string $path, string $name, string $fallback = ''): StreamedResponse
    {
        $disk = Storage::disk(config('filesystems.media'));

        abort_unless($disk->exists($path), 404);

        /**
         * The header is written here rather than left to the disk's own
         * helper, which derives the ASCII fallback with Str::ascii():
         * that empties a Hangul title and Symfony then refuses the
         * empty fallback. Given both halves, it puts the real name in
         * filename*, which every current browser saves the file under,
         * and keeps the ASCII one for anything that cannot read it.
         *
         * Both halves are stripped of the separators and control
         * characters a header cannot carry - a slash throws for the
         * real name too, not only for the fallback - and the fallback
         * is narrowed to the printable ASCII Symfony insists on, minus
         * the percent sign it refuses there. An admin-typed title
         * reaches this, so a 500 is one keystroke away without it.
         */
        $strip = fn (string $value): string => (string) preg_replace('#[\\\\/[:cntrl:]]+#', '', $value);

        $name = $strip($name) ?: 'file.pdf';
        $fallback = preg_replace('/[^\x20-\x24\x26-\x7e]+/', '', $strip($fallback ?: $name)) ?: 'file.pdf';

        return $disk->response($path, null, [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Content-Disposition' => HeaderUtils::makeDisposition('inline', $name, $fallback),
        ], 'inline');
    }
}
