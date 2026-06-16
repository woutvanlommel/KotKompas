<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Header: uitgelicht as a paid moment — gold display count + fill bar --}}
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">Uitgelicht · betalend</p>
                <h3 class="mt-1.5 text-[clamp(1.25rem,1.6vw,1.6rem)] font-medium leading-none tracking-[-0.02em] text-[#0f1720]">Uitgelichte koten</h3>
            </div>

            @if ($slotsTotal > 0)
                <div class="shrink-0 text-right">
                    <p class="text-[clamp(1.75rem,2.4vw,2.5rem)] font-medium leading-none tracking-[-0.02em] tabular-nums text-[#7b6118]">{{ $slotsUsed }}<span class="text-[#caa12a]">/{{ $slotsTotal }}</span></p>
                    <p class="mt-1 text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">Slots in gebruik</p>
                </div>
            @endif
        </div>

        @if ($slotsTotal > 0)
            <div class="mt-3.5 h-1.5 w-full overflow-hidden rounded-full bg-[#e1e6ed]">
                <div class="h-full rounded-full bg-[#caa12a] transition-[width] duration-700 ease-[cubic-bezier(0.22,1,0.36,1)]"
                     style="width: {{ min(100, (int) round($slotsUsed / max(1, $slotsTotal) * 100)) }}%"></div>
            </div>
        @endif

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
