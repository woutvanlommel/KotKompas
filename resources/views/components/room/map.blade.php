@props(['room'])

<div>
    <p class="mb-4 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink/55">
        <span class="inline-block h-px w-9 bg-accent-500"></span> In de buurt
    </p>
    <h2 class="mb-6 text-xl font-medium tracking-[-0.02em]">Wat ligt er in de buurt?</h2>

    <div class="flex h-64 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-hairline bg-canvas-deep">
        <div class="text-center text-ink/40">
            <svg class="mx-auto h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7Z" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="9" r="2.5"/>
            </svg>
            <p class="mt-3 text-sm font-medium">Kaart beschikbaar binnenkort</p>
            <p class="mt-1 text-xs text-ink/35">Nabijgelegen voorzieningen worden hier getoond</p>
        </div>
    </div>
</div>
