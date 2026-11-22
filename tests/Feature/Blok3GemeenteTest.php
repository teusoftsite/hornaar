<?php

use App\Models\Assignment;
use App\Models\Nest;
use App\Notifications\AssignmentCreated;
use App\Notifications\NestStatusChanged;
use Illuminate\Support\Facades\Notification;

function nestIn($g, float $lat = 51.5751, float $lng = 4.6412, string $status = 'gemeld'): Nest
{
    $n = Nest::create(['gemeente_id' => $g->id, 'cbs_code' => $g->cbs_code, 'lat' => $lat, 'lng' => $lng, 'address' => 'Markt 1', 'status' => $status]);
    \App\Models\Report::create(['nest_id' => $n->id, 'token' => \Illuminate\Support\Str::random(32), 'lat' => $lat, 'lng' => $lng, 'reporter_email' => 'm@example.org']);
    return $n;
}

it('laat gemeente A niets zien van gemeente B', function () {
    $a = gemeente('GM0777', 'A'); $b = gemeente('GM0758', 'B');
    $ua = member($a, 'gemeente');
    $nb = nestIn($b);
    nestIn($a, 51.6, 4.7)->update(['address' => 'Eigen straat 1']);
    $nb->update(['address' => 'Andermans straat 9']);

    $this->actingAs($ua)->get('/gemeente?status=alle')->assertSee('Eigen straat 1')->assertDontSee('Andermans straat 9');
    $this->actingAs($ua)->get("/gemeente/nesten/{$nb->id}")->assertNotFound();
    $this->actingAs($ua)->post("/gemeente/nesten/{$nb->id}/goedkeuren")->assertNotFound();
    $this->actingAs($ua)->post("/gemeente/nesten/{$nb->id}/afwijzen", ['note' => 'x'])->assertNotFound();
    $this->actingAs($ua)->get('/gemeente/rapport/export')->assertDontSee('Andermans');
    expect($nb->fresh()->status)->toBe('gemeld');
});

it('stelt een melding binnen 25 meter voor als dubbel en voegt samen', function () {
    $g = gemeente(); $u = member($g, 'gemeente');
    $first = nestIn($g, 51.575100, 4.641200, 'goedgekeurd');
    $dup = nestIn($g, 51.575250, 4.641250);       // ca. 17 m
    $far = nestIn($g, 51.576000, 4.641200);       // ca. 100 m

    $this->actingAs($u)->get("/gemeente/nesten/{$dup->id}")->assertSee('Mogelijk dubbel')->assertSee('#' . $first->id);
    $this->actingAs($u)->get("/gemeente/nesten/{$far->id}")->assertDontSee('Mogelijk dubbel');

    $this->actingAs($u)->post("/gemeente/nesten/{$dup->id}/samenvoegen", ['target_id' => $first->id])->assertRedirect("/gemeente/nesten/{$first->id}");
    expect($dup->fresh()->status)->toBe('samengevoegd')->and($first->reports()->count())->toBe(2);
});

it('keurt goed, geeft opdracht en mailt melder en bestrijder', function () {
    Notification::fake();
    $g = gemeente(); $b = bestrijder(); $c = contract($g, $b);
    $u = member($g, 'gemeente');
    $n = nestIn($g);

    $this->actingAs($u)->post("/gemeente/nesten/{$n->id}/goedkeuren", ['urgent' => 1])->assertSessionHasNoErrors();
    expect($n->fresh()->status)->toBe('goedgekeurd')->and($n->fresh()->urgent)->toBeTrue();
    Notification::assertSentOnDemand(NestStatusChanged::class);

    $this->actingAs($u)->post("/gemeente/nesten/{$n->id}/opdracht", ['contract_id' => $c->id])->assertSessionHasNoErrors();
    expect($n->fresh()->status)->toBe('opdracht_gegeven')->and(Assignment::where('nest_id', $n->id)->count())->toBe(1);
    Notification::assertSentOnDemand(AssignmentCreated::class);
});

it('kan geen contract van een andere gemeente gebruiken', function () {
    $a = gemeente('GM0777', 'A'); $b = gemeente('GM0758', 'B'); $best = bestrijder();
    $cb = contract($b, $best);
    $u = member($a, 'gemeente');
    $n = nestIn($a, status: 'goedgekeurd');
    $this->actingAs($u)->post("/gemeente/nesten/{$n->id}/opdracht", ['contract_id' => $cb->id])->assertNotFound();
});

it('wijst af met reden en bewaakt de statusflow', function () {
    $g = gemeente(); $u = member($g, 'gemeente');
    $n = nestIn($g);
    $this->actingAs($u)->post("/gemeente/nesten/{$n->id}/afwijzen", ['note' => 'Wespennest'])->assertSessionHasNoErrors();
    expect($n->fresh()->status)->toBe('afgewezen');
    $this->actingAs($u)->post("/gemeente/nesten/{$n->id}/goedkeuren")->assertSessionHasErrors('action');
});
