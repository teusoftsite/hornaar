<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Beheerder, gemeente en bestrijder-eigenaar moeten 2FA hebben ingesteld. */
class EnsureTwoFactorEnabled
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && $user->requiresTwoFactor() && ! $user->hasConfirmedTwoFactor() && ! $request->routeIs('tweestaps*', 'logout', 'two-factor.*', 'password.confirm*')) {
            return redirect()->route('tweestaps');
        }
        return $next($request);
    }
}
