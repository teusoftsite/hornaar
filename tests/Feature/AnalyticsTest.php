<?php

it('laadt geen analytics zonder meet-ID', function () {
    config(['meldhoornaar.ga_id' => null]);
    $this->get('/')->assertOk()->assertDontSee('googletagmanager');
});

it('laadt GA4 met cookiebanner op publieke pagina\'s, niet in de beheeromgeving', function () {
    config(['meldhoornaar.ga_id' => 'G-TEST123']);
    $this->get('/')->assertSee('gtag/js?id=G-TEST123', false)->assertSee("analytics_storage: 'denied'", false)->assertSee('Mogen we meten?');
    $this->get('/info/herkennen')->assertSee('G-TEST123');
    $u = member(gemeente(), 'gemeente');
    $this->actingAs($u)->get('/gemeente')->assertOk()->assertDontSee('G-TEST123');
});

it('meldt een conversie na een nieuwe melding', function () {
    config(['meldhoornaar.ga_id' => 'G-TEST123']);
    gemeente();
    $url = $this->postJson('/api/reports', ['plek' => 'openbaar', 'lat' => 51.5751, 'lng' => 4.6412])->json('status_url');
    expect($url)->toContain('nieuw=1');
    $this->get($url)->assertSee("mhEvent('melding_verstuurd'", false);
});
