<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\View\View;

/**
 * Handles announcement display (교회 소식).
 */
class NewsController extends Controller
{
    /**
     * Display a listing of published announcements.
     */
    public function index(): View
    {
        /** Fetch announcements with pinned items first */
        $announcements = Announcement::query()
            ->published()
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->paginate(10);

        return view('pages.news.index', compact('announcements'));
    }

    /**
     * Display a single announcement.
     */
    public function show(Announcement $announcement): View
    {
        abort_unless(Announcement::query()->published()->whereKey($announcement->getKey())->exists(), 404);

        return view('pages.news.show', compact('announcement'));
    }
}
