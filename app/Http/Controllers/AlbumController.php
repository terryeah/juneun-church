<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Handles 앨범: the church's photo albums and video albums.
 *
 * The two kinds sit side by side rather than in separate sections, so
 * one address holds everything the church has recorded and the chips
 * choose which kind is on show - the same shape the panel uses, where
 * 앨범 is one screen and 사진 and 동영상 are what goes in them.
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
     * The audience filters offered to a signed-in 성도.
     *
     * A guest is never shown these: everything they can reach is open
     * already, so every chip would say the same thing.
     */
    private const FILTERS = [
        'all' => '전체',
        'members' => '성도 전용',
        'public' => '모두 공개',
    ];

    /**
     * Display the album grid for one kind.
     *
     * The filter narrows what is already visible rather than widening
     * it - scopeVisible still runs first, so asking for 성도 전용
     * without being one returns nothing rather than everything.
     */
    public function index(Request $request): View
    {
        /**
         * Read as a string rather than cast to one: ?kind[]=video
         * arrives as an array, and casting an array to string is a
         * PHP error rather than a value - an unauthenticated 500 on a
         * public page. string() gives an empty string instead, which
         * falls through to the default like any other nonsense.
         */
        $kind = array_key_exists($request->string('kind')->value(), self::KINDS)
            ? $request->string('kind')->value()
            : Album::TYPE_PHOTO;

        $filter = array_key_exists($request->string('visibility')->value(), self::FILTERS)
            ? $request->string('visibility')->value()
            : 'all';

        $albums = Album::query()
            ->visible()
            ->ofType($kind)
            ->when($filter === 'members', fn ($query) => $query->where('is_members_only', true))
            ->when($filter === 'public', fn ($query) => $query->where('is_members_only', false))
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

        /**
         * Only offer a kind the reader can actually open. Every video
         * album is 성도 전용 today, so a guest was being handed a 동영상
         * chip that led to an empty page - a door painted on a wall.
         */
        $available = Album::query()->visible()->distinct()->pluck('type')->all();

        return view('pages.album.index', [
            'albums' => $albums,
            'kinds' => array_intersect_key(
                self::KINDS,
                array_flip([...$available, $kind]),
            ),
            'kind' => $kind,
            'filters' => self::FILTERS,
            'filter' => $filter,
        ]);
    }

    /**
     * Display a single album: its photographs, or its videos.
     *
     * A 성도 전용 album 404s for a guest rather than 403s: a 403 would
     * confirm that an album lives at that slug, and the slug carries the
     * title, so the URL alone would leak what is meant to be private.
     *
     * That matters more for the videos than it ever did for the photos.
     * Most of them are unlisted on YouTube, viewable by anyone holding
     * the link, so their identifiers must not reach a page a stranger
     * can open - and here they never leave the query.
     */
    public function show(Album $album): View
    {
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
}
