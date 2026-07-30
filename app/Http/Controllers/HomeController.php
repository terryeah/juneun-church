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

        /** Photos for the sliding gallery preview band */
        $recentPhotos = $this->sliderPhotos();

        /** Featured photographs are chosen in 사이트 설정 by gallery filename */
        $heroPhoto = $this->featuredPhoto('home_hero_photo', 'DZ2By_Lk1xC-6.webp');
        $mealPhoto = $this->featuredPhoto('home_meal_photo', 'Dae_8EbzX4z-1.webp');

        return view('pages.home', compact('announcements', 'latestSermon', 'upcomingEvents', 'recentPhotos', 'heroPhoto', 'mealPhoto'));
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
     * @return \Illuminate\Support\Collection<int, Photo>
     */
    private function sliderPhotos(): \Illuminate\Support\Collection
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

        $queues = \App\Models\Album::query()
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
     * Resolve a featured photo from a site-setting filename.
     *
     * The setting stores a gallery photo's filename, so admins can
     * swap the image by uploading a photo and putting its filename in
     * 사이트 설정. Falls back to the given default filename.
     */
    private function featuredPhoto(string $settingKey, string $default): ?Photo
    {
        $filename = \App\Models\SiteSetting::get($settingKey) ?: $default;

        return Photo::query()->where('filename', trim((string) $filename))->first();
    }
}
