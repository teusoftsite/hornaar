@extends('layouts.app', ['title' => 'Particuliere aanvragen'])
@section('content')
<div class="paginakop"><h1>Particuliere aanvragen</h1>
  <span class="small muted">{{ $org->accepts_leads ? 'U ontvangt aanvragen binnen ' . $org->service_radius_km . ' km.' : 'U ontvangt geen aanvragen.' }} <a href="{{ route('bestrijder.instellingen') }}">Instellingen</a></span>
</div>
@if($offered->isNotEmpty())
<h2>Nieuw voor u</h2>
<div class="grid g2" style="margin-bottom:24px">
@foreach($offered as $l)
  <div class="card stack" style="border-color:var(--oranje)">
    <div class="row between"><strong>{{ $l->nest->address ?? 'Nest #' . $l->nest->id }}</strong><span class="small muted">nog {{ now()->diffForHumans($l->expires_at, ['parts' => 2, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]) }}</span></div>
    <p class="small muted" style="margin:0">@if($l->nest->location_type){{ ucfirst($l->nest->location_type) }} · @endif @if($l->nest->height_m)ca. {{ $l->nest->height_m }} m · @endif {{ round(\App\Support\Geo::distance($l->nest->lat, $l->nest->lng, $org->base_lat, $org->base_lng) / 1000, 1) }} km van uw werkplaats</p>
    @foreach($l->nest->reports as $r)@if($r->photo_path)<img class="photo" src="{{ \App\Support\Photo::url($r->photo_path) }}" alt="Foto melder">@endif @endforeach
    <p class="small" style="margin:0">Na accepteren ziet u naam en telefoonnummer. Kosten: {{ \App\Support\Money::format(config('meldhoornaar.lead_fee_cents')) }} excl. btw.</p>
    <div class="row">
      <form method="post" action="{{ route('bestrijder.leads.accept', $l) }}">@csrf<button class="btn orange">Accepteren</button></form>
      <form method="post" action="{{ route('bestrijder.leads.decline', $l) }}">@csrf<button class="btn">Weigeren</button></form>
    </div>
  </div>
@endforeach
</div>
@endif
<h2>Geaccepteerd</h2>
<div class="tablewrap card" style="padding:0"><table class="t">
  <thead><tr><th>Inwoner</th><th>Adres</th><th>Geaccepteerd</th><th>Status</th><th></th></tr></thead>
  @forelse($mine as $l)
  <tr>
    <td><strong>{{ $l->name }}</strong><br><a href="tel:{{ preg_replace('/[^0-9+]/', '', $l->phone) }}">{{ $l->phone }}</a>@if($l->email)<br><span class="small">{{ $l->email }}</span>@endif</td>
    <td>{{ $l->nest->address }}<br><a class="small" target="_blank" rel="noopener" href="https://www.google.com/maps/dir/?api=1&destination={{ $l->nest->lat }},{{ $l->nest->lng }}">navigeer</a></td>
    <td class="small">{{ $l->accepted_at?->format('d-m-Y H:i') }}</td>
    <td><span class="badge {{ $l->status === 'afgerond' ? 'b-bestreden' : 'b-ingepland' }}">{{ $l->status === 'afgerond' ? ($l->result === 'bestreden' ? 'Bestreden' : 'Geen nest') : 'Open' }}</span></td>
    <td>@if($l->status === 'geaccepteerd')
      <form method="post" action="{{ route('bestrijder.leads.complete', $l) }}" class="row" style="gap:6px">@csrf
        <select name="result" style="width:auto"><option value="bestreden">Bestreden</option><option value="geen_nest">Geen nest</option></select>
        <button class="btn small">Afronden</button>
      </form>@endif</td>
  </tr>
  @empty
  <tr><td colspan="5" class="muted" style="padding:24px">Nog geen particuliere aanvragen.</td></tr>
  @endforelse
</table></div>
@endsection
