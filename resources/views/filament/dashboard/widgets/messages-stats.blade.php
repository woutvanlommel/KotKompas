<x-filament-widgets::widget>
    @if ($unread > 0)
        {{-- Ongelezen berichten: sterke behandeling — display count + navy CTA. --}}
        <div class="kk-rise flex flex-col gap-6 rounded-[1.25rem] border border-[#0f17201f] bg-white p-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-5">
                <p class="text-[clamp(2.25rem,3.5vw,3.25rem)] font-medium leading-none tracking-[-0.03em] tabular-nums text-[#0f1720]">{{ $unread }}</p>

                <div>
                    <p class="flex items-center gap-2 text-[0.625rem] font-semibold uppercase tracking-[0.16em] text-[#586573]">
                        Berichten
                        <span class="h-1.5 w-1.5 rounded-full bg-[#ff6700]" aria-hidden="true"></span>
                    </p>
                    <p class="mt-1.5 text-sm text-[#586573]">
                        {{ $unread === 1 ? '1 ongelezen bericht' : $unread . ' ongelezen berichten' }}
                    </p>
                </div>
            </div>

            <a href="{{ $chatUrl }}"
               class="group inline-flex h-11 shrink-0 items-center gap-3 rounded-[4px] bg-[#002f5b] pl-5 pr-1.5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-[#001f3d]">
                Open je chat
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-[3px] bg-[#ff6700]">
                    <svg class="h-4 w-4 transition-transform duration-500 ease-[cubic-bezier(0.65,0,0.35,1)] group-hover:translate-x-0.5 motion-reduce:transition-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </span>
            </a>
        </div>
    @else
        {{-- Zero-state (RULE 5): rustige hairline-rij — geen display-cijfer, geen navy CTA-blok. --}}
        <div class="kk-rise flex items-center justify-between gap-4 rounded-[1.25rem] border border-[#0f17201f] bg-white px-6 py-4">
            <p class="text-sm tracking-[-0.01em] text-[#586573]">Geen ongelezen berichten</p>

            <a href="{{ $chatUrl }}"
               class="group inline-flex shrink-0 items-center gap-1.5 text-sm font-medium tracking-[-0.01em] text-[#586573] transition-colors duration-300 hover:text-[#3a6ea5]">
                Naar berichten
                <svg class="h-3.5 w-3.5 transition-transform duration-500 ease-[cubic-bezier(0.65,0,0.35,1)] group-hover:translate-x-0.5 motion-reduce:transition-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </a>
        </div>
    @endif
</x-filament-widgets::widget>
