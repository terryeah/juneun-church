<?php

namespace App\Http\Controllers;

use App\Models\Bulletin;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Handles the weekly bulletins page (주보).
 */
class BulletinController extends Controller
{
    /**
     * Display bulletins sorted by publish date.
     *
     * A guest is also told whether anything is being held back, so the
     * page only offers to sign them in when there is something behind
     * the login rather than on every visit.
     */
    public function __invoke(): View
    {
        $bulletins = Bulletin::query()
            ->visible()
            ->orderByDesc('published_at')
            ->paginate(20);

        $hasRestricted = Auth::guest()
            && Bulletin::query()->where('is_members_only', true)->exists();

        return view('pages.bulletins', compact('bulletins', 'hasRestricted'));
    }
}
