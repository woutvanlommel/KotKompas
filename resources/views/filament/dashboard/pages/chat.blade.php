<x-filament-panels::page>
    @if(auth()->user()->hasRole('verhuurder'))

        {{-- Verhuurder: two-column layout — conversation list left, chat window right --}}
        <div class="grid grid-cols-3 gap-4 h-[calc(100vh-12rem)]">
            <div class="col-span-1 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm">
                <livewire:chat.conversation-list />
            </div>
            <div class="col-span-2 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm">
                <livewire:chat.chat-window />
            </div>
        </div>

    @elseif(auth()->user()->hasRole('huurder'))

        {{-- Huurder: full-width single chat window --}}
        @if($tenantConversationId)
            <div class="h-[calc(100vh-12rem)] border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm">
                <livewire:chat.chat-window :conversationId="$tenantConversationId" />
            </div>
        @else
            <div class="flex items-center justify-center h-[calc(100vh-12rem)] text-gray-400">
                <div class="text-center">
                    <x-heroicon-o-chat-bubble-left-right class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                    <p class="text-sm">Je bent nog niet gekoppeld aan een kamer.</p>
                    <p class="text-xs mt-1">Zodra je een kamer toegewezen krijgt, kan je hier chatten met je verhuurder.</p>
                </div>
            </div>
        @endif

    @endif
</x-filament-panels::page>
