<x-filament-widgets::widget>
    <x-filament::section>
        {{-- DOMINANT — jouw verhuurderscore owns the card. --}}
        <p class="text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">Verhuurderscore</p>

        @if ($landlordScore === '—')
            {{-- Zero-state recedes — no figure, no display-scale dash; reserve the dominant slot for real data. --}}
            <p class="mt-4 text-sm tracking-[-0.01em] text-[#586573]">Nog geen beoordelingen ontvangen</p>
        @else
            <p class="mt-4 flex items-baseline leading-none text-[clamp(3.25rem,5vw,4.5rem)]">
                <span class="font-medium tracking-[-0.03em] tabular-nums text-[#0f1720]">{{ \Illuminate\Support\Str::before($landlordScore, ' /') }}</span>
                <span class="text-[0.34em] font-medium tracking-[-0.01em] tabular-nums text-[#586573]"> / 5</span>
            </p>

            <p class="mt-3 text-sm tracking-[-0.01em] text-[#586573]">{{ $landlordDescription }}</p>
        @endif

        {{-- Hairline — divides the dominant figure from supporting metrics. --}}
        <div class="mt-6 border-t border-[#0f17201f]"></div>

        {{-- Supporting 2-up — recedes to ~1.5rem under micro-caps labels. --}}
        <div class="mt-6 grid grid-cols-2 gap-6">
            <div>
                <p class="text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">Gemiddelde gebouwscore</p>
                <p class="mt-2 text-[1.5rem] font-medium leading-none tracking-[-0.02em] tabular-nums {{ $scoredCount > 0 ? 'text-[#0f1720]' : 'text-[#586573]' }}">{{ $averageScore }}</p>
                <p class="mt-1.5 text-xs tracking-[-0.01em] text-[#586573]">{{ $averageDescription }}</p>
            </div>

            <div>
                <p class="text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">Best scorend gebouw</p>
                <p class="mt-2 text-[1.5rem] font-medium leading-none tracking-[-0.02em] tabular-nums {{ $bestBuildingScore === '—' ? 'text-[#586573]' : 'text-[#0f1720]' }}">{{ $bestBuildingScore }}</p>
                <p class="mt-1.5 truncate text-xs tracking-[-0.01em] text-[#586573]">{{ $bestBuildingName }}</p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
