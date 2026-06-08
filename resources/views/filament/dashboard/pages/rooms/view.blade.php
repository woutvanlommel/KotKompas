<x-filament-panels::page>
    @php
        $room = $this->record;

        $typeLabels = [
            'studio'            => 'Studio',
            'one_bedroom'       => '1 slaapkamer',
            'two_bedroom'       => '2 slaapkamers',
            'three_bedroom'     => '3 slaapkamers',
            'four_bedroom'      => '4 slaapkamers',
            'five_plus_bedroom' => '5+ slaapkamers',
        ];

        $statusConfig = [
            'available'   => ['label' => 'Beschikbaar',  'bg' => 'bg-green-50',   'text' => 'text-green-700',  'dot' => 'bg-green-500'],
            'rented'      => ['label' => 'Verhuurd',     'bg' => 'bg-blue-50',    'text' => 'text-blue-700',   'dot' => 'bg-blue-500'],
            'maintenance' => ['label' => 'Onderhoud',    'bg' => 'bg-yellow-50',  'text' => 'text-yellow-700', 'dot' => 'bg-yellow-500'],
            'archived'    => ['label' => 'Gearchiveerd', 'bg' => 'bg-gray-100',   'text' => 'text-gray-600',   'dot' => 'bg-gray-400'],
        ];

        $status = $statusConfig[$room->status] ?? $statusConfig['archived'];
        $imageUrl = $room->getFirstMediaUrl('rooms');
    @endphp

    <div class="space-y-8">

        {{-- Hero afbeelding --}}
        <div class="w-full h-72 rounded-2xl overflow-hidden bg-gray-100 relative shadow-sm">
            @if ($imageUrl)
                <img src="{{ $imageUrl }}"
                     alt="{{ $room->title }}"
                     class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex flex-col items-center justify-center gap-3 text-gray-400">
                    <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <p class="text-sm font-medium">Geen afbeelding beschikbaar</p>
                </div>
            @endif

            {{-- Status badge over afbeelding --}}
            <div class="absolute top-4 right-4">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold shadow-sm
                    {{ $status['bg'] }} {{ $status['text'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $status['dot'] }}"></span>
                    {{ $status['label'] }}
                </span>
            </div>
        </div>

        {{-- Titel & meta --}}
        <div class="flex flex-col gap-2">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900">
                        {{ $room->title ?: 'Kamer ' . $room->room_number }}
                    </h1>
                    <p class="mt-1 text-gray-500 text-base">
                        Kamer {{ $room->room_number }} &middot; {{ $typeLabels[$room->type] ?? $room->type }}
                    </p>
                    <p class="mt-0.5 text-gray-400 text-sm">
                        {{ $room->full_address }}
                    </p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-3xl font-bold text-gray-900">€ {{ number_format($room->price_per_month, 2, ',', '.') }}</p>
                    <p class="text-sm text-gray-500">per maand</p>
                </div>
            </div>
        </div>

        {{-- Details grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Kenmerken --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-5">Kenmerken</h2>
                <dl class="space-y-4">
                    @if ($room->surface_m2)
                        <div class="flex items-center justify-between">
                            <dt class="flex items-center gap-2 text-sm text-gray-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                </svg>
                                Oppervlakte
                            </dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $room->surface_m2 }} m²</dd>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <dt class="flex items-center gap-2 text-sm text-gray-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                            Gemeubileerd
                        </dt>
                        <dd class="text-sm font-medium text-gray-900">
                            @if ($room->is_furnished)
                                <span class="inline-flex items-center gap-1 text-green-700">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    Ja
                                </span>
                            @else
                                <span class="text-gray-400">Nee</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="flex items-center gap-2 text-sm text-gray-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                            </svg>
                            Kosten inbegrepen
                        </dt>
                        <dd class="text-sm font-medium text-gray-900">
                            @if ($room->costs_included)
                                <span class="inline-flex items-center gap-1 text-green-700">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    Ja
                                </span>
                            @else
                                <span class="text-gray-400">Nee</span>
                            @endif
                        </dd>
                    </div>
                    @if ($room->available_from)
                        <div class="flex items-center justify-between">
                            <dt class="flex items-center gap-2 text-sm text-gray-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                                </svg>
                                Beschikbaar vanaf
                            </dt>
                            <dd class="text-sm font-medium text-gray-900">
                                {{ $room->available_from->format('d/m/Y') }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Prijs overzicht --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-5">Prijs</h2>
                <dl class="space-y-4">
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-500">Huurprijs</dt>
                        <dd class="text-sm font-medium text-gray-900">€ {{ number_format($room->price_per_month, 2, ',', '.') }} / maand</dd>
                    </div>
                    @if ($room->costs_included)
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-gray-500">Kosten</dt>
                            <dd class="text-sm font-medium text-green-700">Inbegrepen</dd>
                        </div>
                    @endif
                    <div class="border-t border-gray-100 pt-4 flex items-center justify-between">
                        <dt class="text-sm font-semibold text-gray-700">Totaal</dt>
                        <dd class="text-base font-bold text-gray-900">€ {{ number_format($room->total_price, 2, ',', '.') }} / maand</dd>
                    </div>
                </dl>
            </div>

        </div>

        {{-- Huurder --}}
        @if ($room->status === 'rented')
            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6">
                <h2 class="text-base font-semibold text-blue-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    Verhuurd aan
                </h2>
                @if ($room->tenant)
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-blue-200 flex items-center justify-center shrink-0">
                            <span class="text-sm font-semibold text-blue-700">
                                {{ strtoupper(substr($room->tenant->name, 0, 1)) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-blue-900">{{ $room->tenant->name }}</p>
                            <p class="text-xs text-blue-600">{{ $room->tenant->email }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-blue-600 italic">Geen huurder gekoppeld.</p>
                @endif
            </div>
        @endif

        {{-- Beschrijving --}}
        @if ($room->description)
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Beschrijving</h2>
                <div class="prose prose-sm max-w-none text-gray-600">
                    {!! $room->description !!}
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
