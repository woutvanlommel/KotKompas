<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Header: titel + slot-teller --}}
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Uitgelichte koten</h3>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Zet je beste koten bovenaan de zoekresultaten</p>
            </div>

            @if ($slotsTotal > 0)
                <x-filament::badge color="featured">
                    {{ $slotsUsed }} / {{ $slotsTotal }} slots
                </x-filament::badge>
            @endif
        </div>

        {{-- Koten gegroepeerd per gebouw, elk met ster-toggle --}}
        <div class="mt-4 space-y-5">
            @forelse ($groups as $buildingName => $rooms)
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        {{ $buildingName }}
                    </h4>

                    <div class="mt-1 divide-y divide-gray-100 dark:divide-white/10">
                        @foreach ($rooms as $room)
                            @php $featured = $room->isFeatured(); @endphp
                            <div class="flex items-center justify-between gap-3 py-2.5">
                                <p class="min-w-0 truncate text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $room->title ?: 'Kamer ' . $room->room_number }}
                                </p>

                                <button
                                    type="button"
                                    wire:click="toggle({{ $room->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="toggle({{ $room->id }})"
                                    @class([
                                        'inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold transition disabled:opacity-50',
                                        'bg-featured-100 text-featured-700 hover:bg-featured-200' => $featured,
                                        'bg-gray-100 text-gray-600 hover:bg-gray-200' => ! $featured,
                                    ])
                                >
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="{{ $featured ? 'currentColor' : 'none' }}"
                                         stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.5l2.6 5.27 5.82.85-4.21 4.1 1 5.79L11.48 17l-5.2 2.5 1-5.79-4.21-4.1 5.82-.85z" />
                                    </svg>
                                    {{ $featured ? 'Uitgelicht' : 'Uitlichten' }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-gray-400 dark:text-gray-500">
                    Je hebt nog geen beschikbare koten om uit te lichten.
                </p>
            @endforelse
        </div>

        {{-- Geen plan/slots -> nudge naar abonnement --}}
        @if ($slotsTotal === 0)
            <div class="mt-4 rounded-lg bg-gray-50 p-4 text-center dark:bg-white/5">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Je hebt nog geen abonnement met uitlicht-slots.
                </p>
                <x-filament::button tag="a" :href="$manageUrl" size="sm" icon="heroicon-m-credit-card" class="mt-3">
                    Kies een abonnement
                </x-filament::button>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
