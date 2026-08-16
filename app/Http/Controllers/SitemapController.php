<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * Serves the XML sitemap for search engines.
 *
 * Only the pages a crawler can actually read are listed. 교회 소식,
 * 교회 행사, 자료실, 헌금 and 앨범 are 성도 전용 as whole pages now: a
 * crawler is a guest, so all it would find at those addresses is a
 * sign-in notice, which those pages answer with noindex. Listing them
 * here would ask to have that notice indexed. The individual notices
 * and albums are gone with them - their URLs carry their titles, and
 * this document is written for crawlers and cacheable by the CDN in
 * front of the site.
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
            ['loc' => route('location'), 'priority' => '0.8'],
            ['loc' => route('people'), 'priority' => '0.5'],
        ]);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            .$urls->map(fn (array $url): string => '<url><loc>'.e($url['loc']).'</loc>'
                .'<priority>'.$url['priority'].'</priority></url>')->implode('')
            .'</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }
}
