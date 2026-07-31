<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\View\View;

/**
 * Handles the church events page (교회 행사).
 */
class EventController extends Controller
{
    /**
     * Display upcoming events grouped by month.
     */
    public function __invoke(): View
    {
        /** Upcoming and current-month events grouped for monthly tables */
        $eventsByMonth = Event::query()
            ->published()
            ->where(function ($query) {
                $query->whereDate('event_date', '>=', today()->startOfMonth())
                    ->orWhereDate('end_date', '>=', today()->startOfMonth());
            })
            ->orderBy('event_date')
            ->get()
            ->groupBy(fn (Event $event) => $event->event_date->translatedFormat('Y년 n월'));

        return view('pages.events', compact('eventsByMonth'));
    }
}
