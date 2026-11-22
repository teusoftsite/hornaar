@extends('layouts.base')
@section('body')
<header class="balk"><div class="wrap"><a class="logo-link" href="{{ route('home') }}">@include('partials.logo', ['dark' => true, 'h' => 34, 'id' => 'auth'])</a></div></header>
<main><div class="wrap" style="max-width:440px">
  @include('partials.alerts')
  <div class="card stack" style="padding:28px">@yield('content')</div>
</div></main>
@endsection
