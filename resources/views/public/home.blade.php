@extends('layouts.public')
@section('content')
<section class="hero">
  <div class="stack">
    <h1>Nest gezien?<br><span style="color:var(--brown)">Meld het.</span></h1>
    <p class="lead">Foto maken, locatie aanwijzen, versturen. Klaar. De gemeente zorgt dat het nest wordt weggehaald.</p>
    <p class="row" style="font-weight:500"><svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#a84a0c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>Blijf op afstand. Niet zelf verwijderen.</p>
    <p><a class="btn orange big" style="max-width:360px" href="{{ route('melden') }}">Nest melden</a></p>
    <p class="small muted">Geen account nodig. Gratis.</p>
  </div>
  <div class="card stack">
    <h2>Herken de Aziatische hoornaar</h2>
    <p>Donker lijf, <strong>gele poten</strong>, één oranje band op het achterlijf. Kleiner dan de Europese hoornaar.</p>
    <p>Nesten hangen vaak <strong>hoog in bomen</strong> (voorjaar: klein nest onder een dakrand of in een schuur).</p>
    <div class="grid g2" style="gap:12px">
      <div><div class="stat">{{ number_format($stats['gemeld'], 0, ',', '.') }}<small>meldingen dit jaar</small></div></div>
      <div><div class="stat">{{ number_format($stats['bestreden'], 0, ',', '.') }}<small>nesten bestreden</small></div></div>
    </div>
  </div>
</section>
</div>
<div class="stripe"></div>
<section class="dark"><div class="wrap" style="padding-top:56px;padding-bottom:56px">
  <h2>Wat gebeurt er na je melding?</h2>
  <ol class="steps">
    <li><b>1</b><strong>De gemeente kijkt mee</strong><br><span style="color:#a8a096">Je melding komt direct bij jouw gemeente binnen.</span></li>
    <li><b>2</b><strong>Een bestrijder komt</strong><br><span style="color:#a8a096">Meestal ’s avonds, als de hoornaars in het nest zijn.</span></li>
    <li><b>3</b><strong>Je hoort het</strong><br><span style="color:#a8a096">Laat je e-mail achter, dan krijg je bericht als het nest weg is.</span></li>
  </ol>
</div></section>
<div class="wrap">
@endsection
