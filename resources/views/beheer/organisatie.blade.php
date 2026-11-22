@extends('layouts.app', ['title' => $org->name])
@section('content')
<p class="small"><a href="{{ route('beheer.index') }}">← Beheer</a></p>
<h1>{{ $org->name }} <span class="badge">{{ $org->isGemeente() ? 'Gemeente' : 'Bestrijder' }}</span></h1>
<div class="grid g2" style="align-items:start">
  <form method="post" action="{{ route('beheer.organisaties.update', $org) }}" class="card stack">@csrf
    <label class="f">Naam<input name="name" value="{{ old('name', $org->name) }}" required></label>
    @if($org->isGemeente())<label class="f">CBS-code<input name="cbs_code" value="{{ old('cbs_code', $org->cbs_code) }}"></label>@endif
    <label class="f">Adres<input name="address" value="{{ old('address', $org->address) }}"></label>
    <div class="grid g2" style="gap:12px">
      <label class="f">KvK<input name="kvk" value="{{ old('kvk', $org->kvk) }}"></label>
      <label class="f">Btw-nummer<input name="vat_number" value="{{ old('vat_number', $org->vat_number) }}"></label>
      <label class="f">IBAN<input name="iban" value="{{ old('iban', $org->iban) }}"></label>
      <label class="f">E-mail facturen<input type="email" name="invoice_email" value="{{ old('invoice_email', $org->invoice_email) }}"></label>
    </div>
    <label class="f">E-mail nieuwe meldingen/opdrachten<input type="email" name="notify_email" value="{{ old('notify_email', $org->notify_email) }}"></label>
    <label class="row"><input type="checkbox" name="active" value="1" @checked($org->active)> Actief</label>
    <button class="btn orange">Opslaan</button>
  </form>
  <div class="stack">
    <div class="card white">
      <h3>Gebruikers</h3>
      <table class="t">@forelse($org->users as $u)<tr><td>{{ $u->name }} @unless($u->active)<span class="badge">geblokkeerd</span>@endunless<br><span class="small muted">{{ $u->email }} · {{ $roles[$u->role] ?? $u->role }} · 2FA {{ $u->hasConfirmedTwoFactor() ? 'aan' : 'uit' }}</span></td><td class="r"><form method="post" action="{{ route('beheer.gebruikers.toggle', $u) }}">@csrf<button class="btn ghost small">{{ $u->active ? 'Blokkeren' : 'Activeren' }}</button></form></td></tr>@empty<tr><td class="muted">Nog geen gebruikers.</td></tr>@endforelse</table>
    </div>
    <form method="post" action="{{ route('beheer.gebruikers.store', $org) }}" class="card stack">@csrf
      <h3>Gebruiker toevoegen</h3>
      <div class="grid g2" style="gap:12px"><label class="f">Naam<input name="name" required></label><label class="f">E-mail<input type="email" name="email" required></label></div>
      <label class="f">Rol<select name="role">@if($org->isGemeente())<option value="gemeente">{{ $roles['gemeente'] }}</option>@else<option value="bestrijder_eigenaar">{{ $roles['bestrijder_eigenaar'] }}</option><option value="bestrijder_medewerker">{{ $roles['bestrijder_medewerker'] }}</option>@endif</select></label>
      <button class="btn orange">Aanmaken en uitnodigen</button>
      <p class="small muted" style="margin:0">De gebruiker krijgt een mail om een wachtwoord te kiezen.</p>
    </form>
  </div>
</div>
@endsection
