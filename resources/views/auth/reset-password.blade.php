@extends('layouts.auth', ['title' => 'Wachtwoord instellen'])
@section('content')
<h2>Wachtwoord instellen</h2>
<form method="post" action="{{ route('password.update') }}" class="stack">@csrf
  <input type="hidden" name="token" value="{{ $request->route('token') }}">
  <label class="f">E-mail<input type="email" name="email" value="{{ old('email', $request->email) }}" required></label>
  <label class="f">Nieuw wachtwoord (min. 12 tekens)<input type="password" name="password" required autocomplete="new-password"></label>
  <label class="f">Herhaal wachtwoord<input type="password" name="password_confirmation" required autocomplete="new-password"></label>
  <button class="btn orange" style="width:100%">Opslaan</button>
</form>
@endsection
