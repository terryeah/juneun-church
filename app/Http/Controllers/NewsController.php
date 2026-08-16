<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Handles announcement display (교회 소식).
 *
 * The section is 성도 전용 as a whole. Church news is mostly about the
 * people in it - who has joined, who is ill, who is serving where - so
 * the page is closed rather than each notice being marked one by one.
 */
class NewsController extends Controller
{
    /**
     * Display a listing of published announcements.
     */
    public function index(): View
    {
        if (! Auth::user()?->isChurchMember()) {
            return $this->signInPage();
        }

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
     * The check comes before the record is looked at, so the notice's own
     * title never reaches somebody who may not read it. The home page
     * still lists news titles to everyone, and a 404 from there would be
     * a dead end where a sign-in notice is a next step.
     */
    public function show(Announcement $announcement): View
    {
        if (! Auth::user()?->isChurchMember()) {
            return $this->signInPage();
        }

        abort_unless(Announcement::query()->visible()->whereKey($announcement->getKey())->exists(), 404);

        return view('pages.news.show', compact('announcement'));
    }

    /**
     * The sign-in notice standing in for the section, named by the
     * section rather than by whichever notice was asked for.
     */
    private function signInPage(): View
    {
        return view('pages.members-only', [
            'kicker' => '함께 나누는 소식 · News',
            'title' => '교회 소식',
        ]);
    }
}
