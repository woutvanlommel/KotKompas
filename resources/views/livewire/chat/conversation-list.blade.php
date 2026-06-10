<div class="flex flex-col h-full">

    {{-- Send to all --}}
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
            Stuur naar alle huurders
        </p>
        <form wire:submit="sendToAll" class="space-y-2">
            <select
                wire:model="selectedBuildingId"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
                <option value="">Selecteer gebouw...</option>
                @foreach($buildings as $building)
                    <option value="{{ $building->id }}">{{ $building->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <input
                    wire:model="broadcastMessage"
                    type="text"
                    placeholder="Bericht voor alle huurders..."
                    class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
                <button
                    type="submit"
                    class="rounded-lg bg-accent-500 px-3 py-2 text-white text-sm font-medium hover:bg-accent-600 transition"
                >
                    Verstuur
                </button>
            </div>
        </form>
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
