@extends('layouts.base', ['title' => 'Voor gemeenten en bestrijders'])
@push('head')
<style>
body{margin:0;background:#fbf6ec}
a{color:#a84a0c}a:hover{color:#1c1712}
</style><style>*{box-sizing:border-box} img{max-width:100%} @media (max-width:640px){h1{font-size:44px!important}}</style>
@endpush
@section('body')
@if(session('status'))<div class="alert ok" style="margin:0;border-radius:0;text-align:center">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert err" style="margin:0;border-radius:0;text-align:center">{{ $errors->first() }}</div>@endif

<div style="font-family: Inter, system-ui, sans-serif; color: #1c1712; background: #fbf6ec">

<header style="border-bottom: 1px solid #d8c9ae">
<div style="max-width: 1200px; margin: 0 auto; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap">
<img src="/logo.svg" alt="MeldHoornaar.nl" style="height: 40px; width: auto">
<nav style="display: flex; gap: 24px; align-items: center; flex-wrap: wrap; font-size: 15px; font-weight: 500">
<a href="#hoe" style="color: #1c1712; text-decoration: none">Hoe het werkt</a>
<a href="#gemeenten" style="color: #1c1712; text-decoration: none">Gemeenten</a>
<a href="#bestrijders" style="color: #1c1712; text-decoration: none">Bestrijders</a>
<a href="#pilot" style="color: #1c1712; text-decoration: none">Pilot 2027</a>
<a href="{{ route('home') }}" style="color: #1c1712; text-decoration: none">Nest melden (inwoners)</a>
<a href="#contact" style="background: #ec7a1c; color: #1c1712; text-decoration: none; font-weight: 600; padding: 12px 20px; border-radius: 10px">Contact</a>
</nav>
</div>
</header>

<section style="background: #1c1712; color: #f6efe3; position: relative; overflow: hidden">
<div style="max-width: 1200px; margin: 0 auto; padding: 96px 24px 104px; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 48px; align-items: center">
<div style="display: flex; flex-direction: column; gap: 24px">
<p style="margin: 0; font-size: 13px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #f4c430">Aziatische hoornaar · voor gemeenten en bestrijders</p>
<h1 style="margin: 0; font-family: 'Bricolage Grotesque', Inter, sans-serif; font-weight: 800; font-size: 64px; line-height: 1; letter-spacing: -1.5px">Van melding tot nest weg. In één systeem.</h1>
<p style="margin: 0; font-size: 19px; line-height: 29px; color: #d9cfc0; max-width: 540px">Inwoners melden, de gemeente beoordeelt, de bestrijder krijgt de opdracht en rijdt een slimme avondroute. Aan het eind van de maand staan rapportage en factuur per nest klaar.</p>
<div style="display: flex; gap: 12px; flex-wrap: wrap">
<a href="#contact" style="background: #ec7a1c; color: #1c1712; text-decoration: none; font-weight: 600; font-size: 16px; padding: 14px 24px; border-radius: 10px">Pilot aanvragen</a>
<a href="#hoe" style="border: 1.5px solid #f6efe3; color: #f6efe3; text-decoration: none; font-weight: 600; font-size: 16px; padding: 13px 22px; border-radius: 10px">Zo werkt het</a>
</div>
</div>
<div aria-hidden="true" style="display: flex; justify-content: center">
<svg width="380" height="340" viewBox="0 0 380 340">
<g fill="#2f271f">
<polygon points="110,20 162,50 162,110 110,140 58,110 58,50"/>
<polygon points="220,20 272,50 272,110 220,140 168,110 168,50"/>
<polygon points="55,115 107,145 107,205 55,235 3,205 3,145"/>
<polygon points="275,115 327,145 327,205 275,235 223,205 223,145"/>
<polygon points="110,210 162,240 162,300 110,330 58,300 58,240"/>
<polygon points="220,210 272,240 272,300 220,330 168,300 168,240"/>
<polygon points="330,20 377,47 377,103 330,130 283,103 283,47"/>
</g>
<g transform="translate(110 62) scale(2.5)">
<path d="M32 4 L51 15 L51 37 L36.5 45.5 L32 60 L27.5 45.5 L13 37 L13 15 Z" fill="#f6efe3"/>
<rect x="13" y="22" width="38" height="8" fill="#ec7a1c"/>
<rect x="13" y="33" width="38" height="3" fill="#1c1712"/>
</g>
</svg>
</div>
</div>
<div aria-hidden="true" style="height: 12px; background: repeating-linear-gradient(90deg, #ec7a1c 0 48px, #1c1712 48px 64px)"></div>
</section>

<section id="hoe" style="max-width: 1200px; margin: 0 auto; padding: 88px 24px; display: flex; flex-direction: column; gap: 40px">
<div style="display: flex; flex-direction: column; gap: 12px; max-width: 640px">
<h2 style="margin: 0; font-family: 'Bricolage Grotesque', Inter, sans-serif; font-weight: 800; font-size: 40px; line-height: 44px; letter-spacing: -0.8px">Hoe het werkt</h2>
<p style="margin: 0; font-size: 18px; line-height: 28px; color: #6b6156">Vijf stappen, één dossier per nest. Niemand hoeft meer te mailen, bellen of Excel bij te houden.</p>
</div>
<ol style="list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px">
<li style="background: #f3ead9; border: 1px solid #d8c9ae; border-radius: 10px; padding: 24px; display: flex; flex-direction: column; gap: 8px"><span style="font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 32px; color: #a84a0c">1</span><strong style="font-size: 17px">Melding</strong><span style="font-size: 15px; line-height: 22px; color: #6b6156">Inwoner meldt met foto en locatie. Meldingen van Waarneming.nl komen automatisch binnen.</span></li>
<li style="background: #f3ead9; border: 1px solid #d8c9ae; border-radius: 10px; padding: 24px; display: flex; flex-direction: column; gap: 8px"><span style="font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 32px; color: #a84a0c">2</span><strong style="font-size: 17px">Beoordeling</strong><span style="font-size: 15px; line-height: 22px; color: #6b6156">De gemeente keurt goed, wijst af of markeert als urgent. Dubbele meldingen worden samengevoegd.</span></li>
<li style="background: #f3ead9; border: 1px solid #d8c9ae; border-radius: 10px; padding: 24px; display: flex; flex-direction: column; gap: 8px"><span style="font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 32px; color: #a84a0c">3</span><strong style="font-size: 17px">Opdracht</strong><span style="font-size: 15px; line-height: 22px; color: #6b6156">Met één klik naar de gecontracteerde bestrijder, met foto, hoogte en bereikbaarheid.</span></li>
<li style="background: #f3ead9; border: 1px solid #d8c9ae; border-radius: 10px; padding: 24px; display: flex; flex-direction: column; gap: 8px"><span style="font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 32px; color: #a84a0c">4</span><strong style="font-size: 17px">Avondroute</strong><span style="font-size: 15px; line-height: 22px; color: #6b6156">De bestrijder ziet alle nesten op de kaart en rijdt een geoptimaliseerde route na zonsondergang.</span></li>
<li style="background: #f3ead9; border: 1px solid #d8c9ae; border-radius: 10px; padding: 24px; display: flex; flex-direction: column; gap: 8px"><span style="font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 32px; color: #a84a0c">5</span><strong style="font-size: 17px">Rapportage</strong><span style="font-size: 15px; line-height: 22px; color: #6b6156">Maandrapport voor de gemeente en factuur per nest voor de bestrijder. Automatisch.</span></li>
</ol>
</section>

<section style="background: #ec7a1c">
<div style="max-width: 1200px; margin: 0 auto; padding: 64px 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 32px; align-items: end">
<div style="display: flex; flex-direction: column; gap: 4px"><span style="font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 72px; line-height: 1; letter-spacing: -2px">10.000+</span><span style="font-size: 16px; font-weight: 500">nesten gemeld in 2025 – tegen zo’n 2.000 in 2024</span></div>
<div style="display: flex; flex-direction: column; gap: 4px"><span style="font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 72px; line-height: 1; letter-spacing: -2px">2026</span><span style="font-size: 16px; font-weight: 500">Noord-Brabant stopt met standaard bestrijden; gemeenten nemen het over</span></div>
<p style="margin: 0; font-size: 18px; line-height: 27px; font-weight: 500">Gemeenten moeten nu zelf melden, inkopen en verantwoorden. MeldHoornaar maakt dat behapbaar.</p>
</div>
</section>

<section style="max-width: 1200px; margin: 0 auto; padding: 88px 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 24px">
<article id="gemeenten" style="border: 1px solid #d8c9ae; border-radius: 20px; padding: 40px; display: flex; flex-direction: column; gap: 16px; background: #fbf6ec">
<p style="margin: 0; font-size: 13px; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase; color: #a84a0c">Voor gemeenten</p>
<h3 style="margin: 0; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 30px; line-height: 34px">Grip op elk nest, zonder extra fte</h3>
<ul style="margin: 0; padding-left: 20px; display: flex; flex-direction: column; gap: 8px; font-size: 16px; line-height: 24px">
<li>Eén overzicht: kaart, status en doorlooptijd per nest</li>
<li>Werkt met uw bestaande bestrijder of raamovereenkomst</li>
<li>Maandrapportage voor college en raad</li>
<li>Eigen meldpagina in uw huisstijl</li>
</ul>
</article>
<article id="bestrijders" style="border-radius: 20px; padding: 40px; display: flex; flex-direction: column; gap: 16px; background: #1c1712; color: #f6efe3">
<p style="margin: 0; font-size: 13px; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase; color: #f4c430">Voor bestrijders</p>
<h3 style="margin: 0; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 30px; line-height: 34px">Minder rijden, sneller factureren</h3>
<ul style="margin: 0; padding-left: 20px; display: flex; flex-direction: column; gap: 8px; font-size: 16px; line-height: 24px">
<li>Opdrachten van alle gemeenten in één app</li>
<li>Avondroute op volgorde, met navigatie</li>
<li>Foto na bestrijding = nest afgemeld</li>
<li>Factuur per nest staat automatisch klaar</li>
</ul>
</article>
</section>

<section id="pilot" style="background: #f3ead9; border-top: 1px solid #d8c9ae; border-bottom: 1px solid #d8c9ae">
<div style="max-width: 1200px; margin: 0 auto; padding: 88px 24px; display: flex; flex-direction: column; gap: 24px">
<h2 style="margin: 0; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 40px; line-height: 44px; letter-spacing: -0.8px">Pilot 2027</h2>
<p style="margin: 0; font-size: 18px; line-height: 28px; color: #6b6156; max-width: 640px">Drie gemeenten gebruiken MeldHoornaar een jaar gratis, samen met hun eigen bestrijder. Daarna een vast bedrag per maand, afhankelijk van de grootte van de gemeente.</p>
<div style="background: #f4c430; border-radius: 10px; padding: 24px 32px; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap">
<p style="margin: 0; font-size: 18px; font-weight: 600">Interesse of tarieven opvragen? We plannen graag een kennismaking.</p>
<a href="#contact" style="background: #1c1712; color: #f6efe3; text-decoration: none; font-weight: 600; padding: 14px 24px; border-radius: 10px">Neem contact op</a>
</div>
</div>
</section>



<section id="contact" style="max-width: 760px; margin: 0 auto; padding: 88px 24px; display: flex; flex-direction: column; gap: 20px">
<h2 style="margin: 0; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 40px; letter-spacing: -0.8px">Kennismaken?</h2>
<p style="margin: 0; font-size: 18px; line-height: 28px; color: #6b6156">Laat je gegevens achter, dan bellen we je binnen twee werkdagen.</p>
<form action="{{ route('zakelijk.contact') }}" method="post" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px">
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600">Naam<input name="naam" required style="font: 400 16px Inter, sans-serif; padding: 12px 14px; border: 1px solid #d8c9ae; border-radius: 6px; background: #fbf6ec"></label>
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600">Organisatie<input name="organisatie" required style="font: 400 16px Inter, sans-serif; padding: 12px 14px; border: 1px solid #d8c9ae; border-radius: 6px; background: #fbf6ec"></label>
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600">E-mail<input name="email" type="email" required style="font: 400 16px Inter, sans-serif; padding: 12px 14px; border: 1px solid #d8c9ae; border-radius: 6px; background: #fbf6ec"></label>
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600">Telefoon<input name="telefoon" type="tel" style="font: 400 16px Inter, sans-serif; padding: 12px 14px; border: 1px solid #d8c9ae; border-radius: 6px; background: #fbf6ec"></label>
<label style="grid-column: 1 / -1; display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600">Ik ben<select name="type" style="font: 400 16px Inter, sans-serif; padding: 12px 14px; border: 1px solid #d8c9ae; border-radius: 6px; background: #fbf6ec"><option>Gemeente</option><option>Bestrijder</option><option>Anders</option></select></label>
@csrf
<input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position: absolute; left: -9999px">
<button type="submit" style="grid-column: 1 / -1; font: 600 16px Inter, sans-serif; background: #ec7a1c; color: #1c1712; border: 0; border-radius: 10px; padding: 16px; cursor: pointer">Versturen</button>
</form>
</section>
<footer style="background: #1c1712; color: #a8a096">
<div aria-hidden="true" style="height: 8px; background: repeating-linear-gradient(90deg, #ec7a1c 0 48px, #1c1712 48px 64px)"></div>
<div style="max-width: 1200px; margin: 0 auto; padding: 40px 24px; display: flex; justify-content: space-between; align-items: center; gap: 24px; flex-wrap: wrap; font-size: 14px">
<img src="/logo-dark.svg" alt="MeldHoornaar.nl" style="height: 36px; width: auto">
<span>© 2027 MeldHoornaar · <a href="{{ route('home') }}" style="color:#f39a4a">Nest melden</a></span>
</div>
</footer>
</div>

@endsection
