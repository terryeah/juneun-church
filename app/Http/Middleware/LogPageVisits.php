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
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldRecord($request, $response)) {
            $this->record($request);
        }

        return $response;
    }

    /**
     * Whether this request is a page opening worth keeping.
     */
    private function shouldRecord(Request $request, Response $response): bool
    {
        if (! Auth::check() || ! $request->isMethod('GET')) {
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
        activity('page')
            ->causedBy(Auth::user())
            ->withProperties([
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
            ])
            ->event('visited')
            ->log('/'.ltrim($request->path(), '/'));
    }
}
