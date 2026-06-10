<x-filament-widgets::widget>
    <x-filament::section>
        <x-filament::empty-state
            :icon="$icon"
            icon-color="gray"
            :heading="$heading"
            :description="$description"
            :contained="false"
            compact
        >
            <x-slot name="footer">
                <x-filament::badge color="gray">Binnenkort beschikbaar</x-filament::badge>
            </x-slot>
        </x-filament::empty-state>
    </x-filament::section>
</x-filament-widgets::widget>
