{{-- Leaflet kaartcomponent voor de koten-overzichtspagina.
     Toont alle gebouwen met beschikbare kamers als custom pins op een OpenStreetMap kaart.
     Popup: 1 kamer → directe weergave met naam, prijs en link; meerdere kamers → lijst.
     Gebruik: <x-rooms-map :buildings="$mapBuildings" /> --}}

@props(['buildings'])

@php
    $mapData = $buildings->toJson();
@endphp

{{-- Leaflet map: buildings als custom pins, popup met kamerlijst --}}
<div class="relative overflow-hidden rounded-2xl border border-ink/10">
    <div id="kk-map" class="h-[420px] w-full bg-primary-900/5 sm:h-[520px]" aria-label="Kaart met beschikbare koten"></div>
</div>

@once
    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    @endpush
@endonce

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN2GqnY=" crossorigin=""></script>

<script>
(function () {
    const buildings = @json($buildings);

    const map = L.map('kk-map', {
        scrollWheelZoom: false,
        zoomControl: true,
    });

    // OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);

    // Custom pin icon
    function makeIcon(count) {
        const label = count > 1 ? String(count) : '';
        return L.divIcon({
            className: '',
            iconSize: [36, 44],
            iconAnchor: [18, 44],
            popupAnchor: [0, -46],
            html: `
                <div style="position:relative;width:36px;height:44px;">
                    <svg viewBox="0 0 36 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:36px;height:44px;filter:drop-shadow(0 2px 4px rgba(0,0,0,.25))">
                        <path d="M18 0C8.059 0 0 8.059 0 18c0 12.5 18 26 18 26S36 30.5 36 18C36 8.059 27.941 0 18 0Z" fill="#0f172a"/>
                        <circle cx="18" cy="18" r="8" fill="#f59e0b"/>
                    </svg>
                    ${label ? `<span style="position:absolute;top:5px;left:50%;transform:translateX(-50%);font-size:10px;font-weight:700;color:#fff;line-height:1;">${label}</span>` : ''}
                </div>`,
        });
    }

    // Build popup HTML
    function buildPopup(building) {
        const rooms = building.rooms;

        if (rooms.length === 1) {
            const r = rooms[0];
            return `
                <div style="min-width:180px;font-family:inherit;">
                    <p style="font-size:0.75rem;color:#64748b;margin:0 0 2px;">${building.address}</p>
                    <p style="font-size:1rem;font-weight:600;margin:0 0 6px;color:#0f172a;">${r.title}</p>
                    <p style="font-size:0.9rem;color:#0f172a;margin:0 0 10px;">€${r.price.toLocaleString('nl-BE')}<span style="font-size:0.65rem;color:#94a3b8;">/m</span></p>
                    <a href="${r.url}" style="display:inline-flex;align-items:center;gap:6px;font-size:0.78rem;font-weight:600;color:#fff;background:#0f172a;padding:6px 14px;border-radius:8px;text-decoration:none;">
                        Bekijk kot →
                    </a>
                </div>`;
        }

        const listItems = rooms.map(r => `
            <li style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:7px 0;border-bottom:1px solid #f1f5f9;">
                <span style="font-size:0.82rem;font-weight:500;color:#0f172a;">${r.title}</span>
                <span style="font-size:0.82rem;color:#0f172a;white-space:nowrap;">€${r.price.toLocaleString('nl-BE')}/m</span>
                <a href="${r.url}" style="font-size:0.75rem;font-weight:600;color:#0f172a;text-decoration:underline;white-space:nowrap;">Bekijk →</a>
            </li>`).join('');

        return `
            <div style="min-width:240px;font-family:inherit;">
                <p style="font-size:0.78rem;font-weight:700;color:#0f172a;margin:0 0 2px;">${building.name}</p>
                <p style="font-size:0.72rem;color:#64748b;margin:0 0 8px;">${building.address}</p>
                <ul style="list-style:none;margin:0;padding:0;">
                    ${listItems}
                </ul>
            </div>`;
    }

    if (buildings.length === 0) {
        // Default view Belgium if no markers
        map.setView([50.85, 4.35], 8);
        return;
    }

    const markers = buildings.map(building => {
        const marker = L.marker([building.lat, building.lng], {
            icon: makeIcon(building.rooms.length),
            title: building.name,
        });

        marker.bindPopup(buildPopup(building), {
            maxWidth: 300,
            className: 'kk-popup',
        });

        return marker;
    });

    const group = L.featureGroup(markers).addTo(map);
    map.fitBounds(group.getBounds().pad(0.15), { maxZoom: 14 });
})();
</script>

<style>
    .kk-popup .leaflet-popup-content-wrapper {
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,.12);
        padding: 0;
        overflow: hidden;
    }
    .kk-popup .leaflet-popup-content {
        margin: 14px 16px;
    }
    .kk-popup .leaflet-popup-tip-container {
        margin-top: -1px;
    }
</style>
