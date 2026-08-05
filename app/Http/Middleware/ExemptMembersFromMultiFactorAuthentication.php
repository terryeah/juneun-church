<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Auth\MultiFactor\Http\Middleware\EnsureMultiFactorAuthenticationIsEnabled;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

/**
 * Mandatory two-factor authentication, minus the ordinary 성도 and the
 * per-role test accounts.
 *
 * A member signs in only to read their own giving records, and
 * demanding an authenticator app for that turns most of them away.
 * Every staff role keeps the requirement untouched.
 *
 * The panel's isRequired closure cannot make this distinction: Filament
 * evaluates it while registering routes, long before the session exists,
 * purely to decide whether to attach this middleware. Swapping the
 * middleware itself - via Panel::multiFactorAuthenticationRequiredMiddlewareName()
 * - is the only place the decision can see the signed-in user.
 */
class ExemptMembersFromMultiFactorAuthentication extends EnsureMultiFactorAuthenticationIsEnabled
{
    /**
     * Let a member-only account past, and defer to Filament otherwise.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (Filament::auth()->user()?->isExemptFromMultiFactorAuthentication()) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
