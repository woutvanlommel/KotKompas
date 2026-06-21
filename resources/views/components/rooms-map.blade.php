{{-- Leaflet map component for the rooms index page.
     Shows all filtered buildings as custom pins on an OpenStreetMap map.
     - Centred on $defaultCity by default (see variables below).
     - Markers outside the visible viewport are dimmed after dragging (debounced).
     - Popup: 1 room → name, price, link; multiple rooms → list.
     Usage: <x-rooms-map :buildings="$mapBuildings" default-city="hasselt" /> --}}

@props(['buildings', 'defaultCity' => 'belgie', 'height' => '500px', 'partialUrl' => null])

@php
    // Default locations for Belgian cities — adjustable via the defaultCity prop.
    $cities = [
        'belgie'    => ['lat' => 50.641,  'lng' => 4.668,  'zoom' => 8],
        'hasselt'   => ['lat' => 50.9307, 'lng' => 5.3384, 'zoom' => 13],
        'leuven'    => ['lat' => 50.8798, 'lng' => 4.7005, 'zoom' => 13],
        'gent'      => ['lat' => 51.0543, 'lng' => 3.7174, 'zoom' => 13],
        'antwerpen' => ['lat' => 51.2213, 'lng' => 4.4051, 'zoom' => 13],
        'brussel'   => ['lat' => 50.8503, 'lng' => 4.3517, 'zoom' => 13],
        'brugge'    => ['lat' => 51.2093, 'lng' => 3.2247, 'zoom' => 13],
        'mechelen'  => ['lat' => 51.0259, 'lng' => 4.4776, 'zoom' => 13],
        'kortrijk'  => ['lat' => 50.8283, 'lng' => 3.2650, 'zoom' => 13],
        'aalst'     => ['lat' => 50.9368, 'lng' => 4.0404, 'zoom' => 13],
        'genk'      => ['lat' => 50.9651, 'lng' => 5.4990, 'zoom' => 13],
        'roeselare' => ['lat' => 50.9443, 'lng' => 3.1229, 'zoom' => 13],
    ];

    $center = $cities[strtolower($defaultCity)] ?? $cities['belgie'];
@endphp

