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
<div class="stripe"></div>
<table class="meta"><tr>
  <td style="width:55%"><h1>Factuur</h1>
    <div class="mute">Nummer: <strong style="color:#17181B">{{ $invoice->number ?? 'CONCEPT' }}</strong><br>
    Datum: {{ ($invoice->sent_at ?? now())->format('d-m-Y') }}<br>Periode: {{ $invoice->period }}<br>
    @if($invoice->due_on)Vervaldatum: {{ $invoice->due_on->format('d-m-Y') }}@endif</div></td>
  <td><strong><span style="color:#B85F00">Meld</span>Hoornaar.nl</strong><br>{{ $seller['name'] }}<br>{{ $seller['address'] }}<br>
    @if($seller['kvk'])KvK {{ $seller['kvk'] }}<br>@endif @if($seller['vat_number'])Btw {{ $seller['vat_number'] }}<br>@endif
    @if($seller['iban'])IBAN {{ $seller['iban'] }}<br>@endif {{ $seller['email'] }}</td>
</tr></table>
<p style="margin:22px 0 14px"><span class="mute">Aan:</span><br><strong>{{ $b->name }}</strong><br>{{ $b->address }}@if($b->vat_number)<br>Btw {{ $b->vat_number }}@endif</p>
<table class="lines">
  <thead><tr><th>Geaccepteerde particuliere aanvraag</th><th>Datum</th><th class="r" style="width:90px">Bedrag</th></tr></thead>
  <tbody>@foreach($invoice->leads as $l)<tr><td>Aanvraag #{{ $l->id }} – {{ $l->nest->address ?? 'nest #' . $l->nest_id }}</td><td>{{ $l->accepted_at?->format('d-m-Y') }}</td><td class="r">{{ \App\Support\Money::format($l->fee_cents) }}</td></tr>@endforeach</tbody>
</table>
<table class="tot" style="margin-top:10px;width:45%;margin-left:55%">
  <tr><td>Subtotaal</td><td class="r">{{ \App\Support\Money::format($invoice->subtotal_cents) }}</td></tr>
  <tr><td>Btw {{ $invoice->vat_rate }}%</td><td class="r">{{ \App\Support\Money::format($invoice->vat_cents) }}</td></tr>
  <tr><td><strong>Totaal</strong></td><td class="r"><strong>{{ \App\Support\Money::format($invoice->total_cents) }}</strong></td></tr>
</table>
<p style="margin-top:28px">Graag betalen binnen {{ $seller['payment_days'] }} dagen op {{ $seller['iban'] ?? 'onze rekening' }} t.n.v. {{ $seller['name'] }}, onder vermelding van {{ $invoice->number }}.</p>
</body></html>
