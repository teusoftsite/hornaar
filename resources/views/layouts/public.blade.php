@extends('layouts.base')
@section('body')
<header class="top"><div class="wrap">
  <a href="{{ route('home') }}"><img src="/logo.svg" alt="MeldHoornaar.nl"></a>
  <nav class="nav">
    <a href="{{ route('melden') }}" class="{{ request()->routeIs('melden') ? 'on' : '' }}">Nest melden</a>
    <a href="{{ route('zakelijk') }}" class="{{ request()->routeIs('zakelijk') ? 'on' : '' }}">Gemeenten &amp; bestrijders</a>
    @auth<a href="{{ auth()->user()->homeRoute() }}">Mijn omgeving</a>@else<a href="{{ route('login') }}">Inloggen</a>@endauth
  </nav>
</div></header>
<main><div class="wrap">
@include('partials.alerts')
@yield('content')
</div></main>
@include('partials.footer')
@endsection
