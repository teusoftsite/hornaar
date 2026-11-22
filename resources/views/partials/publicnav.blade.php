<header class="balk"><div class="wrap">
  <a class="logo-link" href="{{ route('home') }}" aria-label="MeldHoornaar.nl – home">@include('partials.logo', ['dark' => true, 'h' => 34, 'id' => 'hdr'])</a>
  <nav aria-label="Hoofdmenu">
    @foreach(['melden' => 'Melden', 'info.herkennen' => 'Herkennen', 'info.jaarcyclus' => 'Jaarcyclus', 'info.nesten-zoeken' => 'Nesten zoeken', 'info.achtergrond' => 'Achtergrond', 'info.vragen' => 'Vragen'] as $r => $l)
      <a href="{{ route($r) }}" class="{{ request()->routeIs($r) ? 'on' : '' }}">{{ $l }}</a>
    @endforeach
  </nav>
  <div class="rechts">
    <a href="{{ route('zakelijk') }}" style="border:0;font-size:13px">Voor gemeenten en bestrijders</a>
    @auth<a href="{{ auth()->user()->homeRoute() }}">Mijn omgeving</a>@else<a href="{{ route('login') }}">Inloggen</a>@endauth
  </div>
</div></header>
