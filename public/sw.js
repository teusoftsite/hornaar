/* Service worker: app-schil + bekeken pagina's offline beschikbaar (network-first). */
const CACHE = 'mh-v2';
const SHELL = ['/css/app.css?v=2', '/js/app.js?v=2', '/js/map.js?v=2', '/logo.svg', '/favicon.svg'];
self.addEventListener('install', e => e.waitUntil(caches.open(CACHE).then(c => c.addAll(SHELL)).then(() => self.skipWaiting())));
self.addEventListener('activate', e => e.waitUntil(caches.keys().then(ks => Promise.all(ks.filter(k => k !== CACHE).map(k => caches.delete(k)))).then(() => self.clients.claim())));
self.addEventListener('fetch', e => {
  const r = e.request;
  if (r.method !== 'GET' || new URL(r.url).origin !== location.origin) return;
  if (r.url.includes('/foto/')) return;
  e.respondWith(fetch(r).then(res => {
    if (res.ok && (r.mode === 'navigate' || SHELL.some(s => r.url.endsWith(s)))) { const copy = res.clone(); caches.open(CACHE).then(c => c.put(r, copy)); }
    return res;
  }).catch(() => caches.match(r).then(m => m || caches.match('/bestrijder/opdrachten'))));
});
