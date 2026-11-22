@extends('layouts.app', ['title' => 'Meldingen'])
@include('partials.leaflet')
@section('content')
<div class="row between"><h1>Meldingen {{ $org->name }}</h1><a class="btn ghost small" href="{{ route('gemeente.rapport') }}">Rapportage</a></div>
<div class="grid g4" style="margin-bottom:20px">
  <div class="card"><div class="stat oranje">{{ $counts['gemeld'] ?? 0 }}<small>Te beoordelen</small></div></div>
  <div class="card"><div class="stat">{{ ($counts['goedgekeurd'] ?? 0) }}<small>Wacht op opdracht</small></div></div>
  <div class="card"><div class="stat">{{ ($counts['opdracht_gegeven'] ?? 0) + ($counts['ingepland'] ?? 0) + ($counts['geen_toegang'] ?? 0) }}<small>Bij bestrijder</small></div></div>
  <div class="card"><div class="stat groen">{{ ($counts['bestreden'] ?? 0) + ($counts['gefactureerd'] ?? 0) }}<small>Bestreden</small></div></div>
</div>
<nav class="tabs">
  @foreach(['open' => 'Open', 'gemeld' => 'Te beoordelen', 'goedgekeurd' => 'Goedgekeurd', 'opdracht_gegeven' => 'Opdracht gegeven', 'ingepland' => 'Ingepland', 'geen_toegang' => 'Geen toegang', 'bestreden' => 'Bestreden', 'gefactureerd' => 'Gefactureerd', 'afgewezen' => 'Afgewezen', 'alle' => 'Alle'] as $k => $v)
    <a href="?status={{ $k }}" class="{{ $status === $k ? 'on' : '' }}">{{ $v }}</a>
  @endforeach
</nav>
<div class="grid g2" style="align-items:start">
  <div id="map" class="map tall"></div>
  <div class="tablewrap card white" style="padding:0">
    <table class="t">
      <thead><tr><th>#</th><th>Adres</th><th>Status</th><th>Gemeld</th></tr></thead>
      <tbody>
      @forelse($nests as $n)
        <tr class="link" onclick="location.href='{{ route('gemeente.nesten.show', $n) }}'">
          <td><a href="{{ route('gemeente.nesten.show', $n) }}">{{ $n->id }}</a>@if($n->urgent) <span title="Spoed">⚡</span>@endif</td>
          <td>{{ $n->address ?? number_format($n->lat, 5) . ', ' . number_format($n->lng, 5) }}@if($n->reports->count() > 1)<br><span class="small muted">{{ $n->reports->count() }} meldingen</span>@endif</td>
          <td>@include('partials.badge', ['s' => $n->status])@if($n->activeAssignment)<br><span class="small muted">{{ $n->activeAssignment->contract->bestrijder->name }}</span>@endif</td>
          <td class="small">{{ $n->created_at->format('d-m H:i') }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="muted" style="padding:24px">Geen meldingen in deze lijst.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
<div style="margin-top:16px">{{ $nests->links('partials.pager') }}</div>
@endsection
@push('scripts')
@php($mapData = $nests->map(fn ($n) => ['id' => $n->id, 'lat' => $n->lat, 'lng' => $n->lng, 's' => $n->status, 'url' => route('gemeente.nesten.show', $n), 'a' => $n->address])->values())
<script>
document.addEventListener('DOMContentLoaded', function () {
  var data = {!! json_encode($mapData) !!};
  var m = MH.map('map'), b = [];
  data.forEach(function (n) { MH.pin(n.lat, n.lng, n.s).addTo(m).bindPopup('<a href="' + n.url + '">#' + n.id + '</a> ' + (n.a || '')); b.push([n.lat, n.lng]); });
  if (b.length) m.fitBounds(b, {padding: [30, 30], maxZoom: 15});
});
</script>
@endpush
