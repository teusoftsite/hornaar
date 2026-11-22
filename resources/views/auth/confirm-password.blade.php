@extends('layouts.auth', ['title' => 'Bevestig wachtwoord'])
@section('content')
<h2>Bevestig je wachtwoord</h2>
<form method="post" action="{{ route('password.confirm.store') }}" class="stack">@csrf
  <label class="f">Wachtwoord<input type="password" name="password" required autofocus autocomplete="current-password"></label>
  <button class="btn orange" style="width:100%">Bevestigen</button>
</form>
@endsection
