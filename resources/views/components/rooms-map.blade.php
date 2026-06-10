{{-- Leaflet kaartcomponent voor de koten-overzichtspagina.
     Toont alle gefilterde gebouwen als custom pins op een OpenStreetMap kaart.
     - Standaard gecentreerd op $defaultCity (zie variabelen hieronder).
     - Markers buiten het zichtbare viewport worden gedimmed na het slepen (met debounce).
     - Popup: 1 kamer → naam, prijs, link; meerdere kamers → lijst.
     Gebruik: <x-rooms-map :buildings="$mapBuildings" default-city="hasselt" /> --}}

@props(['buildings', 'defaultCity' => 'belgie'])

@php
    // Standaard locaties voor Belgische steden — aanpasbaar via de defaultCity prop.
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

<style>
    #kk-map { height: 500px; width: 100%; background: #f8fafc; }
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
</style>

<div class="relative overflow-hidden rounded-2xl border border-ink/10">
    <div id="kk-map" aria-label="Kaart met beschikbare koten"></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

<script>
(function () {
    const BUILDINGS = @json($buildings);
    const DEFAULT   = { lat: {{ $center['lat'] }}, lng: {{ $center['lng'] }}, zoom: {{ $center['zoom'] }} };

    // ── Map init ──────────────────────────────────────────────────────────────
    const map = L.map('kk-map', { scrollWheelZoom: true });

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20,
    }).addTo(map);

    // ── Pill marker ───────────────────────────────────────────────────────────
    function makeIcon(b) {
        const label = b.rooms.length === 1
            ? `€${b.rooms[0].price.toLocaleString('nl-BE')}`
            : `${b.rooms.length} koten`;

        return L.divIcon({
            className: '',
            iconAnchor: [0, 32],
            popupAnchor: [0, -34],
            html: `<div style="display:flex;flex-direction:column;align-items:center;transform:translateX(-50%);cursor:pointer;">
                <div style="
                    display:inline-flex;align-items:center;
                    background:#0f172a;color:#fff;
                    font-size:12px;font-weight:700;font-family:inherit;
                    padding:5px 10px;border-radius:999px;white-space:nowrap;
                    box-shadow:0 2px 8px rgba(0,0,0,.3);
                ">${label}</div>
                <div style="
                    width:0;height:0;
                    border-left:5px solid transparent;
                    border-right:5px solid transparent;
                    border-top:6px solid #0f172a;
                "></div>
            </div>`,
        });
    }

    // ── Popup HTML ────────────────────────────────────────────────────────────
    function buildPopup(b) {
        if (b.rooms.length === 1) {
            const r = b.rooms[0];
            return `<div style="min-width:180px;font-family:inherit;">
                <p style="font-size:.75rem;color:#64748b;margin:0 0 2px">${b.address}</p>
                <p style="font-size:1rem;font-weight:600;margin:0 0 6px;color:#0f172a">${r.title}</p>
                <p style="font-size:.9rem;color:#0f172a;margin:0 0 10px">€${r.price.toLocaleString('nl-BE')}<span style="font-size:.65rem;color:#94a3b8">/m</span></p>
                <a href="${r.url}" style="display:inline-flex;align-items:center;gap:6px;font-size:.78rem;font-weight:600;color:#fff;background:#0f172a;padding:6px 14px;border-radius:8px;text-decoration:none;">Bekijk kot →</a>
            </div>`;
        }

        const items = b.rooms.map(r => `
            <li style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:6px 0;border-bottom:1px solid #f1f5f9;">
                <span style="font-size:.82rem;font-weight:500;color:#0f172a;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${r.title}</span>
                <span style="font-size:.82rem;color:#0f172a;white-space:nowrap">€${r.price.toLocaleString('nl-BE')}/m</span>
                <a href="${r.url}" style="font-size:.75rem;font-weight:600;color:#0f172a;text-decoration:underline;white-space:nowrap">Bekijk →</a>
            </li>`).join('');

        return `<div style="min-width:260px;font-family:inherit;">
            <p style="font-size:.82rem;font-weight:700;color:#0f172a;margin:0 0 2px">${b.name}</p>
            <p style="font-size:.72rem;color:#64748b;margin:0 0 8px">${b.address}</p>
            <ul style="list-style:none;margin:0;padding:0">${items}</ul>
        </div>`;
    }

    // ── Markers ───────────────────────────────────────────────────────────────
    const markers = BUILDINGS.map(b => {
        const m = L.marker([b.lat, b.lng], { icon: makeIcon(b), title: b.name });
        m.bindPopup(buildPopup(b), { maxWidth: 320, className: 'kk-popup' });
        m.addTo(map);
        return m;
    });

    // ── Initiële view ─────────────────────────────────────────────────────────
    if (markers.length > 0) {
        const group = L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.15), { maxZoom: 14 });
    } else {
        map.setView([DEFAULT.lat, DEFAULT.lng], DEFAULT.zoom);
    }

    // ── Viewport filtering met debounce ───────────────────────────────────────
    let debounceTimer = null;

    function updateMarkerVisibility() {
        const bounds = map.getBounds();
        markers.forEach((m, i) => {
            const inView = bounds.contains(L.latLng(BUILDINGS[i].lat, BUILDINGS[i].lng));
            const el = m.getElement();
            if (el) {
                el.classList.toggle('kk-pin-out', !inView);
                el.classList.toggle('kk-pin-in', inView);
            }
        });
    }

    map.on('moveend', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(updateMarkerVisibility, 300);
    });
})();
</script>
