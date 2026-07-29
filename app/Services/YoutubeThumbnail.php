<?php

namespace App\Services;

use App\Filament\Support\SaveUploadsAsWebp;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Downloads the highest-quality YouTube thumbnail for a video and
 * stores it on the media disk as youtube/thumbnail-{upload date-time}.
 *
 * The date-time comes from the video's publish timestamp converted to
 * Brisbane time, so the object key is deterministic and repeat runs
 * for the same video reuse the existing object.
 */
class YoutubeThumbnail
{
    /**
     * Store the best available thumbnail and return its path.
     */
    public static function store(string $videoId, ?CarbonInterface $publishedAt = null): ?string
    {
        $publishedAt ??= static::publishedAt($videoId);

        if ($publishedAt === null) {
            return null;
        }

        $path = 'youtube/thumbnail-'.$publishedAt->copy()->setTimezone('Australia/Brisbane')->format('Y-m-d-His');
        $disk = Storage::disk(config('filesystems.media'));

        if ($disk->exists($path)) {
            return $path;
        }

        $binary = static::bestThumbnail($videoId);

        if ($binary === null) {
            return null;
        }

        $disk->put($path, SaveUploadsAsWebp::toWebp($binary) ?? $binary, [
            'ContentType' => 'image/webp',
            'CacheControl' => 'public, max-age=31536000, immutable',
        ]);

        return $path;
    }

    /**
     * The video's publish date-time, scraped from the watch page.
     */
    public static function publishedAt(string $videoId): ?Carbon
    {
        $response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])
            ->get('https://www.youtube.com/watch', ['v' => $videoId]);

        if (! $response->successful()) {
            return null;
        }

        if (! preg_match('/itemprop="datePublished" content="([^"]+)"/', $response->body(), $matches)
            && ! preg_match('/"publishDate":"([^"]+)"/', $response->body(), $matches)) {
            return null;
        }

        return Carbon::parse($matches[1]);
    }

    /**
     * The highest-resolution thumbnail binary YouTube offers.
     */
    private static function bestThumbnail(string $videoId): ?string
    {
        foreach (['maxresdefault', 'sddefault', 'hqdefault'] as $variant) {
            $response = Http::get("https://i.ytimg.com/vi/{$videoId}/{$variant}.jpg");

            if ($response->successful() && strlen($response->body()) > 2048) {
                return $response->body();
            }
        }

        return null;
    }
}
