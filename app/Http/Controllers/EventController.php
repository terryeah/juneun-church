<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Handles the church events page (교회 행사).
 *
 * The page is 성도 전용 as a whole: the church's gatherings say where
 * the congregation will be and when, which is the congregation's own
 * business rather than the street's.
 */
class EventController extends Controller
{
    /**
     * Display upcoming events grouped by month.
     */
    public function __invoke(): View
    {
        /** Asked before anything is fetched, so a guest's response never holds the diary. */
        if (! Auth::user()?->isChurchMember()) {
            return view('pages.members-only', [
                'kicker' => '함께하는 시간 · Events',
                'title' => '교회 행사',
                'body' => '교회 행사에는 성도가 모이는 시간과 장소가 담겨 있어 성도에게만 공개됩니다.',
            ]);
        }

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
