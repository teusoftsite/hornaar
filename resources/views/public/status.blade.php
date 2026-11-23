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
    <p class="muted">{{ $nest->gemeente ? 'Gemeente: ' . $nest->gemeente->name : 'Je gemeente doet nog niet mee met MeldHoornaar. We sturen je melding door naar de juiste instantie.' }}</p>
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
