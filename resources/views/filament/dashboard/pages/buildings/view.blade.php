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
            'available'   => ['label' => 'Beschikbaar',  'bg' => 'bg-[#e7f6ec]', 'text' => 'text-[#15803d]', 'dot' => 'bg-[#15803d]'],
            'rented'      => ['label' => 'Verhuurd',     'bg' => 'bg-[#eaf1f8]', 'text' => 'text-[#2e5884]', 'dot' => 'bg-[#2e5884]'],
            'maintenance' => ['label' => 'Onderhoud',    'bg' => 'bg-[#fff3e0]', 'text' => 'text-[#c2510a]', 'dot' => 'bg-[#c2510a]'],
            'archived'    => ['label' => 'Gearchiveerd', 'bg' => 'bg-[#e1e6ed]', 'text' => 'text-[#586573]', 'dot' => 'bg-[#586573]'],
        ];
    @endphp

    @php
        $rooms = $record->rooms()->orderBy('room_number')->get();
        $availableCount = $rooms->where('status', 'available')->count();
    @endphp

    <div class="space-y-8">

        {{-- Hero --}}
        <div class="w-full h-72 rounded-2xl overflow-hidden bg-[#e1e6ed] relative shadow-sm">
            <div class="w-full h-full flex flex-col items-center justify-center gap-3 text-[#9aa6b4]">
                <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
                <p class="text-sm font-medium">Geen afbeelding beschikbaar</p>
            </div>

            {{-- Kamers badge --}}
            @if ($rooms->count())
                <div class="absolute top-4 right-4">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-[0.375rem] text-xs font-semibold tabular-nums shadow-sm bg-[#0f1720] text-white">
                        {{ $rooms->count() }} {{ Str::plural('kamer', $rooms->count()) }}
                        @if ($availableCount)
                            &middot; <span class="text-[#caa12a]">{{ $availableCount }} beschikbaar</span>
                        @endif
                    </span>
                </div>
            @endif
        </div>

        {{-- Titel & meta --}}
        <div>
            <h1 class="text-4xl font-medium tracking-[-0.02em] text-[#0f1720]">{{ $record->name }}</h1>
            <p class="mt-1 text-gray-500 text-base">{{ $record->full_address }} &middot; {{ $record->country }}</p>
            @if ($record->description)
                <div class="mt-4 prose prose-sm max-w-none text-gray-600">
                    @richtext($record->description)
                </div>
            @endif
        </div>

        {{-- Locatie kaart --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
            <h2 class="text-sm font-semibold tracking-[-0.01em] text-[#0f1720] mb-5 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#9aa6b4]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
                Locatie
            </h2>
            <dl class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-5">
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Straat</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $record->street }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Huisnummer</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $record->house_number }}</dd>
                </div>
                @if ($record->box)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Bus/Apt</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">{{ $record->box }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Postcode</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $record->postal_code }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Plaats</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $record->city }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Land</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $record->country }}</dd>
                </div>
            </dl>
        </div>

        <!-- Kamers -->

        <div>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-sm font-semibold tracking-[-0.01em] text-[#0f1720]">
                    Kamers
                    @if ($rooms->count())
                        <span class="ml-2 inline-flex items-center justify-center rounded-[0.375rem] px-2 py-0.5 bg-[#e1e6ed] text-[#586573] text-xs font-semibold tabular-nums">
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
                            $coverMedia = $room->getFirstMedia('cover');
                            $imageUrl   = $coverMedia?->getUrl('webp') ?: $coverMedia?->getUrl();
                            $status   = $statusConfig[$room->status] ?? $statusConfig['archived'];
                            $viewUrl  = \App\Filament\Dashboard\Resources\Rooms\RoomResource::getUrl('view', ['record' => $room->id]);
                        @endphp

                        <a href="{{ $viewUrl }}"
                           class="group flex flex-col bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden transition hover:shadow-md hover:border-gray-300">

                            {{-- Afbeelding --}}
                            <div class="relative h-48 bg-[#e1e6ed] overflow-hidden">
                                @if ($imageUrl)
                                    <img src="{{ $imageUrl }}"
                                         alt="{{ $room->title }}"
                                         class="w-full h-full object-cover transition group-hover:scale-105 duration-300">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center gap-2 text-[#9aa6b4]">
                                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                                        </svg>
                                        <span class="text-xs">Geen afbeelding</span>
                                    </div>
                                @endif

                                {{-- Status badge --}}
                                <div class="absolute top-3 right-3">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-[0.375rem] text-xs font-semibold tabular-nums shadow-sm
                                        {{ $status['bg'] }} {{ $status['text'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $status['dot'] }}"></span>
                                        {{ $status['label'] }}
                                    </span>
                                </div>

                                {{-- Kamernummer badge --}}
                                <div class="absolute top-3 left-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-[0.375rem] text-xs font-semibold tabular-nums bg-[#0f1720] text-white shadow-sm">
                                        #{{ $room->room_number }}
                                    </span>
                                </div>

                                {{-- Uitlicht-toggle: één element dat status (goud = uitgelicht)
                                     én de actie draagt. Stop/prevent: de card is zelf een link. --}}
                                @php $featured = $room->isFeatured(); @endphp
                                <button type="button"
                                        wire:click.prevent.stop="mountAction('featureRoom', { room: {{ $room->id }} })"
                                        @class([
                                            'absolute bottom-3 right-3 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold shadow-sm transition',
                                            'bg-featured-100 text-featured-700 hover:bg-featured-200' => $featured,
                                            'bg-white/90 text-gray-700 hover:bg-white' => ! $featured,
                                        ])>
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="{{ $featured ? 'currentColor' : 'none' }}"
                                         stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.5l2.6 5.27 5.82.85-4.21 4.1 1 5.79L11.48 17l-5.2 2.5 1-5.79-4.21-4.1 5.82-.85z" />
                                    </svg>
                                    {{ $featured ? 'Uitgelicht' : 'Uitlichten' }}
                                </button>
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
                                    <span class="text-xl font-medium tracking-[-0.02em] tabular-nums text-[#0f1720]">€ {{ number_format($room->price_per_month, 0, ',', '.') }}</span>
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
                                    @if ($room->costTypes->where('pivot.frequency', 'monthly')->isEmpty())
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
