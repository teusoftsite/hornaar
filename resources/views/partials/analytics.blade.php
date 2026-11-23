@php($gaId = config('meldhoornaar.ga_id'))
@if($gaId && ! request()->routeIs('gemeente.*', 'bestrijder.*', 'beheer.*', 'login', 'password.*', 'two-factor.*', 'tweestaps'))
{{-- Google Analytics 4 met Consent Mode v2: niets wordt opgeslagen tot de bezoeker akkoord geeft. --}}
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
var mhConsent = null; try { mhConsent = localStorage.getItem('mh_consent'); } catch (e) {}
gtag('consent', 'default', {ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied', analytics_storage: 'denied', wait_for_update: 500});
if (mhConsent === 'ja') gtag('consent', 'update', {ad_storage: 'granted', ad_user_data: 'granted', ad_personalization: 'granted', analytics_storage: 'granted'});
gtag('js', new Date());
gtag('config', '{{ $gaId }}', {anonymize_ip: true});
window.mhEvent = function (name, params) { gtag('event', name, params || {}); };
</script>
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
<div id="mh-consent" role="dialog" aria-label="Cookies" hidden
  style="position:fixed;left:16px;right:16px;bottom:16px;max-width:560px;margin:0 auto;background:#17181B;color:#F2F3F5;border-radius:12px;padding:20px;box-shadow:0 18px 40px rgba(0,0,0,.28);z-index:1000;font:400 14px/20px Inter,sans-serif">
  <strong style="font-family:'Space Grotesk',Inter,sans-serif;font-size:16px">Mogen we meten?</strong>
  <p style="margin:6px 0 14px;color:#C9CDD2">We gebruiken cookies van Google Analytics om te zien hoe de site gebruikt wordt en om onze advertenties te verbeteren. Melden werkt ook zonder. <a href="{{ route('privacy') }}" style="color:#FFAA45">Meer weten</a></p>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <button type="button" data-mh-consent="ja" style="background:#F38B12;color:#17181B;border:0;border-radius:8px;padding:10px 16px;font:600 14px Inter,sans-serif;cursor:pointer">Akkoord</button>
    <button type="button" data-mh-consent="nee" style="background:transparent;color:#F2F3F5;border:1px solid #3A3F45;border-radius:8px;padding:10px 16px;font:600 14px Inter,sans-serif;cursor:pointer">Alleen noodzakelijk</button>
  </div>
</div>
<script>
(function () {
  var box = document.getElementById('mh-consent');
  if (mhConsent !== 'ja' && mhConsent !== 'nee') box.hidden = false;
  box.addEventListener('click', function (e) {
    var v = e.target.getAttribute('data-mh-consent'); if (!v) return;
    try { localStorage.setItem('mh_consent', v); } catch (err) {}
    var g = v === 'ja' ? 'granted' : 'denied';
    gtag('consent', 'update', {ad_storage: g, ad_user_data: g, ad_personalization: g, analytics_storage: g});
    box.hidden = true;
  });
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href^="mailto:"], a[href^="tel:"]');
    if (a) mhEvent(a.href.indexOf('mailto:') === 0 ? 'contact_mail' : 'contact_bellen', {pagina: location.pathname});
    var w = e.target.closest && e.target.closest('[data-mh-cookies]');
    if (w) { e.preventDefault(); box.hidden = false; }
  });
})();
</script>
@endif
