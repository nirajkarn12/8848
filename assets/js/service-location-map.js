(function () {
  'use strict';

  var DEFAULT_LAT = 27.7172;
  var DEFAULT_LNG = 85.3240;
  var DEFAULT_ZOOM = 13;

  function parseNum(value, fallback) {
    var n = parseFloat(value);
    return isFinite(n) ? n : fallback;
  }

  function setText(el, text) {
    if (el) el.textContent = text || '';
  }

  function buildMapsLinks(lat, lng, address) {
    var query = (lat != null && lng != null) ? (lat + ',' + lng) : (address || '');
    return {
      google: 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(query),
      osm: (lat != null && lng != null)
        ? ('https://www.openstreetmap.org/?mlat=' + encodeURIComponent(lat) + '&mlon=' + encodeURIComponent(lng) + '#map=16/' + encodeURIComponent(lat) + '/' + encodeURIComponent(lng))
        : ('https://www.openstreetmap.org/search?query=' + encodeURIComponent(address || '')),
      directions: (lat != null && lng != null)
        ? ('https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(lat + ',' + lng))
        : ('https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(address || ''))
    };
  }

  function reverseGeocode(lat, lng, done) {
    var url = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + encodeURIComponent(lat) + '&lon=' + encodeURIComponent(lng) + '&addressdetails=1';
    fetch(url, {
      headers: { 'Accept': 'application/json' }
    }).then(function (res) {
      return res.json();
    }).then(function (data) {
      done(null, data && data.display_name ? data.display_name : '');
    }).catch(function (err) {
      done(err || new Error('Reverse geocode failed'));
    });
  }

  function searchAddress(query, done) {
    var url = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=5&q=' + encodeURIComponent(query);
    fetch(url, {
      headers: { 'Accept': 'application/json' }
    }).then(function (res) {
      return res.json();
    }).then(function (data) {
      done(null, Array.isArray(data) ? data : []);
    }).catch(function (err) {
      done(err || new Error('Search failed'));
    });
  }

  function initPicker(root) {
    if (!window.L) return;

    var mapEl = root.querySelector('[data-map-canvas]');
    var latInput = root.querySelector('[data-map-lat]');
    var lngInput = root.querySelector('[data-map-lng]');
    var addressInputSelector = root.getAttribute('data-address-input') || '';
    var addressInput = addressInputSelector ? document.querySelector(addressInputSelector) : null;
    var searchInput = root.querySelector('[data-map-search]');
    var searchBtn = root.querySelector('[data-map-search-btn]');
    var locateBtn = root.querySelector('[data-map-locate-btn]');
    var metaEl = root.querySelector('[data-map-meta]');
    var statusEl = root.querySelector('[data-map-status]');

    var startLat = parseNum(latInput && latInput.value, DEFAULT_LAT);
    var startLng = parseNum(lngInput && lngInput.value, DEFAULT_LNG);
    var hasCoords = !!(latInput && lngInput && latInput.value && lngInput.value);

    var map = L.map(mapEl).setView([startLat, startLng], hasCoords ? 16 : DEFAULT_ZOOM);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    var marker = L.marker([startLat, startLng], { draggable: true }).addTo(map);

    function updateMeta(lat, lng) {
      setText(metaEl, 'Pinned: ' + Number(lat).toFixed(6) + ', ' + Number(lng).toFixed(6));
    }

    function setLocation(lat, lng, options) {
      options = options || {};
      marker.setLatLng([lat, lng]);
      if (options.pan !== false) {
        map.setView([lat, lng], options.zoom || map.getZoom());
      }
      if (latInput) latInput.value = Number(lat).toFixed(7);
      if (lngInput) lngInput.value = Number(lng).toFixed(7);
      updateMeta(lat, lng);

      if (options.fillAddress && addressInput) {
        setText(statusEl, 'Finding address...');
        reverseGeocode(lat, lng, function (err, label) {
          if (!err && label) {
            addressInput.value = label;
            setText(statusEl, 'Address updated from map pin.');
          } else {
            setText(statusEl, 'Pin saved. Type the address if needed.');
          }
        });
      }
    }

    if (hasCoords) {
      updateMeta(startLat, startLng);
    } else {
      setText(metaEl, 'Tap the map or search to pin the cleaning location.');
    }

    marker.on('dragend', function () {
      var pos = marker.getLatLng();
      setLocation(pos.lat, pos.lng, { fillAddress: true, pan: false });
      root.classList.remove('is-invalid-map');
    });

    map.on('click', function (e) {
      setLocation(e.latlng.lat, e.latlng.lng, { fillAddress: true, pan: false });
      root.classList.remove('is-invalid-map');
    });

    if (searchBtn && searchInput) {
      searchBtn.addEventListener('click', function () {
        var q = (searchInput.value || '').trim();
        if (q.length < 3) {
          setText(statusEl, 'Type at least 3 characters to search.');
          return;
        }
        setText(statusEl, 'Searching...');
        searchAddress(q, function (err, results) {
          if (err || !results.length) {
            setText(statusEl, 'No places found. Try a nearby landmark.');
            return;
          }
          var hit = results[0];
          setLocation(parseFloat(hit.lat), parseFloat(hit.lon), { fillAddress: false, zoom: 16 });
          if (addressInput) {
            addressInput.value = hit.display_name || q;
          }
          setText(statusEl, 'Location selected from search.');
        });
      });

      searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          searchBtn.click();
        }
      });
    }

    if (locateBtn) {
      locateBtn.addEventListener('click', function () {
        if (!navigator.geolocation) {
          setText(statusEl, 'GPS is not available on this device.');
          return;
        }
        setText(statusEl, 'Getting your current location...');
        navigator.geolocation.getCurrentPosition(function (pos) {
          setLocation(pos.coords.latitude, pos.coords.longitude, { fillAddress: true, zoom: 17 });
        }, function () {
          setText(statusEl, 'Could not access GPS. Pin the map manually.');
        }, { enableHighAccuracy: true, timeout: 10000 });
      });
    }

    setTimeout(function () { map.invalidateSize(); }, 200);
  }

  function initViewer(root) {
    if (!window.L) return;

    var mapEl = root.querySelector('[data-map-canvas]');
    var lat = parseNum(root.getAttribute('data-lat'), null);
    var lng = parseNum(root.getAttribute('data-lng'), null);
    var address = root.getAttribute('data-address') || '';
    var metaEl = root.querySelector('[data-map-meta]');
    var googleLink = root.querySelector('[data-map-google]');
    var osmLink = root.querySelector('[data-map-osm]');
    var dirLink = root.querySelector('[data-map-directions]');

    if (lat == null || lng == null) {
      setText(metaEl, address ? ('Address: ' + address) : 'No map pin saved for this booking.');
      var links = buildMapsLinks(null, null, address);
      if (googleLink) googleLink.href = links.google;
      if (osmLink) osmLink.href = links.osm;
      if (dirLink) dirLink.href = links.directions;
      mapEl.style.display = 'none';
      return;
    }

    var map = L.map(mapEl).setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
    L.marker([lat, lng]).addTo(map).bindPopup(address || 'Service location').openPopup();

    setText(metaEl, 'Service pin: ' + Number(lat).toFixed(6) + ', ' + Number(lng).toFixed(6));
    var mapLinks = buildMapsLinks(lat, lng, address);
    if (googleLink) googleLink.href = mapLinks.google;
    if (osmLink) osmLink.href = mapLinks.osm;
    if (dirLink) dirLink.href = mapLinks.directions;

    setTimeout(function () { map.invalidateSize(); }, 200);
  }

  function bindRequiredValidation() {
    document.querySelectorAll('form').forEach(function (form) {
      if (form.__serviceMapBound) return;
      var picker = form.querySelector('[data-service-map="picker"][data-map-required="1"]');
      if (!picker) return;
      form.__serviceMapBound = true;
      form.addEventListener('submit', function (e) {
        var latInput = picker.querySelector('[data-map-lat]');
        var lngInput = picker.querySelector('[data-map-lng]');
        var statusEl = picker.querySelector('[data-map-status]');
        var lat = latInput ? latInput.value : '';
        var lng = lngInput ? lngInput.value : '';
        if (!lat || !lng) {
          e.preventDefault();
          var msg = picker.getAttribute('data-required-message') || 'Please pin your exact service location on the map before submitting.';
          setText(statusEl, msg);
          picker.scrollIntoView({ behavior: 'smooth', block: 'center' });
          picker.classList.add('is-invalid-map');
          return false;
        }
        picker.classList.remove('is-invalid-map');
      });
    });
  }

  function boot() {
    document.querySelectorAll('[data-service-map="picker"]').forEach(initPicker);
    document.querySelectorAll('[data-service-map="view"]').forEach(initViewer);
    bindRequiredValidation();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
