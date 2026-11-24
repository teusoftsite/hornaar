@extends('layouts.public', ['title' => 'Nest melden'])
@push('head')<style>.plek{display:flex;gap:12px;align-items:flex-start;border:1px solid #DCE0E4;border-radius:10px;padding:14px 16px;cursor:pointer}.plek input{width:20px;height:20px;margin:2px 0 0;accent-color:#B85F00;flex:none}.plek:has(input:checked){border:2px solid #F38B12;background:#FFF6EC;padding:13px 15px}</style>@endpush
@include('partials.leaflet')
@if($turnstile)
@push('head')<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>@endpush
@endif
@section('content')
<section style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 48px; align-items: start; padding: 24px 0 56px">
<div style="display: flex; flex-direction: column; gap: 20px; padding-top: 16px">
<h1 style="margin: 0; font-family: 'Space Grotesk', Inter, sans-serif; font-weight: 700; font-size: clamp(40px, 7vw, 64px); line-height: 1; letter-spacing: -1.5px">Nest gezien?<br><span style="color: #B85F00">Meld het.</span></h1>
<p style="margin: 0; font-size: 20px; line-height: 30px; color: #5B6169; max-width: 440px">Foto maken, locatie delen, versturen. Wij controleren je melding en koppelen je aan een bestrijder bij jou in de buurt.</p>
<p style="margin: 0; display: flex; gap: 10px; align-items: center; font-size: 15px; font-weight: 500"><svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#B85F00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>Blijf op afstand. Niet zelf verwijderen.</p>
</div>

<form id="meld" method="post" action="{{ route('melden.store') }}" enctype="multipart/form-data" style="background: #FFFFFF; border: 1px solid #DCE0E4; border-radius: 12px; padding: 32px; display: flex; flex-direction: column; gap: 16px">
@csrf
<fieldset style="border: 0; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px">
<legend style="font-size: 18px; font-weight: 600; margin-bottom: 10px">1. Waar hangt het nest?</legend>
@foreach(['eigen' => ['Op mijn eigen terrein', 'Tuin, schuur, dak of oprit. We koppelen je aan een bestrijder bij jou in de buurt.'], 'openbaar' => ['Op een openbare plek', 'Park, speeltuin, straat of schoolplein. We sturen je melding door naar de gemeente.']] as $k => [$t, $d])
<label class="plek"><input type="radio" name="plek" value="{{ $k }}" @checked(old('plek', 'eigen') === $k)><span style="display: flex; flex-direction: column; gap: 2px"><strong style="font-size: 16px">{{ $t }}</strong><span style="font-size: 14px; color: #5B6169">{{ $d }}</span></span></label>
@endforeach
</fieldset>
<label for="photo" style="font: 600 18px Inter, sans-serif; background: #17181B; color: #F2F3F5; border: 0; border-radius: 10px; padding: 20px; min-height: 64px; display: flex; align-items: center; justify-content: center; gap: 12px; cursor: pointer"><svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3z"/><circle cx="12" cy="13" r="3"/></svg><span id="fotostatus">2. Maak een foto</span></label>
<input id="photo" type="file" name="photo" accept="image/*" capture="environment" style="position:absolute;width:1px;height:1px;opacity:0">
<img id="preview" class="photo" alt="" hidden>
<button type="button" id="locbtn" style="font: 600 18px Inter, sans-serif; background: #17181B; color: #F2F3F5; border: 0; border-radius: 10px; padding: 20px; min-height: 64px; display: flex; align-items: center; justify-content: center; gap: 12px; cursor: pointer"><svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg><span>3. Deel mijn locatie</span></button>
<p class="small muted" style="margin:0">Of tik op de kaart waar het nest zit. Je kunt de pin verslepen.</p>
<div id="pickmap" class="map" style="height: 260px"></div>
<input type="hidden" name="lat" id="lat" value="{{ old('lat') }}"><input type="hidden" name="lng" id="lng" value="{{ old('lng') }}">
<p id="where" class="small" style="margin:0"></p>
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600">Opmerking (mag leeg)<input type="text" name="note" maxlength="1000" value="{{ old('note') }}" placeholder="bijv. hoog in de eik achter het huis"></label>
<fieldset style="border: 0; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px">
<legend style="font-size: 18px; font-weight: 600; margin-bottom: 6px">Je gegevens</legend>
<p id="contactuitleg" style="margin: 0; font-size: 14px; color: #5B6169"></p>
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600"><span>Naam <span class="verplicht"></span></span><input name="name" value="{{ old('name') }}" autocomplete="name" maxlength="120"></label>
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600"><span>Telefoon <span class="verplicht"></span></span><input name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" maxlength="30"></label>
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600">E-mail (mag leeg, voor updates)<input type="email" name="email" value="{{ old('email') }}" autocomplete="email"></label>
<label id="akkoordrij" style="display: flex; gap: 10px; align-items: flex-start; font-size: 14px"><input type="checkbox" name="akkoord" value="1" @checked(old('akkoord')) style="width: 18px; height: 18px; margin: 2px 0 0; accent-color: #B85F00; flex: none"> Ik geef toestemming om mijn naam, telefoonnummer en de plek van het nest te delen met de bestrijder.</label>
</fieldset>
@if($turnstile)<div class="cf-turnstile" data-sitekey="{{ $turnstile }}" data-language="nl"></div>@endif
<button type="submit" id="send" style="font: 600 18px Inter, sans-serif; background: #F38B12; color: #17181B; border: 0; border-radius: 10px; padding: 18px; min-height: 60px; cursor: pointer">4. Versturen</button>
<p style="margin: 0; font-size: 13px; color: #5B6169; text-align: center">Geen account nodig. Gratis. <a href="{{ route('privacy') }}">Privacy</a></p>
</form>
</section>
@endsection
@section('after')
<section style="background: #17181B; color: #F2F3F5">
<div style="max-width: 1100px; margin: 0 auto; padding: 72px 24px; display: flex; flex-direction: column; gap: 32px">
<h2 style="margin: 0; font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 36px; letter-spacing: -0.6px">Wat gebeurt er na je melding?</h2>
<ol style="list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px">
<li style="display: flex; flex-direction: column; gap: 6px"><span style="font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 40px; color: #FFAA45">1</span><strong style="font-size: 18px">Wij controleren je foto</strong><span style="font-size: 15px; line-height: 22px; color: #A3A9B1">Is het echt een Aziatische hoornaar? Dat checken we direct. Op een openbare plek gaat je melding daarna naar de gemeente, en is het verder geregeld.</span></li>
<li style="display: flex; flex-direction: column; gap: 6px"><span style="font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 40px; color: #FFAA45">2</span><strong style="font-size: 18px">Een bestrijder neemt contact op</strong><span style="font-size: 15px; line-height: 22px; color: #A3A9B1">Op eigen terrein belt of mailt een bestrijder uit jouw regio je. De afspraak en de kosten regel je samen.</span></li>
<li style="display: flex; flex-direction: column; gap: 6px"><span style="font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 40px; color: #FFAA45">3</span><strong style="font-size: 18px">Het nest wordt weggehaald</strong><span style="font-size: 15px; line-height: 22px; color: #A3A9B1">Meestal ’s avonds, als de hoornaars in het nest zijn.</span></li>
</ol>
</div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var lat = document.getElementById('lat'), lng = document.getElementById('lng'), where = document.getElementById('where');
  var p = MH.picker(document.getElementById('pickmap'), lat, lng, function (a, b) {
    where.textContent = 'Adres zoeken…';
    fetch('https://api.pdok.nl/bzk/locatieserver/search/v3_1/reverse?lat=' + a + '&lon=' + b + '&rows=1&type=adres')
      .then(function (r) { return r.json(); })
      .then(function (j) { var d = j.response.docs[0]; where.textContent = d ? '📍 ' + d.weergavenaam : '📍 Plek gekozen'; })
      .catch(function () { where.textContent = '📍 Plek gekozen'; });
  });
  document.getElementById('locbtn').addEventListener('click', function () {
    var b = this, t = b.querySelector('span'); t.textContent = 'Locatie zoeken…';
    if (!navigator.geolocation) { t.textContent = 'Niet beschikbaar – tik op de kaart'; return; }
    navigator.geolocation.getCurrentPosition(function (pos) {
      p.set(pos.coords.latitude, pos.coords.longitude, 18); t.textContent = '✓ Locatie gedeeld'; b.style.background = '#1E7A46';
    }, function () { t.textContent = 'Geweigerd – tik op de kaart'; }, {enableHighAccuracy: true, timeout: 15000});
  });
  var form = document.getElementById('meld');
  function plekUpdate() {
    var eigen = form.querySelector('input[name=plek]:checked').value === 'eigen';
    ['name', 'phone'].forEach(function (n) { form.elements[n].required = eigen; });
    form.elements.akkoord.required = eigen;
    document.getElementById('akkoordrij').style.display = eigen ? 'flex' : 'none';
    form.querySelectorAll('.verplicht').forEach(function (s) { s.textContent = eigen ? '' : '(mag leeg)'; });
    document.getElementById('contactuitleg').textContent = eigen
      ? 'Een bestrijder bij jou in de buurt belt je om een afspraak te maken.'
      : 'Handig als de gemeente nog een vraag heeft over je melding.';
  }
  form.querySelectorAll('input[name=plek]').forEach(function (r) { r.addEventListener('change', plekUpdate); });
  plekUpdate();
  document.getElementById('photo').addEventListener('change', function () {
    var f = this.files[0]; if (!f) return;
    document.getElementById('fotostatus').textContent = '✓ Foto toegevoegd';
    var img = document.getElementById('preview'); img.src = URL.createObjectURL(f); img.hidden = false;
  });
  document.getElementById('meld').addEventListener('submit', function (e) {
    if (!lat.value) { e.preventDefault(); alertBox('Kies eerst de plek van het nest op de kaart.'); return; }
    if (!window.fetch || !window.FormData) return;
    e.preventDefault();
    var btn = document.getElementById('send'); btn.disabled = true; btn.textContent = 'Versturen…';
    fetch('{{ route('api.reports.store') }}', {method: 'POST', body: new FormData(this), headers: {'Accept': 'application/json'}})
      .then(function (r) { return r.json().then(function (j) { return {ok: r.ok, j: j}; }); })
      .then(function (x) {
        if (x.ok) { location.href = x.j.status_url; return; }
        var msg = x.j.errors ? Object.values(x.j.errors).flat().join(' ') : (x.j.message || 'Er ging iets mis.');
        alertBox(msg); btn.disabled = false; btn.textContent = '4. Versturen';
      })
      .catch(function () { alertBox('Geen verbinding. Probeer het zo opnieuw.'); btn.disabled = false; btn.textContent = '4. Versturen'; });
  });
  function alertBox(t) {
    var a = document.querySelector('.alert.err') || document.createElement('div');
    a.className = 'alert err'; a.setAttribute('role', 'alert'); a.textContent = t;
    document.getElementById('meld').prepend(a); window.scrollTo({top: 0, behavior: 'smooth'});
  }
});
</script>
@endpush
