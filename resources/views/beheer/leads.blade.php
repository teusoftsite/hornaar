@extends('layouts.app', ['title' => 'Particuliere aanvragen'])
@section('content')
<h1>Particuliere aanvragen</h1>
<div class="grid g4" style="margin-bottom:20px">
  @foreach(\App\Models\Lead::STATUSES as $k => $v)
    <div class="card"><div class="stat {{ $k === 'geen_bestrijder' && ($counts[$k] ?? 0) ? 'rood' : '' }}">{{ $counts[$k] ?? 0 }}<small>{{ $v }}</small></div></div>
  @endforeach
</div>
@if(! config('meldhoornaar.seller.iban') || ! config('meldhoornaar.seller.kvk'))
  <p class="alert warn">Vul KvK-nummer en IBAN van MeldHoornaar in de .env in (MH_SELLER_KVK, MH_SELLER_IBAN, MH_SELLER_VAT) voordat je facturen verstuurt.</p>
@endif
<div class="card" style="padding:0;margin-bottom:24px">
  <h3 style="padding:16px 20px 0">Facturen aan bestrijders</h3>
  <div class="tablewrap"><table class="t">
    <thead><tr><th>Nummer</th><th>Bestrijder</th><th>Periode</th><th>Aanvragen</th><th class="r">Totaal</th><th>Status</th><th></th></tr></thead>
    @forelse($invoices as $i)
    <tr><td>{{ $i->number ?? 'Concept' }}</td><td>{{ $i->bestrijder->name }}</td><td>{{ $i->period }}</td><td>{{ $i->lead_count }}</td><td class="r">{{ \App\Support\Money::format($i->total_cents) }}</td>
      <td>@include('partials.badge', ['s' => $i->status])</td>
      <td class="row" style="gap:6px"><a class="btn small" target="_blank" href="{{ route('beheer.leadfacturen.pdf', $i) }}">PDF</a>
        @if($i->status === 'concept')<form method="post" action="{{ route('beheer.leadfacturen.send', $i) }}">@csrf<button class="btn orange small">Versturen</button></form>@endif
        @if($i->status === 'verstuurd')<form method="post" action="{{ route('beheer.leadfacturen.paid', $i) }}">@csrf<button class="btn green small">Betaald</button></form>@endif
      </td></tr>
    @empty
    <tr><td colspan="7" class="muted" style="padding:20px">Nog geen facturen. Ze worden de 1e van de maand aangemaakt.</td></tr>
    @endforelse
  </table></div>
</div>
<div class="card" style="padding:0">
  <h3 style="padding:16px 20px 0">Aanvragen</h3>
  <div class="tablewrap"><table class="t">
    <thead><tr><th>#</th><th>Adres</th><th>Inwoner</th><th>Status</th><th>Bestrijder</th><th>Aangemaakt</th></tr></thead>
    @forelse($leads as $l)
    <tr><td>{{ $l->id }}</td><td>{{ $l->nest->address }}</td><td>{{ $l->name }}<br><span class="small">{{ $l->phone }}</span></td>
      <td><span class="badge {{ $l->status === 'geen_bestrijder' ? 'b-te_laat' : '' }}">{{ $l->statusLabel() }}</span>@if($l->status === 'aangeboden')<br><span class="small muted">bij {{ $l->offeredTo?->name }}</span>@endif</td>
      <td>{{ $l->bestrijder?->name }}</td><td class="small">{{ $l->created_at->format('d-m-Y H:i') }}</td></tr>
    @empty
    <tr><td colspan="6" class="muted" style="padding:20px">Nog geen aanvragen.</td></tr>
    @endforelse
  </table></div>
</div>
<div style="margin-top:16px">{{ $leads->links('partials.pager') }}</div>
@endsection
