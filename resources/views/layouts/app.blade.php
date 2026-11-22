@extends('layouts.base')
@section('body')
@php($u = auth()->user())
<header class="top"><div class="wrap">
  <a href="{{ $u->homeRoute() }}"><img src="/logo.svg" alt="MeldHoornaar.nl"></a>
  <nav class="nav">
    @if($u->isGemeente())
      <a href="{{ route('gemeente.index') }}" class="{{ request()->routeIs('gemeente.index','gemeente.nesten.*') ? 'on' : '' }}">Meldingen</a>
      <a href="{{ route('gemeente.rapport') }}" class="{{ request()->routeIs('gemeente.rapport') ? 'on' : '' }}">Rapportage</a>
    @elseif($u->isBestrijder())
      <a href="{{ route('bestrijder.index') }}" class="{{ request()->routeIs('bestrijder.index') ? 'on' : '' }}">Vandaag</a>
      <a href="{{ route('bestrijder.opdrachten') }}" class="{{ request()->routeIs('bestrijder.opdrachten','bestrijder.werkbon') ? 'on' : '' }}">Opdrachten</a>
      <a href="{{ route('bestrijder.route') }}" class="{{ request()->routeIs('bestrijder.route') ? 'on' : '' }}">Route</a>
      @if($u->isBestrijderEigenaar())
      <a href="{{ route('bestrijder.facturen') }}" class="{{ request()->routeIs('bestrijder.facturen*') ? 'on' : '' }}">Facturen</a>
      <a href="{{ route('bestrijder.instellingen') }}" class="{{ request()->routeIs('bestrijder.instellingen') ? 'on' : '' }}">Instellingen</a>
      @endif
    @elseif($u->isBeheerder())
      <a href="{{ route('beheer.index') }}" class="{{ request()->routeIs('beheer.*') ? 'on' : '' }}">Beheer</a>
    @endif
    <span class="who">{{ $u->name }} · {{ $u->organisation?->name ?? 'MeldHoornaar' }}</span>
    <form method="post" action="{{ route('logout') }}">@csrf<button>Uitloggen</button></form>
  </nav>
</div></header>
<div class="stripe"></div>
<main><div class="wrap">
@include('partials.alerts')
@yield('content')
</div></main>
<div class="offline" id="offline">Je bent offline. Werkbonnen worden bewaard en later verstuurd.</div>
@endsection
@push('scripts')
<script src="/js/app.js?v=1" defer></script>
@endpush
