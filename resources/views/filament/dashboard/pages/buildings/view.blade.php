<x-filament-panels::page>
    @php
        $record = $this->record;

        $typeLabels = [
            'studio'            => 'Studio',
            'one_bedroom'       => '1 slaapkamer',
            'two_bedroom'       => '2 slaapkamers',
            'three_bedroom'     => '3 slaapkamers',
            'four_bedroom'      => '4 slaapkamers',
            'five_plus_bedroom' => '5+ slaapkamers',
        ];

        $statusConfig = [
            'available'   => ['label' => 'Beschikbaar',  'bg' => 'bg-green-50',  'text' => 'text-green-700',  'dot' => 'bg-green-500'],
            'rented'      => ['label' => 'Verhuurd',     'bg' => 'bg-blue-50',   'text' => 'text-blue-700',   'dot' => 'bg-blue-500'],
            'maintenance' => ['label' => 'Onderhoud',    'bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'dot' => 'bg-yellow-500'],
            'archived'    => ['label' => 'Gearchiveerd', 'bg' => 'bg-gray-100',  'text' => 'text-gray-600',   'dot' => 'bg-gray-400'],
        ];
    @endphp

    <div class="space-y-8">
        <!-- Header -->
        <div>
            <h1 class="text-5xl font-bold text-gray-900">{{ $record->name }}</h1>
            @if ($record->description)
                <div class="mt-4 prose prose-sm max-w-none text-gray-600">
                    {!! $record->description !!}
                </div>
            @endif
        </div>

        <!-- Locatie Kaart -->
        <div class="grid gap-6">
            <div class="bg-white rounded-lg border border-gray-200 p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Locatie</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Straat</p>
                        <p class="mt-2 text-lg text-gray-900">{{ $record->street }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Huisnummer</p>
                        <p class="mt-2 text-lg text-gray-900">{{ $record->house_number }}</p>
                    </div>
                    @if ($record->box)
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Bus/Apt</p>
                            <p class="mt-2 text-lg text-gray-900">{{ $record->box }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Postcode</p>
                        <p class="mt-2 text-lg text-gray-900">{{ $record->postal_code }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Plaats</p>
                        <p class="mt-2 text-lg text-gray-900">{{ $record->city }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Land</p>
                        <p class="mt-2 text-lg text-gray-900">{{ $record->country }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kamers -->
        @php $rooms = $record->rooms()->orderBy('room_number')->get(); @endphp

        <div>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">
                    Kamers
                    @if ($rooms->count())
                        <span class="ml-2 inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-gray-600 text-xs font-semibold">
                            {{ $rooms->count() }}
                        </span>
                    @endif
                </h2>
            </div>

            @if ($rooms->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl border border-dashed border-gray-200 text-center">
                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                    </svg>
                    <p class="text-sm font-medium text-gray-500">Nog geen kamers</p>
                    <p class="mt-1 text-xs text-gray-400">Klik op "Kamer toevoegen" om te beginnen.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($rooms as $room)
                        @php
                            $imageUrl = $room->getFirstMediaUrl('rooms');
                            $status   = $statusConfig[$room->status] ?? $statusConfig['archived'];
                            $viewUrl  = \App\Filament\Dashboard\Resources\Rooms\RoomResource::getUrl('view', ['record' => $room->id]);
                        @endphp

                        <a href="{{ $viewUrl }}"
                           class="group flex flex-col bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden transition hover:shadow-md hover:border-gray-300">

                            {{-- Afbeelding --}}
                            <div class="relative h-48 bg-gray-100 overflow-hidden">
                                @if ($imageUrl)
                                    <img src="{{ $imageUrl }}"
                                         alt="{{ $room->title }}"
                                         class="w-full h-full object-cover transition group-hover:scale-105 duration-300">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center gap-2 text-gray-300">
                                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                                        </svg>
                                        <span class="text-xs">Geen afbeelding</span>
                                    </div>
                                @endif

                                {{-- Status badge --}}
                                <div class="absolute top-3 right-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold shadow-sm
                                        {{ $status['bg'] }} {{ $status['text'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $status['dot'] }}"></span>
                                        {{ $status['label'] }}
                                    </span>
                                </div>

                                {{-- Kamernummer badge --}}
                                <div class="absolute top-3 left-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-white/90 text-gray-700 shadow-sm">
                                        #{{ $room->room_number }}
                                    </span>
                                </div>
                            </div>

                            {{-- Kaart body --}}
                            <div class="flex flex-col flex-1 p-5 gap-3">

                                {{-- Titel & type --}}
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 leading-snug group-hover:text-primary-600 transition truncate">
                                        {{ $room->title ?: 'Kamer ' . $room->room_number }}
                                    </h3>
                                    <p class="mt-0.5 text-xs text-gray-500">
                                        {{ $typeLabels[$room->type] ?? $room->type }}
                                    </p>
                                </div>

                                {{-- Prijs --}}
                                <div class="flex items-baseline gap-1">
                                    <span class="text-xl font-bold text-gray-900">€ {{ number_format($room->price_per_month, 0, ',', '.') }}</span>
                                    <span class="text-xs text-gray-400">/ maand</span>
                                </div>

                                {{-- Meta chips --}}
                                <div class="flex flex-wrap gap-2 mt-auto pt-2 border-t border-gray-100">
                                    @if ($room->surface_m2)
                                        <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                            </svg>
                                            {{ $room->surface_m2 }} m²
                                        </span>
                                    @endif
                                    @if ($room->is_furnished)
                                        <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                            </svg>
                                            Gemeubileerd
                                        </span>
                                    @endif
                                    @if ($room->costs_included)
                                        <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                            Kosten inbegrepen
                                        </span>
                                    @endif
                                    @if ($room->available_from)
                                        <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                                            </svg>
                                            Vrij {{ $room->available_from->format('d/m/Y') }}
                                        </span>
                                    @endif
                                </div>

                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-filament-panels::page>
