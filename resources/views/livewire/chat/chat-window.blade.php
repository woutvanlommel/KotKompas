<div class="flex flex-col h-full">
    @if($isBroadcastMode)

        {{-- Broadcast header --}}
        <div class="flex items-center gap-3 px-3 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
            @if(auth()->user()->hasRole('verhuurder'))
            <button
                type="button"
                class="md:hidden mr-1 p-1 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                aria-label="Terug naar gesprekken"
                x-on:click="window.dispatchEvent(new CustomEvent('back-to-list'))"
            >
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </button>
            @endif
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-accent-100 dark:bg-accent-900/40 text-accent-700 dark:text-accent-300 shrink-0">
                <x-heroicon-o-megaphone class="w-5 h-5" />
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-gray-900 dark:text-white">Alle huurders</p>
                <p class="text-xs text-gray-500">Stuur één bericht naar alle huurders van een gebouw</p>
            </div>
        </div>

        {{-- Broadcast form --}}
        <div class="flex-1 flex items-start justify-center bg-gray-50 dark:bg-gray-900/40 overflow-y-auto">
            <form wire:submit="sendBroadcast" class="w-full max-w-xl mx-auto px-4 sm:px-6 py-8 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gebouw</label>
                    <select
                        wire:model="broadcastBuildingId"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                        <option value="">Selecteer gebouw...</option>
                        @foreach($buildings as $building)
                            <option value="{{ $building->id }}">{{ $building->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bericht</label>
                    <textarea
                        wire:model.blur="broadcastMessage"
                        rows="4"
                        placeholder="Schrijf een bericht voor alle huurders..."
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
                    ></textarea>
                </div>
                <button
                    type="submit"
                    class="w-full rounded-xl bg-accent-500 px-4 py-2.5 text-white text-sm font-medium hover:bg-accent-600 transition-colors"
                >
                    Verstuur naar alle huurders
                </button>
            </form>
        </div>

    @elseif($conversation)
        @php
            $other = auth()->user()->hasRole('verhuurder') ? $conversation->tenant : $conversation->landlord;
            $otherName = trim($other->name.' '.$other->lastname);
            $otherInitials = strtoupper(mb_substr($other->name, 0, 1).mb_substr($other->lastname, 0, 1));
        @endphp

        {{-- Header --}}
        <div class="flex items-center gap-3 px-3 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
            @if(auth()->user()->hasRole('verhuurder'))
            <button
                type="button"
                class="md:hidden mr-1 p-1 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                aria-label="Terug naar gesprekken"
                x-on:click="window.dispatchEvent(new CustomEvent('back-to-list'))"
            >
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </button>
            @endif
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 text-sm font-semibold shrink-0">
                {{ $otherInitials }}
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $otherName }}</p>
                <p class="text-xs text-gray-500 truncate">{{ $conversation->building->name }}</p>
            </div>
        </div>

        {{-- Messages --}}
        <div
            class="chat-messages flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900/40"
            x-data
            x-init="$el.scrollTop = $el.scrollHeight"
            x-on:scroll-to-bottom.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
        >
            <div class="max-w-3xl mx-auto w-full px-4 sm:px-6 py-6 space-y-4">
                @foreach($messages as $message)
                    <div class="flex {{ $message['is_mine'] ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[75%]">
                            @if(! $message['is_mine'])
                                <p class="text-xs text-gray-500 mb-1 ml-1">{{ $message['sender_name'] }}</p>
                            @endif
                            <div class="px-4 py-2.5 rounded-2xl shadow-sm {{ $message['is_mine'] ? 'bg-primary-600 text-white rounded-br-md' : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-bl-md' }}">
                                <p class="text-sm leading-relaxed break-words whitespace-pre-wrap">{{ $message['body'] }}</p>
                            </div>
                            <p class="text-xs text-gray-400 mt-1 {{ $message['is_mine'] ? 'text-right mr-1' : 'ml-1' }}">
                                {{ \Carbon\Carbon::parse($message['created_at'])->format('H:i') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Input --}}
        <div class="border-t border-gray-200 dark:border-gray-700 shrink-0">
            <form wire:submit="sendMessage" class="max-w-3xl mx-auto w-full flex items-end gap-3 px-4 sm:px-6 py-4">
                <div
                    class="chat-input-wrapper flex-1 rounded-2xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus-within:ring-2 focus-within:ring-primary-500 focus-within:border-primary-500"
                >
                <textarea
                    wire:model.blur="newMessage"
                    rows="1"
                    placeholder="Schrijf een bericht..."
                    class="block w-full bg-transparent pl-5 pr-2 py-2.5 text-sm focus:outline-none resize-none overflow-hidden"
                    autocomplete="off"
                    x-data
                    x-on:input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px';"
                    x-on:livewire:updated="if (!$el.value) { $el.style.height = 'auto'; }"
                    x-on:keydown.enter.prevent="if (!$event.shiftKey) { $wire.newMessage = $el.value; $wire.sendMessage(); $el.style.height = 'auto'; }"
                ></textarea>
                </div>
                <button
                    type="submit"
                    class="flex items-center justify-center w-11 h-11 shrink-0 rounded-full bg-primary-600 text-white hover:bg-primary-700 transition-colors"
                    aria-label="Verstuur bericht"
                >
                    <x-heroicon-s-paper-airplane class="w-5 h-5" />
                </button>
            </form>
        </div>

    @else
        <div class="flex-1 flex items-center justify-center text-gray-400">
            <div class="text-center">
                <x-heroicon-o-chat-bubble-left-right class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                <p class="text-sm">Selecteer een gesprek</p>
            </div>
        </div>
    @endif

    @script
    <script>
        const authId = @js(auth()->id());
        let currentChannelName = null;

        function subscribeToConversation(conversationId) {
            if (currentChannelName) {
                Echo.leave(currentChannelName);
                currentChannelName = null;
            }
            if (!conversationId) return;

            currentChannelName = `conversation.${conversationId}`;

            Echo.private(currentChannelName)
                .listen('MessageSent', (e) => {
                    if (e.sender_id !== authId) {
                        $wire.dispatch('message-received', {
                            message: {
                                id:          e.id,
                                body:        e.body,
                                sender_id:   e.sender_id,
                                sender_name: e.sender_name ?? '',
                                created_at:  e.created_at,
                                is_mine:     false,
                            }
                        });
                    }
                });
        }

        subscribeToConversation(@js($conversation?->id));

        Livewire.on('conversation-selected', ({ conversationId }) => {
            subscribeToConversation(conversationId);
        });

        Livewire.on('broadcast-selected', () => {
            if (currentChannelName) {
                Echo.leave(currentChannelName);
                currentChannelName = null;
            }
        });
    </script>
    @endscript
</div>
