<?php

namespace App\Http\Controllers;

use App\Models\Bulletin;
use Illuminate\View\View;

/**
 * Handles the weekly bulletins page (주보).
 */
class BulletinController extends Controller
{
    /**
     * Display bulletins sorted by publish date.
     */
    public function __invoke(): View
    {
        $bulletins = Bulletin::query()
            ->visible()
            ->orderByDesc('published_at')
            ->paginate(20);

        return view('pages.bulletins', compact('bulletins'));
    }
}
