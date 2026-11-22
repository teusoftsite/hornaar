@extends('layouts.public', ['title' => 'Nest melden'])
@include('partials.leaflet')
@if($turnstile)
@push('head')<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>@endpush
@endif
@section('content')
<h1>Nest melden</h1>
<p class="muted">Blijf op afstand (minstens 5 meter). Niet zelf verwijderen of verstoren.</p>
<form id="meld" method="post" action="{{ route('melden.store') }}" enctype="multipart/form-data" class="grid g2" style="align-items:start">
  @csrf
  <div class="card stack">
    <label class="btn dark big" for="photo"><span id="fotostatus">1. Maak een foto</span></label>
    <input id="photo" type="file" name="photo" accept="image/*" capture="environment" style="position:absolute;width:1px;height:1px;opacity:0">
    <img id="preview" class="photo" alt="" hidden>
    <button type="button" id="locbtn" class="btn dark big">2. Gebruik mijn locatie</button>
    <p class="small muted" style="margin:0">Of tik op de kaart waar het nest zit. Je kunt de pin verslepen.</p>
    <div id="pickmap" class="map"></div>
    <input type="hidden" name="lat" id="lat" value="{{ old('lat') }}"><input type="hidden" name="lng" id="lng" value="{{ old('lng') }}">
    <p id="where" class="small" style="margin:0"></p>
  </div>
  <div class="card stack">
    <div class="f" style="font-size:13px;font-weight:600">Waar zit het nest?</div>
    <div class="choice">
      @foreach(['boom' => 'In een boom', 'gebouw' => 'Aan een gebouw', 'struik' => 'In een struik/heg', 'grond' => 'Laag/grond'] as $k => $v)
      <label><input type="radio" name="location_type" value="{{ $k }}" @checked(old('location_type') === $k)><span>{{ $v }}</span></label>
      @endforeach
    </div>
    <label class="f">Geschatte hoogte in meters (mag leeg)<input type="number" name="height_m" min="0" max="40" step="0.5" value="{{ old('height_m') }}" inputmode="decimal"></label>
    <label class="f">Opmerking (mag leeg)<textarea name="note" maxlength="1000" placeholder="bijv. hoog in de eik achter het huis">{{ old('note') }}</textarea></label>
    <label class="f">E-mail voor updates (mag leeg)<input type="email" name="email" value="{{ old('email') }}" autocomplete="email"></label>
    @if($turnstile)<div class="cf-turnstile" data-sitekey="{{ $turnstile }}" data-language="nl"></div>@endif
    <button type="submit" id="send" class="btn orange big">3. Versturen</button>
    <p class="small muted" style="margin:0;text-align:center">Je e-mail gebruiken we alleen voor updates over dit nest. <a href="{{ route('privacy') }}">Privacy</a></p>
  </div>
</form>
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
    var b = this; b.textContent = 'Locatie zoeken…';
    if (!navigator.geolocation) { b.textContent = 'Niet beschikbaar – tik op de kaart'; return; }
    navigator.geolocation.getCurrentPosition(function (pos) {
      p.set(pos.coords.latitude, pos.coords.longitude, 18); b.textContent = '✓ Locatie gevonden'; b.classList.add('green');
    }, function () { b.textContent = 'Geweigerd – tik op de kaart'; }, {enableHighAccuracy: true, timeout: 15000});
  });
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
        alertBox(msg); btn.disabled = false; btn.textContent = '3. Versturen';
      })
      .catch(function () { alertBox('Geen verbinding. Probeer het zo opnieuw.'); btn.disabled = false; btn.textContent = '3. Versturen'; });
  });
  function alertBox(t) {
    var a = document.querySelector('.alert.err') || document.createElement('div');
    a.className = 'alert err'; a.setAttribute('role', 'alert'); a.textContent = t;
    document.querySelector('main .wrap').prepend(a); window.scrollTo({top: 0, behavior: 'smooth'});
  }
});
</script>
@endpush
