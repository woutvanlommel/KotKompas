@props(['room', 'breakdown' => null])

@php
    $criteria = $breakdown ? [
        'Hygiëne' => $breakdown['hygiene'],
        'Grootte' => $breakdown['size'],
        'Prijs-kwaliteit' => $breakdown['value'],
        'Communicatie verhuurder' => $breakdown['communication'],
    ] : [];
@endphp

{{-- Kotscore-sectie: alleen zichtbaar zodra er beoordelingen zijn. Individuele
     beoordelingen worden bewust nooit getoond — reviews zijn anoniem. --}}
@if ($room->score !== null && $room->reviews_count > 0 && $breakdown)
    <section aria-labelledby="kotscore-heading">
        <p class="mb-4 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink/55">
            <span class="inline-block h-px w-9 bg-accent-500" aria-hidden="true"></span> Kotscore
        </p>
        <h2 id="kotscore-heading" class="mb-6 text-xl font-medium tracking-[-0.02em]">Wat zeggen ex-huurders?</h2>

        <div class="flex flex-col gap-8 rounded-2xl border border-hairline bg-canvas-deep p-6 md:flex-row md:items-center md:gap-12 md:p-8">

            {{-- Totaalscore --}}
            <div class="shrink-0 md:w-56">
                <p class="flex items-baseline gap-1.5">
                    <span class="text-[clamp(2.5rem,4vw,3.5rem)] font-medium leading-none tracking-[-0.04em] text-ink">{{ number_format($room->score, 1, ',', '.') }}</span>
                    <span class="text-lg text-ink/45">/5</span>
                </p>
                <p class="mt-3 text-sm leading-relaxed text-ink/60">
                    Op basis van {{ $room->reviews_count }} {{ $room->reviews_count === 1 ? 'beoordeling' : 'beoordelingen' }} door ex-huurders.
                </p>
                <p class="mt-1.5 text-xs leading-relaxed text-ink/45">
                    Recente beoordelingen wegen zwaarder. Beoordelingen zijn anoniem.
                </p>
            </div>

            {{-- Breakdown per criterium --}}
            <div class="min-w-0 flex-1">
                <dl class="space-y-4">
                    @foreach ($criteria as $label => $value)
                        <div>
                            <div class="mb-1.5 flex items-baseline justify-between gap-4">
                                <dt class="text-[0.7rem] font-medium uppercase tracking-[0.1em] text-ink/70">{{ $label }}</dt>
                                <dd class="text-sm font-semibold text-ink">{{ number_format($value, 1, ',', '.') }}</dd>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-ink/10">
                                <div class="h-full rounded-full bg-secondary-600" style="width: {{ min(100, round(($value / 5) * 100)) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </dl>
                <p class="mt-4 text-xs leading-relaxed text-ink/45">
                    De kotscore is het gemiddelde van hygiëne, grootte en prijs-kwaliteit;
                    communicatie telt mee in de score van de verhuurder.
                </p>
            </div>
        </div>
    </section>
@endif
