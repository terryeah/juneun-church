<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\View\View;

/**
 * Handles the photo gallery (갤러리).
 */
class GalleryController extends Controller
{
    /**
     * Display the album grid.
     */
    public function index(): View
    {
        $albums = Album::query()
            ->visible()
            ->withCount('photos')
            ->orderByDesc('event_date')
            ->paginate(12);

        return view('pages.gallery.index', compact('albums'));
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
