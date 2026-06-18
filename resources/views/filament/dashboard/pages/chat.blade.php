<x-filament-panels::page>
    @if(auth()->user()->hasRole('verhuurder'))

        {{-- Verhuurder: responsive grid — two-column on md+, single-panel with Alpine toggle on mobile --}}
        <div
            x-data="{ mobileView: 'list' }"
            @conversation-selected.window="mobileView = 'chat'"
            @broadcast-selected.window="mobileView = 'chat'"
            @back-to-list.window="mobileView = 'list'"
            class="grid grid-cols-1 md:grid-cols-3 gap-4 h-[calc(100vh-12rem)]"
        >
            <div
                class="col-span-1 md:block border border-[#0f17201f] rounded-[1.25rem] overflow-hidden bg-white"
                :class="{ 'hidden': mobileView !== 'list' }"
            >
                <livewire:chat.conversation-list />
            </div>
            <div
                class="col-span-1 md:col-span-2 md:block border border-[#0f17201f] rounded-[1.25rem] overflow-hidden bg-white"
                :class="{ 'hidden': mobileView !== 'chat' }"
            >
                <livewire:chat.chat-window />
            </div>
        </div>

    @elseif(auth()->user()->hasRole('huurder'))

        {{-- Huurder: list + window — een huurder kan met meerdere verhuurders
             praten (toegewezen kamer + aanvragen op vrije koten). --}}
        <div
            x-data="{ mobileView: 'list' }"
            @conversation-selected.window="mobileView = 'chat'"
            @back-to-list.window="mobileView = 'list'"
            class="grid grid-cols-1 md:grid-cols-3 gap-4 h-[calc(100vh-12rem)]"
        >
            <div
                class="col-span-1 md:block border border-[#0f17201f] rounded-[1.25rem] overflow-hidden bg-white"
                :class="{ 'hidden': mobileView !== 'list' }"
            >
                <livewire:chat.conversation-list />
            </div>
            <div
                class="col-span-1 md:col-span-2 md:block border border-[#0f17201f] rounded-[1.25rem] overflow-hidden bg-white"
                :class="{ 'hidden': mobileView !== 'chat' }"
            >
                <livewire:chat.chat-window :conversationId="$tenantConversationId" />
            </div>
        </div>

    @endif
</x-filament-panels::page>
