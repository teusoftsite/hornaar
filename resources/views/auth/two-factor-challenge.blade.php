@extends('layouts.auth', ['title' => 'Tweestapsverificatie'])
@section('content')
<h2>Tweestapsverificatie</h2>
<form method="post" action="{{ route('two-factor.login.store') }}" class="stack" id="tf">@csrf
  <label class="f">Code uit je authenticator-app<input name="code" inputmode="numeric" autocomplete="one-time-code" autofocus></label>
  <details><summary class="small">Telefoon kwijt? Gebruik een herstelcode</summary>
    <label class="f" style="margin-top:8px">Herstelcode<input name="recovery_code" autocomplete="off"></label>
  </details>
  <button class="btn orange" style="width:100%">Doorgaan</button>
</form>
@endsection
