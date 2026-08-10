<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records which pages a signed-in person opened.
 *
 * Added at the church's request while the site is new, so there is an
 * audit trail of who went where. It follows signed-in accounts only - a
 * visitor who never identifies themselves is not tracked, and nothing
 * here tries to.
 *
 * Every page opening is kept, refreshes and repeat visits included.
 * What is left out is not page openings at all: Livewire's constant
 * background traffic, asset requests, and links the browser fetched by
 * itself before anyone clicked. Recording those would not make the
 * trail more complete, it would bury it - and a prefetch would credit
 * someone with a page they never opened.
 */
class LogPageVisits
{
    /**
     * The query string keys worth keeping.
     *
     * These four say which part of a page was open: the week of 헌금
     * 내역, the tab of 자료실, the filter on 갤러리, and how far down a
     * list someone had paged.
     *
     * Everything else is dropped, and the list is a whitelist rather
     * than a list of exclusions on purpose. Filament keeps a table's
     * search box and filters in the query string, so a 성도's phone
     * number typed into 교적 would otherwise be copied into the audit
     * trail - where it would outlive the 교적 record itself, sit in
     * every nightly backup, and go well beyond the "which pages did
     * someone open" the church is announcing. A whitelist cannot start
     * doing that when a new screen is added; a blacklist can.
     */
    private const KEPT_QUERY = ['week', 'type', 'visibility', 'page'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Write the visit once the page is already on its way.
     *
     * Laravel calls this after the response has been sent, so the row
     * costs the reader nothing. Writing it in handle() instead would
     * hold the page back for the round trip - and on this server that
     * is a third database write on a request that already makes two.
     */
    public function terminate(Request $request, Response $response): void
    {
        if ($this->shouldRecord($request, $response)) {
            $this->record($request);
        }
    }

    /**
     * Whether this request is a page opening worth keeping.
     */
    private function shouldRecord(Request $request, Response $response): bool
    {
        if (! Auth::check() || ! $request->isMethod('GET')) {
            return false;
        }

        /** The account that maintains the site is not congregation activity. */
        if (Auth::user()->is_audit_exempt) {
            return false;
        }

        /** Livewire's polling and every other background call. */
        if ($request->ajax() || $request->expectsJson() || $request->hasHeader('X-Livewire')) {
            return false;
        }

        /** A link the browser fetched on its own, before anyone clicked. */
        if ($request->hasHeader('Purpose') || $request->hasHeader('Sec-Purpose')) {
            return false;
        }

        if ($request->is('livewire/*', 'storage/*', 'build/*', 'sitemap.xml')) {
            return false;
        }

        return $response->getStatusCode() === 200
            && str_contains((string) $response->headers->get('Content-Type'), 'text/html');
    }

    /**
     * Write the visit.
     */
    private function record(Request $request): void
    {
        $kept = array_intersect_key($request->query(), array_flip(self::KEPT_QUERY));

        activity('page')
            ->causedBy(Auth::user())
            ->withProperties([
                'url' => $request->url().($kept === [] ? '' : '?'.http_build_query($kept)),
                'ip' => $request->ip(),
            ])
            ->event('visited')
            ->log('/'.ltrim($request->path(), '/'));
    }
}
