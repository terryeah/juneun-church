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
 * Imports the church's recent public Instagram photo posts as albums.
 *
 * Every photo post becomes its own album named from the post caption,
 * with carousel posts contributing all of their images, so the gallery
 * reflects church events rather than a single Instagram dump. Video
 * posts and reels are skipped. Instagram's anonymous endpoint exposes
 * only the twelve most recent posts; albums are keyed by post shortcode
 * so repeated runs never duplicate, and titles or visibility curated in
 * the admin panel afterwards are preserved.
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
    protected $description = "Import the church's recent public Instagram photo posts as gallery albums";

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

        $imported = 0;

        foreach ($posts as $node) {
            $slug = 'ig-'.Str::lower($node['shortcode']);

            if (Album::query()->where('slug', $slug)->exists()) {
                continue;
            }

            $takenAt = isset($node['taken_at_timestamp'])
                ? Carbon::createFromTimestamp($node['taken_at_timestamp'])
                : today();

            $album = Album::query()->create([
                'title' => $this->titleFor($node),
                'slug' => $slug,
                'description' => $this->captionFor($node),
                'event_date' => $takenAt,
                'is_published' => true,
            ]);

            foreach ($this->imageUrls($node) as $index => $url) {
                $image = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);

                if (! $image->successful()) {
                    $this->warn("Skipped an image of {$node['shortcode']}: download failed.");

                    continue;
                }

                $filename = $node['shortcode'].'-'.($index + 1).'.jpg';
                $path = 'gallery/instagram/'.$filename;
                Storage::disk(config('filesystems.media'))->put($path, $image->body());

                Photo::query()->create([
                    'album_id' => $album->id,
                    'filename' => $filename,
                    'original_filename' => $filename,
                    'path' => $path,
                    'file_size' => strlen($image->body()),
                    'sort_order' => ($index + 1) * 10,
                ]);
            }

            if ($album->photos()->count() === 0) {
                $album->delete();

                continue;
            }

            $album->update(['cover_photo_path' => $album->photos()->first()?->path]);
            $imported++;
        }

        $this->info("Imported {$imported} new album(s) from Instagram.");

        return self::SUCCESS;
    }

    /**
     * Every image URL of a post: the main image plus carousel children.
     *
     * @return array<int, string>
     */
    private function imageUrls(array $node): array
    {
        $children = collect($node['edge_sidecar_to_children']['edges'] ?? [])
            ->pluck('node')
            ->reject(fn (array $child) => $child['is_video'] ?? false)
            ->pluck('display_url');

        return $children->isNotEmpty()
            ? $children->values()->all()
            : [$node['display_url']];
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
     * Album title: the first meaningful caption line without hashtags.
     */
    private function titleFor(array $node): string
    {
        $caption = $this->captionFor($node);

        if (blank($caption)) {
            return 'Instagram '.$node['shortcode'];
        }

        return Str::of($caption)->limit(40)->value();
    }

    /**
     * Post caption without hashtags, squashed to a single line.
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
