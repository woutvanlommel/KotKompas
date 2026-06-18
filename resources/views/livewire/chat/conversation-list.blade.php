<div class="flex flex-col h-full">

    @if ($isLandlord)
    {{-- Pinned broadcast entry --}}
    <div class="border-b border-[#0f17201f] shrink-0">
        <button
            wire:click="selectBroadcast"
            class="w-full text-left px-4 py-3.5 hover:bg-[#edf0f4] transition-colors {{ $broadcastActive ? 'bg-[#edf0f4] border-l-2 border-[#0f1720]' : 'border-l-2 border-transparent' }}"
        >
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-[#f4ecd6] text-[#7b6118] shrink-0">
                    <x-heroicon-o-megaphone class="w-5 h-5" />
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-sm text-[#0f1720] truncate">Alle huurders</p>
                    <p class="text-xs text-[#9aa6b4] mt-0.5">Stuur een bericht naar iedereen</p>
                </div>
            </div>
        </button>
    </div>

    {{-- Tenant finder --}}
    <div class="px-3 py-3 border-b border-[#0f17201f] space-y-2 shrink-0">
        <select
            wire:model.live="filterBuildingId"
            class="w-full rounded-[4px] border border-[#0f17201f] bg-white px-3 py-2 text-sm text-[#0f1720] focus:outline-none focus:ring-2 focus:ring-[#9aa6b4]"
        >
            <option value="">Selecteer gebouw...</option>
            @foreach($buildings as $building)
                <option value="{{ $building->id }}">{{ $building->name }}</option>
            @endforeach
        </select>

        @if($availableTenants)
            <select
                wire:model.live="filterTenantId"
                class="w-full rounded-[4px] border border-[#0f17201f] bg-white px-3 py-2 text-sm text-[#0f1720] focus:outline-none focus:ring-2 focus:ring-[#9aa6b4]"
            >
                <option value="">Selecteer huurder...</option>
                @foreach($availableTenants as $tenant)
                    <option value="{{ $tenant['id'] }}">{{ $tenant['name'] }}</option>
                @endforeach
            </select>
        @endif
    </div>
    @endif

    {{-- Conversation list --}}
    <div class="flex-1 overflow-y-auto divide-y divide-[#0f17201f]">
        @forelse($conversations as $convo)
            <button
                wire:click="selectConversation({{ $convo['id'] }})"
                class="w-full text-left px-4 py-3.5 hover:bg-[#edf0f4] transition-colors {{ $activeConversationId === $convo['id'] ? 'bg-[#edf0f4] border-l-2 border-[#0f1720]' : 'border-l-2 border-transparent' }}"
            >
                <div class="flex items-start gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-[#edf0f4] text-[#586573] text-sm font-semibold shrink-0">
                        {{ strtoupper(mb_substr($convo['name'], 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-medium text-sm text-[#0f1720] truncate">
                                {{ $convo['name'] }}
                            </p>
                            <span class="text-xs text-[#9aa6b4] shrink-0">{{ $convo['last_message_at'] }}</span>
                        </div>
                        <p class="text-xs text-[#586573] mt-0.5 truncate">{{ $convo['building_name'] }}</p>
                        <div class="flex items-center justify-between gap-2 mt-1">
                            <p class="text-xs text-[#9aa6b4] truncate">
                                {{ $convo['last_message'] ?: 'Nog geen berichten' }}
                            </p>
                            @if($convo['unread'] > 0)
                                <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-[#0f1720] text-white text-xs font-semibold shrink-0">
                                    {{ $convo['unread'] }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </button>
        @empty
            <div class="flex flex-col items-center justify-center h-full text-center px-4 py-10 text-[#9aa6b4]">
                <x-heroicon-o-chat-bubble-left-right class="w-10 h-10 mb-2 text-[#9aa6b4]" />
                <p class="text-sm">Geen gesprekken gevonden.</p>
            </div>
        @endforelse
    </div>
</div>
