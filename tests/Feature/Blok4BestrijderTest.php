<?php

use App\Models\Assignment;
use App\Models\Nest;
use App\Models\WorkOrder;
use App\Services\RouteOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function opdracht($contract, float $lat = 51.575, float $lng = 4.641, array $o = []): Assignment
{
    $n = Nest::create(['gemeente_id' => $contract->gemeente_id, 'lat' => $lat, 'lng' => $lng, 'address' => "Straat {$lat}", 'status' => 'opdracht_gegeven'] + $o);
    return Assignment::create(['nest_id' => $n->id, 'contract_id' => $contract->id, 'status' => 'open']);
}

it('legt een route van 6 nesten in een logische volgorde', function () {
    // Zes punten op een lijn ten oosten van de werkplaats, door elkaar aangeleverd.
    $stops = collect([5, 2, 6, 1, 4, 3])->map(fn ($i) => ['id' => $i, 'lat' => 51.57, 'lng' => 4.64 + $i * 0.01])->all();
    $r = app(RouteOptimizer::class)->optimize($stops, 51.57, 4.64);
    expect($r['order'])->toBe([1, 2, 3, 4, 5, 6]);
    // heen en terug ≈ 2 × 6 × 0,01° lengte ≈ 2 × 4,16 km
    expect($r['meters'])->toBeGreaterThan(8000)->toBeLessThan(8700);
});

it('plant, optimaliseert en toont de route', function () {
    $g = gemeente(); $b = bestrijder(); $c = contract($g, $b);
    $owner = member($b, 'bestrijder_eigenaar');
    $as = collect([3, 1, 2])->map(fn ($i) => opdracht($c, 51.57, 4.64 + $i * 0.01));
    foreach ($as as $a) {
        $this->actingAs($owner)->post("/bestrijder/opdrachten/{$a->id}/plannen", ['planned_on' => today()->toDateString(), 'assigned_user_id' => $owner->id])->assertSessionHasNoErrors();
    }
    expect($as[0]->nest->fresh()->status)->toBe('ingepland');
    $this->actingAs($owner)->post('/bestrijder/route/optimaliseren', ['datum' => today()->toDateString(), 'medewerker' => $owner->id])->assertRedirect();
    expect($as->map(fn ($a) => $a->fresh()->route_order)->all())->toBe([3, 1, 2]);
    $this->actingAs($owner)->get('/bestrijder/route')->assertOk()->assertSee('Straat');
});

it('verwerkt een offline werkbon later, precies één keer', function () {
    Storage::fake('local');
    $g = gemeente(); $b = bestrijder(); $c = contract($g, $b);
    $m = member($b, 'bestrijder_medewerker');
    $a = opdracht($c, o: ['height_m' => 10]);
    $uuid = (string) Str::uuid();
    $payload = ['client_uuid' => $uuid, 'result' => 'bestreden', 'agent' => 'Diatomeeënaarde', 'surcharges' => ['hoogwerker'],
        'started_at' => now()->subHours(3)->toIso8601String(), 'finished_at' => now()->subHours(2)->toIso8601String(),
        'photo_after' => UploadedFile::fake()->image('na.jpg')];

    // Eerst (uren later) verstuurd vanuit de wachtrij, daarna nogmaals (dubbele sync).
    $this->actingAs($m)->post("/bestrijder/werkbon/{$a->id}", $payload, ['Accept' => 'application/json'])->assertCreated();
    $this->actingAs($m)->post("/bestrijder/werkbon/{$a->id}", $payload, ['Accept' => 'application/json'])->assertOk();

    expect(WorkOrder::count())->toBe(1)
        ->and($a->fresh()->status)->toBe('afgerond')
        ->and($a->nest->fresh()->status)->toBe('bestreden')
        ->and(WorkOrder::first()->finished_at->lt(now()->subHour()))->toBeTrue()
        ->and(WorkOrder::first()->invoiceLine->amount_cents)->toBe(14500 + 7500);
});

it('maakt voorrijkosten bij geen toegang en niets bij geen nest', function () {
    $g = gemeente(); $b = bestrijder(); $c = contract($g, $b);
    $m = member($b, 'bestrijder_medewerker');
    $a1 = opdracht($c); $a2 = opdracht($c, 51.6);
    $this->actingAs($m)->post("/bestrijder/werkbon/{$a1->id}", ['client_uuid' => (string) Str::uuid(), 'result' => 'geen_toegang'])->assertRedirect();
    $this->actingAs($m)->post("/bestrijder/werkbon/{$a2->id}", ['client_uuid' => (string) Str::uuid(), 'result' => 'geen_nest'])->assertRedirect();
    expect($a1->nest->fresh()->status)->toBe('geen_toegang')
        ->and(WorkOrder::where('assignment_id', $a1->id)->first()->invoiceLine->amount_cents)->toBe(4500)
        ->and($a2->nest->fresh()->status)->toBe('afgewezen')
        ->and(WorkOrder::where('assignment_id', $a2->id)->first()->invoiceLine)->toBeNull();
});

it('laat bestrijder A geen opdrachten van bestrijder B zien of afronden', function () {
    $g = gemeente(); $b1 = bestrijder('A BV'); $b2 = bestrijder('B BV');
    $c2 = contract($g, $b2);
    $a = opdracht($c2);
    $u1 = member($b1, 'bestrijder_eigenaar');
    $this->actingAs($u1)->get('/bestrijder/opdrachten')->assertDontSee($a->nest->address);
    $this->actingAs($u1)->get("/bestrijder/werkbon/{$a->id}")->assertNotFound();
    $this->actingAs($u1)->post("/bestrijder/werkbon/{$a->id}", ['client_uuid' => (string) Str::uuid(), 'result' => 'bestreden'])->assertNotFound();
});
