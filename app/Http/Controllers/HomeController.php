<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\Photo;
use App\Models\Sermon;
use Illuminate\View\View;

/**
 * Handles the landing page (홈).
 */
class HomeController extends Controller
{
    /**
     * Display the home page with the latest content from each section.
     */
    public function __invoke(): View
    {
        /** Latest three announcements with pinned items first */
        $announcements = Announcement::query()
            ->published()
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        /** Most recent worship recording */
        $latestSermon = Sermon::query()
            ->published()
            ->with('serviceType')
            ->orderByDesc('sermon_date')
            ->first();

        /** Next three upcoming events */
        $upcomingEvents = Event::query()
            ->published()
            ->whereDate('event_date', '>=', today())
            ->orderBy('event_date')
            ->limit(3)
            ->get();

        /** Three recent photos for the full-bleed gallery preview band */
        $recentPhotos = Photo::query()
            ->whereHas('album', fn ($query) => $query->where('is_published', true))
            ->latest()
            ->limit(3)
            ->get();

        return view('pages.home', compact('announcements', 'latestSermon', 'upcomingEvents', 'recentPhotos'));
    }
}
