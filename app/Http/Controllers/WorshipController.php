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
     * Display the archive of recent worship recordings.
     *
     * The archive always aims to show six recordings, three per row on
     * desktop. The latest recording is excluded because it is featured
     * on the home page, unless skipping it would leave fewer than six.
     */
    public function __invoke(): View
    {
        $sermons = Sermon::query()
            ->published()
            ->with('serviceType')
            ->orderByDesc('sermon_date')
            ->take(7)
            ->get();

        $sermons = $sermons->count() > 6
            ? $sermons->skip(1)->values()
            : $sermons->take(6);

        return view('pages.worship', compact('sermons'));
    }
}
