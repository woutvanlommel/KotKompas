<x-filament-widgets::widget>
    <x-filament::section>
        <div class="grid gap-8 lg:grid-cols-12 lg:gap-12">
            {{-- TOTAAL — de verhuurderscore: het portfolio-totaal (50% kotkwaliteit, 50% communicatie). --}}
            <div class="lg:col-span-5 lg:border-r lg:border-[#0f17201f] lg:pr-12">
                <p class="text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">Verhuurderscore</p>

                @if ($landlordScore === '—')
                    <p class="mt-4 text-sm tracking-[-0.01em] text-[#586573]">Nog geen beoordelingen ontvangen.</p>
                @else
                    <p class="mt-4 flex items-baseline leading-none text-[2rem]">
                        <span class="font-medium tracking-[-0.02em] tabular-nums text-[#0f1720]">{{ \Illuminate\Support\Str::before($landlordScore, ' /') }}</span>
                        <span class="text-[0.6em] font-medium tracking-[-0.01em] tabular-nums text-[#586573]"> / 5</span>
                    </p>
                    <p class="mt-3 max-w-[42ch] text-sm tracking-[-0.01em] text-[#586573]">{{ $landlordDescription }}</p>
                @endif
            </div>

            {{-- OPSPLITSING — exact de criteria waar het totaal uit opbouwt. --}}
            <div class="lg:col-span-7">
                <p class="text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">Per criterium · gemiddeld</p>

                @if (! $hasBreakdown)
                    {{-- Verborgen tot genoeg beoordelingen — anoniem houden. --}}
                    @php $maskedPct = min(100, max(0, $minReviews > 0 ? $reviewsCount / $minReviews * 100 : 0)); @endphp
                    @if ($reviewsCount === 0)
                        <p class="mt-6 text-sm tracking-[-0.01em] text-[#586573]">
                            Nog <span class="tabular-nums">{{ $minReviews }}</span> beoordelingen nodig voor een anonieme opsplitsing per criterium.
                        </p>
                    @else
                        <div class="mt-6 flex items-baseline gap-2">
                            <span class="text-[clamp(1.75rem,2.4vw,2.5rem)] font-medium leading-none tracking-[-0.03em] tabular-nums text-[#0f1720]">{{ $reviewsCount }}</span>
                            <span class="text-[1.25rem] font-medium leading-none tracking-[-0.02em] tabular-nums text-[#586573]">/ {{ $minReviews }}</span>
                            <span class="text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">beoordelingen</span>
                        </div>
                    @endif
                    <div class="mt-3 h-1.5 w-full max-w-md overflow-hidden rounded-[2px] bg-[#e1e6ed]">
                        <div class="h-full rounded-[2px] bg-[#0f1720] transition-[width] duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none" style="width: {{ $maskedPct }}%"></div>
                    </div>
                    @if ($reviewsCount > 0)
                        <p class="mt-3 text-sm tracking-[-0.01em] text-[#586573]">Nog <span class="tabular-nums">{{ max(0, $minReviews - $reviewsCount) }}</span> te gaan.</p>
                    @endif
                @else
                    {{-- De vier criteria; een zwakke plek (< 3,5) warmt op naar #c2510a. --}}
                    <div class="mt-6 grid gap-x-10 gap-y-5 sm:grid-cols-2">
                        @foreach ($criteria as $criterion)
                            @php
                                $score = $criterion['score'];
                                $pct = min(100, max(0, $score / 5 * 100));
                                $isWeak = $score < 3.5;
                                $fill = $isWeak ? '#c2510a' : '#0f1720';
                                $numeralColor = $isWeak ? '#c2510a' : '#0f1720';
                            @endphp
                            <div>
                                <div class="flex items-baseline justify-between gap-3">
                                    <p class="text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">{{ $criterion['label'] }}</p>
                                    <span class="shrink-0">
                                        <span class="text-[1.25rem] font-medium leading-none tracking-[-0.02em] tabular-nums" style="color: {{ $numeralColor }};">{{ number_format($score, 1, ',', '.') }}</span><span class="text-sm text-[#586573]"> /5</span>
                                    </span>
                                </div>
                                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-[2px] bg-[#e1e6ed]">
                                    <div class="h-full rounded-[2px] transition-[width] duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] motion-reduce:transition-none" style="width: {{ $pct }}%; background: {{ $fill }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
