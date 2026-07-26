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
            ->published()
            ->withCount('photos')
            ->orderByDesc('event_date')
            ->paginate(12);

        return view('pages.gallery.index', compact('albums'));
    }

    /**
     * Display a single album with its photo grid.
     */
    public function show(Album $album): View
    {
        abort_unless($album->is_published, 404);

        $photos = $album->photos()->paginate(24);

        return view('pages.gallery.show', compact('album', 'photos'));
    }
}
