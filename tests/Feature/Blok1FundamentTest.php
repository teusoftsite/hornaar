<?php

use App\Models\Organisation;
use App\Models\User;
use App\Notifications\AccountInvite;
use App\Support\Role;
use Illuminate\Support\Facades\Notification;

it('laat de beheerder een gemeente en een bestrijder aanmaken die daarna kunnen inloggen', function () {
    Notification::fake();
    $admin = member(null, Role::BEHEERDER);

    $this->actingAs($admin)->post('/beheer/organisaties', ['type' => 'gemeente', 'name' => 'Gemeente Etten-Leur', 'cbs_code' => 'GM0777'])->assertRedirect();
    $this->actingAs($admin)->post('/beheer/organisaties', ['type' => 'bestrijder', 'name' => 'Plaagdier BV'])->assertRedirect();
    $g = Organisation::where('cbs_code', 'GM0777')->firstOrFail();
    $b = Organisation::where('name', 'Plaagdier BV')->firstOrFail();

    $this->actingAs($admin)->post("/beheer/organisaties/{$g->id}/gebruikers", ['name' => 'Gemma', 'email' => 'gemma@example.org', 'role' => 'gemeente'])->assertSessionHasNoErrors();
    $this->actingAs($admin)->post("/beheer/organisaties/{$b->id}/gebruikers", ['name' => 'Bart', 'email' => 'bart@example.org', 'role' => 'bestrijder_eigenaar'])->assertSessionHasNoErrors();
    Notification::assertSentTo(User::where('email', 'gemma@example.org')->first(), AccountInvite::class);

    auth()->logout();
    foreach (['gemma@example.org' => '/gemeente', 'bart@example.org' => '/bestrijder'] as $email => $home) {
        User::where('email', $email)->first()->forceFill(['password' => 'nieuw-wachtwoord-123', 'two_factor_secret' => encrypt('X'), 'two_factor_confirmed_at' => now()])->save();
        // Met 2FA aan stuurt Fortify eerst naar de challenge; zonder 2FA rechtstreeks naar de omgeving.
        $this->post('/login', ['email' => $email, 'password' => 'nieuw-wachtwoord-123'])->assertRedirect('/two-factor-challenge');
        $this->flushSession();
        $u = User::where('email', $email)->first();
        $this->actingAs($u)->get($home)->assertOk();
        auth()->logout();
    }
});

it('weigert een gemeente-gebruiker zonder 2FA de omgeving en stuurt naar instellen', function () {
    $u = member(gemeente(), Role::GEMEENTE, twoFactor: false);
    $this->actingAs($u)->get('/gemeente')->assertRedirect('/tweestaps');
    $this->actingAs($u)->get('/tweestaps')->assertOk()->assertSee('Instellen');
});

it('laat een medewerker zonder 2FA wel binnen', function () {
    $u = member(bestrijder(), Role::BESTRIJDER_MEDEWERKER, twoFactor: false);
    $this->actingAs($u)->get('/bestrijder')->assertOk();
});

it('blokkeert inloggen voor een geblokkeerd account', function () {
    $u = member(gemeente(), Role::GEMEENTE);
    $u->update(['active' => false]);
    $this->post('/login', ['email' => $u->email, 'password' => 'geheim-wachtwoord-123'])->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('houdt rollen gescheiden', function () {
    $g = member(gemeente(), Role::GEMEENTE);
    $m = member(bestrijder(), Role::BESTRIJDER_MEDEWERKER);
    $this->actingAs($g)->get('/beheer')->assertForbidden();
    $this->actingAs($g)->get('/bestrijder')->assertForbidden();
    $this->actingAs($m)->get('/bestrijder/facturen')->assertForbidden();
    $this->actingAs($m)->get('/gemeente')->assertForbidden();
});

it('toont alle pagina\'s per rol zonder fouten', function () {
    $this->seed(\Database\Seeders\DemoSeeder::class);
    $this->get('/')->assertOk();
    $this->get('/melden')->assertOk();
    $this->get('/zakelijk')->assertOk();
    $this->get('/privacy')->assertOk();
    foreach (['/info', '/info/herkennen', '/info/jaarcyclus', '/info/nesten-zoeken', '/info/achtergrond', '/info/vragen'] as $url) {
        $this->get($url)->assertOk()->assertSee('Hoornaar');
    }
    $this->get('/login')->assertOk();
    $this->get('/forgot-password')->assertOk();

    $admin = member(null, Role::BEHEERDER);
    $this->actingAs($admin)->get('/beheer')->assertOk();
    $this->actingAs($admin)->get('/beheer/organisaties/1')->assertOk();

    $g = User::where('email', 'gemeente@demo.nl')->first()->forceFill(['two_factor_secret' => encrypt('X'), 'two_factor_confirmed_at' => now()]);
    $g->save();
    $this->actingAs($g)->get('/gemeente')->assertOk()->assertSee('Markt 12');
    $this->actingAs($g)->get('/gemeente/nesten/1')->assertOk();
    $this->actingAs($g)->get('/gemeente/rapport')->assertOk();
    $this->actingAs($g)->get('/gemeente/rapport/export')->assertOk();

    $e = User::where('email', 'eigenaar@demo.nl')->first()->forceFill(['two_factor_secret' => encrypt('X'), 'two_factor_confirmed_at' => now()]);
    $e->save();
    foreach (['/bestrijder', '/bestrijder/opdrachten', '/bestrijder/route', '/bestrijder/facturen', '/bestrijder/instellingen'] as $url) {
        $this->actingAs($e)->get($url)->assertOk();
    }
});
