<!doctype html>
<html lang="nl"><head><meta charset="utf-8">
<style>
@page{margin:36px 44px}
body{font-family:DejaVu Sans,sans-serif;font-size:10pt;color:#17181B}
h1{font-size:20pt;margin:0 0 4px}
.stripe{height:6px;background:#F38B12;margin-bottom:18px}
table{width:100%;border-collapse:collapse}
.meta td{vertical-align:top;padding:0}
.lines th{text-align:left;border-bottom:2px solid #DCE0E4;padding:6px 4px;font-size:9pt;color:#5B6169}
.lines td{border-bottom:1px solid #DCE0E4;padding:6px 4px}
.r{text-align:right}.mute{color:#5B6169}.tot td{border:0;padding:3px 4px}
</style></head>
<body>
@php($b = $invoice->bestrijder)
@php($g = $invoice->gemeente)
<div class="stripe"></div><div style="font-weight:bold;font-size:13pt;margin-bottom:14px"><span style="color:#B85F00">Meld</span>Hoornaar<span style="color:#5B6169;font-weight:normal;font-size:9pt"> · factuur namens de bestrijder</span></div>
<table class="meta"><tr>
  <td style="width:55%"><h1>{{ $invoice->credits_invoice_id ? 'Creditfactuur' : 'Factuur' }}</h1>
    <div class="mute">Nummer: <strong style="color:#17181B">{{ $invoice->number ?? 'CONCEPT' }}</strong><br>
    Datum: {{ ($invoice->sent_at ?? now())->format('d-m-Y') }}<br>
    Periode: {{ $invoice->period }}<br>
    @if($invoice->due_on)Vervaldatum: {{ $invoice->due_on->format('d-m-Y') }}@endif
    @if($invoice->credits_invoice_id)<br>Crediteert factuur {{ \App\Models\Invoice::find($invoice->credits_invoice_id)?->number }}@endif</div>
  </td>
  <td><strong>{{ $b->name }}</strong><br>{{ $b->address }}<br>
    @if($b->kvk)KvK {{ $b->kvk }}<br>@endif @if($b->vat_number)Btw {{ $b->vat_number }}<br>@endif
    @if($b->iban)IBAN {{ $b->iban }}<br>@endif {{ $b->invoice_email }}</td>
</tr></table>
<p style="margin:22px 0 14px"><span class="mute">Aan:</span><br><strong>{{ $g->name }}</strong><br>{{ $g->address }}</p>
<table class="lines">
  <thead><tr><th>Omschrijving</th><th class="r" style="width:110px">Bedrag</th></tr></thead>
  <tbody>@foreach($invoice->lines as $l)<tr><td>{{ $l->description }}</td><td class="r">{{ \App\Support\Money::format($l->amount_cents) }}</td></tr>@endforeach</tbody>
</table>
<table class="tot" style="margin-top:10px;width:45%;margin-left:55%">
  <tr><td>Subtotaal</td><td class="r">{{ \App\Support\Money::format($invoice->subtotal_cents) }}</td></tr>
  <tr><td>Btw {{ $invoice->vat_rate }}%</td><td class="r">{{ \App\Support\Money::format($invoice->vat_cents) }}</td></tr>
  <tr><td><strong>Totaal</strong></td><td class="r"><strong>{{ \App\Support\Money::format($invoice->total_cents) }}</strong></td></tr>
</table>
@if(! $invoice->credits_invoice_id && $invoice->total_cents > 0)
<p style="margin-top:28px">Graag betalen binnen {{ $invoice->contract->payment_days }} dagen op {{ $b->iban ?? 'onze rekening' }} t.n.v. {{ $b->name }}, onder vermelding van factuurnummer {{ $invoice->number }}.</p>
@endif
<p class="mute" style="margin-top:40px;font-size:8.5pt">Bestrijding van de Aziatische hoornaar (Vespa velutina) in opdracht van {{ $g->name }}. Werkbonnen met foto's zijn in te zien via MeldHoornaar.nl.</p>
</body></html>
