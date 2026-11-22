@extends('layouts.app', ['title' => 'Rapportage'])
@section('content')
<div class="row between">
  <h1>Rapportage {{ $year }}</h1>
  <div class="row">
    <form><select name="jaar" onchange="this.form.submit()">@for($y = now()->year; $y >= 2026; $y--)<option @selected($y == $year)>{{ $y }}</option>@endfor</select></form>
    <a class="btn ghost small" href="{{ route('gemeente.rapport.export', ['jaar' => $year]) }}">Download CSV</a>
    <button class="btn ghost small" onclick="print()">Afdrukken</button>
  </div>
</div>
<div class="grid g4" style="margin-bottom:20px">
  <div class="card"><div class="stat">{{ $byStatus->sum() }}<small>Meldingen (excl. dubbel)</small></div></div>
  <div class="card"><div class="stat">{{ $cleared }}<small>Nesten bestreden</small></div></div>
  <div class="card"><div class="stat">{{ $byStatus['afgewezen'] ?? 0 }}<small>Afgewezen</small></div></div>
  <div class="card"><div class="stat">{{ \App\Support\Money::format($cost) }}<small>Gefactureerd (incl. btw)</small></div></div>
</div>
<div class="grid g2" style="align-items:start">
  <div class="card white">
    <h3>Meldingen per maand</h3>
    @php($max = max(1, $byMonth->max() ?? 1))
    <svg viewBox="0 0 360 160" width="100%" role="img" aria-label="Meldingen per maand">
      @foreach(range(1, 12) as $m)
        @php($v = $byMonth[$m] ?? 0)
        @php($h = round($v / $max * 120))
        <rect x="{{ ($m - 1) * 30 + 4 }}" y="{{ 130 - $h }}" width="22" height="{{ $h }}" rx="3" fill="#F38B12"><title>{{ $v }}</title></rect>
        <text x="{{ ($m - 1) * 30 + 15 }}" y="148" font-size="10" text-anchor="middle" fill="#5B6169">{{ ['j','f','m','a','m','j','j','a','s','o','n','d'][$m - 1] }}</text>
        @if($v)<text x="{{ ($m - 1) * 30 + 15 }}" y="{{ 126 - $h }}" font-size="10" text-anchor="middle" fill="#17181B">{{ $v }}</text>@endif
      @endforeach
    </svg>
  </div>
  <div class="card white">
    <h3>Per status</h3>
    <table class="t">@foreach(\App\Support\NestStatus::LABELS as $k => $v) @if(($byStatus[$k] ?? 0) > 0)<tr><td>@include('partials.badge', ['s' => $k])</td><td class="r">{{ $byStatus[$k] }}</td></tr>@endif @endforeach</table>
  </div>
</div>
<div class="card white" style="margin-top:20px">
  <h3>Facturen</h3>
  <div class="tablewrap"><table class="t">
    <thead><tr><th>Nummer</th><th>Bestrijder</th><th>Periode</th><th>Status</th><th class="r">Bedrag</th><th></th></tr></thead>
    @forelse($invoices as $i)
      <tr><td>{{ $i->number }}</td><td>{{ $i->bestrijder->name }}</td><td>{{ $i->period }}</td><td>@include('partials.badge', ['s' => $i->status])</td><td class="r">{{ \App\Support\Money::format($i->total_cents) }}</td><td><a href="{{ route('gemeente.facturen.pdf', $i) }}">PDF</a></td></tr>
    @empty
      <tr><td colspan="6" class="muted">Nog geen facturen.</td></tr>
    @endforelse
  </table></div>
</div>
@endsection
