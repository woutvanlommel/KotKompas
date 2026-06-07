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

    </div>
</x-filament-panels::page>
