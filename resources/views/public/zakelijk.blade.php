@extends('layouts.base', ['title' => 'Voor gemeenten en bestrijders'])
@section('body')
<div style="font-family: Inter, system-ui, sans-serif; color: #17181B; background: #EEF0F2">

<header style="border-bottom: 1px solid #DCE0E4">
<div style="max-width: 1200px; margin: 0 auto; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap">
<a href="{{ route('home') }}" style="text-decoration:none">@include('partials.logo', ['dark' => false, 'h' => 40, 'id' => 'zakh'])</a>
<nav style="display: flex; gap: 24px; align-items: center; flex-wrap: wrap; font-size: 15px; font-weight: 500">
<a href="#hoe" style="color: #17181B; text-decoration: none">Hoe het werkt</a>
<a href="#gemeenten" style="color: #17181B; text-decoration: none">Gemeenten</a>
<a href="#bestrijders" style="color: #17181B; text-decoration: none">Bestrijders</a>
<a href="#pilot" style="color: #17181B; text-decoration: none">Pilot 2027</a>
          <a href="{{ route('melden') }}" style="color: #17181B; text-decoration: none">Nest melden (inwoners)</a>
<a href="#pilot" style="background: #F38B12; color: #17181B; text-decoration: none; font-weight: 600; padding: 12px 20px; border-radius: 10px">Contact</a>
</nav>
</div>
</header>

<section style="background: #17181B; color: #F2F3F5; position: relative; overflow: hidden">
<div style="max-width: 1200px; margin: 0 auto; padding: 96px 24px 104px; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 48px; align-items: center">
<div style="display: flex; flex-direction: column; gap: 24px">
<p style="margin: 0; font-size: 13px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #FFAA45">Aziatische hoornaar · voor gemeenten en bestrijders</p>
<h1 style="margin: 0; font-family: 'Space Grotesk', Inter, sans-serif; font-weight: 700; font-size: 64px; line-height: 1; letter-spacing: -1.5px">Van melding tot nest weg. In één systeem.</h1>
<p style="margin: 0; font-size: 19px; line-height: 29px; color: #C9CDD2; max-width: 540px">Inwoners melden, de gemeente beoordeelt, de bestrijder krijgt de opdracht en rijdt een slimme avondroute. Aan het eind van de maand staan rapportage en factuur per nest klaar.</p>
<div style="display: flex; gap: 12px; flex-wrap: wrap">
<a href="#pilot" style="background: #F38B12; color: #17181B; text-decoration: none; font-weight: 600; font-size: 16px; padding: 14px 24px; border-radius: 10px">Pilot aanvragen</a>
<a href="#hoe" style="border: 1.5px solid #F2F3F5; color: #F2F3F5; text-decoration: none; font-weight: 600; font-size: 16px; padding: 13px 22px; border-radius: 10px">Zo werkt het</a>
</div>
</div>
<div aria-hidden="true" style="display: flex; justify-content: center">
<svg width="380" height="340" viewBox="0 0 380 340">
<g fill="#24272B">
<polygon points="110,20 162,50 162,110 110,140 58,110 58,50"/>
<polygon points="220,20 272,50 272,110 220,140 168,110 168,50"/>
<polygon points="55,115 107,145 107,205 55,235 3,205 3,145"/>
<polygon points="275,115 327,145 327,205 275,235 223,205 223,145"/>
<polygon points="110,210 162,240 162,300 110,330 58,300 58,240"/>
<polygon points="220,210 272,240 272,300 220,330 168,300 168,240"/>
<polygon points="330,20 377,47 377,103 330,130 283,103 283,47"/>
</g>
<g transform="translate(110 62) scale(2.5)">
<path d="M32 4 L51 15 L51 37 L36.5 45.5 L32 60 L27.5 45.5 L13 37 L13 15 Z" fill="#F2F3F5"/>
<rect x="13" y="22" width="38" height="8" fill="#F38B12"/>
<rect x="13" y="33" width="38" height="3" fill="#17181B"/>
</g>
</svg>
</div>
</div>
</section>

