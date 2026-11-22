@extends('layouts.app', ['title' => 'Vandaag'])
@section('content')
<h1>Goedemiddag, {{ explode(' ', auth()->user()->name)[0] }}</h1>
<p id="queue" class="small" style="color:var(--oranjetekst)"></p>
<div class="grid g4" style="margin-bottom:20px">
  <a class="card" style="text-decoration:none;color:inherit" href="{{ route('bestrijder.opdrachten') }}"><div class="stat">{{ $open }}<small>Nieuwe opdrachten (nog plannen)</small></div></a>
  <div class="card"><div class="stat">{{ $planned }}<small>Ingepland</small></div></div>
  @if(auth()->user()->isBestrijderEigenaar())
  <a class="card" style="text-decoration:none;color:inherit" href="{{ route('bestrijder.facturen') }}"><div class="stat">{{ $concepts }}<small>Conceptfacturen</small></div></a>
  <a class="card" style="text-decoration:none;color:inherit" href="{{ route('bestrijder.facturen') }}"><div class="stat" style="{{ $late ? 'color:var(--rood)' : '' }}">{{ $late }}<small>Te laat betaald</small></div></a>
  @endif
</div>
<div class="row between"><h2>Vandaag</h2><a class="btn small" href="{{ route('bestrijder.route') }}">Route bekijken</a></div>
@forelse($today as $a)
  <div class="card white row between" style="margin-bottom:10px">
    <span><strong>{{ $a->route_order ? $a->route_order . '. ' : '' }}{{ $a->nest->address ?? 'Nest #' . $a->nest->id }}</strong><br><span class="small muted">{{ $a->contract?->gemeente?->name }} @if($a->nest->height_m)· {{ $a->nest->height_m }} m @endif @if($a->nest->urgent)· ⚡ spoed @endif</span></span>
    <a class="btn orange" href="{{ route('bestrijder.werkbon', $a) }}">Werkbon</a>
  </div>
@empty
  <p class="muted">Niets gepland voor vandaag.</p>
@endforelse
@endsection
