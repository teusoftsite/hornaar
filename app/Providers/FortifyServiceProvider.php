<?php

namespace App\Providers;

use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;
use Laravel\Fortify\Contracts\PasswordResetResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $home = new class implements LoginResponse, TwoFactorLoginResponse {
            public function toResponse($request)
            {
                return redirect()->intended($request->user()->homeRoute());
            }
        };
        $this->app->instance(LoginResponse::class, $home);
        $this->app->instance(TwoFactorLoginResponse::class, $home);
        $this->app->instance(PasswordResetResponse::class, new class implements PasswordResetResponse {
            public function toResponse($request)
            {
                return redirect()->route('login')->with('status', 'Uw wachtwoord is ingesteld. U kunt nu inloggen.');
            }
        });
    }

    public function boot(): void
    {
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();
            if ($user && $user->active && (! $user->organisation || $user->organisation->active)
                && Hash::check($request->password, $user->password)) {
                return $user;
            }
            return null;
        });

        Fortify::loginView(fn () => view('auth.login'));
        Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', ['request' => $request]));
        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip()));
        });
        RateLimiter::for('two-factor', fn (Request $request) => Limit::perMinute(5)->by($request->session()->get('login.id')));
    }
}
