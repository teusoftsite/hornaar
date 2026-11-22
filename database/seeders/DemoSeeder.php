<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Nest;
use App\Models\Organisation;
use App\Models\Report;
use App\Models\StatusEvent;
use App\Models\User;
use App\Support\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/** Demo/test-omgeving: php artisan db:seed --class=DemoSeeder. Wachtwoord van alle demo-accounts: "demo-wachtwoord". */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $gemeente = Organisation::firstOrCreate(['cbs_code' => 'GM0777'], [
            'type' => 'gemeente', 'name' => 'Gemeente Etten-Leur', 'address' => 'Roosendaalseweg 4, 4875 AC Etten-Leur',
            'notify_email' => 'meldingen@example.org', 'invoice_email' => 'facturen@example.org',
        ]);
        $bestrijder = Organisation::firstOrCreate(['name' => 'Demo Plaagdierbeheer BV', 'type' => 'bestrijder'], [
            'address' => 'Industrieweg 1, 4870 AA Etten-Leur', 'kvk' => '12345678', 'vat_number' => 'NL001234567B01',
            'iban' => 'NL91ABNA0417164300', 'invoice_email' => 'administratie@example.org', 'notify_email' => 'planning@example.org',
            'base_lat' => 51.5705, 'base_lng' => 4.6380, 'vat_rate' => 21,
        ]);
        Contract::firstOrCreate(['gemeente_id' => $gemeente->id, 'bestrijder_id' => $bestrijder->id], [
            'valid_from' => now()->startOfYear(), 'standard_rate_cents' => 14500, 'surcharge_height_cents' => 7500,
            'surcharge_urgent_cents' => 5000, 'callout_cents' => 4500, 'payment_days' => 30,
        ]);

        foreach ([
            ['gemeente@demo.nl', 'Gemma Gemeente', Role::GEMEENTE, $gemeente],
            ['eigenaar@demo.nl', 'Bart Bestrijder', Role::BESTRIJDER_EIGENAAR, $bestrijder],
            ['medewerker@demo.nl', 'Mo Medewerker', Role::BESTRIJDER_MEDEWERKER, $bestrijder],
        ] as [$email, $name, $role, $org]) {
            User::firstOrCreate(['email' => $email], ['name' => $name, 'password' => 'demo-wachtwoord', 'role' => $role, 'organisation_id' => $org->id]);
        }

        $spots = [[51.5751, 4.6412, 'Markt 12, Etten-Leur'], [51.5683, 4.6250, 'Bisschopsmolen 3, Etten-Leur'], [51.5802, 4.6555, 'Hoge Haansberg 8, Etten-Leur'], [51.5630, 4.6480, 'Liesbos, Etten-Leur']];
        foreach ($spots as [$lat, $lng, $addr]) {
            if (Nest::where('address', $addr)->exists()) continue;
            $nest = Nest::create(['gemeente_id' => $gemeente->id, 'cbs_code' => 'GM0777', 'lat' => $lat, 'lng' => $lng, 'address' => $addr, 'location_type' => 'boom', 'height_m' => rand(3, 15), 'status' => 'gemeld']);
            StatusEvent::create(['nest_id' => $nest->id, 'to' => 'gemeld', 'note' => 'Demo']);
            Report::create(['nest_id' => $nest->id, 'token' => Str::random(32), 'lat' => $lat, 'lng' => $lng, 'note' => 'Groot nest hoog in de boom']);
        }
    }
}
