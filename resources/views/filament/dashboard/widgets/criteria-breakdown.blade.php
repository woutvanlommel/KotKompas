<x-filament-widgets::widget>
    <x-filament::section>
        <p class="text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">Score per criterium · gemiddeld</p>

        @if (! $hasBreakdown)
            {{-- Masked state — the survey stays anonymous until enough reviews land. --}}
            @php $maskedPct = min(100, max(0, $minReviews > 0 ? $reviewsCount / $minReviews * 100 : 0)); @endphp
            @if ($reviewsCount === 0)
                {{-- Zero-state recedes — no display numeral, just hairline guidance. The 0% bar
                     stays as a visual affordance, not a focal figure. --}}
                <p class="mt-6 text-sm tracking-[-0.01em] text-[#586573]">
                    Nog <span class="tabular-nums">{{ $minReviews }}</span> beoordelingen nodig voor een anonieme opsplitsing per criterium.
                </p>
            @else
                {{-- Progress toward the threshold becomes the focal figure instead of a bare dash. --}}
                <div class="mt-6 flex items-baseline gap-2">
                    <span class="text-[clamp(2rem,3vw,3rem)] font-medium leading-none tracking-[-0.03em] tabular-nums text-[#0f1720]">{{ $reviewsCount }}</span>
                    <span class="text-[clamp(1.25rem,1.6vw,1.5rem)] font-medium leading-none tracking-[-0.02em] tabular-nums text-[#586573]">/ {{ $minReviews }}</span>
                    <span class="text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">beoordelingen</span>
                </div>
            @endif
            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-[2px] bg-[#e1e6ed]">
                <div class="h-full rounded-[2px] bg-[#3a6ea5] transition-[width] duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none"
                     style="width: {{ $maskedPct }}%"></div>
            </div>
            @if ($reviewsCount > 0)
                <p class="mt-3 text-sm tracking-[-0.01em] text-[#586573]">
                    Nog <span class="tabular-nums">{{ max(0, $minReviews - $reviewsCount) }}</span> te gaan voor een anonieme opsplitsing per criterium.
                </p>
            @endif
        @else
            {{-- One row per criterion, all four comparable. The threshold-aware fill (warm #c2510a
                 below 3.5) plus a score-proportional numeral makes the weak spot jump out — this
                 widget exists to surface the weak spot, so no single criterion is promoted. --}}
            <div class="mt-6 space-y-6">
                @foreach ($criteria as $criterion)
                    @php
                        $score = $criterion['score'];
                        $pct = min(100, max(0, $score / 5 * 100));
                        $isWeak = $score < 3.5;
                        $fill = $isWeak ? '#c2510a' : '#3a6ea5';
                        // Numeral scales with score so magnitude reads: ~1.5rem at 1.0 → ~2.25rem at 5.0.
                        $ratio = min(1, max(0, ($score - 1) / 4));
                        $numeralRem = round(1.5 + $ratio * 0.75, 3);
                        $numeralColor = $isWeak ? '#c2510a' : '#0f1720';
                    @endphp
                    <div>
                        <div class="flex items-baseline justify-between gap-3">
                            <p class="text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">{{ $criterion['label'] }}</p>
                            <span class="shrink-0">
                                <span class="font-medium leading-none tracking-[-0.02em] tabular-nums" style="font-size: {{ $numeralRem }}rem; color: {{ $numeralColor }};">{{ number_format($score, 1, ',', '.') }}</span><span class="text-sm text-[#586573]"> /5</span>
                            </span>
                        </div>
                        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-[2px] bg-[#e1e6ed]">
                            <div class="h-full rounded-[2px] transition-[width] duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none"
                                 style="width: {{ $pct }}%; background: {{ $fill }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
