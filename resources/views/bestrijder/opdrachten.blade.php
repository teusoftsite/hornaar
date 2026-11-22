@extends('layouts.app', ['title' => 'Opdrachten'])
@section('content')
<h1>Opdrachten</h1>
<p id="queue" class="small" style="color:var(--brown)"></p>
<div class="tablewrap card white" style="padding:0">
<table class="t">
  <thead><tr><th>Nest</th><th>Gemeente</th><th>Status</th><th>Plannen</th><th></th></tr></thead>
  @forelse($assignments as $a)
  <tr>
    <td><strong>{{ $a->nest->address ?? '#' . $a->nest->id }}</strong> @if($a->nest->urgent)<span class="badge b-te_laat">Spoed</span>@endif<br>
      <span class="small muted">#{{ $a->nest->id }} @if($a->nest->location_type)· {{ $a->nest->location_type }}@endif @if($a->nest->height_m)· {{ $a->nest->height_m }} m @endif · <a target="_blank" rel="noopener" href="https://www.google.com/maps/dir/?api=1&destination={{ $a->nest->lat }},{{ $a->nest->lng }}">navigeer</a></span></td>
    <td>{{ $a->contract->gemeente->name }}</td>
    <td>@include('partials.badge', ['s' => $a->status === 'open' ? 'opdracht_gegeven' : 'ingepland'])@if($a->planned_on)<br><span class="small">{{ $a->planned_on->translatedFormat('D j M') }}{{ $a->assignee ? ' · ' . $a->assignee->name : '' }}</span>@endif</td>
    <td>
      <form method="post" action="{{ route('bestrijder.opdrachten.plan', $a) }}" class="row" style="gap:6px">@csrf
        <input type="date" name="planned_on" value="{{ optional($a->planned_on)->toDateString() ?? today()->toDateString() }}" min="{{ today()->toDateString() }}" style="width:auto">
        @if(auth()->user()->isBestrijderEigenaar())
        <select name="assigned_user_id" style="width:auto">@foreach($team as $t)<option value="{{ $t->id }}" @selected(($a->assigned_user_id ?? auth()->id()) === $t->id)>{{ $t->name }}</option>@endforeach</select>
        @endif
        <button class="btn small">{{ $a->planned_on ? 'Wijzig' : 'Plan' }}</button>
      </form>
    </td>
    <td><a class="btn orange small" href="{{ route('bestrijder.werkbon', $a) }}">Werkbon</a></td>
  </tr>
  @empty
  <tr><td colspan="5" class="muted" style="padding:24px">Geen openstaande opdrachten.</td></tr>
  @endforelse
</table>
</div>
@if($done->isNotEmpty())
<h2 style="margin-top:32px">Recent afgerond</h2>
<table class="t">
  @foreach($done as $a)
  <tr><td>#{{ $a->nest->id }} {{ $a->nest->address }}</td><td>{{ \App\Models\WorkOrder::RESULTS[$a->workOrders->last()?->result] ?? '' }}</td><td class="small muted">{{ $a->updated_at->format('d-m-Y') }}</td></tr>
  @endforeach
</table>
@endif
@endsection
