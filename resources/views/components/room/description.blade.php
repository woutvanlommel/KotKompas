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
                    <x-heroicon-o-envelope class="h-4 w-4 shrink-0 text-ink/40" aria-hidden="true" />
                    <span>verhuurder@email.be</span>
                </div>
                <div class="flex items-center gap-2">
                    <x-heroicon-o-phone class="h-4 w-4 shrink-0 text-ink/40" aria-hidden="true" />
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
                <x-heroicon-o-lock-closed class="h-5 w-5 text-primary-900" aria-hidden="true" />
            </div>
            <p class="text-sm font-medium text-ink">Verhuurdergegevens</p>
            <p class="max-w-[14rem] text-xs leading-relaxed text-ink/55">Beschikbaar via KotKompas — binnenkort te ontsluiten.</p>
        </div>

    </div>

</div>
