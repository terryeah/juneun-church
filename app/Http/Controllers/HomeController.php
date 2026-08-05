<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Announcement;
use App\Models\Event;
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
            ->published()
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

        /** Next three upcoming events */
        $upcomingEvents = Event::query()
            ->published()
            ->whereDate('event_date', '>=', today())
            ->orderBy('event_date')
            ->limit(3)
            ->get();

        /** Photos for the sliding gallery preview band */
        $recentPhotos = $this->sliderPhotos();

        /** Featured photographs are chosen in 사이트 설정 by gallery filename */
        $heroPhoto = $this->featuredPhoto('home_hero_photo');
        $highlightPhoto = $this->featuredPhoto('highlight_photo', 'highlight_link_album');

        return view('pages.home', compact('announcements', 'latestSermon', 'upcomingEvents', 'recentPhotos', 'heroPhoto', 'highlightPhoto'));
    }

    /**
     * The ten photos shown in the home slider.
     *
     * Hand-picked photos (홈 슬라이더에 표시) come first. Any remaining
     * slots are filled round-robin across the published albums - the
     * newest photo of each album, then each album's next-newest, and
     * so on - so the band always shows ten photos from a spread of
     * events rather than one album's dump.
     *
     * @return Collection<int, Photo>
     */
    private function sliderPhotos(): Collection
    {
        $picked = Photo::query()
            ->whereHas('album', fn ($query) => $query->where('is_published', true))
            ->where('featured_in_slider', true)
            ->latest()
            ->limit(10)
            ->get();

        if ($picked->count() >= 10) {
            return $picked->values();
        }

        $queues = Album::query()
            ->where('is_published', true)
            ->orderByDesc('event_date')
            ->with(['photos' => fn ($query) => $query->latest()])
            ->get()
            ->map(fn ($album) => $album->photos->whereNotIn('id', $picked->pluck('id'))->values())
            ->filter(fn ($photos) => $photos->isNotEmpty())
            ->values();

        $slider = $picked->values();

        for ($round = 0; $slider->count() < 10; $round++) {
            $added = false;

            foreach ($queues as $queue) {
                if ($slider->count() >= 10) {
                    break;
                }

                if ($queue->has($round)) {
                    $slider->push($queue[$round]);
                    $added = true;
                }
            }

            if (! $added) {
                break;
            }
        }

        return $slider;
    }

    /**
     * Resolve a featured photo named in 사이트 설정.
     *
     * The setting stores a gallery photo's filename, so admins can
     * swap the image by putting a new filename in 사이트 설정. A
     * replacement upload arrives under a fresh filename though, which
     * leaves the stored one pointing at nothing, so where the section
     * also names an album the first photo of that album stands in -
     * the same album-keyed approach the 오시는 길 notice uses.
     *
     * @param  string  $settingKey  Setting holding the photo filename
     * @param  ?string  $albumSettingKey  Setting holding the album slug to fall back to
     */
    private function featuredPhoto(string $settingKey, ?string $albumSettingKey = null): ?Photo
    {
        $filename = trim((string) SiteSetting::get($settingKey));

        $photo = $filename === ''
            ? null
            : Photo::query()->where('filename', $filename)->first();

        if ($photo !== null || $albumSettingKey === null) {
            return $photo;
        }

        $slug = trim((string) SiteSetting::get($albumSettingKey));

        return $slug === ''
            ? null
            : Album::query()->where('slug', $slug)->first()?->photos()->first();
    }
}
