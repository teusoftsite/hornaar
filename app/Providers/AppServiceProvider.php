<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Carbon::setLocale('nl');
        Password::defaults(fn () => $this->app->environment('production') ? Password::min(12)->uncompromised() : Password::min(12));
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        // Meldingen: max 5 per IP per uur (bouwontwerp).
        RateLimiter::for('reports', fn (Request $r) => Limit::perHour(5)->by($r->ip())->response(
            fn () => $r->expectsJson()
                ? response()->json(['message' => 'Je hebt het afgelopen uur al 5 meldingen gedaan. Probeer het later opnieuw.'], 429)
                : back()->withErrors(['lat' => 'Je hebt het afgelopen uur al 5 meldingen gedaan. Probeer het later opnieuw.'])
        ));
    }
}
