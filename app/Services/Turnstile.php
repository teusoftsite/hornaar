<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/** Cloudflare Turnstile (optioneel: zonder secret key altijd geldig). */
class Turnstile
{
    public function verify(?string $token, ?string $ip): bool
    {
        $secret = config('services.turnstile.secret_key');
        if (! $secret) {
            return true;
        }
        if (! $token) {
            return false;
        }
        try {
            return (bool) Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $secret, 'response' => $token, 'remoteip' => $ip,
            ])->json('success');
        } catch (\Throwable) {
            return true; // Cloudflare onbereikbaar: melding niet blokkeren, rate limit blijft actief
        }
    }
}
