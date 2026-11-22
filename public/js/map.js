/* Kaarthulp: Leaflet + PDOK BRT-achtergrondkaart. */
window.MH = window.MH || {};
MH.COLORS = {gemeld:'#f4c430',goedgekeurd:'#f39a4a',opdracht_gegeven:'#ec7a1c',ingepland:'#3b6fb6',geen_toegang:'#b3261e',bestreden:'#1f6b45',gefactureerd:'#1f6b45',afgewezen:'#6b6156',samengevoegd:'#6b6156'};
MH.map = function (el, center, zoom) {
  var m = L.map(el, {scrollWheelZoom: false}).setView(center || [52.1, 5.2], zoom || 8);
  L.tileLayer('https://service.pdok.nl/brt/achtergrondkaart/wmts/v2_0/standaard/EPSG:3857/{z}/{x}/{y}.png', {
    maxZoom: 19, minZoom: 6, attribution: 'Kaart: <a href="https://www.pdok.nl">PDOK</a> / Kadaster'
  }).addTo(m);
  return m;
};
MH.pin = function (lat, lng, status, label) {
  var c = MH.COLORS[status] || '#1c1712';
  var icon = L.divIcon({className: '', iconSize: [26, 26], iconAnchor: [13, 13],
    html: '<div style="width:26px;height:26px;border-radius:50%;background:' + c + ';border:3px solid #1c1712;color:#fff;font:700 11px Inter;display:flex;align-items:center;justify-content:center">' + (label || '') + '</div>'});
  return L.marker([lat, lng], {icon: icon});
};
/* Locatiekiezer: sleepbare pin die lat/lng-velden vult. */
MH.picker = function (el, latInput, lngInput, onChange) {
  var m = MH.map(el, [52.1, 5.2], 7), marker = null;
  function set(lat, lng, zoom) {
    latInput.value = lat.toFixed(6); lngInput.value = lng.toFixed(6);
    if (!marker) { marker = L.marker([lat, lng], {draggable: true}).addTo(m); marker.on('dragend', function () { var p = marker.getLatLng(); set(p.lat, p.lng); }); }
    else marker.setLatLng([lat, lng]);
    if (zoom) m.setView([lat, lng], zoom);
    onChange && onChange(lat, lng);
  }
  m.on('click', function (e) { set(e.latlng.lat, e.latlng.lng); });
  if (latInput.value && lngInput.value) set(+latInput.value, +lngInput.value, 17);
  return {map: m, set: set};
};
