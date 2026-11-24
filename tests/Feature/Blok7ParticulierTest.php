<?php

use App\Models\Lead;
use App\Models\LeadInvoice;
use App\Models\Nest;
use App\Models\Organisation;
use App\Models\Report;
use App\Notifications\LeadAccepted;
use App\Notifications\LeadNoBestrijder;
use App\Notifications\LeadOffered;
use App\Services\LeadInvoiceService;
use App\Services\LeadService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

function particulierMelding(float $lat = 51.575, float $lng = 4.641): Report
{
    $n = Nest::create(['gemeente_id' => null, 'lat' => $lat, 'lng' => $lng, 'address' => 'Tuinstraat 1', 'status' => 'gemeld', 'plek' => 'eigen']);
    return Report::create(['nest_id' => $n->id, 'token' => Str::random(32), 'lat' => $lat, 'lng' => $lng, 'reporter_email' => 'inwoner@example.org']);
}

function leadBestrijder(string $name, float $lat, float $lng, int $radius = 25): Organisation
{
    $b = bestrijder($name);
    $b->update(['base_lat' => $lat, 'base_lng' => $lng, 'accepts_leads' => true, 'service_radius_km' => $radius, 'phone' => '0612345678']);
    return $b;
}

function vraagBestrijder($test, Report $r): void
{
    $test->post("/m/{$r->token}/bestrijder", ['name' => 'Jan Inwoner', 'phone' => '06 12345678', 'email' => 'inwoner@example.org', 'akkoord' => '1'])->assertRedirect();
}

it('toont het aanvraagformulier alleen bij eigen terrein', function () {
    $r = particulierMelding();
    $this->get("/m/{$r->token}")->assertOk()->assertSee('Bestrijder vragen');

    $r->nest->update(['plek' => 'openbaar']);
    $this->get("/m/{$r->token}")->assertOk()->assertDontSee('Bestrijder vragen');
});

it('biedt de lead aan de dichtstbijzijnde bestrijder aan', function () {
    Notification::fake();
    $ver = leadBestrijder('Ver BV', 51.70, 4.64);
    $dichtbij = leadBestrijder('Dichtbij BV', 51.58, 4.64);
    $r = particulierMelding();
    vraagBestrijder($this, $r);

    $lead = Lead::first();
    expect($lead->status)->toBe('aangeboden')->and($lead->offered_to_id)->toBe($dichtbij->id);
    Notification::assertSentOnDemand(LeadOffered::class);
});

it('slaat bestrijders buiten het werkgebied en zonder leads over', function () {
    Notification::fake();
    leadBestrijder('Te ver BV', 52.5, 4.64, 25);
    bestrijder('Geen leads BV');
    vraagBestrijder($this, particulierMelding());
    expect(Lead::first()->status)->toBe('geen_bestrijder');
    Notification::assertSentOnDemand(LeadNoBestrijder::class);
});

it('zet door naar de volgende na weigeren en na verlopen', function () {
    Notification::fake();
    $a = leadBestrijder('A BV', 51.58, 4.64);
    $b = leadBestrijder('B BV', 51.60, 4.64);
    $c = leadBestrijder('C BV', 51.62, 4.64);
    vraagBestrijder($this, particulierMelding());
    $lead = Lead::first();

    $this->actingAs(member($a, 'bestrijder_eigenaar'))->post("/bestrijder/particulier/{$lead->id}/weigeren")->assertRedirect();
    expect($lead->fresh()->offered_to_id)->toBe($b->id);

    $this->travel(config('meldhoornaar.lead_response_hours') + 1)->hours();
    $this->artisan('leads:doorzetten')->assertSuccessful();
    expect($lead->fresh()->offered_to_id)->toBe($c->id)->and($lead->fresh()->declined_ids)->toBe([$a->id, $b->id]);
});

