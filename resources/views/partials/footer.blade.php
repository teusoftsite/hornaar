<footer class="foot"><div class="wrap">
  <a href="{{ route('home') }}" style="text-decoration:none">@include('partials.logo', ['dark' => true, 'h' => 30, 'id' => 'ftr'])</a>
  <span><a href="{{ route('zakelijk') }}">Voor gemeenten en bestrijders</a> · <a href="{{ route('info') }}">Alles over de hoornaar</a> · <a href="{{ route('privacy') }}">Privacy</a>@if(config('meldhoornaar.ga_id')) · <a href="#" data-mh-cookies>Cookies</a>@endif · © {{ date('Y') }} EasySoft Software BV</span>
</div></footer>
