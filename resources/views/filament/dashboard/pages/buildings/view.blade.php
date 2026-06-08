<x-filament-panels::page>
    @php
        $record = $this->record;
    @endphp

    <div class="space-y-8">
        <!-- Header -->
        <div>
            <h1 class="text-5xl font-bold text-gray-900">{{ $record->name }}</h1>
            @if ($record->description)
                <div class="mt-4 prose prose-sm max-w-none text-gray-600">
                    {!! $record->description !!}
                </div>
            @endif
        </div>

        <!-- Locatie Kaart -->
        <div class="grid gap-6">
            <div class="bg-white rounded-lg border border-gray-200 p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Locatie</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Straat</p>
                        <p class="mt-2 text-lg text-gray-900">{{ $record->street }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Huisnummer</p>
                        <p class="mt-2 text-lg text-gray-900">{{ $record->house_number }}</p>
                    </div>
                    @if ($record->box)
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Bus/Apt</p>
                            <p class="mt-2 text-lg text-gray-900">{{ $record->box }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Postcode</p>
                        <p class="mt-2 text-lg text-gray-900">{{ $record->postal_code }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Plaats</p>
                        <p class="mt-2 text-lg text-gray-900">{{ $record->city }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Land</p>
                        <p class="mt-2 text-lg text-gray-900">{{ $record->country }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kamers -->
        <div>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Kamers ({{ $record->rooms->count() }})</h2>
                <a
                    href="{{ route('filament.dashboard.resources.rooms.create', ['building_id' => $record->id]) }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Kamer toevoegen
                </a>
            </div>

            @if ($record->rooms->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($record->rooms as $room)
                        <a href="{{ route('filament.dashboard.resources.rooms.edit', $room->id) }}" class="block hover:opacity-75 transition-opacity">
                            <x-rooms.room-card :room="$room" />
                        </a>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-lg border border-gray-200 p-8 shadow-sm text-center">
                    <p class="text-gray-500">Geen kamers beschikbaar</p>
                </div>
            @endif
        </div>

    </div>
</x-filament-panels::page>
