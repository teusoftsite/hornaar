@extends('layouts.app', ['title' => 'Instellingen'])
@include('partials.leaflet')
@section('content')
<h1>Instellingen</h1>
<div class="grid g2" style="align-items:start">
  <form method="post" action="{{ route('bestrijder.instellingen.update') }}" class="card stack">@csrf
    <h3>Bedrijfsgegevens (op je facturen)</h3>
    <label class="f">Bedrijfsnaam<input name="name" value="{{ old('name', $org->name) }}" required></label>
    <label class="f">Adres<input name="address" value="{{ old('address', $org->address) }}"></label>
    <div class="grid g2" style="gap:12px">
      <label class="f">KvK<input name="kvk" value="{{ old('kvk', $org->kvk) }}"></label>
      <label class="f">Btw-nummer<input name="vat_number" value="{{ old('vat_number', $org->vat_number) }}"></label>
      <label class="f">IBAN<input name="iban" value="{{ old('iban', $org->iban) }}"></label>
      <label class="f">Btw-tarief<select name="vat_rate">@foreach([21, 9, 0] as $r)<option value="{{ $r }}" @selected($org->vat_rate == $r)>{{ $r }}%</option>@endforeach</select></label>
    </div>
    <label class="f">E-mail facturen (antwoordadres)<input type="email" name="invoice_email" value="{{ old('invoice_email', $org->invoice_email) }}"></label>
    <label class="f">E-mail nieuwe opdrachten<input type="email" name="notify_email" value="{{ old('notify_email', $org->notify_email) }}"></label>
    <div class="f" style="font-size:13px;font-weight:600">Werkplaats (start en eind van de route) – tik op de kaart</div>
    <div id="basemap" class="map" style="height:260px"></div>
    <input type="hidden" name="base_lat" id="blat" value="{{ $org->base_lat }}"><input type="hidden" name="base_lng" id="blng" value="{{ $org->base_lng }}">
    <button class="btn orange">Opslaan</button>
  </form>
  <div class="stack">
    <div class="card stack">
      <h3>Team</h3>
      <table class="t">@foreach($org->users as $u)<tr><td>{{ $u->name }}<br><span class="small muted">{{ $u->email }} · {{ \App\Support\Role::LABELS[$u->role] }}</span></td><td class="r">@if($u->id !== auth()->id())<form method="post" action="{{ route('bestrijder.instellingen.users.toggle', $u) }}">@csrf<button class="btn ghost small">{{ $u->active ? 'Blokkeren' : 'Activeren' }}</button></form>@endif</td></tr>@endforeach</table>
      <form method="post" action="{{ route('bestrijder.instellingen.users') }}" class="stack">@csrf
        <div class="grid g2" style="gap:12px"><label class="f">Naam<input name="name" required></label><label class="f">E-mail<input type="email" name="email" required></label></div>
        <button class="btn small">Medewerker uitnodigen</button>
      </form>
    </div>
    <div class="card">
      <h3>Contracten</h3>
      <table class="t">@forelse($org->contractsAsBestrijder as $c)<tr><td>{{ $c->gemeente->name }}<br><span class="small muted">vanaf {{ \Carbon\Carbon::parse($c->valid_from)->format('d-m-Y') }}{{ $c->valid_until ? ' t/m ' . \Carbon\Carbon::parse($c->valid_until)->format('d-m-Y') : '' }}</span></td><td class="r small">{{ \App\Support\Money::format($c->standard_rate_cents) }} per nest<br>voorrijden {{ \App\Support\Money::format($c->callout_cents) }}</td></tr>@empty<tr><td class="muted">Nog geen contracten.</td></tr>@endforelse</table>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script>document.addEventListener('DOMContentLoaded', function () { MH.picker(document.getElementById('basemap'), document.getElementById('blat'), document.getElementById('blng')); });</script>
@endpush
