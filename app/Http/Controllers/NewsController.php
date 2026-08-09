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
            ->visible()
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->paginate(10);

        return view('pages.news.index', compact('announcements'));
    }

    /**
     * Display a single announcement.
     *
     * A 성도 전용 notice 404s for a guest rather than 403s: a 403 would
     * confirm that a notice lives at that slug, and the slug carries the
     * title, so the URL alone would leak what is meant to be private.
     */
    public function show(Announcement $announcement): View
    {
        abort_unless(Announcement::query()->visible()->whereKey($announcement->getKey())->exists(), 404);

        return view('pages.news.show', compact('announcement'));
    }
}
