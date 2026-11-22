@extends('layouts.auth', ['title' => 'Wachtwoord vergeten'])
@section('content')
<h2>Wachtwoord vergeten</h2>
<form method="post" action="{{ route('password.email') }}" class="stack">@csrf
  <label class="f">E-mail<input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
  <button class="btn orange" style="width:100%">Stuur herstellink</button>
</form>
<p class="small"><a href="{{ route('login') }}">Terug naar inloggen</a></p>
@endsection
