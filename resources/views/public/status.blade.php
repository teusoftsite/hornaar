@extends('layouts.public', ['title' => 'Melding #' . $nest->id])
@section('content')
@php($steps = ['gemeld' => 'Gemeld', 'goedgekeurd' => 'Beoordeeld door gemeente', 'ingepland' => 'Bestrijding ingepland', 'bestreden' => 'Nest bestreden'])
@php($rank = ['gemeld' => 0, 'goedgekeurd' => 1, 'opdracht_gegeven' => 1, 'ingepland' => 2, 'geen_toegang' => 2, 'bestreden' => 3, 'gefactureerd' => 3])
@php($current = $nest->mergedInto ?? $nest)
<div class="grid g2" style="align-items:start">
  <div class="stack">
    <h1>Melding #{{ $nest->id }}</h1>
    @if($nest->mergedInto)
      <p class="alert ok">Dit nest was al eerder gemeld (#{{ $nest->mergedInto->id }}). We volgen het daar verder.</p>
    @endif
    @if($current->status === 'afgewezen')
      <div class="card"><h3>Beoordeeld</h3><p>Volgens de gemeente gaat het waarschijnlijk niet om een nest van de Aziatische hoornaar. Bedankt voor het melden!</p></div>
    @else
      <ol class="timeline">
        @foreach($steps as $k => $label)
          @php($done = ($rank[$current->status] ?? 0) >= $rank[$k])
          <li style="{{ $done ? '' : 'opacity:.45' }}"><strong>{{ $label }}</strong>@if($done && $k === 'gemeld')<br><span class="small muted">{{ $nest->created_at->translatedFormat('j F Y, H:i') }}</span>@endif</li>
        @endforeach
      </ol>
    @endif
    @if(! $nest->isParticulier())
      <p class="muted">{{ $nest->gemeente ? 'Gemeente: ' . $nest->gemeente->name : 'Openbare plek: we sturen je melding door naar de gemeente.' }}</p>
    @elseif(! $lead && $nest->status === 'gemeld' && ! $nest->mergedInto)
      <div class="card stack" style="border-color:var(--oranje)">
        <h3 style="margin:0">Een bestrijder laten komen</h3>
        <p style="margin:0">Het nest hangt op je eigen terrein. Wij zoeken de dichtstbijzijnde bestrijder voor je. Die belt je, spreekt een prijs af en rekent met je af. MeldHoornaar zelf is gratis.</p>
        <form method="post" action="{{ route('melding.bestrijder', $report->token) }}" class="stack">@csrf
          <label class="f">Naam<input name="name" value="{{ old('name', $report->reporter_name) }}" required autocomplete="name"></label>
          <label class="f">Telefoon<input name="phone" type="tel" value="{{ old('phone', $report->reporter_phone) }}" required autocomplete="tel"></label>
          <label class="f">E-mail (voor updates)<input name="email" type="email" value="{{ old('email', $report->reporter_email) }}" autocomplete="email"></label>
          <label class="row small"><input type="checkbox" name="akkoord" value="1" required> Ik geef toestemming om mijn naam, telefoonnummer en de plek van het nest te delen met de bestrijder.</label>
          <button class="btn orange">Bestrijder vragen</button>
        </form>
      </div>
    @elseif($lead)
      <div class="card stack">
        <h3 style="margin:0">Bestrijder</h3>
        @switch($lead->status)
          @case('zoeken') @case('aangeboden')
            <p style="margin:0">We zoeken een bestrijder bij jou in de buurt. Je hoort het zo snel mogelijk.</p> @break
          @case('geaccepteerd') @case('afgerond')
            <p style="margin:0"><strong>{{ $lead->bestrijder?->name }}</strong> komt je nest weghalen en neemt contact met je op.@if($lead->bestrijder?->phone) Telefoon: <a href="tel:{{ preg_replace('/[^0-9+]/', '', $lead->bestrijder->phone) }}">{{ $lead->bestrijder->phone }}</a>@endif</p> @break
          @default
            <p style="margin:0">Op dit moment is er geen bestrijder beschikbaar. We zoeken verder en nemen contact met je op.</p>
        @endswitch
      </div>
    @endif
    <p class="small muted">Bewaar deze pagina om je melding te volgen.</p>
  </div>
  <div class="card stack">
    <h3>Wat kun je nu doen?</h3>
    <p>Blijf uit de buurt van het nest en waarschuw buren met kinderen of huisdieren.</p>
    <p>Nog een nest gezien? <a href="{{ route('melden') }}">Meld het ook</a>.</p>
  </div>
</div>
@endsection
@if(request()->boolean('nieuw'))
@push('scripts')
<script>if (window.mhEvent) mhEvent('melding_verstuurd', {gemeente: @json($nest->gemeente?->name ?? 'onbekend')}); if (history.replaceState) history.replaceState(null, '', location.pathname);</script>
@endpush
@endif
