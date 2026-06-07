<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold text-gray-900">Panden</h1>
                <p class="mt-2 text-gray-600">Beheer je gebouwen en kamers</p>
            </div>
            @if ($headerActions = $this->getHeaderActions())
                <div class="flex gap-2">
                    @foreach ($headerActions as $action)
                        {{ $action }}
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Buildings Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($this->getFilteredTableQuery()->get() as $record)
                <a href="{{ $this->getResource()::getUrl('view', ['record' => $record]) }}"
                   class="block group">
                    <x-filament::section class="h-full transition-all hover:shadow-lg">
                        <div class="space-y-3">
                            <h3 class="text-xl font-bold text-gray-900 group-hover:text-blue-600 transition">
                                {{ $record->name }}
                            </h3>
                            @if ($record->description)
                                <p class="text-sm text-gray-600 line-clamp-2">
                                    {{ $record->description }}
                                </p>
                            @endif
                            <p class="text-sm text-gray-500">
                                📍 {{ $record->fullAddress }}
                            </p>
                            <div class="pt-3 border-t border-gray-200">
                                <p class="text-xs text-gray-500">Klik om details te bekijken →</p>
                            </div>
                        </div>
                    </x-filament::section>
                </a>
            @empty
                <x-filament::section class="col-span-full">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m0 0l7-7 7 7"></path>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">Geen panden gevonden</h3>
                        <p class="mt-2 text-gray-600">Voeg je eerste gebouw toe om te starten</p>
                    </div>
                </x-filament::section>
            @endforelse
        </div>

    </div>
</x-filament-panels::page>
