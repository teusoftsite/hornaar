<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Gebruik: ->middleware('role:gemeente') of 'role:bestrijder' (eigenaar+medewerker) of 'role:bestrijder_eigenaar'. */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();
        if (! $user || ! ($user->active ?? true) || ($user->organisation && ! ($user->organisation->active ?? true))) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Dit account is niet actief.']);
        }
        $ok = collect($roles)->contains(fn ($r) => match ($r) {
            'bestrijder' => $user->isBestrijder(),
            default => $user->role === $r,
        });
        abort_unless($ok, 403);
        return $next($request);
    }
}
