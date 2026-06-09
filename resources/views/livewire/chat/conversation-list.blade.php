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
                class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition {{ $activeConversationId === $convo['id'] ? 'bg-primary-50 dark:bg-primary-900/20 border-l-2 border-primary-600' : '' }}"
            >
                <div class="flex items-center justify-between">
                    <p class="font-medium text-sm text-gray-900 dark:text-white truncate">
                        {{ $convo['tenant_name'] }}
                    </p>
                    <div class="flex items-center gap-2 shrink-0 ml-2">
                        @if($convo['unread'] > 0)
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-primary-600 text-white text-xs font-semibold">
                                {{ $convo['unread'] }}
                            </span>
                        @endif
                        <span class="text-xs text-gray-400">{{ $convo['last_message_at'] }}</span>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">{{ $convo['building_name'] }}</p>
                @if($convo['last_message'])
                    <p class="text-xs text-gray-400 truncate mt-0.5">{{ $convo['last_message'] }}</p>
                @endif
            </button>
        @empty
            <p class="p-4 text-sm text-gray-400">Geen gesprekken gevonden.</p>
        @endforelse
    </div>
</div>
