<x-filament-widgets::widget>
    <x-filament::section>
        <p class="text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">Score per criterium · gemiddeld</p>

        @if (! $hasBreakdown)
            {{-- Calm masked state — keeps the survey anonymous until enough reviews land. --}}
            <div class="mt-6 flex items-baseline gap-3">
                <p class="text-[clamp(1.75rem,2.4vw,2.5rem)] font-medium leading-none tracking-[-0.03em] tabular-nums text-[#9aa6b4]">—</p>
                <p class="text-sm tracking-[-0.01em] text-[#586573]">
                    Zichtbaar vanaf {{ $minReviews }} beoordelingen.
                </p>
            </div>
            <p class="mt-2 text-sm tracking-[-0.01em] text-[#9aa6b4]">
                Nog <span class="tabular-nums">{{ max(0, $minReviews - $reviewsCount) }}</span> te gaan voor een anonieme opsplitsing per criterium.
            </p>
        @else
            {{-- One score bar per criterion — same idiom as the status bar, read top to bottom. --}}
            <div class="mt-6 space-y-6">
                @foreach ($criteria as $criterion)
                    @php $pct = min(100, max(0, $criterion['score'] / 5 * 100)); @endphp
                    <div>
                        <div class="flex items-baseline justify-between gap-3">
                            <p class="text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">{{ $criterion['label'] }}</p>
                            <span class="shrink-0">
                                <span class="text-[clamp(1.5rem,2vw,2rem)] font-medium leading-none tracking-[-0.02em] tabular-nums text-[#0f1720]">{{ number_format($criterion['score'], 1, ',', '.') }}</span><span class="text-sm text-[#9aa6b4]"> /5</span>
                            </span>
                        </div>
                        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-[#e1e6ed]">
                            <div class="h-full rounded-full bg-[#3a6ea5] transition-[width] duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
