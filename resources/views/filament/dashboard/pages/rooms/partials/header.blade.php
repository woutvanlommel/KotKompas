<div class="flex flex-col gap-2">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-4xl font-bold text-gray-900">
                {{ $room->title ?: 'Kamer ' . $room->room_number }}
            </h1>
            <p class="mt-1 text-gray-500 text-base">
                Kamer {{ $room->room_number }} &middot; {{ $typeLabels[$room->type] ?? $room->type }}
            </p>
            <p class="mt-0.5 text-gray-400 text-sm">
                {{ $room->full_address }}
            </p>
        </div>
        <div class="flex items-start gap-3 shrink-0">
            <div class="text-right">
                <p class="text-3xl font-medium tracking-[-0.02em] tabular-nums text-[#0f1720]">€ {{ number_format($room->price_per_month, 2, ',', '.') }}</p>
                <p class="text-sm text-gray-500">per maand</p>
            </div>
            <button wire:click="mountAction('editBasics')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-100 transition mt-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                </svg>
                Bewerken
            </button>
        </div>
    </div>
</div>
