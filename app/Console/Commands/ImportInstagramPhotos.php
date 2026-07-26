<?php

namespace App\Console\Commands;

use App\Models\Album;
use App\Models\Photo;
use App\Models\SiteSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Imports the church's recent public Instagram photos into the gallery.
 *
 * Instagram's anonymous web profile endpoint exposes only the twelve most
 * recent posts; video posts and reels are skipped. Photos are stored on
 * the media disk inside an "Instagram" album and already-imported posts
 * are recognised by shortcode, so the command can run repeatedly (for
 * example from the scheduler) without creating duplicates.
 */
class ImportInstagramPhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instagram:import {--handle= : Instagram username, defaults to the instagram_url site setting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Import the church's recent public Instagram photos into the gallery";

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $handle = $this->option('handle') ?: $this->handleFromSettings();

        if (blank($handle)) {
            $this->error('No Instagram handle configured. Set the instagram_url site setting or pass --handle.');

            return self::FAILURE;
        }

        $response = Http::withHeaders([
            'x-ig-app-id' => '936619743392459',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36',
        ])->get('https://i.instagram.com/api/v1/users/web_profile_info/', ['username' => $handle]);

        if (! $response->successful()) {
            $this->error("Instagram profile request failed with status {$response->status()}.");

            return self::FAILURE;
        }

        $edges = $response->json('data.user.edge_owner_to_timeline_media.edges', []);
        $posts = collect($edges)->pluck('node')->reject(fn (array $node) => $node['is_video']);

        if ($posts->isEmpty()) {
            $this->warn('No public photo posts found.');

            return self::SUCCESS;
        }

        $album = Album::query()->firstOrCreate(
            ['slug' => 'instagram'],
            [
                'title' => 'Instagram',
                'description' => '교회 인스타그램(@'.$handle.')의 최근 사진입니다.',
                'event_date' => today(),
                'is_published' => true,
            ],
        );

        $imported = 0;

        foreach ($posts as $node) {
            $filename = $node['shortcode'].'.jpg';

            if ($album->photos()->where('filename', $filename)->exists()) {
                continue;
            }

            $image = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($node['display_url']);

            if (! $image->successful()) {
                $this->warn("Skipped {$node['shortcode']}: image download failed.");

                continue;
            }

            $path = 'gallery/instagram/'.$filename;
            Storage::disk(config('filesystems.media'))->put($path, $image->body());

            Photo::query()->create([
                'album_id' => $album->id,
                'filename' => $filename,
                'original_filename' => $filename,
                'path' => $path,
                'width' => $node['dimensions']['width'] ?? null,
                'height' => $node['dimensions']['height'] ?? null,
                'file_size' => strlen($image->body()),
                'caption' => $this->captionFor($node),
                'sort_order' => $this->sortOrderFor($node),
            ]);

            $imported++;
        }

        $latest = $posts->max('taken_at_timestamp');

        $album->update([
            'event_date' => $latest ? Carbon::createFromTimestamp($latest) : $album->event_date,
            'cover_photo_path' => $album->photos()->first()?->path,
        ]);

        $this->info("Imported {$imported} new photo(s) into the Instagram album.");

        return self::SUCCESS;
    }

    /**
     * Derive the Instagram username from the instagram_url site setting.
     */
    private function handleFromSettings(): ?string
    {
        $url = SiteSetting::get('instagram_url');

        return $url ? Str::of($url)->after('instagram.com/')->trim('/')->value() : null;
    }

    /**
     * Sort key that places newer posts first in the unsigned column.
     *
     * The photo relation orders ascending, so the post timestamp is
     * subtracted from a fixed future instant (2100-01-01 UTC).
     */
    private function sortOrderFor(array $node): int
    {
        return max(0, 4102444800 - ($node['taken_at_timestamp'] ?? 0));
    }

    /**
     * First line of the post caption, without hashtags, capped for storage.
     */
    private function captionFor(array $node): ?string
    {
        $text = $node['edge_media_to_caption']['edges'][0]['node']['text'] ?? null;

        if (blank($text)) {
            return null;
        }

        return Str::of($text)
            ->replaceMatches('/#[^\s#]+/u', '')
            ->squish()
            ->limit(200)
            ->value() ?: null;
    }
}
