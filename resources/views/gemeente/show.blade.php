@extends('layouts.app', ['title' => 'Nest #' . $nest->id])
@include('partials.leaflet')
@section('content')
<p class="small"><a href="{{ route('gemeente.index') }}">← Meldingen</a></p>
<div class="row between">
  <h1>Nest #{{ $nest->id }} @include('partials.badge', ['s' => $nest->status]) @if($nest->urgent)<span class="badge b-spoed">Spoed</span>@endif</h1>
</div>
<p class="muted" style="margin-top:-8px">{{ $nest->address ?? 'Adres onbekend' }} · gemeld {{ $nest->created_at->translatedFormat('j F Y H:i') }}@if($nest->location_type) · {{ ucfirst($nest->location_type) }}@endif @if($nest->height_m) · ca. {{ rtrim(rtrim(number_format($nest->height_m, 1, ',', ''), '0'), ',') }} m hoog @endif</p>

<div class="grid g2" style="align-items:start">
  <div class="stack">
    <div id="map" class="map"></div>
    <p class="small"><a target="_blank" rel="noopener" href="https://www.google.com/maps?q={{ $nest->lat }},{{ $nest->lng }}">Open in Google Maps</a> · {{ number_format($nest->lat, 6) }}, {{ number_format($nest->lng, 6) }}</p>

    @if($duplicates->isNotEmpty())
    <div class="card" style="border-color:var(--oranje)">
      <h3>Mogelijk dubbel</h3>
      <p class="small">Binnen 25 meter ligt al een open melding:</p>
      @foreach($duplicates as $d)
        <form method="post" action="{{ route('gemeente.nesten.merge', $nest) }}" class="row between" style="margin-top:8px">@csrf
          <input type="hidden" name="target_id" value="{{ $d->id }}">
          <span><a href="{{ route('gemeente.nesten.show', $d) }}">#{{ $d->id }}</a> {{ $d->address }} @include('partials.badge', ['s' => $d->status]) <span class="small muted">{{ round(\App\Support\Geo::distance($nest->lat, $nest->lng, $d->lat, $d->lng)) }} m</span></span>
          <button class="btn small">Samenvoegen</button>
        </form>
      @endforeach
    </div>
    @endif

    @foreach($nest->reports as $r)
    <div class="card white stack">
      <div class="row between"><strong>Melding {{ $loop->iteration }}</strong><span class="small muted">{{ $r->created_at->format('d-m-Y H:i') }}</span></div>
      @if($r->photo_path)<a href="{{ \App\Support\Photo::url($r->photo_path) }}" target="_blank"><img class="photo" src="{{ \App\Support\Photo::url($r->photo_path) }}" alt="Foto melding"></a>@else<p class="small muted">Geen foto.</p>@endif
      @if($r->note)<p style="margin:0">“{{ $r->note }}”</p>@endif
      <p class="small muted" style="margin:0">@if($r->reporter_name || $r->reporter_phone){{ $r->reporter_name }}@if($r->reporter_phone) · <a href="tel:{{ preg_replace('/[^0-9+]/', '', $r->reporter_phone) }}">{{ $r->reporter_phone }}</a>@endif @if($r->reporter_email) · {{ $r->reporter_email }}@endif @else{{ $r->reporter_email ? 'Melder wil updates per e-mail' : 'Anoniem' }}@endif</p>
    </div>
    @endforeach
  </div>

  <div class="stack">
    @if($nest->status === 'gemeld')
    <div class="card stack">
      <h3>Beoordelen</h3>
      <form method="post" action="{{ route('gemeente.nesten.approve', $nest) }}" class="stack">@csrf
        <label class="row small"><input type="checkbox" name="urgent" value="1"> Spoed (bijv. bij school of speeltuin)</label>
        <button class="btn green" style="width:100%">Goedkeuren: Aziatische hoornaar</button>
      </form>
      <form method="post" action="{{ route('gemeente.nesten.reject', $nest) }}" class="stack">@csrf
        <label class="f">Reden afwijzen<select name="note" required><option value="">Kies…</option><option>Europese hoornaar</option><option>Wespennest</option><option>Bijen/hommels</option><option>Geen nest te zien</option><option>Oud/verlaten nest</option></select></label>
        <button class="btn ghost" style="width:100%">Afwijzen</button>
      </form>
    </div>
    @endif

    @if(in_array($nest->status, ['goedgekeurd', 'geen_toegang', 'opdracht_gegeven']))
    <div class="card stack">
      <h3>{{ $nest->status === 'goedgekeurd' ? 'Opdracht geven' : 'Opnieuw toewijzen' }}</h3>
      @if($contracts->isEmpty())
        <p class="small">Er is nog geen actief contract met een bestrijder. Neem contact op met MeldHoornaar.</p>
      @else
      <form method="post" action="{{ route('gemeente.nesten.assign', $nest) }}" class="stack">@csrf
        <label class="f">Bestrijder<select name="contract_id" required>@foreach($contracts as $c)<option value="{{ $c->id }}">{{ $c->bestrijder->name }} – {{ \App\Support\Money::format($c->standard_rate_cents) }} per nest</option>@endforeach</select></label>
        <button class="btn orange" style="width:100%">Opdracht versturen</button>
      </form>
      @endif
    </div>
    @endif

    @foreach($nest->assignments as $a)
      @foreach($a->workOrders as $wo)
      <div class="card white stack">
        <div class="row between"><strong>Werkbon {{ $a->contract->bestrijder->name }}</strong><span class="small muted">{{ optional($wo->finished_at)->format('d-m-Y H:i') }}</span></div>
        <p style="margin:0">Resultaat: <strong>{{ \App\Models\WorkOrder::RESULTS[$wo->result] }}</strong></p>
        @if($wo->agent)<p class="small" style="margin:0">Middel: {{ $wo->agent }} @if($wo->admission_number)(toelating {{ $wo->admission_number }})@endif @if($wo->quantity)· {{ $wo->quantity }}@endif</p>@endif
        <div class="row">@if($wo->photo_before)<img class="photo" style="max-width:48%" src="{{ \App\Support\Photo::url($wo->photo_before) }}" alt="Voor">@endif @if($wo->photo_after)<img class="photo" style="max-width:48%" src="{{ \App\Support\Photo::url($wo->photo_after) }}" alt="Na">@endif</div>
        @if($wo->notes)<p class="small" style="margin:0">{{ $wo->notes }}</p>@endif
      </div>
      @endforeach
    @endforeach

    <div class="card white">
      <h3>Geschiedenis</h3>
      <ol class="timeline">
        @foreach($nest->events as $e)
        <li><strong>{{ \App\Support\NestStatus::label($e->to) }}</strong> <span class="small muted">{{ $e->created_at->format('d-m-Y H:i') }}{{ $e->user ? ' · ' . $e->user->name : '' }}</span>@if($e->note)<br><span class="small">{{ $e->note }}</span>@endif</li>
        @endforeach
      </ol>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var m = MH.map('map', [{{ $nest->lat }}, {{ $nest->lng }}], 17);
  MH.pin({{ $nest->lat }}, {{ $nest->lng }}, @json($nest->status)).addTo(m);
  L.circle([{{ $nest->lat }}, {{ $nest->lng }}], {radius: 25, color: '#F38B12', weight: 1, fillOpacity: .05}).addTo(m);
  @foreach($duplicates as $d) MH.pin({{ $d->lat }}, {{ $d->lng }}, @json($d->status), '{{ $d->id }}').addTo(m); @endforeach
});
</script>
@endpush
