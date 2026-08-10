<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Handles the photo gallery (갤러리).
 */
class GalleryController extends Controller
{
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
     * Display the album grid.
     *
     * The filter narrows what is already visible rather than widening
     * it - scopeVisible still runs first, so asking for 성도 전용 as a
     * guest returns nothing rather than everything.
     */
    public function index(Request $request): View
    {
        $filter = array_key_exists((string) $request->query('visibility'), self::FILTERS)
            ? (string) $request->query('visibility')
            : 'all';

        $albums = Album::query()
            ->visible()
            ->when($filter === 'members', fn ($query) => $query->where('is_members_only', true))
            ->when($filter === 'public', fn ($query) => $query->where('is_members_only', false))
            ->withCount('photos')
            ->orderByDesc('event_date')
            ->paginate(12)
            ->withQueryString();

        return view('pages.gallery.index', [
            'albums' => $albums,
            'filters' => self::FILTERS,
            'filter' => $filter,
        ]);
    }

    /**
     * Display a single album with its photo grid.
     *
     * A 성도 전용 album 404s for a guest rather than 403s: a 403 would
     * confirm that an album lives at that slug, and the slug carries the
     * title, so the URL alone would leak what is meant to be private.
     */
    public function show(Album $album): View
    {
        abort_unless(Album::query()->visible()->whereKey($album->getKey())->exists(), 404);

        $photos = $album->photos()->paginate(24);

        return view('pages.gallery.show', compact('album', 'photos'));
    }
}
