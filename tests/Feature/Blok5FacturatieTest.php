<?php

use App\Models\Assignment;
use App\Models\Invoice;
use App\Models\Nest;
use App\Notifications\InvoiceReminder;
use App\Notifications\InvoiceSent;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function bestrijd($contract, $user, string $result = 'bestreden', array $surcharges = []): void
{
    $n = Nest::create(['gemeente_id' => $contract->gemeente_id, 'lat' => 51.57 + mt_rand() / mt_getrandmax() / 100, 'lng' => 4.64, 'address' => 'Laan ' . Str::random(4), 'status' => 'ingepland']);
    $a = Assignment::create(['nest_id' => $n->id, 'contract_id' => $contract->id, 'status' => 'ingepland', 'planned_on' => today(), 'assigned_user_id' => $user->id]);
    test()->actingAs($user)->post("/bestrijder/werkbon/{$a->id}", ['client_uuid' => (string) Str::uuid(), 'result' => $result, 'surcharges' => $surcharges])->assertSessionHasNoErrors();
}

it('maakt van 14 nesten in een maand precies één juiste factuur', function () {
    Notification::fake(); Storage::fake('local');
    $g = gemeente(); $b = bestrijder(); $c = contract($g, $b);
    $owner = member($b, 'bestrijder_eigenaar');

    $this->travelTo(now()->setDate(2027, 8, 10));
    foreach (range(1, 10) as $i) bestrijd($c, $owner);                    // 10 × 145
    foreach (range(1, 2) as $i) bestrijd($c, $owner, surcharges: ['hoogwerker']); // 2 × 220
    bestrijd($c, $owner, surcharges: ['urgent']);                         // 1 × 195
    bestrijd($c, $owner, 'geen_toegang');                                  // 1 × 45

    $this->travelTo(now()->setDate(2027, 9, 1)->setTime(6, 0));
    $this->artisan('facturen:maand')->assertSuccessful();

    expect(Invoice::count())->toBe(1);
    $inv = Invoice::first();
    $sub = 10 * 14500 + 2 * 22000 + 19500 + 4500; // 213.500
    expect($inv->lines()->count())->toBe(14)
        ->and($inv->period)->toBe('2027-08')
        ->and($inv->subtotal_cents)->toBe($sub)
        ->and($inv->vat_cents)->toBe((int) round($sub * 0.21))
        ->and($inv->total_cents)->toBe($sub + (int) round($sub * 0.21))
        ->and($inv->status)->toBe('concept')->and($inv->number)->toBeNull();

    // Draait de taak nog eens, dan geen dubbele factuur.
    $this->artisan('facturen:maand');
    expect(Invoice::count())->toBe(1);

    $this->actingAs($owner)->post("/bestrijder/facturen/{$inv->id}/versturen")->assertSessionHasNoErrors();
    $inv->refresh();
    expect($inv->number)->toBe('2027-0001')->and($inv->status)->toBe('verstuurd')->and($inv->due_on->toDateString())->toBe('2027-10-01');
    Storage::disk('local')->assertExists($inv->pdf_path);
    Notification::assertSentOnDemand(InvoiceSent::class);
    expect(Nest::where('status', 'gefactureerd')->count())->toBe(13);
});

it('houdt aparte facturen per contract en nummert per bestrijder door', function () {
    Notification::fake(); Storage::fake('local');
    $g1 = gemeente('GM0777', 'A'); $g2 = gemeente('GM0758', 'B'); $b = bestrijder();
    $c1 = contract($g1, $b); $c2 = contract($g2, $b);
    $owner = member($b, 'bestrijder_eigenaar');
    $this->travelTo(now()->setDate(2027, 8, 10));
    bestrijd($c1, $owner); bestrijd($c2, $owner);
    $this->travelTo(now()->setDate(2027, 9, 1));
    $this->artisan('facturen:maand');
    expect(Invoice::count())->toBe(2);
    foreach (Invoice::orderBy('id')->get() as $i) $this->actingAs($owner)->post("/bestrijder/facturen/{$i->id}/versturen");
    expect(Invoice::orderBy('id')->pluck('number')->all())->toBe(['2027-0001', '2027-0002']);
});