<section id="hoe" style="max-width: 1200px; margin: 0 auto; padding: 88px 24px; display: flex; flex-direction: column; gap: 40px">
<div style="display: flex; flex-direction: column; gap: 12px; max-width: 640px">
<h2 style="margin: 0; font-family: 'Space Grotesk', Inter, sans-serif; font-weight: 700; font-size: 40px; line-height: 44px; letter-spacing: -0.8px">Hoe het werkt</h2>
<p style="margin: 0; font-size: 18px; line-height: 28px; color: #5B6169">Vijf stappen, één dossier per nest. Niemand hoeft meer te mailen, bellen of Excel bij te houden.</p>
</div>
<ol style="list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px">
<li style="background: #FFFFFF; border: 1px solid #DCE0E4; border-radius: 10px; padding: 24px; display: flex; flex-direction: column; gap: 8px"><span style="font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 32px; color: #B85F00">1</span><strong style="font-size: 17px">Melding</strong><span style="font-size: 15px; line-height: 22px; color: #5B6169">Inwoner meldt met foto en locatie. Meldingen van Waarneming.nl komen automatisch binnen.</span></li>
<li style="background: #FFFFFF; border: 1px solid #DCE0E4; border-radius: 10px; padding: 24px; display: flex; flex-direction: column; gap: 8px"><span style="font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 32px; color: #B85F00">2</span><strong style="font-size: 17px">Beoordeling</strong><span style="font-size: 15px; line-height: 22px; color: #5B6169">De gemeente keurt goed, wijst af of markeert als urgent. Dubbele meldingen worden samengevoegd.</span></li>
<li style="background: #FFFFFF; border: 1px solid #DCE0E4; border-radius: 10px; padding: 24px; display: flex; flex-direction: column; gap: 8px"><span style="font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 32px; color: #B85F00">3</span><strong style="font-size: 17px">Opdracht</strong><span style="font-size: 15px; line-height: 22px; color: #5B6169">Met één klik naar de gecontracteerde bestrijder, met foto, hoogte en bereikbaarheid.</span></li>
<li style="background: #FFFFFF; border: 1px solid #DCE0E4; border-radius: 10px; padding: 24px; display: flex; flex-direction: column; gap: 8px"><span style="font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 32px; color: #B85F00">4</span><strong style="font-size: 17px">Avondroute</strong><span style="font-size: 15px; line-height: 22px; color: #5B6169">De bestrijder ziet alle nesten op de kaart en rijdt een geoptimaliseerde route na zonsondergang.</span></li>
<li style="background: #FFFFFF; border: 1px solid #DCE0E4; border-radius: 10px; padding: 24px; display: flex; flex-direction: column; gap: 8px"><span style="font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 32px; color: #B85F00">5</span><strong style="font-size: 17px">Rapportage</strong><span style="font-size: 15px; line-height: 22px; color: #5B6169">Maandrapport voor de gemeente en factuur per nest voor de bestrijder. Automatisch.</span></li>
</ol>
</section>

<section style="background: #F38B12">
<div style="max-width: 1200px; margin: 0 auto; padding: 64px 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 32px; align-items: end">
<div style="display: flex; flex-direction: column; gap: 4px"><span style="font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 72px; line-height: 1; letter-spacing: -2px">10.000+</span><span style="font-size: 16px; font-weight: 500">nesten gemeld in 2025 – tegen zo’n 2.000 in 2024</span></div>
<div style="display: flex; flex-direction: column; gap: 4px"><span style="font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 72px; line-height: 1; letter-spacing: -2px">2026</span><span style="font-size: 16px; font-weight: 500">Noord-Brabant stopt met standaard bestrijden; gemeenten nemen het over</span></div>
<p style="margin: 0; font-size: 18px; line-height: 27px; font-weight: 500">Gemeenten moeten nu zelf melden, inkopen en verantwoorden. MeldHoornaar maakt dat behapbaar.</p>
</div>
</section>

