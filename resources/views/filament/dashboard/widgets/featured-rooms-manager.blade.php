<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Section marker --}}
        <div class="-mx-6 -mt-6 mb-6 border-b border-[#0f17201f] px-6 py-5">
            <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.16em] text-[#586573]">002 / Abonnement &amp; uitgelicht</p>
        </div>

        @if ($isSubscribed)
            {{-- Subscription line — compact, no second hero numeral (the masthead owns that) --}}
            <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-3">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span class="text-base font-medium tracking-[-0.01em] text-[#0f1720]">{{ $planLabel }}</span>
                    <span class="inline-flex items-center gap-1.5 rounded-md bg-[#e7f6ec] px-2 py-0.5 text-[0.625rem] font-semibold uppercase tracking-[0.1em] text-[#15803d]">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#15803d]"></span>
                        Actief
                    </span>
                    @if ($renewsAt)
                        <span class="text-xs tracking-[-0.01em] text-[#586573]">verlengt {{ $renewsAt->format('d/m/Y') }}</span>
                    @endif
                </div>

                <a href="{{ $manageUrl }}"
                   class="group inline-flex items-center gap-1.5 text-xs font-medium uppercase tracking-[0.04em] text-[#0f1720] transition-colors duration-300 hover:text-[#586573]">
                    Beheer abonnement
                    <svg class="h-3.5 w-3.5 transition-transform duration-500 ease-[cubic-bezier(0.65,0,0.35,1)] group-hover:translate-x-0.5 motion-reduce:transition-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </a>
            </div>

            {{-- Slot rail — horizontal meter, label + tank + count on one line (not a hero figure) --}}
            @if ($slotsTotal > 0)
                <div class="mt-6 flex items-center gap-4"
                     x-data="{ shown: false }" x-init="$nextTick(() => shown = true)">
                    <p class="shrink-0 text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">Uitgelicht-slots</p>
                    <div class="flex flex-1 items-center gap-1.5" role="presentation">
                        @for ($i = 0; $i < $slotsTotal; $i++)
                            <span class="h-1.5 flex-1 overflow-hidden rounded-full bg-[#e1e6ed]">
                                <span
                                    @class([
                                        'block h-full origin-left rounded-full transition-transform duration-[600ms] ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none',
                                        'bg-[#caa12a]' => $i < $slotsUsed,
                                        'bg-transparent' => $i >= $slotsUsed,
                                    ])
                                    :class="shown ? 'scale-x-100' : 'scale-x-0'"
                                    style="transition-delay: {{ $i * 90 }}ms"></span>
                            </span>
                        @endfor
                    </div>
                    <p class="shrink-0 text-sm tabular-nums">
                        <span class="font-medium text-[#0f1720]">{{ $slotsUsed }}</span><span class="text-[#9aa6b4]">/{{ $slotsTotal }}</span>
                    </p>
                </div>
                <p class="mt-2 text-xs tracking-[-0.01em] text-[#586573]">
                    {{ $remainingSlots > 0
                        ? $remainingSlots . ' ' . ($remainingSlots === 1 ? 'slot vrij' : 'slots vrij') . ' om koten uit te lichten'
                        : 'Alle slots in gebruik — upgrade voor meer zichtbaarheid' }}
                </p>
            @endif
        @else
            {{-- Geen plan: compacte prompt --}}
            <div class="flex flex-wrap items-center justify-between gap-4">
                <p class="text-sm tracking-[-0.01em] text-[#586573]">
                    <span class="font-medium text-[#0f1720]">Nog geen abonnement.</span> Kies een plan om koten uit te lichten.
                </p>
                <a href="{{ $manageUrl }}"
                   class="group inline-flex h-10 shrink-0 items-center gap-3 rounded-[4px] bg-[#0f1720] pl-4 pr-1.5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-[#00101e]">
                    Kies een plan
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-[3px] bg-[#ff6700]">
                        <svg class="h-4 w-4 transition-transform duration-500 ease-[cubic-bezier(0.65,0,0.35,1)] group-hover:translate-x-0.5 motion-reduce:transition-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </span>
                </a>
            </div>
        @endif

        {{-- Jouw koten — numbered editorial index (the card's real job: manage what's featured) --}}
        @if ($slotsTotal > 0)
            @php $idx = 0; @endphp
            <div class="mt-7 border-t border-[#0f17201f] pt-6">
                <p class="mb-4 text-[0.625rem] font-semibold uppercase tracking-[0.16em] text-[#586573]">Jouw koten</p>
                <div class="chat-messages max-h-[24rem] space-y-5 overflow-y-auto pr-1">
                    @forelse ($groups as $buildingName => $rooms)
                        <div>
                            <h4 class="mb-1 text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#9aa6b4]">
                                {{ $buildingName }}
                            </h4>

                            <div class="divide-y divide-[#0f17201f]">
                                @foreach ($rooms as $room)
                                    @php $featured = $room->isFeatured(); $idx++; @endphp
                                    <div class="group/row flex items-center gap-4 py-3">
                                        <span class="w-6 shrink-0 text-xs font-medium tabular-nums text-[#9aa6b4]">{{ str_pad((string) $idx, 2, '0', STR_PAD_LEFT) }}</span>

                                        <p class="min-w-0 flex-1 truncate text-sm font-medium tracking-[-0.01em] text-[#0f1720]">
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
                                            <svg class="h-3.5 w-3.5 transition-transform duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover/row:rotate-[14deg] motion-reduce:transition-none" viewBox="0 0 24 24" fill="{{ $featured ? 'currentColor' : 'none' }}"
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
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
