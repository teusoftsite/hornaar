@extends('layouts.base')
@section('body')
@include('partials.publicnav')
<main><div class="wrap">
@include('partials.alerts')
@yield('content')
</div></main>
@include('partials.footer')
@endsection
