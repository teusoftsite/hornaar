@extends('layouts.app', ['title' => 'Facturen'])
@section('content')
<h1>Facturen</h1>
<p class="muted">Op de 1e van de maand maken we automatisch conceptfacturen per gemeente. Nog niet gefactureerd: <strong>{{ \App\Support\Money::format($unbilled) }}</strong> excl. btw.</p>
<div class="tablewrap card white" style="padding:0"><table class="t">
  <thead><tr><th>Nummer</th><th>Gemeente</th><th>Periode</th><th>Status</th><th>Vervalt</th><th class="r">Totaal</th></tr></thead>
  @forelse($invoices as $i)
  <tr class="link" onclick="location.href='{{ route('bestrijder.facturen.show', $i) }}'">
    <td><a href="{{ route('bestrijder.facturen.show', $i) }}">{{ $i->number ?? 'Concept' }}</a></td><td>{{ $i->gemeente->name }}</td><td>{{ $i->period }}</td>
    <td>@if($i->credits_invoice_id)<span class="badge">Credit</span> @endif @include('partials.badge', ['s' => $i->status])</td>
    <td class="small">{{ optional($i->due_on)->format('d-m-Y') }}</td><td class="r">{{ \App\Support\Money::format($i->total_cents) }}</td>
  </tr>
  @empty
  <tr><td colspan="6" class="muted" style="padding:24px">Nog geen facturen.</td></tr>
  @endforelse
</table></div>
<div style="margin-top:16px">{{ $invoices->links('partials.pager') }}</div>
@endsection
