@extends('layouts.base')
@section('body')
@php($u = auth()->user())
<header class="balk"><div class="wrap">
  <a class="logo-link" href="{{ $u->homeRoute() }}" aria-label="MeldHoornaar.nl">@include('partials.logo', ['dark' => true, 'h' => 34, 'id' => 'app'])</a>
  <nav aria-label="Hoofdmenu">
    @if($u->isGemeente())
      <a href="{{ route('gemeente.index') }}" class="{{ request()->routeIs('gemeente.index','gemeente.nesten.*') ? 'on' : '' }}">Meldingen</a>
      <a href="{{ route('gemeente.rapport') }}" class="{{ request()->routeIs('gemeente.rapport') ? 'on' : '' }}">Rapportage</a>
    @elseif($u->isBestrijder())
      <a href="{{ route('bestrijder.index') }}" class="{{ request()->routeIs('bestrijder.index') ? 'on' : '' }}">Overzicht</a>
      <a href="{{ route('bestrijder.opdrachten') }}" class="{{ request()->routeIs('bestrijder.opdrachten','bestrijder.werkbon') ? 'on' : '' }}">Opdrachten</a>
      <a href="{{ route('bestrijder.route') }}" class="{{ request()->routeIs('bestrijder.route') ? 'on' : '' }}">Avondroute</a>
      <a href="{{ route('bestrijder.leads') }}" class="{{ request()->routeIs('bestrijder.leads') ? 'on' : '' }}">Particulier</a>
      @if($u->isBestrijderEigenaar())
      <a href="{{ route('bestrijder.facturen') }}" class="{{ request()->routeIs('bestrijder.facturen*') ? 'on' : '' }}">Facturen</a>
      <a href="{{ route('bestrijder.instellingen') }}" class="{{ request()->routeIs('bestrijder.instellingen') ? 'on' : '' }}">Instellingen</a>
      @endif
    @elseif($u->isBeheerder())
      <a href="{{ route('beheer.index') }}" class="{{ request()->routeIs('beheer.index', 'beheer.organisaties.*') ? 'on' : '' }}">Beheer</a>
      <a href="{{ route('beheer.leads') }}" class="{{ request()->routeIs('beheer.leads') ? 'on' : '' }}">Particulier</a>
    @endif
  </nav>
  <div class="rechts">
    <span>{{ $u->organisation?->name ?? 'MeldHoornaar' }} · {{ $u->name }}</span>
    <form method="post" action="{{ route('logout') }}">@csrf<button>Uitloggen</button></form>
  </div>
</div></header>
<main><div class="wrap">
@include('partials.alerts')
@yield('content')
</div></main>
<div class="offline" id="offline">Je bent offline. Werkbonnen worden bewaard en later verstuurd.</div>
@endsection
@push('scripts')
<script src="/js/app.js?v=2" defer></script>
@endpush
