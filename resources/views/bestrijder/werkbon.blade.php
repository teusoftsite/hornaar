@extends('layouts.app', ['title' => 'Werkbon nest #' . $nest->id])
@section('content')
<p class="small"><a href="{{ route('bestrijder.opdrachten') }}">← Opdrachten</a></p>
<h1>Werkbon nest #{{ $nest->id }}</h1>
<p class="muted" style="margin-top:-8px">{{ $nest->address }} · {{ $assignment->contract->gemeente->name }} @if($nest->height_m)· ca. {{ $nest->height_m }} m @endif @if($nest->urgent)· ⚡ spoed @endif
 · <a target="_blank" rel="noopener" href="https://www.google.com/maps/dir/?api=1&destination={{ $nest->lat }},{{ $nest->lng }}">navigeer</a></p>
@if($assignment->status === 'afgerond')
  <p class="alert ok">Deze opdracht is afgerond.</p>
@else
<div class="grid g2" style="align-items:start">
  <form id="wb" method="post" action="{{ route('bestrijder.werkbon.store', $assignment) }}" enctype="multipart/form-data" class="card stack">@csrf
    <input type="hidden" name="client_uuid" id="uuid">
    <input type="hidden" name="started_at" id="started">
    <div class="f" style="font-size:13px;font-weight:600">Resultaat</div>
    <div class="choice">
      @foreach(\App\Models\WorkOrder::RESULTS as $k => $v)
        <label><input type="radio" name="result" value="{{ $k }}" required @checked($k === 'bestreden')><span>{{ $v }}</span></label>
      @endforeach
    </div>
    <label class="f">Foto vóór<input type="file" name="photo_before" accept="image/*" capture="environment"></label>
    <label class="f">Foto ná<input type="file" name="photo_after" accept="image/*" capture="environment"></label>
    <div class="grid g2" style="gap:12px">
      <label class="f">Middel<input name="agent" list="middelen" value="{{ old('agent') }}"></label>
      <label class="f">Toelatingsnummer Ctgb<input name="admission_number" value="{{ old('admission_number') }}"></label>
    </div>
    <datalist id="middelen"><option>Diatomeeënaarde</option><option>Pyrethrum</option><option>Stikstof (bevriezen)</option><option>Mechanisch verwijderd</option></datalist>
    <label class="f">Hoeveelheid<input name="quantity" placeholder="bijv. 30 g" value="{{ old('quantity') }}"></label>
    <div class="f" style="font-size:13px;font-weight:600">Toeslagen</div>
    <label class="row"><input type="checkbox" name="surcharges[]" value="hoogwerker" @checked(($nest->height_m ?? 0) >= 8)> Hoogwerker / telescoopsteel</label>
    <label class="row"><input type="checkbox" name="surcharges[]" value="urgent" @checked($nest->urgent)> Spoed</label>
    <label class="f">Opmerkingen<textarea name="notes">{{ old('notes') }}</textarea></label>
    <button class="btn orange big" id="save">Werkbon opslaan</button>
    <p class="small muted" style="margin:0">Geen bereik? Opslaan werkt toch: de werkbon wordt verstuurd zodra je weer online bent.</p>
  </form>
  <div class="stack">
    @foreach($nest->reports as $r)
      @if($r->photo_path)<img class="photo" src="{{ \App\Support\Photo::url($r->photo_path) }}" alt="Foto melder">@endif
      @if($r->note)<p class="small">Melder: “{{ $r->note }}”</p>@endif
    @endforeach
  </div>
</div>
@endif
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  var f = document.getElementById('wb'); if (!f) return;
  document.getElementById('uuid').value = (crypto.randomUUID ? crypto.randomUUID() : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) { var r = Math.random() * 16 | 0; return (c === 'x' ? r : (r & 3 | 8)).toString(16); }));
  document.getElementById('started').value = new Date().toISOString();
  f.addEventListener('submit', function (e) {
    e.preventDefault();
    var btn = document.getElementById('save'); btn.disabled = true; btn.textContent = 'Opslaan…';
    var fd = new FormData(f); fd.append('finished_at', new Date().toISOString());
    var url = f.action;
    function queue() {
      MHQ.save(url, fd).then(function () { location.href = '{{ route('bestrijder.opdrachten') }}'; });
    }
    if (!navigator.onLine) return queue();
    fetch(url, {method: 'POST', body: fd, credentials: 'same-origin', headers: {'Accept': 'application/json'}})
      .then(function (r) {
        if (r.ok || r.status === 409) { location.href = '{{ route('bestrijder.opdrachten') }}'; return; }
        return r.json().then(function (j) { alert(j.message || 'Controleer de invoer.'); btn.disabled = false; btn.textContent = 'Werkbon opslaan'; });
      })
      .catch(queue);
  });
});
</script>
@endpush
