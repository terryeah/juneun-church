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
     * The latest recording is excluded because it is featured on the
     * home page; the archive shows the six recordings before it.
     */
    public function __invoke(): View
    {
        $sermons = Sermon::query()
            ->published()
            ->with('serviceType')
            ->orderByDesc('sermon_date')
            ->skip(1)
            ->take(6)
            ->get();

        return view('pages.worship', compact('sermons'));
    }
}
