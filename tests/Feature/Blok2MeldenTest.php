<?php

use App\Models\Nest;
use App\Models\Report;
use App\Notifications\NewNestReported;
use App\Notifications\ReportReceived;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

it('zet een melding in Etten-Leur binnen 5 seconden bij de gemeente', function () {
    Notification::fake();
    Storage::fake('local');
    $g = gemeente();
    $gu = member($g, 'gemeente');

    $start = microtime(true);
    $res = $this->postJson('/api/reports', [
        'lat' => 51.5751, 'lng' => 4.6412, 'note' => 'Hoog in de eik', 'email' => 'melder@example.org',
        'location_type' => 'boom', 'height_m' => 12,
        'photo' => UploadedFile::fake()->image('nest.jpg', 2400, 1800),
    ])->assertCreated()->assertJsonPath('gemeente', 'Gemeente Etten-Leur');
    expect(microtime(true) - $start)->toBeLessThan(5);

    $nest = Nest::findOrFail($res->json('id'));
    expect($nest->gemeente_id)->toBe($g->id)->and($nest->status)->toBe('gemeld')->and($nest->address)->toContain('Etten-Leur');

    $report = $nest->reports->first();
    expect($report->ip_hash)->not->toBe('127.0.0.1')->toHaveLength(64);
    Storage::disk('local')->assertExists($report->photo_path);
    [$w] = getimagesize(Storage::disk('local')->path($report->photo_path));
    expect($w)->toBeLessThanOrEqual(1600);

    Notification::assertSentOnDemand(ReportReceived::class);
    Notification::assertSentOnDemand(NewNestReported::class);
    $this->actingAs($gu)->get('/gemeente')->assertSee('Markt 1');
});

it('toont de statuspagina via het token', function () {
    gemeente();
    $id = $this->postJson('/api/reports', ['lat' => 51.5751, 'lng' => 4.6412])->assertCreated()->json('id');
    $token = Report::where('nest_id', $id)->value('token');
    $this->get("/m/{$token}")->assertOk()->assertSee('Melding #' . $id);
    $this->get('/m/bestaatniet')->assertNotFound();
});

it('accepteert het gewone formulier zonder JavaScript', function () {
    gemeente();
    $this->post('/melden', ['lat' => 51.5751, 'lng' => 4.6412])->assertRedirectContains('/m/');
});

it('weigert locaties buiten Nederland en ontbrekende locatie', function () {
    $this->postJson('/api/reports', ['lat' => 48.85, 'lng' => 2.35])->assertUnprocessable();
    $this->postJson('/api/reports', ['note' => 'x'])->assertUnprocessable()->assertJsonValidationErrors('lat');
});

it('beperkt tot 5 meldingen per IP per uur', function () {
    gemeente();
    foreach (range(1, 5) as $i) {
        $this->postJson('/api/reports', ['lat' => 51.57 + $i / 1000, 'lng' => 4.64])->assertCreated();
    }
    $this->postJson('/api/reports', ['lat' => 51.58, 'lng' => 4.64])->assertStatus(429);
});

it('parkeert meldingen uit een niet-deelnemende gemeente en koppelt ze later', function () {
    $id = $this->postJson('/api/reports', ['lat' => 51.5751, 'lng' => 4.6412])->assertCreated()->json('id');
    expect(Nest::find($id)->gemeente_id)->toBeNull()->and(Nest::find($id)->cbs_code)->toBe('GM0777');

    $admin = member(null, 'beheerder');
    $this->actingAs($admin)->post('/beheer/organisaties', ['type' => 'gemeente', 'name' => 'Etten-Leur', 'cbs_code' => 'GM0777']);
    expect(Nest::find($id)->gemeente_id)->not->toBeNull();
});

it('gebruikt geïmporteerde grenzen vóór PDOK', function () {
    \App\Models\MunicipalityArea::create(['cbs_code' => 'GM0999', 'name' => 'Testgemeente', 'year' => 2026, 'min_lat' => 51, 'max_lat' => 52, 'min_lng' => 4, 'max_lng' => 5,
        'geometry' => json_encode(['type' => 'Polygon', 'coordinates' => [[[4, 51], [5, 51], [5, 52], [4, 52], [4, 51]]]])]);
    $g = gemeente('GM0999', 'Testgemeente');
    $id = $this->postJson('/api/reports', ['lat' => 51.5, 'lng' => 4.5])->assertCreated()->json('id');
    expect(Nest::find($id)->gemeente_id)->toBe($g->id);
});
