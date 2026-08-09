<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Announcement;
use Illuminate\Http\Response;

/**
 * Serves the XML sitemap for search engines.
 *
 * Lists every public page plus published announcements and albums so
 * crawlers discover new content without a plugin. 성도 전용 notices are
 * left out whoever is signed in, so the URL of a private notice is
 * never handed to a crawler or cached into the CDN's copy.
 */
class SitemapController extends Controller
{
    /**
     * Build and return the sitemap.
     */
    public function __invoke(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('worship'), 'priority' => '0.8'],
            ['loc' => route('events'), 'priority' => '0.8'],
            ['loc' => route('news.index'), 'priority' => '0.8'],
            ['loc' => route('downloads'), 'priority' => '0.6'],
            ['loc' => route('gallery.index'), 'priority' => '0.6'],
            ['loc' => route('people'), 'priority' => '0.5'],
            ['loc' => route('giving'), 'priority' => '0.5'],
            ['loc' => route('location'), 'priority' => '0.8'],
        ]);

        $urls = $urls
            ->merge(Announcement::query()->visible(isMember: false)->latest('published_at')->limit(200)->get()
                ->map(fn (Announcement $a) => [
                    'loc' => route('news.show', $a),
                    'lastmod' => $a->updated_at?->toDateString(),
                    'priority' => '0.6',
                ]))
            ->merge(Album::query()->where('is_published', true)->latest('event_date')->limit(200)->get()
                ->map(fn (Album $album) => [
                    'loc' => route('gallery.show', $album),
                    'lastmod' => $album->updated_at?->toDateString(),
                    'priority' => '0.4',
                ]));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            .$urls->map(function (array $url): string {
                $entry = '<url><loc>'.e($url['loc']).'</loc>';

                if (! empty($url['lastmod'])) {
                    $entry .= '<lastmod>'.$url['lastmod'].'</lastmod>';
                }

                return $entry.'<priority>'.$url['priority'].'</priority></url>';
            })->implode('')
            .'</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }
}
