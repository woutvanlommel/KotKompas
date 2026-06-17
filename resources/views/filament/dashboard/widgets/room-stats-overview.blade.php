<x-filament-widgets::widget>
    <x-filament::section>
        <p class="text-[0.625rem] font-semibold uppercase tracking-[0.16em] text-[#586573]">Koten verhuurd</p>

        @if ($total === 0)
            {{-- Calm empty state — no portfolio yet, nothing to count. --}}
            <p class="mt-4 text-sm tracking-[-0.01em] text-[#586573]">Nog geen koten toegevoegd.</p>
        @else
            {{-- DOMINANT — verhuurd telt, met de portefeuille als gedempte noemer. --}}
            <p class="mt-4 flex items-baseline leading-none text-[clamp(3.25rem,5vw,4.5rem)]">
                <span class="font-medium tracking-[-0.03em] tabular-nums text-[#0f1720]">{{ $rented }}</span>
                <span class="text-[0.34em] font-medium tracking-[-0.01em] tabular-nums text-[#586573]"> / {{ $total }}</span>
            </p>

            {{-- Hairline — divides the dominant figure from supporting metrics. --}}
            <div class="mt-6 border-t border-[#0f17201f]"></div>

            <div class="mt-6 grid grid-cols-2 gap-6">
                <div>
                    <p class="text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">Beschikbaar</p>
                    <p class="mt-2 text-[1.5rem] font-medium leading-none tracking-[-0.02em] tabular-nums {{ $available > 0 ? 'text-[#0f1720]' : 'text-[#586573]' }}">{{ $available }}</p>
                    <p class="mt-1.5 text-xs tracking-[-0.01em] text-[#586573]">{{ $available === 1 ? 'kot beschikbaar' : 'koten beschikbaar' }}</p>
                </div>

                <div>
                    <p class="text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">Gem. basishuur</p>
                    <p class="mt-2 text-[1.5rem] font-medium leading-none tracking-[-0.02em] tabular-nums {{ $averagePrice !== null ? 'text-[#0f1720]' : 'text-[#586573]' }}">{{ $averagePrice ?? '—' }}</p>
                    <p class="mt-1.5 text-xs tracking-[-0.01em] text-[#586573]">Per maand, excl. vaste kosten</p>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
