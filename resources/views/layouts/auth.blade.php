@extends('layouts.base')
@section('body')
<div class="stripe"></div>
<main><div class="wrap" style="max-width:440px">
  <p style="text-align:center;margin:24px 0"><a href="{{ route('home') }}"><img src="/logo.svg" alt="MeldHoornaar.nl" style="height:44px"></a></p>
  @include('partials.alerts')
  <div class="card white stack">@yield('content')</div>
</div></main>
@endsection