it('laat een conceptregel aanpassen met reden, maar een verstuurde factuur niet', function () {
    Notification::fake(); Storage::fake('local');
    $g = gemeente(); $b = bestrijder(); $c = contract($g, $b);
    $owner = member($b, 'bestrijder_eigenaar');
    $this->travelTo(now()->setDate(2027, 8, 10)); bestrijd($c, $owner);
    $this->travelTo(now()->setDate(2027, 9, 1)); $this->artisan('facturen:maand');
    $inv = Invoice::first(); $line = $inv->lines->first();

    $this->actingAs($owner)->post("/bestrijder/facturen/regel/{$line->id}", ['amount' => '120,00', 'description' => 'Bestrijding (korting)', 'reason' => 'Klein nest'])->assertSessionHasNoErrors();
    expect($inv->fresh()->subtotal_cents)->toBe(12000);

    $this->actingAs($owner)->post("/bestrijder/facturen/{$inv->id}/versturen");
    $this->actingAs($owner)->post("/bestrijder/facturen/regel/{$line->id}", ['amount' => '1', 'description' => 'x', 'reason' => 'y'])->assertSessionHasErrors('action');
    expect($line->fresh()->amount_cents)->toBe(12000);

    $this->actingAs($owner)->post("/bestrijder/facturen/{$inv->id}/crediteren")->assertRedirect();
    $credit = Invoice::whereNotNull('credits_invoice_id')->first();
    expect($credit->total_cents)->toBe(-$inv->fresh()->total_cents)->and($credit->number)->toBe('2027-0002');
});

it('markeert te laat en stuurt herinneringen na 7 en 14 dagen', function () {
    Notification::fake(); Storage::fake('local');
    $g = gemeente(); $b = bestrijder(); $c = contract($g, $b);
    $owner = member($b, 'bestrijder_eigenaar');
    $this->travelTo(now()->setDate(2027, 8, 10)); bestrijd($c, $owner);
    $this->travelTo(now()->setDate(2027, 9, 1)); $this->artisan('facturen:maand');
    $inv = Invoice::first();
    $this->actingAs($owner)->post("/bestrijder/facturen/{$inv->id}/versturen"); // vervalt 1 okt

    $this->travelTo(now()->setDate(2027, 10, 2)); $this->artisan('facturen:herinneringen');
    expect($inv->fresh()->status)->toBe('te_laat')->and($inv->fresh()->reminders_sent)->toBe(0);
    $this->travelTo(now()->setDate(2027, 10, 8)); $this->artisan('facturen:herinneringen');
    expect($inv->fresh()->reminders_sent)->toBe(1);
    $this->travelTo(now()->setDate(2027, 10, 9)); $this->artisan('facturen:herinneringen');
    expect($inv->fresh()->reminders_sent)->toBe(1);
    $this->travelTo(now()->setDate(2027, 10, 15)); $this->artisan('facturen:herinneringen');
    expect($inv->fresh()->reminders_sent)->toBe(2);
    Notification::assertSentOnDemandTimes(InvoiceReminder::class, 2);

    $this->actingAs($owner)->post("/bestrijder/facturen/{$inv->id}/betaald");
    expect($inv->fresh()->status)->toBe('betaald');
});

it('laat facturen van een andere bestrijder niet zien', function () {
    $g = gemeente(); $b1 = bestrijder('A'); $b2 = bestrijder('B'); $c2 = contract($g, $b2);
    $inv = Invoice::create(['bestrijder_id' => $b2->id, 'gemeente_id' => $g->id, 'contract_id' => $c2->id, 'period' => '2027-08', 'status' => 'concept']);
    $u1 = member($b1, 'bestrijder_eigenaar');
    $this->actingAs($u1)->get("/bestrijder/facturen/{$inv->id}")->assertNotFound();
    $this->actingAs($u1)->post("/bestrijder/facturen/{$inv->id}/versturen")->assertNotFound();
    $ug = member(gemeente('GM0758', 'Andere'), 'gemeente');
    $this->actingAs($ug)->get("/gemeente/facturen/{$inv->id}/pdf")->assertNotFound();
});
