@extends('layouts.base')
@section('body')
@include('partials.publicnav')
<main><div class="wrap">
@include('partials.alerts')
@yield('content')
</div></main>
@yield('after')
@include('partials.footer')
@endsection
