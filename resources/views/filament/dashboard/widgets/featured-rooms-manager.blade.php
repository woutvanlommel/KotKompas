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
                <div class="h-full rounded-full bg-[#caa12a] transition-[width] duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none"
                     style="width: {{ min(100, (int) round($slotsUsed / max(1, $slotsTotal) * 100)) }}%"></div>
            </div>
        @endif

        {{-- Koten gegroepeerd per gebouw, elk met ster-toggle --}}
        <div class="mt-6 space-y-6">
            @forelse ($groups as $buildingName => $rooms)
                <div>
                    <h4 class="text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">
                        {{ $buildingName }}
                    </h4>

                    <div class="mt-1.5 divide-y divide-[#0f17201f]">
                        @foreach ($rooms as $room)
                            @php $featured = $room->isFeatured(); @endphp
                            <div class="flex items-center justify-between gap-3 py-3">
                                <p class="min-w-0 truncate text-sm font-medium tracking-[-0.01em] text-[#0f1720]">
                                    {{ $room->title ?: 'Kamer ' . $room->room_number }}
                                </p>

                                <button
                                    type="button"
                                    wire:click="toggle({{ $room->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="toggle({{ $room->id }})"
                                    @class([
                                        'inline-flex shrink-0 items-center gap-1.5 rounded-md px-2 py-0.5 text-xs font-medium tabular-nums transition duration-180 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none disabled:opacity-50',
                                        'bg-[#f9f0d6] text-[#7b6118] hover:bg-[#f0dca3]' => $featured,
                                        'bg-[#e1e6ed] text-[#586573] hover:bg-[#d3dae3]' => ! $featured,
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
                <p class="py-6 text-center text-sm tracking-[-0.01em] text-[#586573]">
                    Je hebt nog geen beschikbare koten om uit te lichten.
                </p>
            @endforelse
        </div>

        {{-- Geen plan/slots -> nudge naar abonnement --}}
        @if ($slotsTotal === 0)
            <div class="mt-6 flex flex-col gap-4 rounded-[1.25rem] border border-[#0f17201f] bg-[#e1e6ed] p-5 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm tracking-[-0.01em] text-[#586573]">
                    Je hebt nog geen abonnement met uitlicht-slots.
                </p>
                <a href="{{ $manageUrl }}"
                   class="group inline-flex h-11 shrink-0 items-center gap-3 rounded-[4px] bg-[#00101e] pl-5 pr-1.5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-[#001f3d]">
                    Kies een abonnement
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-[3px] bg-[#ff6700]">
                        <svg class="h-4 w-4 transition-transform duration-500 ease-[cubic-bezier(0.65,0,0.35,1)] group-hover:translate-x-0.5 motion-reduce:transition-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </span>
                </a>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
