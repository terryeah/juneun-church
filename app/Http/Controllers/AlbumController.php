<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Handles 앨범: the church's photo albums and video albums.
 *
 * The two kinds sit side by side rather than in separate sections, so
 * one address holds everything the church has recorded and the chips
 * choose which kind is on show - the same shape the panel uses, where
 * 앨범 is one screen and 사진 and 동영상 are what goes in them.
 *
 * The section is 성도 전용 as a whole: every album is a room full of
 * the congregation's faces, and the videos are unlisted on YouTube, so
 * the page is closed rather than each album being marked one by one.
 */
class AlbumController extends Controller
{
    /**
     * The kinds of album, as the chips at the top of the page.
     */
    private const KINDS = [
        Album::TYPE_PHOTO => '사진',
        Album::TYPE_VIDEO => '동영상',
    ];

    /**
     * One of a fixed set of choices, or the fallback.
     *
     * The type check comes first and is the point of this: ?kind[]=x
     * arrives as an array, and casting an array to a string in PHP is
     * an error rather than a value - which the framework turns into an
     * unauthenticated 500 on a public page. Request::string() does not
     * help, because it performs that same cast internally.
     *
     * @param  array<string, string>  $options
     */
    private static function chosen(mixed $value, array $options, string $fallback): string
    {
        return is_string($value) && array_key_exists($value, $options) ? $value : $fallback;
    }

    /**
     * Display the album grid for one kind.
     *
     * There is no audience chip any more. It sorted the shelf into 성도
     * 전용 and 모두 공개, which said something while the page was open
     * to the street; now that only a 성도 reaches it, every album on it
     * is theirs and the chip only repeated the page.
     */
    public function index(Request $request): View
    {
        if (! Auth::user()?->isChurchMember()) {
            return $this->signInPage();
        }

        /**
         * Only offer a kind there is something to see in, and do not
         * open an empty one either - a chip leading to '등록된 앨범이
         * 없습니다' is a door painted on a wall.
         */
        $available = array_intersect_key(
            self::KINDS,
            array_flip(Album::query()->visible()->distinct()->pluck('type')->all()),
        );

        $kind = static::chosen(
            $request->query('kind'),
            $available,
            array_key_first($available) ?? Album::TYPE_PHOTO,
        );

        $albums = Album::query()
            ->visible()
            ->ofType($kind)
            ->withCount($kind === Album::TYPE_VIDEO ? 'videos' : 'photos')
            /**
             * The id breaks ties. Without it two albums sharing an
             * event date have no defined order, so one can appear on
             * both pages and the other on neither.
             */
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('pages.album.index', [
            'albums' => $albums,
            /**
             * The chips list what can be opened, which may be nothing
             * at all on a site with no albums yet; the label is passed
             * separately so the page can still name itself.
             */
            'kinds' => $available,
            'kind' => $kind,
            'kindLabel' => self::KINDS[$kind],
        ]);
    }

    /**
     * Display a single album: its photographs, or its videos.
     *
     * The check comes before the album is looked at, so its title never
     * reaches somebody who may not open it - and nor do the YouTube
     * identifiers, which are what the videos actually are: most are
     * unlisted, viewable by anyone holding one.
     */
    public function show(Album $album): View
    {
        if (! Auth::user()?->isChurchMember()) {
            return $this->signInPage();
        }

        abort_unless(Album::query()->visible()->whereKey($album->getKey())->exists(), 404);

        if ($album->holdsVideos()) {
            return view('pages.album.videos', [
                'album' => $album,
                'videos' => $album->videos()->paginate(24),
            ]);
        }

        return view('pages.album.show', [
            'album' => $album,
            'photos' => $album->photos()->paginate(24),
        ]);
    }

    /**
     * The sign-in notice standing in for the section, named by the
     * section rather than by whichever album was asked for.
     */
    private function signInPage(): View
    {
        return view('pages.members-only', [
            'kicker' => '주는교회의 순간들 · Moments',
            'title' => '앨범',
            'body' => '예배와 교회 행사의 사진과 영상에는 성도의 얼굴이 담겨 있어 성도에게만 공개됩니다.',
        ]);
    }
}
