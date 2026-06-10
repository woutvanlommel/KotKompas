<div class="flex flex-col h-full">

    {{-- Pinned broadcast entry --}}
    <div class="border-b border-gray-200 dark:border-gray-700 shrink-0">
        <button
            wire:click="selectBroadcast"
            class="w-full text-left px-4 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors {{ $broadcastActive ? 'bg-primary-50 dark:bg-primary-900/20 border-l-2 border-primary-600' : 'border-l-2 border-transparent' }}"
        >
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-accent-100 dark:bg-accent-900/40 text-accent-700 dark:text-accent-300 shrink-0">
                    <x-heroicon-o-megaphone class="w-5 h-5" />
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-sm text-gray-900 dark:text-white truncate">Alle huurders</p>
                    <p class="text-xs text-gray-400 mt-0.5">Stuur een bericht naar iedereen</p>
                </div>
            </div>
        </button>
    </div>

    {{-- Conversation list --}}
    <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
        @forelse($conversations as $convo)
            <button
                wire:click="selectConversation({{ $convo['id'] }})"
                class="w-full text-left px-4 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors {{ $activeConversationId === $convo['id'] ? 'bg-primary-50 dark:bg-primary-900/20 border-l-2 border-primary-600' : 'border-l-2 border-transparent' }}"
            >
                <div class="flex items-start gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 text-sm font-semibold shrink-0">
                        {{ strtoupper(mb_substr($convo['tenant_name'], 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-medium text-sm text-gray-900 dark:text-white truncate">
                                {{ $convo['tenant_name'] }}
                            </p>
                            <span class="text-xs text-gray-400 shrink-0">{{ $convo['last_message_at'] }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $convo['building_name'] }}</p>
                        <div class="flex items-center justify-between gap-2 mt-1">
                            <p class="text-xs text-gray-400 truncate">
                                {{ $convo['last_message'] ?: 'Nog geen berichten' }}
                            </p>
                            @if($convo['unread'] > 0)
                                <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-primary-600 text-white text-xs font-semibold shrink-0">
                                    {{ $convo['unread'] }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </button>
        @empty
            <div class="flex flex-col items-center justify-center h-full text-center px-4 py-10 text-gray-400">
                <x-heroicon-o-chat-bubble-left-right class="w-10 h-10 mb-2 text-gray-300" />
                <p class="text-sm">Geen gesprekken gevonden.</p>
            </div>
        @endforelse
    </div>
</div>
