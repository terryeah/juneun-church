<?php

namespace App\Http\Controllers;

use App\Models\Sermon;
use Illuminate\View\View;

/**
 * Handles the worship information page (예배 안내).
 */
class WorshipController extends Controller
{
    /**
     * Display service times, the featured sermon and the sermon archive.
     */
    public function __invoke(): View
    {
        /** Most recent recording, featured above the archive grid */
        $featured = Sermon::query()
            ->published()
            ->with('serviceType')
            ->orderByDesc('sermon_date')
            ->first();

        /** Remaining recordings, newest first */
        $sermons = Sermon::query()
            ->published()
            ->with('serviceType')
            ->when($featured, fn ($query) => $query->whereKeyNot($featured->id))
            ->orderByDesc('sermon_date')
            ->paginate(9);

        return view('pages.worship', compact('featured', 'sermons'));
    }
}
