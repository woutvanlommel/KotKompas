@php
    use Filament\Support\Enums\MaxWidth;

    $maxWidth = $this->getMaxContentWidth();
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $this->getHeading() }}</h1>
            <p class="mt-2 text-gray-600">{{ $this->record->name }}</p>
        </div>

        <x-filament::section>
            {{ $this->form }}
        </x-filament::section>
    </div>

    @if ($headerActions = $this->getHeaderActions())
        <x-filament::section>
            <div class="flex gap-3">
                @foreach ($headerActions as $action)
                    {{ $action }}
                @endforeach
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
