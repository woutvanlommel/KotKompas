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
        <div class="text-right shrink-0">
            <p class="text-3xl font-bold text-gray-900">€ {{ number_format($room->price_per_month, 2, ',', '.') }}</p>
            <p class="text-sm text-gray-500">per maand</p>
        </div>
    </div>
</div>
