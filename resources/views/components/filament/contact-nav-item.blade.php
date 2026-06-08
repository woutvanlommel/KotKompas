@if (auth()->user()?->hasRole('verhuurder'))
    <ul class="fi-sidebar-nav-groups">
        <x-filament-panels::sidebar.item
            :url="route('filament.dashboard.pages.contact')"
            :active="request()->routeIs('filament.dashboard.pages.contact')"
            :icon="\Filament\Support\Icons\Heroicon::OutlinedEnvelope"
            :active-icon="\Filament\Support\Icons\Heroicon::Envelope"
        >
            Contact
        </x-filament-panels::sidebar.item>
    </ul>
@endif
