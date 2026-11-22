@extends('layouts.app', ['title' => 'Route'])
@include('partials.leaflet')
@section('content')
<div class="row between">
  <h1>Route {{ \Carbon\Carbon::parse($date)->translatedFormat('l j F') }}</h1>
  <form class="row">
    <input type="date" name="datum" value="{{ $date }}" style="width:auto" onchange="this.form.submit()">
    @if(auth()->user()->isBestrijderEigenaar())
    <select name="medewerker" style="width:auto" onchange="this.form.submit()">@foreach($team as $t)<option value="{{ $t->id }}" @selected($who === $t->id)>{{ $t->name }}</option>@endforeach</select>
    @endif
  </form>
</div>
@if(! $org->base_lat)<p class="alert err">Stel je werkplaats in bij Instellingen, dan begint en eindigt de route daar.</p>@endif
<div class="grid g2" style="align-items:start">
  <div id="map" class="map tall"></div>
  <div class="stack">
    @if($stops->count() > 1)
    <form method="post" action="{{ route('bestrijder.route.optimize') }}">@csrf
      <input type="hidden" name="datum" value="{{ $date }}"><input type="hidden" name="medewerker" value="{{ $who }}">
      <button class="btn orange" style="width:100%">Kortste volgorde berekenen</button>
    </form>
    @endif
    @forelse($stops as $a)
      <div class="card white row between">
        <span><strong>{{ $loop->iteration }}. {{ $a->nest->address ?? 'Nest #' . $a->nest->id }}</strong><br><span class="small muted">{{ $a->contract->gemeente->name }} @if($a->nest->height_m)· {{ $a->nest->height_m }} m @endif</span></span>
        <span class="row" style="gap:6px"><a class="btn ghost small" target="_blank" rel="noopener" href="https://www.google.com/maps/dir/?api=1&destination={{ $a->nest->lat }},{{ $a->nest->lng }}">Navigeer</a><a class="btn orange small" href="{{ route('bestrijder.werkbon', $a) }}">Werkbon</a></span>
      </div>
    @empty
      <p class="muted">Geen nesten gepland op deze dag. Plan ze via <a href="{{ route('bestrijder.opdrachten') }}">Opdrachten</a>.</p>
    @endforelse
    @if($stops->count() > 1)
      <a class="btn ghost" target="_blank" rel="noopener" href="https://www.google.com/maps/dir/{{ $org->base_lat ? $org->base_lat . ',' . $org->base_lng . '/' : '' }}{{ $stops->map(fn ($a) => $a->nest->lat . ',' . $a->nest->lng)->implode('/') }}{{ $org->base_lat ? '/' . $org->base_lat . ',' . $org->base_lng : '' }}">Hele route in Google Maps</a>
    @endif
  </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var m = MH.map('map'), pts = [];
  @if($org->base_lat) var base = [{{ $org->base_lat }}, {{ $org->base_lng }}]; L.marker(base).addTo(m).bindPopup('Werkplaats'); pts.push(base); @endif
  @foreach($stops as $a) MH.pin({{ $a->nest->lat }}, {{ $a->nest->lng }}, 'ingepland', '{{ $loop->iteration }}').addTo(m); pts.push([{{ $a->nest->lat }}, {{ $a->nest->lng }}]); @endforeach
  @if($org->base_lat) if (pts.length > 1) pts.push(base); @endif
  if (pts.length > 1) { L.polyline(pts, {color: '#17181B', weight: 3, dashArray: '6 6'}).addTo(m); m.fitBounds(pts, {padding: [30, 30]}); }
  else if (pts.length) m.setView(pts[0], 14);
});
</script>
@endpush
