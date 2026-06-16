<x-filament-widgets::widget>
    @if ($isSubscribed)
        {{-- Active: invert to navy — the "you're in" moment. --}}
        <div class="kk-rise relative overflow-hidden rounded-[1.25rem] bg-[#00101e] p-6 text-white">
            <p class="text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-white/45">Abonnement</p>
            <p class="mt-2 text-[clamp(1.75rem,2.4vw,2.4rem)] font-medium leading-[0.95] tracking-[-0.02em]">{{ $planLabel }}</p>

            @if ($renewsAt)
                <div class="mt-6 flex items-center justify-between border-t border-white/15 pt-4 text-sm">
                    <span class="text-white/55">Verlengt op</span>
                    <span class="font-medium tabular-nums">{{ $renewsAt->format('d/m/Y') }}</span>
                </div>
            @endif

            <a href="{{ $manageUrl }}"
               class="group mt-6 inline-flex h-11 items-center gap-3 rounded-[4px] border border-white/20 pl-5 pr-1.5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-white/[0.06]">
                Beheer abonnement
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-[3px] bg-[#ff6700]">
                    <svg class="h-4 w-4 transition-transform duration-500 ease-[cubic-bezier(0.65,0,0.35,1)] group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </span>
            </a>
        </div>
    @else
        {{-- Inactive: dim, dashed — clearly "not on yet". --}}
        <div class="kk-rise rounded-[1.25rem] border border-dashed border-[#0f172033] bg-white/40 p-6">
            <p class="text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">Abonnement</p>
            <p class="mt-2 text-[clamp(1.5rem,2vw,2rem)] font-medium leading-none tracking-[-0.02em] text-[#9aa6b4]">Geen actief plan</p>
            <p class="mt-3 max-w-sm text-sm text-[#586573]">Kies een plan om koten uit te lichten en bovenaan de zoekresultaten te verschijnen.</p>

            <a href="{{ $manageUrl }}"
               class="group mt-6 inline-flex h-11 items-center gap-3 rounded-[4px] bg-[#00101e] pl-5 pr-1.5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-[#001f3d]">
                Kies een abonnement
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-[3px] bg-[#ff6700]">
                    <svg class="h-4 w-4 transition-transform duration-500 ease-[cubic-bezier(0.65,0,0.35,1)] group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </span>
            </a>
        </div>
    @endif
</x-filament-widgets::widget>
