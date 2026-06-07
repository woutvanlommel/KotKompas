<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Gebouw Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-4xl font-bold text-gray-900">{{ $record->name }}</h1>
                <p class="mt-2 text-lg text-gray-600">{{ $record->fullAddress }}</p>
            </div>
            @if ($headerActions = $this->getHeaderActions())
                <div class="flex gap-2">
                    @foreach ($headerActions as $action)
                        {{ $action }}
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Gebouw Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <x-filament::section class="col-span-1">
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-500">STRAAT</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $record->street }} {{ $record->house_number }}</p>
                </div>
            </x-filament::section>

            <x-filament::section class="col-span-1">
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-500">PLAATS</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $record->postal_code }} {{ $record->city }}</p>
                </div>
            </x-filament::section>

            <x-filament::section class="col-span-1">
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-500">LAND</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $record->country }}</p>
                </div>
            </x-filament::section>
        </div>

        @if ($record->description)
            <x-filament::section>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-500">BESCHRIJVING</p>
                    <p class="text-gray-700">{{ $record->description }}</p>
                </div>
            </x-filament::section>
        @endif

        <!-- Kamers Section -->
        <x-filament::section>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-900">Kamers</h2>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        + Kamer toevoegen
                    </button>
                </div>
                <p class="text-sm text-gray-600">Hier komen je kamers te staan</p>
            </div>
        </x-filament::section>

        <!-- Contracten Section (toekomstig) -->
        <x-filament::section>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-900">Contracten</h2>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        + Contract toevoegen
                    </button>
                </div>
                <p class="text-sm text-gray-600">Hier komen je contracten te staan</p>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
