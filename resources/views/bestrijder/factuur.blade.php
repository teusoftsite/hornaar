@extends('layouts.app', ['title' => 'Factuur ' . ($invoice->number ?? 'concept')])
@section('content')
<p class="small"><a href="{{ route('bestrijder.facturen') }}">← Facturen</a></p>
<div class="row between">
  <h1>{{ $invoice->credits_invoice_id ? 'Creditfactuur' : 'Factuur' }} {{ $invoice->number ?? '(concept)' }} @include('partials.badge', ['s' => $invoice->status])</h1>
  <div class="row">
    <a class="btn ghost small" href="{{ route('bestrijder.facturen.pdf', $invoice) }}" target="_blank">PDF bekijken</a>
    @if($invoice->isEditable())
      <form method="post" action="{{ route('bestrijder.facturen.send', $invoice) }}" onsubmit="return confirm('Factuur definitief maken en mailen naar {{ $invoice->gemeente->invoice_email ?? 'de gemeente' }}?')">@csrf<button class="btn orange small">Versturen</button></form>
    @elseif(in_array($invoice->status, ['verstuurd', 'te_laat']))
      <form method="post" action="{{ route('bestrijder.facturen.paid', $invoice) }}">@csrf<button class="btn green small">Betaald</button></form>
      @if(! $invoice->credits_invoice_id)
      <form method="post" action="{{ route('bestrijder.facturen.credit', $invoice) }}" onsubmit="return confirm('Creditfactuur maken en versturen voor het volledige bedrag?')">@csrf<button class="btn ghost small">Crediteren</button></form>
      @endif
    @endif
  </div>
</div>
<p class="muted">{{ $invoice->gemeente->name }} · periode {{ $invoice->period }} @if($invoice->due_on)· vervalt {{ $invoice->due_on->format('d-m-Y') }}@endif @if($invoice->reminders_sent)· {{ $invoice->reminders_sent }} herinnering(en) verstuurd @endif</p>
@if(! $invoice->gemeente->invoice_email && $invoice->isEditable())<p class="alert err">De gemeente heeft nog geen factuur-e-mailadres. Versturen maakt de factuur definitief, maar mailt niet.</p>@endif
<div class="card white" style="padding:0"><table class="t">
  <thead><tr><th>Omschrijving</th><th class="r">Bedrag</th>@if($invoice->isEditable())<th></th>@endif</tr></thead>
  @foreach($invoice->lines as $l)
  <tr>
    @if($invoice->isEditable())
    <td colspan="3">
      <details><summary class="row between" style="cursor:pointer"><span>{{ $l->description }}</span><strong>{{ \App\Support\Money::format($l->amount_cents) }}</strong></summary>
        <form method="post" action="{{ route('bestrijder.facturen.line', $l) }}" class="grid g3" style="margin-top:10px;gap:10px">@csrf
          <label class="f">Omschrijving<input name="description" value="{{ $l->description }}" required></label>
          <label class="f">Bedrag (€)<input name="amount" value="{{ number_format($l->amount_cents / 100, 2, ',', '') }}" required inputmode="decimal"></label>
          <label class="f">Reden aanpassing<input name="reason" required></label>
          <button class="btn small">Opslaan</button>
        </form>
      </details>
    </td>
    @else
    <td>{{ $l->description }}</td><td class="r">{{ \App\Support\Money::format($l->amount_cents) }}</td>
    @endif
  </tr>
  @endforeach
  <tr><td class="r">Subtotaal</td><td class="r">{{ \App\Support\Money::format($invoice->subtotal_cents) }}</td>@if($invoice->isEditable())<td></td>@endif</tr>
  <tr><td class="r">Btw {{ $invoice->vat_rate }}%</td><td class="r">{{ \App\Support\Money::format($invoice->vat_cents) }}</td>@if($invoice->isEditable())<td></td>@endif</tr>
  <tr><td class="r"><strong>Totaal</strong></td><td class="r"><strong>{{ \App\Support\Money::format($invoice->total_cents) }}</strong></td>@if($invoice->isEditable())<td></td>@endif</tr>
</table></div>
@endsection
