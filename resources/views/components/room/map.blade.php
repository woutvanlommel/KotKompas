@props(['room'])

<div>
    <p class="mb-4 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink/55">
        <span class="inline-block h-px w-9 bg-accent-500"></span> In de buurt
    </p>
    <h2 class="mb-6 text-xl font-medium tracking-[-0.02em]">Wat ligt er in de buurt?</h2>

    <div class="flex h-56 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-hairline bg-canvas-deep sm:h-64">
        <div class="text-center text-ink/40">
            <x-heroicon-o-map-pin class="mx-auto h-10 w-10" aria-hidden="true" />
            <p class="mt-3 text-sm font-medium">Kaart beschikbaar binnenkort</p>
            <p class="mt-1 text-xs text-ink/35">Nabijgelegen voorzieningen worden hier getoond</p>
        </div>
    </div>
</div>
