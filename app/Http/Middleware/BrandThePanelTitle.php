<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Puts the church's name in front of the panel's browser tab title.
 *
 * The public site reads '브리즈번 주는교회 · 헌금', and the panel read
 * '성도 수정 - 브리즈번 주는교회'. Filament writes that line into its
 * base layout with the order fixed, and offers no setting for it, so
 * the choice was between copying that whole layout into the app - 167
 * lines of head, scripts and plugin render hooks that would then go
 * quietly stale on every Filament release - and correcting the one line
 * afterwards. This is the one line.
 *
 * If a future Filament writes the title some other way, the pattern
 * stops matching and the title is simply left as Filament wrote it.
 */
class BrandThePanelTitle
{
    /**
     * Rewrite the title of a rendered panel page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return $response;
        }

        $content = $response->getContent();

        if ($content === false) {
            return $response;
        }

        $rewritten = $this->rewrite($content, (string) config('app.name'));

        return $rewritten === null ? $response : $response->setContent($rewritten);
    }

    /**
     * Swap the two halves of the first title, or leave the page alone.
     *
     * Only the first match is replaced, and the search stops there, so
     * this reads the opening of the document rather than all of it.
     */
    private function rewrite(string $content, string $brand): ?string
    {
        return preg_replace_callback(
            '#<title>(.*?)</title>#s',
            function (array $matches) use ($brand): string {
                $written = trim((string) preg_replace('/\s+/', ' ', $matches[1]));
                $suffix = ' - '.$brand;

                if ($written === $brand || ! str_ends_with($written, $suffix)) {
                    return $matches[0];
                }

                $page = substr($written, 0, -strlen($suffix));

                return '<title>'.e($brand.' · '.$page).'</title>';
            },
            $content,
            limit: 1,
        );
    }
}
