<x-filament-panels::page>
    @php
        $room = $this->record;

        $typeLabels = [
            'kamer'       => 'Kamer',
            'studio'      => 'Studio',
            'appartement' => 'Appartement',
        ];

        $statusConfig = [
            'available'   => ['label' => 'Beschikbaar',  'bg' => 'bg-[#e7f6ec]', 'text' => 'text-[#15803d]', 'dot' => 'bg-[#15803d]'],
            'rented'      => ['label' => 'Verhuurd',     'bg' => 'bg-[#eaf1f8]', 'text' => 'text-[#2e5884]', 'dot' => 'bg-[#2e5884]'],
            'maintenance' => ['label' => 'Onderhoud',    'bg' => 'bg-[#fff3e0]', 'text' => 'text-[#c2510a]', 'dot' => 'bg-[#c2510a]'],
            'archived'    => ['label' => 'Gearchiveerd', 'bg' => 'bg-[#e1e6ed]', 'text' => 'text-[#586573]', 'dot' => 'bg-[#586573]'],
        ];

        $status   = $statusConfig[$room->status] ?? $statusConfig['archived'];
        $coverMedia = $room->getFirstMedia('cover');
        $imageUrl = $coverMedia?->getUrl('webp') ?: $coverMedia?->getUrl();
    @endphp

    <div class="space-y-8">
        @include('filament.dashboard.pages.rooms.partials.hero')
        @include('filament.dashboard.pages.rooms.partials.header')
        @include('filament.dashboard.pages.rooms.partials.details')
        @include('filament.dashboard.pages.rooms.partials.costs')
        @include('filament.dashboard.pages.rooms.partials.facilities')
        @include('filament.dashboard.pages.rooms.partials.status-tenant')
        @include('filament.dashboard.pages.rooms.partials.documents')
        @include('filament.dashboard.pages.rooms.partials.description')
        @include('filament.dashboard.pages.rooms.partials.gallery')
    </div>
</x-filament-panels::page>
