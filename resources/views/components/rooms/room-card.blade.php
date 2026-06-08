@props(['room'])

<div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow">
    <div class="flex items-start justify-between mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ $room->title }}</h3>
            <p class="text-sm text-gray-500 mt-1">Kamer {{ $room->room_number }}</p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-50 text-blue-700">
            {{ ucfirst($room->type) }}
        </span>
    </div>

    @if ($room->description)
        <p class="text-gray-600 text-sm mb-4">{{ $room->description }}</p>
    @endif

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Oppervlakte</p>
            <p class="mt-1 text-sm text-gray-900">{{ $room->surface_m2 }} m²</p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Prijs per maand</p>
            <p class="mt-1 text-sm font-semibold text-gray-900">€ {{ number_format($room->price_per_month, 2, ',', '.') }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Beschikbaar vanaf</p>
            <p class="mt-1 text-sm text-gray-900">{{ $room->available_from->format('d-m-Y') }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Status</p>
            <p class="mt-1 text-sm text-gray-900">{{ ucfirst($room->status) }}</p>
        </div>
    </div>

    <div class="flex flex-wrap gap-2 mb-4">
        @if ($room->is_furnished)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">
                Gemeubileerd
            </span>
        @endif
        @if ($room->costs_included)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700">
                Kosten inbegrepen
            </span>
        @endif
    </div>

    @if ($room->extra_costs && count($room->extra_costs) > 0)
        <div class="border-t border-gray-200 pt-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Extra kosten</p>
            <div class="space-y-1">
                @foreach ($room->extra_costs as $cost => $amount)
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>{{ ucfirst(str_replace('_', ' ', $cost)) }}</span>
                        <span>€ {{ number_format($amount, 2, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
