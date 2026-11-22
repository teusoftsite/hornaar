@extends('layouts.app', ['title' => 'Beheer'])
@section('content')
<h1>Beheer</h1>
<div class="grid g4" style="margin-bottom:20px">
  <div class="card"><div class="stat">{{ $gemeenten->count() }}<small>Gemeenten</small></div></div>
  <div class="card"><div class="stat">{{ $bestrijders->count() }}<small>Bestrijders</small></div></div>
  <div class="card"><div class="stat">{{ $stats['open'] }}<small>open nesten (van {{ $stats['nesten'] }})</small></div></div>
  <div class="card"><div class="stat">{{ $stats['gebruikers'] }}<small>Gebruikers</small></div></div>
</div>
<div class="grid g2" style="align-items:start">
  <div class="stack">
    @foreach(['gemeenten' => $gemeenten, 'bestrijders' => $bestrijders] as $label => $list)
    <div class="card white" style="padding:0">
      <h3 style="padding:16px 16px 0">{{ ucfirst($label) }}</h3>
      <table class="t">@forelse($list as $o)<tr class="link" onclick="location.href='{{ route('beheer.organisaties.show', $o) }}'"><td><a href="{{ route('beheer.organisaties.show', $o) }}">{{ $o->name }}</a> @unless($o->active)<span class="badge">inactief</span>@endunless</td><td class="small muted">{{ $o->cbs_code }}</td><td class="r small">{{ $o->users_count }} gebr.</td></tr>@empty<tr><td class="muted">Nog geen.</td></tr>@endforelse</table>
    </div>
    @endforeach
    <form method="post" action="{{ route('beheer.organisaties.store') }}" class="card stack">@csrf
      <h3>Organisatie toevoegen</h3>
      <div class="grid g2" style="gap:12px">
        <label class="f">Type<select name="type"><option value="gemeente">Gemeente</option><option value="bestrijder">Bestrijder</option></select></label>
        <label class="f">CBS-code (gemeente)<input name="cbs_code" placeholder="GM0777" value="{{ old('cbs_code') }}"></label>
      </div>
      <label class="f">Naam<input name="name" required value="{{ old('name') }}"></label>
      <div class="grid g2" style="gap:12px">
        <label class="f">E-mail nieuwe meldingen/opdrachten<input type="email" name="notify_email" value="{{ old('notify_email') }}"></label>
        <label class="f">E-mail facturen<input type="email" name="invoice_email" value="{{ old('invoice_email') }}"></label>
      </div>
      <button class="btn orange">Toevoegen</button>
    </form>
  </div>
  <div class="stack">
    <div class="card white" style="padding:0">
      <h3 style="padding:16px 16px 0">Contracten</h3>
      <table class="t">@forelse($contracts as $c)<tr><td>{{ $c->gemeente->name }} ↔ {{ $c->bestrijder->name }}<br><span class="small muted">{{ \Carbon\Carbon::parse($c->valid_from)->format('d-m-Y') }} – {{ $c->valid_until ? \Carbon\Carbon::parse($c->valid_until)->format('d-m-Y') : 'doorlopend' }}</span></td><td class="r small">{{ \App\Support\Money::format($c->standard_rate_cents) }}<br>hoog +{{ \App\Support\Money::format($c->surcharge_height_cents) }} · spoed +{{ \App\Support\Money::format($c->surcharge_urgent_cents) }} · voorrijden {{ \App\Support\Money::format($c->callout_cents) }}</td></tr>@empty<tr><td class="muted">Nog geen contracten.</td></tr>@endforelse</table>
    </div>
    <form method="post" action="{{ route('beheer.contracten.store') }}" class="card stack">@csrf
      <h3>Contract vastleggen</h3>
      <div class="grid g2" style="gap:12px">
        <label class="f">Gemeente<select name="gemeente_id" required>@foreach($gemeenten as $o)<option value="{{ $o->id }}">{{ $o->name }}</option>@endforeach</select></label>
        <label class="f">Bestrijder<select name="bestrijder_id" required>@foreach($bestrijders as $o)<option value="{{ $o->id }}">{{ $o->name }}</option>@endforeach</select></label>
        <label class="f">Geldig vanaf<input type="date" name="valid_from" value="{{ today()->toDateString() }}" required></label>
        <label class="f">Geldig t/m (leeg = doorlopend)<input type="date" name="valid_until"></label>
        <label class="f">Tarief per nest (€)<input name="standard_rate" required inputmode="decimal" placeholder="145,00"></label>
        <label class="f">Toeslag hoogwerker (€)<input name="surcharge_height" inputmode="decimal" placeholder="75,00"></label>
        <label class="f">Toeslag spoed (€)<input name="surcharge_urgent" inputmode="decimal" placeholder="50,00"></label>
        <label class="f">Voorrijkosten geen toegang (€)<input name="callout" inputmode="decimal" placeholder="45,00"></label>
        <label class="f">Betaaltermijn (dagen)<input type="number" name="payment_days" value="30" required></label>
      </div>
      <button class="btn orange">Vastleggen</button>
    </form>
    @if($unrouted->isNotEmpty())
    <div class="card white">
      <h3>Meldingen zonder deelnemende gemeente</h3>
      <table class="t">@foreach($unrouted as $n)<tr><td>#{{ $n->id }} {{ $n->address }}<br><span class="small muted">{{ $n->cbs_code }} · {{ $n->created_at->format('d-m-Y') }}</span></td><td>
        <form method="post" action="{{ route('beheer.nesten.route', $n) }}" class="row" style="gap:6px">@csrf<select name="gemeente_id" style="width:auto">@foreach($gemeenten as $o)<option value="{{ $o->id }}">{{ $o->name }}</option>@endforeach</select><button class="btn small">Toewijzen</button></form>
      </td></tr>@endforeach</table>
    </div>
    @endif
  </div>
</div>
@endsection
