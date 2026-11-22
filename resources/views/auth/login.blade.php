@extends('layouts.auth', ['title' => 'Inloggen'])
@section('content')
<h2>Inloggen</h2>
<p class="small muted" style="margin-top:-8px">Voor gemeenten en bestrijders. Inwoners melden zonder account.</p>
<form method="post" action="{{ route('login.store') }}" class="stack">@csrf
  <label class="f">E-mail<input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"></label>
  <label class="f">Wachtwoord<input type="password" name="password" required autocomplete="current-password"></label>
  <label class="row small"><input type="checkbox" name="remember"> Ingelogd blijven</label>
  <button class="btn orange" style="width:100%">Inloggen</button>
</form>
<p class="small"><a href="{{ route('password.request') }}">Wachtwoord vergeten?</a></p>
@endsection
