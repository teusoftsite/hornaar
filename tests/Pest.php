<?php

use App\Models\Contract;
use App\Models\Organisation;
use App\Models\User;
use App\Support\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

pest()->extend(Tests\TestCase::class)->use(RefreshDatabase::class)->beforeEach(function () {
    // PDOK nooit echt aanroepen in tests.
    Http::fake(['api.pdok.nl/*' => Http::response(['response' => ['docs' => [[
        'weergavenaam' => 'Markt 1, 4871AA Etten-Leur', 'gemeentecode' => '0777', 'gemeentenaam' => 'Etten-Leur',
    ]]]])]);
})->in('Feature');

function gemeente(string $code = 'GM0777', string $name = 'Gemeente Etten-Leur'): Organisation
{
    return Organisation::create(['type' => 'gemeente', 'name' => $name, 'cbs_code' => $code, 'notify_email' => strtolower($code) . '@example.org', 'invoice_email' => 'facturen-' . strtolower($code) . '@example.org']);
}

function bestrijder(string $name = 'Plaagdier BV'): Organisation
{
    return Organisation::create(['type' => 'bestrijder', 'name' => $name, 'iban' => 'NL91ABNA0417164300', 'vat_rate' => 21, 'base_lat' => 51.57, 'base_lng' => 4.64, 'notify_email' => 'planning@example.org']);
}

function contract(Organisation $g, Organisation $b, array $o = []): Contract
{
    return Contract::create($o + ['gemeente_id' => $g->id, 'bestrijder_id' => $b->id, 'valid_from' => now()->subYear(), 'standard_rate_cents' => 14500, 'surcharge_height_cents' => 7500, 'surcharge_urgent_cents' => 5000, 'callout_cents' => 4500, 'payment_days' => 30]);
}

/** Gebruiker met (indien verplicht) bevestigde 2FA. */
function member(?Organisation $org, string $role, bool $twoFactor = true): User
{
    $u = User::create(['name' => ucfirst($role), 'email' => $role . '.' . uniqid() . '@example.org', 'password' => 'geheim-wachtwoord-123', 'role' => $role, 'organisation_id' => $org?->id]);
    if ($twoFactor && in_array($role, Role::REQUIRES_2FA, true)) {
        $u->forceFill(['two_factor_secret' => encrypt('SECRET'), 'two_factor_recovery_codes' => encrypt('[]'), 'two_factor_confirmed_at' => now()])->save();
    }
    return $u;
}
