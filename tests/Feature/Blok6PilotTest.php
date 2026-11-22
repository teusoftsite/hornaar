<?php

use App\Models\Nest;
use App\Models\Report;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

it('wist e-mailadressen 90 dagen na afsluiten van het nest', function () {
    $g = gemeente();
    $old = Nest::create(['gemeente_id' => $g->id, 'lat' => 51.5, 'lng' => 4.6, 'status' => 'bestreden']);
    $open = Nest::create(['gemeente_id' => $g->id, 'lat' => 51.5, 'lng' => 4.6, 'status' => 'ingepland']);
    $r1 = Report::create(['nest_id' => $old->id, 'token' => 'a', 'lat' => 51.5, 'lng' => 4.6, 'reporter_email' => 'a@example.org']);
    $r2 = Report::create(['nest_id' => $open->id, 'token' => 'b', 'lat' => 51.5, 'lng' => 4.6, 'reporter_email' => 'b@example.org']);
    Nest::whereKey([$old->id, $open->id])->update(['updated_at' => now()->subDays(91)]);

    $this->artisan('privacy:opschonen')->assertSuccessful();
    expect($r1->fresh()->reporter_email)->toBeNull()->and($r2->fresh()->reporter_email)->toBe('b@example.org');
});

it('serveert foto\'s alleen via een geldige ondertekende URL', function () {
    Storage::fake('local');
    gemeente();
    $id = $this->postJson('/api/reports', ['lat' => 51.5751, 'lng' => 4.6412, 'photo' => UploadedFile::fake()->image('x.jpg')])->json('id');
    $path = Report::where('nest_id', $id)->value('photo_path');

    $this->get('/foto/' . $path)->assertForbidden();
    $this->get(\App\Support\Photo::url($path))->assertOk();
    $this->get(URL::temporarySignedRoute('foto', now()->subMinute(), ['path' => $path]))->assertForbidden();
});

it('haalt EXIF-gegevens (zoals GPS) uit foto\'s', function () {
    Storage::fake('local');
    gemeente();
    // JPEG met APP1/Exif-blok.
    $img = imagecreatetruecolor(50, 50); ob_start(); imagejpeg($img); $jpeg = ob_get_clean();
    $exif = "\xFF\xE1" . pack('n', 2 + 14) . "Exif\0\0GPSDATA!";
    $file = UploadedFile::fake()->createWithContent('gps.jpg', substr($jpeg, 0, 2) . $exif . substr($jpeg, 2));
    $id = $this->postJson('/api/reports', ['lat' => 51.5751, 'lng' => 4.6412, 'photo' => $file])->assertCreated()->json('id');
    $stored = Storage::disk('local')->get(Report::where('nest_id', $id)->value('photo_path'));
    expect($stored)->not->toContain('GPSDATA')->not->toContain('Exif');
});

it('stuurt het contactformulier door', function () {
    \Illuminate\Support\Facades\Mail::fake();
    $this->post('/zakelijk/contact', ['naam' => 'Jan', 'organisatie' => 'Gemeente X', 'email' => 'jan@example.org', 'type' => 'Gemeente'])->assertRedirect();
    $this->post('/zakelijk/contact', ['naam' => 'Bot', 'organisatie' => 'Spam', 'email' => 'b@example.org', 'website' => 'http://spam'])->assertRedirect();
});

it('heeft een geldige PWA-manifest en service worker', function () {
    expect(json_decode(file_get_contents(public_path('manifest.webmanifest')), true)['lang'])->toBe('nl');
    expect(file_exists(public_path('sw.js')))->toBeTrue();
});
