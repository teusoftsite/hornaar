/* Kaarthulp: Leaflet + PDOK BRT-achtergrondkaart. */
window.MH = window.MH || {};
MH.COLORS = {gemeld:'#F38B12',goedgekeurd:'#B85F00',opdracht_gegeven:'#1d5fa8',ingepland:'#1d5fa8',geen_toegang:'#C42B1C',bestreden:'#1E7A46',gefactureerd:'#1E7A46',afgewezen:'#5B6169',samengevoegd:'#5B6169'};
MH.map = function (el, center, zoom) {
  var m = L.map(el, {scrollWheelZoom: false}).setView(center || [52.1, 5.2], zoom || 8);
  L.tileLayer('https://service.pdok.nl/brt/achtergrondkaart/wmts/v2_0/standaard/EPSG:3857/{z}/{x}/{y}.png', {
    maxZoom: 19, minZoom: 6, attribution: 'Kaart: <a href="https://www.pdok.nl">PDOK</a> / Kadaster'
  }).addTo(m);
  return m;
};
MH.pin = function (lat, lng, status, label) {
  var c = MH.COLORS[status] || '#17181B';
  var icon = L.divIcon({className: '', iconSize: [26, 26], iconAnchor: [13, 13],
    html: '<div style="width:26px;height:26px;border-radius:50%;background:' + c + ';border:3px solid #17181B;color:#fff;font:700 11px Inter;display:flex;align-items:center;justify-content:center">' + (label || '') + '</div>'});
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
