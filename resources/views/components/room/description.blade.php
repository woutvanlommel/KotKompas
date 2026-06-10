@props(['room'])

<div class="grid gap-10 lg:grid-cols-[1fr_320px] lg:items-start">

    {{-- Beschrijving --}}
    <div>
        <p class="mb-4 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink/55">
            <span class="inline-block h-px w-9 bg-accent-500"></span> Over dit kot
        </p>
        @if ($room->description ?? null)
            <div class="leading-relaxed text-ink/75">
                @richtext($room->description)
            </div>
        @else
            <p class="text-sm italic text-ink/40">Geen beschrijving toegevoegd.</p>
        @endif
    </div>

    {{-- Verhuurder kaartje (geblurd tot betaling) --}}
    <div class="relative rounded-2xl border border-hairline bg-canvas-deep p-6 lg:sticky lg:top-28">

        {{-- Geblurde placeholder --}}
        <div class="select-none space-y-4 blur-sm" aria-hidden="true">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-900 text-lg font-medium text-white">V</div>
                <div>
                    <p class="text-sm font-medium text-ink">Verhuurder naam</p>
                    <p class="text-xs text-ink/55">Lid sinds 2024</p>
                </div>
            </div>
            <div class="space-y-2 border-t border-hairline pt-4 text-sm text-ink/70">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 shrink-0 text-ink/40" viewBox="0 0 16 16" fill="none"><path d="M2 4h12v9H2V4Z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/><path d="M2 4l6 5 6-5" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
                    <span>verhuurder@email.be</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 shrink-0 text-ink/40" viewBox="0 0 16 16" fill="none"><path d="M3 2h3l1.5 4L6 7.5a9 9 0 0 0 2.5 2.5L10 8.5 14 10v3a1 1 0 0 1-1 1A11 11 0 0 1 2 3a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
                    <span>+32 4xx xxx xxx</span>
                </div>
            </div>
            <button disabled class="w-full rounded-xl bg-primary-900 py-3 text-sm font-medium text-white opacity-60">
                Contacteer verhuurder
            </button>
        </div>

        {{-- Lock overlay --}}
        <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 rounded-2xl bg-canvas/70 text-center backdrop-blur-[2px]">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-900/10">
                <svg class="h-5 w-5 text-primary-900" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <rect x="3" y="7" width="10" height="7" rx="1.5" stroke="currentColor" stroke-width="1.3"/>
                    <path d="M5 7V5a3 3 0 1 1 6 0v2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                    <circle cx="8" cy="10.5" r="1" fill="currentColor"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-ink">Verhuurdergegevens</p>
            <p class="max-w-[14rem] text-xs leading-relaxed text-ink/55">Beschikbaar via KotKompas — binnenkort te ontsluiten.</p>
        </div>

    </div>

</div>
