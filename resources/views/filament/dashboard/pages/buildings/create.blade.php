@php
    use Filament\Support\Enums\MaxWidth;

    $maxWidth = $this->getMaxContentWidth();
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $this->getHeading() }}</h1>
            <p class="mt-2 text-gray-600">Voeg een nieuw gebouw toe aan je portefeuille</p>
        </div>

        <x-filament::section>
            {{ $this->form }}
        </x-filament::section>
    </div>
</x-filament-panels::page>
