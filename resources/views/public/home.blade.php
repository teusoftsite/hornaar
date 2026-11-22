@extends('layouts.base')
@section('body')
<div style="font-family: Inter, system-ui, sans-serif; color: #17181B; background: #EEF0F2">

@include('partials.publicnav')

<section style="max-width: 1100px; margin: 0 auto; padding: 64px 24px 80px; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 48px; align-items: start">
<div style="display: flex; flex-direction: column; gap: 20px; padding-top: 16px">
<h1 style="margin: 0; font-family: 'Space Grotesk', Inter, sans-serif; font-weight: 700; font-size: 64px; line-height: 1; letter-spacing: -1.5px">Nest gezien?<br><span style="color: #B85F00">Meld het.</span></h1>
<p style="margin: 0; font-size: 20px; line-height: 30px; color: #5B6169; max-width: 440px">Foto maken, locatie delen, versturen. Klaar. De gemeente zorgt dat het nest wordt weggehaald.</p>
<p style="margin: 0; display: flex; gap: 10px; align-items: center; font-size: 15px; font-weight: 500"><svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#B85F00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>Blijf op afstand. Niet zelf verwijderen.</p>
</div>

<form id="snel" method="post" action="{{ route('melden.store') }}" enctype="multipart/form-data" style="background: #FFFFFF; border: 1px solid #DCE0E4; border-radius: 12px; padding: 32px; display: flex; flex-direction: column; gap: 16px">
@csrf
@include('partials.alerts')
<label style="font: 600 18px Inter, sans-serif; background: #17181B; color: #F2F3F5; border: 0; border-radius: 10px; padding: 20px; min-height: 64px; display: flex; align-items: center; justify-content: center; gap: 12px; cursor: pointer"><svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3z"/><circle cx="12" cy="13" r="3"/></svg><span id="fotostatus">1. Maak een foto</span><input type="file" name="photo" accept="image/*" capture="environment" style="position: absolute; width: 1px; height: 1px; opacity: 0" onchange="document.getElementById('fotostatus').textContent='✓ Foto toegevoegd'"></label>
<button type="button" id="locbtn" style="font: 600 18px Inter, sans-serif; background: #17181B; color: #F2F3F5; border: 0; border-radius: 10px; padding: 20px; min-height: 64px; display: flex; align-items: center; justify-content: center; gap: 12px; cursor: pointer"><svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg><span>2. Deel mijn locatie</span></button>
<input type="hidden" name="lat" id="lat"><input type="hidden" name="lng" id="lng">
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600">Opmerking (mag leeg)<input type="text" name="note" maxlength="1000" placeholder="bijv. hoog in de eik achter het huis" style="font: 400 16px Inter, sans-serif; padding: 12px 14px; border: 1px solid #DCE0E4; border-radius: 6px; background: #EEF0F2; color: #17181B"></label>
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600">E-mail voor updates (mag leeg)<input type="email" name="email" autocomplete="email" style="font: 400 16px Inter, sans-serif; padding: 12px 14px; border: 1px solid #DCE0E4; border-radius: 6px; background: #EEF0F2; color: #17181B"></label>
<button type="submit" style="font: 600 18px Inter, sans-serif; background: #F38B12; color: #17181B; border: 0; border-radius: 10px; padding: 18px; min-height: 60px; cursor: pointer">3. Versturen</button>
<p style="margin: 0; font-size: 13px; color: #5B6169; text-align: center">Geen account nodig. Gratis. Liever de plek op de kaart aanwijzen? <a href="{{ route('melden') }}">Uitgebreid melden</a> · <a href="{{ route('privacy') }}">Privacy</a></p>
</form>
</section>


<section style="max-width: 1100px; margin: 0 auto; padding: 0 24px 72px; display: flex; flex-direction: column; gap: 20px">
<figure style="margin: 0; position: relative; border-radius: 12px; overflow: hidden">
<img src="/img/info/6a8640ec99196a11072911c7a45be953.jpg" alt="Aziatische hoornaar van opzij: zwart borststuk, één oranje band, gele pootpunten" style="width: 100%; height: 420px; object-fit: cover; display: block">
<figcaption style="position: absolute; left: 0; right: 0; bottom: 0; padding: 48px 28px 22px; background: linear-gradient(transparent, rgba(23,24,27,.85)); color: #F2F3F5; display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap">
<span style="display: flex; flex-direction: column; gap: 4px"><strong style="font-family: 'Space Grotesk', Inter, sans-serif; font-size: 28px; letter-spacing: -.5px">Zo herken je hem</strong><span style="font-size: 16px">Zwart borststuk, één oranje band, gele pootpunten.</span></span>
<span style="font-size: 11px; color: #C9CDD2">AI-beeld, naar foto van Vespa-Watch (INBO), CC BY 4.0</span></figcaption></figure>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px">
<a href="{{ route('info.herkennen') }}" style="background: #FFFFFF; border: 1px solid #DCE0E4; border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; text-decoration: none; color: #17181B">
<img src="/img/info/44deab85b484bc27d424972734ca04d7.jpg" alt="Aziatische en Europese hoornaar naast elkaar" loading="lazy" style="width: 100%; height: 220px; object-fit: cover; display: block">
<span style="padding: 16px 20px 20px; display: flex; flex-direction: column; gap: 6px"><strong style="font-family: 'Space Grotesk', Inter, sans-serif; font-size: 18px; color: #B85F00">Aziatisch of Europees? →</strong><span style="font-size: 14px; line-height: 21px; color: #5B6169; font-weight: 400">De Europese hoornaar is roodbruin met veel geel en mag blijven. Zie het verschil.</span></span></a>
<a href="{{ route('info.nesten-zoeken') }}" style="background: #FFFFFF; border: 1px solid #DCE0E4; border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; text-decoration: none; color: #17181B">
<img src="/img/info/9a982e969d75f4b618ac4ad286a7763f.jpg" alt="Voorjaarsnest onder een afdak" loading="lazy" style="width: 100%; height: 220px; object-fit: cover; display: block">
<span style="padding: 16px 20px 20px; display: flex; flex-direction: column; gap: 6px"><strong style="font-family: 'Space Grotesk', Inter, sans-serif; font-size: 18px; color: #B85F00">Voorjaarsnest →</strong><span style="font-size: 14px; line-height: 21px; color: #5B6169; font-weight: 400">April tot juni: klein en bol, onder een afdak, in een schuur of carport.</span></span></a>
<a href="{{ route('info.nesten-zoeken') }}" style="background: #FFFFFF; border: 1px solid #DCE0E4; border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; text-decoration: none; color: #17181B">
<img src="/img/info/495bd1a17692afb7432d59717ca259be.jpg" alt="Zomernest hoog in een eik" loading="lazy" style="width: 100%; height: 220px; object-fit: cover; display: block">
<span style="padding: 16px 20px 20px; display: flex; flex-direction: column; gap: 6px"><strong style="font-family: 'Space Grotesk', Inter, sans-serif; font-size: 18px; color: #B85F00">Zomernest →</strong><span style="font-size: 14px; line-height: 21px; color: #5B6169; font-weight: 400">Vanaf juli: groot, hoog in een boom, opening aan de zijkant. Goed te zien als de bladeren vallen.</span></span></a>
</div>
<p style="margin: 0; font-size: 12px; color: #5B6169">Foto's: AI-beeld, naar foto van Vespa-Watch (INBO), CC BY 4.0. <a href="{{ route('info') }}">Alles over de Aziatische hoornaar</a></p>
</section>
<section style="background: #17181B; color: #F2F3F5">
<div style="max-width: 1100px; margin: 0 auto; padding: 72px 24px; display: flex; flex-direction: column; gap: 32px">
<h2 style="margin: 0; font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 36px; letter-spacing: -0.6px">Wat gebeurt er na je melding?</h2>
<ol style="list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px">
<li style="display: flex; flex-direction: column; gap: 6px"><span style="font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 40px; color: #FFAA45">1</span><strong style="font-size: 18px">De gemeente kijkt mee</strong><span style="font-size: 15px; line-height: 22px; color: #A3A9B1">Je melding komt direct bij jouw gemeente binnen.</span></li>
<li style="display: flex; flex-direction: column; gap: 6px"><span style="font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 40px; color: #FFAA45">2</span><strong style="font-size: 18px">Een bestrijder komt</strong><span style="font-size: 15px; line-height: 22px; color: #A3A9B1">Meestal ’s avonds, als de hoornaars in het nest zijn.</span></li>
<li style="display: flex; flex-direction: column; gap: 6px"><span style="font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 40px; color: #FFAA45">3</span><strong style="font-size: 18px">Je hoort het</strong><span style="font-size: 15px; line-height: 22px; color: #A3A9B1">Laat je e-mail achter, dan krijg je bericht als het nest weg is.</span></li>
</ol>
</div>
</section>

<footer style="background: #17181B; color: #A3A9B1; border-top: 1px solid #2C3035">
<div style="max-width: 1100px; margin: 0 auto; padding: 32px 24px; display: flex; justify-content: space-between; align-items: center; gap: 24px; flex-wrap: wrap; font-size: 14px">
@include('partials.logo', ['dark' => true, 'h' => 32, 'id' => 'homef'])
       <a href="{{ route('zakelijk') }}" style="color: #FFAA45">Voor gemeenten en bestrijders</a>
</div>
</footer>
</div>
@endsection
@push('scripts')
<script>
document.getElementById('locbtn').addEventListener('click', function () {
  var b = this, t = b.querySelector('span'); t.textContent = 'Locatie zoeken…';
  if (!navigator.geolocation) { t.textContent = 'Niet beschikbaar'; return; }
  navigator.geolocation.getCurrentPosition(function (p) {
    document.getElementById('lat').value = p.coords.latitude; document.getElementById('lng').value = p.coords.longitude;
    t.textContent = '✓ Locatie gedeeld'; b.style.background = '#1E7A46';
  }, function () { t.textContent = 'Geweigerd – gebruik uitgebreid melden'; }, {enableHighAccuracy: true, timeout: 15000});
});
document.getElementById('snel').addEventListener('submit', function (e) {
  if (!document.getElementById('lat').value) { e.preventDefault(); location.href = '{{ route('melden') }}'; }
});
</script>
@endpush