<section style="max-width: 1200px; margin: 0 auto; padding: 88px 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 24px">
<article id="gemeenten" style="border: 1px solid #DCE0E4; border-radius: 12px; padding: 40px; display: flex; flex-direction: column; gap: 16px; background: #EEF0F2">
<p style="margin: 0; font-size: 13px; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase; color: #B85F00">Voor gemeenten</p>
<h3 style="margin: 0; font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 30px; line-height: 34px">Grip op elk nest, zonder extra fte</h3>
<ul style="margin: 0; padding-left: 20px; display: flex; flex-direction: column; gap: 8px; font-size: 16px; line-height: 24px">
<li>Eén overzicht: kaart, status en doorlooptijd per nest</li>
<li>Werkt met uw bestaande bestrijder of raamovereenkomst</li>
<li>Maandrapportage voor college en raad</li>
<li>Eigen meldpagina in uw huisstijl</li>
</ul>
</article>
<article id="bestrijders" style="border-radius: 12px; padding: 40px; display: flex; flex-direction: column; gap: 16px; background: #17181B; color: #F2F3F5"><img src="/img/info/bestrijder.jpg" alt="Bestrijder in beschermend pak haalt vanuit een hoogwerker een hoornaarnest weg" style="width: 100%; height: 220px; object-fit: cover; border-radius: 8px; display: block" loading="lazy"><span style="font-size: 11px; color: #A3A9B1">Foto ter illustratie</span>
<p style="margin: 0; font-size: 13px; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase; color: #FFAA45">Voor bestrijders</p>
<h3 style="margin: 0; font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 30px; line-height: 34px">Minder rijden, sneller factureren</h3>
<ul style="margin: 0; padding-left: 20px; display: flex; flex-direction: column; gap: 8px; font-size: 16px; line-height: 24px">
<li>Opdrachten van alle gemeenten in één app</li>
<li>Avondroute op volgorde, met navigatie</li>
<li>Foto na bestrijding = nest afgemeld</li>
<li>Factuur per nest staat automatisch klaar</li>
</ul>
</article>
</section>

<section id="pilot" style="background: #FFFFFF; border-top: 1px solid #DCE0E4; border-bottom: 1px solid #DCE0E4">
<div style="max-width: 1200px; margin: 0 auto; padding: 88px 24px; display: flex; flex-direction: column; gap: 24px">
<h2 style="margin: 0; font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 40px; line-height: 44px; letter-spacing: -0.8px">Pilot 2027</h2>
<p style="margin: 0; font-size: 18px; line-height: 28px; color: #5B6169; max-width: 640px">Drie gemeenten gebruiken MeldHoornaar een jaar gratis, samen met hun eigen bestrijder. Daarna een vast bedrag per maand, afhankelijk van de grootte van de gemeente.</p>
<div style="background: #FFF1E0; border-radius: 10px; padding: 24px 32px; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap">
<p style="margin: 0; font-size: 18px; font-weight: 600">Interesse of tarieven opvragen? We plannen graag een kennismaking.</p>
<a href="mailto:info@meldhoornaar.nl" style="background: #17181B; color: #F2F3F5; text-decoration: none; font-weight: 600; padding: 14px 24px; border-radius: 10px">Neem contact op</a>
</div>
</div>
</section>



<footer style="background: #17181B; color: #A3A9B1">
<div style="max-width: 1200px; margin: 0 auto; padding: 40px 24px; display: flex; justify-content: space-between; align-items: center; gap: 24px; flex-wrap: wrap; font-size: 14px">
@include('partials.logo', ['dark' => true, 'h' => 32, 'id' => 'zakf'])
<span>© 2027 MeldHoornaar · [KVK-NUMMER] · info@meldhoornaar.nl</span>
</div>
</footer>
</div>
@endsection