{{-- Leaflet CSS --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.Default.css">

<style>
    #kk-map { height: {{ $height }}; width: 100%; background: #f8fafc; }
    .kk-pin-out { opacity: 0.25; transition: opacity .3s; }
    .kk-pin-in  { opacity: 1;    transition: opacity .3s; }
    .kk-popup .leaflet-popup-content-wrapper {
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,.12);
        padding: 0;
        overflow: hidden;
    }
    .kk-popup .leaflet-popup-content { margin: 14px 16px; }
    .kk-popup .leaflet-popup-tip-container { margin-top: -1px; }
    .kk-cluster {
        display: flex; align-items: center; justify-content: center;
        width: 36px !important; height: 36px !important;
        background: #0f172a; color: #fff;
        font-size: 12px; font-weight: 700; font-family: inherit;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(0,0,0,.35);
        transform: translate(-50%, -50%);
    }
    @keyframes kk-pulse {
        0%, 100% { opacity: 1; }
        50%       { opacity: .45; }
    }
    .kk-skeleton {
        background: #e2e8f0;
        border-radius: 1.25rem;
        animation: kk-pulse 1.6s ease-in-out infinite;
    }
</style>

<div class="relative overflow-hidden rounded-2xl border border-ink/10">
    <div id="kk-map" aria-label="Kaart met beschikbare koten"></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.js"></script>

<script>
(function () {
    const BUILDINGS   = @json($buildings);
    const DEFAULT     = { lat: {{ $center['lat'] }}, lng: {{ $center['lng'] }}, zoom: {{ $center['zoom'] }} };
    const PARTIAL_URL = @json($partialUrl);

    // ── Map init ──────────────────────────────────────────────────────────────
    const map = L.map('kk-map', { scrollWheelZoom: true });

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20,
    }).addTo(map);

    // ── Pill marker ───────────────────────────────────────────────────────────
    function makeIcon(b) {
        const featured = b.rooms.some(r => r.featured);
        const label    = b.rooms.length === 1
            ? `€${b.rooms[0].price_per_month.toLocaleString('nl-BE')}/maand`
            : `${b.rooms.length} koten`;

        const bg     = featured ? '#f97316' : '#0f172a'; // oranje = uitgelicht
        const tip    = featured ? '#f97316' : '#0f172a';
        const prefix = featured
            ? `<svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor" style="flex-shrink:0;margin-right:4px" aria-hidden="true"><path d="M5 0l1.12 3.44H10L7.19 5.56l1.12 3.44L5 7 1.69 9l1.12-3.44L0 3.44h3.88z"/></svg>`
            : '';

        return L.divIcon({
            className: '',
            iconAnchor: [0, 32],
            popupAnchor: [0, -34],
            html: `<div style="display:flex;flex-direction:column;align-items:center;transform:translateX(-50%);cursor:pointer;">
                <div style="
                    display:inline-flex;align-items:center;
                    background:${bg};color:#fff;
                    font-size:12px;font-weight:700;font-family:inherit;
                    padding:5px 10px;border-radius:999px;white-space:nowrap;
                    box-shadow:0 2px 8px rgba(0,0,0,.3);
                ">${prefix}${label}</div>
                <div style="
                    width:0;height:0;
                    border-left:5px solid transparent;
                    border-right:5px solid transparent;
                    border-top:6px solid ${tip};
                "></div>
            </div>`,
        });
    }

    // ── Popup HTML ────────────────────────────────────────────────────────────
    function buildPopup(b) {
        if (b.rooms.length === 1) {
            const r = b.rooms[0];
            const featuredBadge = r.featured
                ? `<span style="display:inline-flex;align-items:center;gap:4px;font-size:.65rem;font-weight:700;color:#f97316;margin-bottom:6px;">
                    <svg width="9" height="9" viewBox="0 0 10 10" fill="currentColor" aria-hidden="true"><path d="M5 0l1.12 3.44H10L7.19 5.56l1.12 3.44L5 7 1.69 9l1.12-3.44L0 3.44h3.88z"/></svg>
                    Uitgelicht
                   </span><br>`
                : '';
            return `<div style="min-width:180px;font-family:inherit;">
                <p style="font-size:.75rem;color:#64748b;margin:0 0 2px">${b.address}</p>
                ${featuredBadge}
                <p style="font-size:1rem;font-weight:600;margin:0 0 6px;color:#0f172a">${r.title}</p>
                <p style="font-size:.9rem;color:#0f172a;margin:0 0 10px">€${r.price_per_month.toLocaleString('nl-BE')}<span style="font-size:.65rem;color:#94a3b8">/maand</span></p>
                <a href="${r.url}" style="display:inline-flex;align-items:center;gap:6px;font-size:.78rem;font-weight:600;color:#fff;background:#0f172a;padding:6px 14px;border-radius:8px;text-decoration:none;">Bekijk kot →</a>
            </div>`;
        }

        const items = b.rooms.map(r => `
            <li style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:6px 0;border-bottom:1px solid #f1f5f9;">
                <span style="display:flex;align-items:center;gap:5px;font-size:.82rem;font-weight:500;color:#0f172a;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    ${r.featured ? `<svg width="9" height="9" viewBox="0 0 10 10" fill="#f97316" style="flex-shrink:0" aria-hidden="true"><path d="M5 0l1.12 3.44H10L7.19 5.56l1.12 3.44L5 7 1.69 9l1.12-3.44L0 3.44h3.88z"/></svg>` : ''}
                    ${r.title}
                </span>
                <span style="font-size:.82rem;color:#0f172a;white-space:nowrap">€${r.price_per_month.toLocaleString('nl-BE')}/maand</span>
                <a href="${r.url}" style="font-size:.75rem;font-weight:600;color:#0f172a;text-decoration:underline;white-space:nowrap">Bekijk →</a>
            </li>`).join('');

        return `<div style="min-width:260px;font-family:inherit;">
            <p style="font-size:.82rem;font-weight:700;color:#0f172a;margin:0 0 2px">${b.name}</p>
            <p style="font-size:.72rem;color:#64748b;margin:0 0 8px">${b.address}</p>
            <ul style="list-style:none;margin:0;padding:0">${items}</ul>
        </div>`;
    }

    // ── Markers & clustering ──────────────────────────────────────────────────
    const clusterGroup = L.markerClusterGroup({
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        spiderfyOnMaxZoom: true,
        disableClusteringAtZoom: 16,
        maxClusterRadius: 60,
        iconCreateFunction: function (cluster) {
            return L.divIcon({
                className: 'kk-cluster',
                html: `<span>${cluster.getChildCount()}</span>`,
                iconSize: [36, 36],
                iconAnchor: [18, 18],
            });
        },
    });

    const markers = BUILDINGS.map(b => {
        const m = L.marker([b.lat, b.lng], { icon: makeIcon(b), title: b.name });
        m.bindPopup(buildPopup(b), { maxWidth: 320, className: 'kk-popup' });
        return m;
    });

    clusterGroup.addLayers(markers);
    map.addLayer(clusterGroup);

    // ── Viewport filtering met debounce ───────────────────────────────────────
    let debounceTimer = null;
    let fetchController = null;

    function updateMarkerVisibility() {
        const bounds = map.getBounds();
        markers.forEach((m, i) => {
            const inView = bounds.contains(L.latLng(BUILDINGS[i].lat, BUILDINGS[i].lng));
            const el = m.getElement();
            if (el) {
                el.classList.toggle('kk-pin-out', !inView);
                el.classList.toggle('kk-pin-in', inView);
            }
            // Alleen zichtbare (niet-geclusterde) markers hebben een element.
        });
    }

    // ── Lijst updaten op basis van kaartgrenzen ───────────────────────────────
    // Grid-klassen matchen de view: kaart = 2 cols, grid = 4 cols, lijst = 1 col.
    const view = new URLSearchParams(window.location.search).get('view') || 'grid';
    const skeletonGrid = view === 'map'
        ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2'
        : view === 'list'
            ? 'grid-cols-1'
            : 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4';
    const SKELETON_HTML = `<div class="grid ${skeletonGrid} gap-5">
        ${'<div class="kk-skeleton aspect-square"></div>'.repeat(12)}
    </div>`;

    function setLoading(on) {
        const target = document.getElementById('kk-map-rooms');
        if (!target) return;
        if (on) {
            target.innerHTML = SKELETON_HTML;
            if (view === 'map') {
                const offset = target.getBoundingClientRect().top + window.scrollY - 96;
                window.scrollTo({ top: Math.max(0, offset), behavior: 'smooth' });
            }
        }
    }

    function fetchRoomsInBounds() {
        // Lazy lookup: #kk-map-rooms staat in de tweede kolom en bestaat pas
        // nadat het volledige document geparsed is.
        const target = document.getElementById('kk-map-rooms');
        if (! PARTIAL_URL || ! target) return;

        const b = map.getBounds();
        const params = new URLSearchParams(window.location.search);
        params.set('bounds_sw_lat', b.getSouth().toFixed(6));
        params.set('bounds_sw_lng', b.getWest().toFixed(6));
        params.set('bounds_ne_lat', b.getNorth().toFixed(6));
        params.set('bounds_ne_lng', b.getEast().toFixed(6));

        if (fetchController) fetchController.abort();
        fetchController = new AbortController();

        setLoading(true);

        fetch(PARTIAL_URL + '?' + params.toString(), { signal: fetchController.signal })
            .then(r => r.text())
            .then(html => {
                target.innerHTML = html;
                setLoading(false);
                bindPaginationLinks(target);
            })
            .catch(err => {
                if (err.name !== 'AbortError') setLoading(false);
            });
    }

    function bindPaginationLinks(target) {
        target = target || document.getElementById('kk-map-rooms');
        if (! target) return;
        target.querySelectorAll('a[href*="kaart-koten"]').forEach(a => {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                if (fetchController) fetchController.abort();
                fetchController = new AbortController();
                const t = document.getElementById('kk-map-rooms');
                setLoading(true);
                fetch(this.href, { signal: fetchController.signal })
                    .then(r => r.text())
                    .then(html => {
                        t.innerHTML = html;
                        setLoading(false);
                        bindPaginationLinks(t);
                    })
                    .catch(err => {
                        if (err.name !== 'AbortError') setLoading(false);
                    });
            });
        });
    }

    map.on('moveend', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            updateMarkerVisibility();
            fetchRoomsInBounds();
        }, 400);
    });

    // ── Initiële view ─────────────────────────────────────────────────────────
    // Listener eerst koppelen zodat fitBounds zijn moveend ook opvangt.
    // map.whenReady zorgt dat de bounds al beschikbaar zijn voor de eerste fetch.
    map.whenReady(function () {
        fetchRoomsInBounds();
    });

    if (markers.length > 0) {
        map.fitBounds(clusterGroup.getBounds().pad(0.15), { maxZoom: 14 });
    } else {
        map.setView([DEFAULT.lat, DEFAULT.lng], DEFAULT.zoom);
    }
})();
</script>
