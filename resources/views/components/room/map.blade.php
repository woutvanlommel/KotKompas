@props(['room'])

@php
    $building  = $room->building;
    $hasCoords = $building->latitude && $building->longitude;

    $categoryIcons = [
        'supermarket'   => ['emoji' => '🛒', 'label' => 'Supermarkt'],
        'convenience'   => ['emoji' => '🏪', 'label' => 'Winkel'],
        'pharmacy'      => ['emoji' => '💊', 'label' => 'Apotheek'],
        'hospital'      => ['emoji' => '🏥', 'label' => 'Ziekenhuis'],
        'bus_stop'      => ['emoji' => '🚌', 'label' => 'Bushalte'],
        'train_station' => ['emoji' => '🚂', 'label' => 'Treinstation'],
        'tram_stop'     => ['emoji' => '🚊', 'label' => 'Tramhalte'],
        'cafe'          => ['emoji' => '☕', 'label' => 'Café'],
        'fast_food'     => ['emoji' => '🍔', 'label' => 'Fast food'],
    ];

    $poisData = ($building->poiCache ?? collect())->map(fn ($p) => [
        'category' => $p->category,
        'name'     => $p->name,
        'lat'      => (float) $p->latitude,
        'lng'      => (float) $p->longitude,
    ])->values();
@endphp

<div>
    <p class="mb-4 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink/55">
        <span class="inline-block h-px w-9 bg-accent-500"></span> In de buurt
    </p>
    <h2 class="mb-6 text-xl font-medium tracking-[-0.02em]">Wat ligt er in de buurt?</h2>

    @if ($hasCoords)
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">

        <style>
            .kk-poi-map { height: 320px; width: 100%; }
            .poi-popup .leaflet-popup-content-wrapper { border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.12); }
            .poi-popup .leaflet-popup-content { margin: 10px 14px; font-family: inherit; }
        </style>

        <div class="overflow-hidden rounded-2xl border border-ink/10">
            <div
                class="kk-poi-map"
                aria-label="Kaart met nabijgelegen voorzieningen"
                data-lat="{{ (float) $building->latitude }}"
                data-lng="{{ (float) $building->longitude }}"
                data-name="{{ e($building->name) }}"
                data-pois="{{ json_encode($poisData) }}"
                data-icons="{{ json_encode($categoryIcons) }}"
            ></div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

        <script>
        (function () {
            var el    = document.querySelector('.kk-poi-map');
            if (!el) return;

            var lat   = parseFloat(el.dataset.lat);
            var lng   = parseFloat(el.dataset.lng);
            var name  = el.dataset.name;
            var pois  = JSON.parse(el.dataset.pois);
            var icons = JSON.parse(el.dataset.icons);

            function esc(s) {
                var d = document.createElement('div');
                d.appendChild(document.createTextNode(String(s)));
                return d.innerHTML;
            }

            function initMap() {
                var map = L.map(el, { scrollWheelZoom: false });

                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                    subdomains: 'abcd',
                    maxZoom: 20,
                }).addTo(map);

                // Building pin
                var buildingIcon = L.divIcon({
                    className: '',
                    iconAnchor: [0, 32],
                    popupAnchor: [0, -34],
                    html: '<div style="display:flex;flex-direction:column;align-items:center;transform:translateX(-50%);">'
                        + '<div style="background:#0f172a;color:#fff;font-size:12px;font-weight:700;font-family:inherit;padding:5px 10px;border-radius:999px;white-space:nowrap;box-shadow:0 2px 8px rgba(0,0,0,.3);">' + esc(name) + '</div>'
                        + '<div style="width:0;height:0;border-left:5px solid transparent;border-right:5px solid transparent;border-top:6px solid #0f172a;"></div>'
                        + '</div>',
                });

                L.marker([lat, lng], { icon: buildingIcon, zIndexOffset: 1000 }).addTo(map);

                // POI markers
                pois.forEach(function (poi) {
                    var meta = icons[poi.category] || { emoji: '📍', label: poi.category };

                    var icon = L.divIcon({
                        className: '',
                        iconAnchor: [14, 14],
                        popupAnchor: [0, -16],
                        // meta.emoji is safe: sourced from the server-side $categoryIcons PHP constant, never from user/DB input
                    html: '<div style="width:28px;height:28px;background:#fff;border:2px solid #e2e8f0;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;box-shadow:0 2px 6px rgba(0,0,0,.15);">' + meta.emoji + '</div>',
                    });

                    L.marker([poi.lat, poi.lng], { icon: icon })
                        .bindPopup(
                            '<div><span style="font-size:.7rem;color:#64748b;">' + esc(meta.label) + '</span><br>'
                            + '<span style="font-size:.85rem;font-weight:600;color:#0f172a;">' + esc(poi.name) + '</span></div>',
                            { className: 'poi-popup', maxWidth: 200 }
                        )
                        .addTo(map);
                });

                // Fit bounds
                if (pois.length > 0) {
                    var points = [[lat, lng]].concat(pois.map(function (p) { return [p.lat, p.lng]; }));
                    map.fitBounds(L.latLngBounds(points).pad(0.15), { maxZoom: 16 });
                } else {
                    map.setView([lat, lng], 15);
                }

                map.invalidateSize();
            }

            // The map div is inside an Alpine x-show wrapper that starts hidden.
            // Use IntersectionObserver to init Leaflet only once the element is visible.
            // If already visible on observe (e.g. scrolled into view before Alpine fires),
            // the callback fires immediately — so this covers both cases.
            var initialized = false;
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting && !initialized) {
                        initialized = true;
                        initMap();
                        observer.disconnect();
                    }
                });
            });

            observer.observe(el);

            // Fallback: if Alpine has already shown the element by the time this runs,
            // IntersectionObserver may not fire. Poll until visible then init.
            var fallback = setInterval(function () {
                if (initialized) { clearInterval(fallback); return; }
                if (el.offsetParent !== null) {
                    clearInterval(fallback);
                    initialized = true;
                    initMap();
                }
            }, 100);
        })();
        </script>
    @else
        <div class="flex h-56 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-hairline bg-canvas-deep sm:h-64">
            <div class="text-center text-ink/40">
                <x-heroicon-o-map-pin class="mx-auto h-10 w-10" aria-hidden="true" />
                <p class="mt-3 text-sm font-medium">Kaart niet beschikbaar</p>
                <p class="mt-1 text-xs text-ink/35">Geen locatiegegevens gevonden voor dit gebouw</p>
            </div>
        </div>
    @endif
</div>
