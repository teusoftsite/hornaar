/* Ingelogde omgeving: service worker, offline-melding en offline werkbonnen (IndexedDB-wachtrij). */
(function () {
  var bar = document.getElementById('offline');
  function online() { if (bar) bar.style.display = navigator.onLine ? 'none' : 'block'; if (navigator.onLine) MHQ.flush(); }
  window.addEventListener('online', online); window.addEventListener('offline', online);
  if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js').catch(function () {});

  var DB = 'meldhoornaar', STORE = 'werkbonnen';
  function db() { return new Promise(function (res, rej) {
    var r = indexedDB.open(DB, 1);
    r.onupgradeneeded = function () { r.result.createObjectStore(STORE, {keyPath: 'uuid'}); };
    r.onsuccess = function () { res(r.result); }; r.onerror = function () { rej(r.error); };
  }); }
  function tx(mode, fn) { return db().then(function (d) { return new Promise(function (res, rej) {
    var t = d.transaction(STORE, mode), s = t.objectStore(STORE), out = fn(s);
    t.oncomplete = function () { res(out && out.result !== undefined ? out.result : out); }; t.onerror = function () { rej(t.error); };
  }); }); }

  window.MHQ = {
    /* Bewaar een werkbon (FormData → plain object met Blobs). */
    save: function (url, form) {
      var item = {uuid: form.get('client_uuid'), url: url, fields: [], at: Date.now()};
      form.forEach(function (v, k) { item.fields.push([k, v]); });
      return tx('readwrite', function (s) { return s.put(item); });
    },
    count: function () { return tx('readonly', function (s) { return s.count(); }); },
    flush: function () {
      if (!navigator.onLine) return Promise.resolve();
      var token = document.querySelector('meta[name=csrf-token]').content;
      return tx('readonly', function (s) { return s.getAll(); }).then(function (items) {
        return Promise.all((items || []).map(function (it) {
          var fd = new FormData(); it.fields.forEach(function (f) { fd.append(f[0], f[1]); });
          return fetch(it.url, {method: 'POST', body: fd, credentials: 'same-origin', headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': token}})
            .then(function (r) { if (r.ok || r.status === 409 || r.status === 422) return tx('readwrite', function (s) { return s.delete(it.uuid); }); });
        }));
      }).then(MHQ.badge).catch(function () {});
    },
    badge: function () { return MHQ.count().then(function (n) { var el = document.getElementById('queue'); if (el) { el.textContent = n ? n + ' werkbon(nen) wachten op verbinding' : ''; } }); }
  };
  document.addEventListener('DOMContentLoaded', function () { online(); MHQ.badge(); });
})();