it('rekent de fee bij accepteren en toont dan pas de contactgegevens', function () {
    Notification::fake();
    $b = leadBestrijder('A BV', 51.58, 4.64);
    $r = particulierMelding();
    vraagBestrijder($this, $r);
    $lead = Lead::first();
    $owner = member($b, 'bestrijder_eigenaar');

    $this->actingAs($owner)->get('/bestrijder/particulier')->assertOk()->assertSee('Tuinstraat 1')->assertDontSee('Jan Inwoner');
    $this->actingAs($owner)->post("/bestrijder/particulier/{$lead->id}/accepteren")->assertRedirect();
    $lead->refresh();
    expect($lead->status)->toBe('geaccepteerd')->and($lead->fee_cents)->toBe(1000)->and($r->nest->fresh()->status)->toBe('opdracht_gegeven');
    Notification::assertSentOnDemand(LeadAccepted::class);
    $this->actingAs($owner)->get('/bestrijder/particulier')->assertSee('Jan Inwoner');
    $this->get("/m/{$r->token}")->assertSee('A BV');

    $this->actingAs($owner)->post("/bestrijder/particulier/{$lead->id}/afronden", ['result' => 'bestreden'])->assertRedirect();
    expect($r->nest->fresh()->status)->toBe('bestreden');
});

it('laat een andere bestrijder niet accepteren', function () {
    Notification::fake();
    leadBestrijder('A BV', 51.58, 4.64);
    $ander = leadBestrijder('B BV', 51.60, 4.64);
    vraagBestrijder($this, particulierMelding());
    $lead = Lead::first();
    $this->actingAs(member($ander, 'bestrijder_eigenaar'))->post("/bestrijder/particulier/{$lead->id}/accepteren");
    expect($lead->fresh()->status)->toBe('aangeboden');
});

it('maakt maandelijks een leadfactuur per bestrijder', function () {
    Notification::fake();
    config(['meldhoornaar.seller.iban' => 'NL91ABNA0417164300', 'meldhoornaar.seller.kvk' => '12345678']);
    $b = leadBestrijder('A BV', 51.58, 4.64);
    $owner = member($b, 'bestrijder_eigenaar');
    foreach ([1, 2] as $i) {
        vraagBestrijder($this, particulierMelding(51.575 + $i / 1000));
        $this->actingAs($owner)->post('/bestrijder/particulier/' . Lead::latest('id')->first()->id . '/accepteren');
    }
    $this->travelTo(now()->addMonthNoOverflow()->startOfMonth()->addHours(3));
    app(LeadInvoiceService::class)->generateMonthly(now());
    $inv = LeadInvoice::first();
    expect($inv->lead_count)->toBe(2)->and($inv->subtotal_cents)->toBe(2000)->and($inv->total_cents)->toBe(2420);

    $admin = member(null, 'beheerder');
    $this->actingAs($admin)->get('/beheer/particulier')->assertOk()->assertSee('A BV');
    $this->actingAs($admin)->post("/beheer/particulier/facturen/{$inv->id}/versturen")->assertRedirect();
    expect($inv->fresh()->number)->toStartWith('MH-')->and($inv->fresh()->status)->toBe('verstuurd');
    $this->actingAs($admin)->get("/beheer/particulier/facturen/{$inv->id}/pdf")->assertOk();
});

it('stuurt eigen terrein niet naar de gemeente, openbare plek wel', function () {
    $g = gemeente();
    $eigen = $this->postJson('/api/reports', ['plek' => 'eigen', 'lat' => 51.5751, 'lng' => 4.6412])->assertCreated()->json('id');
    $openbaar = $this->postJson('/api/reports', ['plek' => 'openbaar', 'lat' => 51.5751, 'lng' => 4.6412])->assertCreated()->json('id');
    expect(Nest::find($eigen)->gemeente_id)->toBeNull()->and(Nest::find($eigen)->isParticulier())->toBeTrue();
    expect(Nest::find($openbaar)->gemeente_id)->toBe($g->id);
    $this->postJson('/api/reports', ['lat' => 51.5751, 'lng' => 4.6412])->assertJsonValidationErrors('plek');
    $this->get('/melden')->assertOk()->assertSee('Op mijn eigen terrein')->assertSee('Wat gebeurt er na je melding?');
    $this->get('/info')->assertRedirect('/');
    $this->get('/')->assertOk()->assertSee('Meld een nest')->assertSee('Hoofdstuk 7');
});
