@props(['room'])

{{-- Beschrijving. De verhuurder-/contactkaart staat in <x-room.landlord> (net onder dit blok). --}}
<div>
    <p class="mb-4 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink/55">
        <span class="inline-block h-px w-9 bg-accent-500"></span> Over dit kot
    </p>
    @if ($room->description ?? null)
        <div class="kk-richtext">
            @richtext($room->description)
        </div>
    @else
        <p class="text-sm italic text-ink/40">Geen beschrijving toegevoegd.</p>
    @endif
</div>
