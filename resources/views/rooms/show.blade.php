{{-- Detail page for an individual room.
     Components live in resources/views/components/room/.
     Content is rendered server-side (visible to crawlers); Alpine only adds a
     subtle fade-in on top of already-present markup. --}}

@php
    $typeLabels = [
        'studio'            => 'Studio',
        'one_bedroom'       => '1 slaapkamer',
        'two_bedroom'       => '2 slaapkamers',
        'three_bedroom'     => '3 slaapkamers',
        'four_bedroom'      => '4 slaapkamers',
        'five_plus_bedroom' => '5+ slaapkamers',
    ];
    $typeLabel = $typeLabels[$room->type ?? ''] ?? null;
    $city      = $room->building?->city;
    $price     = (float) ($room->total_monthly_price ?? $room->price_per_month ?? 0);

    // Schone, unieke meta-description per kot (max ~155 tekens).
    $metaParts = array_filter([
        $typeLabel,
        $city ? "in {$city}" : null,
        $price > 0 ? '€' . number_format($price, 0, ',', '.') . '/maand' : null,
        ($room->surface_m2 ?? null) ? $room->surface_m2 . ' m²' : null,
    ]);
    $metaDescription = trim(implode(' · ', $metaParts));
    $metaDescription = $metaDescription !== ''
        ? $metaDescription . ' — bekijk foto’s, KotScore en plan een bezichtiging op KotKompas.'
        : 'Bekijk dit studentenkot, de KotScore en plan rechtstreeks een bezichtiging op KotKompas.';

    // og:image → eerste echte kotfoto, absoluut gemaakt voor social scrapers.
    $ogImage = $room->getFirstMediaUrl('cover', 'webp')
        ?: $room->getFirstMediaUrl('gallery', 'webp')
        ?: $room->getFirstMediaUrl('cover')
        ?: $room->getFirstMediaUrl('gallery')
        ?: null;
    if ($ogImage && str_starts_with($ogImage, '/')) {
        $ogImage = url($ogImage);
    }

    // JSON-LD: Apartment + Offer
    $apartmentSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type'    => 'Apartment',
        'name'     => $room->title ?? 'Studentenkot',
        'description' => $metaDescription,
        'url'      => url()->current(),
        'image'    => $ogImage ? [$ogImage] : null,
        'numberOfRooms' => $room->type === 'studio' ? 1 : null,
        'floorSize' => ($room->surface_m2 ?? null) ? [
            '@type' => 'QuantitativeValue',
            'value' => (float) $room->surface_m2,
            'unitCode' => 'MTK',
        ] : null,
        'address' => $room->building ? array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => trim(($room->building->street ?? '') . ' ' . ($room->building->house_number ?? '')) ?: null,
            'postalCode' => $room->building->postal_code ?? null,
            'addressLocality' => $room->building->city ?? null,
            'addressCountry' => $room->building->country ?? 'BE',
        ]) : null,
        'geo' => ($room->building?->latitude && $room->building?->longitude) ? [
            '@type' => 'GeoCoordinates',
            'latitude' => (float) $room->building->latitude,
            'longitude' => (float) $room->building->longitude,
        ] : null,
        'offers' => $price > 0 ? [
            '@type' => 'Offer',
            'price' => number_format($price, 2, '.', ''),
            'priceCurrency' => 'EUR',
            'availability' => $room->status === 'available'
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'url' => url()->current(),
        ] : null,
    ], fn ($v) => $v !== null);

    // JSON-LD: BreadcrumbList
    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => array_values(array_filter([
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Koten', 'item' => route('rooms.index')],
            $city ? ['@type' => 'ListItem', 'position' => 3, 'name' => $city, 'item' => route('rooms.index', ['q' => $city])] : null,
            ['@type' => 'ListItem', 'position' => $city ? 4 : 3, 'name' => $room->title ?? 'Kot', 'item' => url()->current()],
        ])),
    ];
@endphp

<x-layout
    :title="($room->title ?? 'Kot') . ' — KotKompas'"
    :description="$metaDescription"
    :og-image="$ogImage"
    body-class="bg-canvas text-ink">

    <x-slot:head>
        <script type="application/ld+json">{!! json_encode($apartmentSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    </x-slot:head>

    <x-public-nav />

    <section class="mx-auto w-full max-w-[88rem] px-5 pb-24 pt-32 sm:px-8">

        <a href="{{ route('rooms.index') }}"
           class="mb-10 inline-flex items-center gap-2 text-sm text-ink/55 transition-colors hover:text-ink">
            <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Terug naar overzicht
        </a>

        {{-- Hoofdcontent: server-side gerenderd en altijd zichtbaar (crawlbaar). --}}
        <div>
            <x-room.header :room="$room" />

            <x-room.gallery :room="$room" />

            <div class="mt-10 md:mt-16">
                <x-room.description :room="$room" />
            </div>
        </div>

        {{-- Verhuurder-kaart is een Livewire-component (<livewire:room.landlord-card>). --}}
        <div class="mt-12 md:mt-16">
            <livewire:room.landlord-card :room-id="$room->id" />
        </div>

        <div class="mt-12 md:mt-16">
            <div class="space-y-12 md:space-y-16">
                <x-room.facilities :room="$room" />
                <x-room.score :room="$room" :breakdown="$scoreBreakdown" />
                <x-room.map :room="$room" />
                <x-room.pricing :room="$room" />
            </div>
        </div>

    </section>

    <x-footer />

</x-layout>
