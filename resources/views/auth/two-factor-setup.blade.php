@extends('layouts.auth', ['title' => 'Tweestapsverificatie instellen'])
@section('content')
<h2>Tweestapsverificatie</h2>
@if($user->hasConfirmedTwoFactor())
  <p class="alert ok">Tweestapsverificatie staat aan.</p>
  @if(session('status') === 'two-factor-authentication-confirmed')
    <p>Bewaar deze herstelcodes op een veilige plek. Elke code werkt één keer.</p>
    <pre style="background:var(--panel);padding:12px;border-radius:8px">@foreach($user->recoveryCodes() as $c){{ $c }}
@endforeach</pre>
  @endif
  <a class="btn orange" href="{{ $user->homeRoute() }}" style="width:100%">Verder</a>
@elseif($user->two_factor_secret)
  <p>Scan deze QR-code met een authenticator-app (bijv. Microsoft Authenticator of Google Authenticator) en vul de code in.</p>
  <div style="background:#fff;padding:12px;border-radius:8px;text-align:center">{!! $user->twoFactorQrCodeSvg() !!}</div>
  <form method="post" action="{{ route('two-factor.confirm') }}" class="stack">@csrf
    <label class="f">Code<input name="code" inputmode="numeric" autocomplete="one-time-code" required autofocus></label>
    <button class="btn orange" style="width:100%">Bevestigen</button>
  </form>
@else
  <p>Voor jouw rol is tweestapsverificatie verplicht. Je hebt een authenticator-app op je telefoon nodig.</p>
  <form method="post" action="{{ route('two-factor.enable') }}">@csrf
    <button class="btn orange" style="width:100%">Instellen</button>
  </form>
@endif
@endsection
