<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Photo;
use App\Models\Sermon;
use App\Models\SiteSetting;
use Illuminate\Support\Collection;
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
        /** Latest four announcements with pinned items first */
        $announcements = Announcement::query()
            ->visible()
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        /** Most recent worship recording */
        $latestSermon = Sermon::query()
            ->published()
            ->with('serviceType')
            ->orderByDesc('sermon_date')
            ->first();

        /** Photos for the sliding gallery preview band */
        $recentPhotos = $this->sliderPhotos();

        /**
         * The 하이라이트 section is whichever 교회 소식 carries the flag.
         * 교회 소식 is a 성도 전용 page now, so the flag is the whole
         * decision: an editor ticking 하이라이트 is choosing to put that
         * notice's opening lines on a page anybody can read, and the
         * band is the same for a guest and a 성도 alike.
         */
        $highlight = Announcement::query()
            ->visible()
            ->where('is_highlighted', true)
            ->first();

        /** The hero photograph is chosen in 사이트 설정 by gallery filename */
        $heroPhoto = $this->featuredPhoto('home_hero_photo');

        return view('pages.home', compact('announcements', 'latestSermon', 'recentPhotos', 'heroPhoto', 'highlight'));
    }

    /**
     * The ten photos shown in the home slider.
     *
     * Hand-picked photos only. 앨범 is a 성도 전용 page now, so an
     * album's own audience says nothing about who may see a picture on
     * the front page; the only thing that says it is 홈 슬라이더에 표시
     * beside the photograph itself. An admin ticking that box is
     * deciding this one picture may be seen by anyone.
     *
     * The band used to top itself up round-robin from every published
     * album when fewer than ten were pinned. That filler was chosen by
     * nobody, and most of the church's photographs now sit in albums
     * kept to the 교적, so it would have carried them onto a public
     * page. Nothing pinned means no band, which the view already draws
     * correctly.
     *
     * @return Collection<int, Photo>
     */
    private function sliderPhotos(): Collection
    {
        return Photo::query()
            ->whereHas('album', fn ($query) => $query->where('is_published', true))
            ->where('featured_in_slider', true)
            ->latest()
            ->limit(10)
            /** Each photo links back to its album, so fetch them together. */
            ->with('album')
            ->get();
    }

    /**
     * Resolve a featured photo named in 사이트 설정.
     *
     * The setting stores a gallery photo's filename, so admins can swap
     * the image by putting a new filename in 사이트 설정.
     *
     * @param  string  $settingKey  Setting holding the photo filename
     */
    private function featuredPhoto(string $settingKey): ?Photo
    {
        $filename = trim((string) SiteSetting::get($settingKey));

        return $filename === ''
            ? null
            : Photo::query()->where('filename', $filename)->first();
    }
}
