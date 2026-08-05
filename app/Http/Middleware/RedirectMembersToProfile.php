<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps an ordinary 성도 on their own profile page.
 *
 * The 'member' role carries no permissions, so the dashboard and every
 * resource would answer 403. Redirecting the whole panel to the profile
 * page turns that dead end into the one page such an account can
 * actually use, and settles where they land after signing in: Filament
 * sends everyone to the panel root first, which this catches.
 *
 * Registered as panel auth middleware, so the login screen is never
 * touched and staff pay one role check per request. The panel's own
 * auth routes (profile, logout) are let through unconditionally,
 * otherwise the redirect would chase its own tail.
 */
class RedirectMembersToProfile
{
    /**
     * Send member-only accounts to their profile, and everyone else on.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $panel = Filament::getCurrentOrDefaultPanel();
        $profileUrl = $panel->getProfileUrl();

        if (blank($profileUrl)
            || $request->routeIs($panel->generateRouteName('auth.*'))
            || ! Filament::auth()->user()?->isMemberOnly()) {
            return $next($request);
        }

        return redirect($profileUrl);
    }
}
