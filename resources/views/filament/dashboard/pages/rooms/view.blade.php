<x-filament-panels::page>
    @php
        $room = $this->record;

        $typeLabels = [
            'studio'            => 'Studio',
            'one_bedroom'       => '1 slaapkamer',
            'two_bedroom'       => '2 slaapkamers',
            'three_bedroom'     => '3 slaapkamers',
            'four_bedroom'      => '4 slaapkamers',
            'five_plus_bedroom' => '5+ slaapkamers',
        ];

        $statusConfig = [
            'available'   => ['label' => 'Beschikbaar',  'bg' => 'bg-green-50',  'text' => 'text-green-700',  'dot' => 'bg-green-500'],
            'rented'      => ['label' => 'Verhuurd',     'bg' => 'bg-blue-50',   'text' => 'text-blue-700',   'dot' => 'bg-blue-500'],
            'maintenance' => ['label' => 'Onderhoud',    'bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'dot' => 'bg-yellow-500'],
            'archived'    => ['label' => 'Gearchiveerd', 'bg' => 'bg-gray-100',  'text' => 'text-gray-600',   'dot' => 'bg-gray-400'],
        ];

        $status   = $statusConfig[$room->status] ?? $statusConfig['archived'];
        $coverMedia = $room->getFirstMedia('cover');
        $imageUrl = $coverMedia?->getUrl('webp') ?: $coverMedia?->getUrl();
    @endphp

    <div class="space-y-8">
        @include('filament.dashboard.pages.rooms.partials.hero')
        @include('filament.dashboard.pages.rooms.partials.header')
        @include('filament.dashboard.pages.rooms.partials.details')
        @include('filament.dashboard.pages.rooms.partials.status-tenant')
        @include('filament.dashboard.pages.rooms.partials.description')
        @include('filament.dashboard.pages.rooms.partials.gallery')
    </div>
</x-filament-panels::page>
