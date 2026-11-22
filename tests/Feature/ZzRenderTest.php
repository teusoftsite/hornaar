<?php
it('rendert schermen voor visuele controle', function () {
    $this->seed(\Database\Seeders\DemoSeeder::class);
    $n = \App\Models\Nest::first();
    $this->actingAs(\App\Models\User::where('email', 'gemeente@demo.nl')->first()->forceFill(['two_factor_secret' => encrypt('X'), 'two_factor_confirmed_at' => now()]));
    $this->post("/gemeente/nesten/{$n->id}/goedkeuren", ['urgent' => 1]);
    $this->post("/gemeente/nesten/{$n->id}/opdracht", ['contract_id' => 1]);
    foreach (['/gemeente', '/gemeente/nesten/2', '/gemeente/rapport'] as $u) file_put_contents(public_path('_tmp' . str_replace('/', '_', $u) . '.html'), $this->get($u)->getContent());
    $this->actingAs(\App\Models\User::where('email', 'eigenaar@demo.nl')->first()->forceFill(['two_factor_secret' => encrypt('X'), 'two_factor_confirmed_at' => now()]));
    foreach (['/bestrijder', '/bestrijder/opdrachten', '/bestrijder/werkbon/1'] as $u) file_put_contents(public_path('_tmp' . str_replace('/', '_', $u) . '.html'), $this->get($u)->getContent());
})->skip(! getenv('RENDER'));
