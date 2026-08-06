<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Removes deleted or replaced media from Cloudflare's edge cache.
 *
 * Uploads to R2 are stamped with a one-year immutable Cache-Control
 * header, and media.juneun.com is cached at the edge honouring it. So
 * deleting a photo or a bulletin at origin no longer takes it down:
 * the old object keeps being served from the same URL to anyone who
 * has it, for up to a year, until somebody purges it.
 *
 * Purging is best effort by design. A church losing its CDN token, or
 * Cloudflare being unreachable, must never stop an administrator
 * deleting a photograph a family asked to have removed - so every
 * failure here is logged and swallowed. Local and test environments
 * carry no token at all and skip the call entirely.
 *
 * Requires an API token with Zone > Cache Purge on the zone, alongside
 * the zone identifier, both from config/services.php.
 */
class CloudflareCachePurger
{
    /**
     * Cloudflare accepts at most thirty URLs per purge-by-URL call,
     * and deleting one album cascades to two objects per photograph.
     */
    private const URLS_PER_CALL = 30;

    /**
     * URLs collected during this request, awaiting the flush.
     *
     * @var list<string>
     */
    private static array $pending = [];

    /**
     * Whether the Cloudflare credentials have been configured.
     */
    public static function isConfigured(): bool
    {
        return filled(config('services.cloudflare.api_token'))
            && filled(config('services.cloudflare.zone_id'));
    }

    /**
     * Queue the given media-disk paths to be purged from the edge once
     * the response has been sent.
     *
     * Deferring keeps the CDN off the critical path: deleting an album
     * of a hundred photographs would otherwise make two hundred URLs'
     * worth of HTTP calls while the administrator waits.
     *
     * @param  array<int, ?string>  $paths
     */
    public static function forget(array $paths): void
    {
        if (! static::isConfigured()) {
            return;
        }

        $disk = Storage::disk(config('filesystems.media'));

        $urls = array_map(
            fn (string $path): string => $disk->url($path),
            array_filter($paths, 'filled'),
        );

        if ($urls === []) {
            return;
        }

        if (static::$pending === []) {
            defer(static::flush(...));
        }

        static::$pending = [...static::$pending, ...$urls];
    }

    /**
     * Send everything queued so far, thirty URLs at a time.
     */
    public static function flush(): void
    {
        $urls = array_values(array_unique(static::$pending));

        static::$pending = [];

        foreach (array_chunk($urls, self::URLS_PER_CALL) as $batch) {
            static::purge($batch);
        }
    }

    /**
     * Purge one batch, reporting rather than raising any failure.
     *
     * @param  list<string>  $urls
     */
    private static function purge(array $urls): void
    {
        try {
            $response = Http::withToken(config('services.cloudflare.api_token'))
                ->post(
                    sprintf(
                        'https://api.cloudflare.com/client/v4/zones/%s/purge_cache',
                        config('services.cloudflare.zone_id'),
                    ),
                    ['files' => $urls],
                );

            if ($response->successful() && $response->json('success') === true) {
                return;
            }

            Log::warning('Cloudflare cache purge refused', [
                'status' => $response->status(),
                'errors' => $response->json('errors'),
                'urls' => $urls,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Cloudflare cache purge failed', [
                'message' => $exception->getMessage(),
                'urls' => $urls,
            ]);
        }
    }
}
