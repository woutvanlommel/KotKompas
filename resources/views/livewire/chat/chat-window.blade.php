<div class="flex flex-col h-full">
    @if($conversation)
        {{-- Header --}}
        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <div>
                <p class="font-semibold text-gray-900 dark:text-white">
                    @if(auth()->user()->hasRole('verhuurder'))
                        {{ $conversation->tenant->name }} {{ $conversation->tenant->lastname }}
                    @else
                        {{ $conversation->landlord->name }} {{ $conversation->landlord->lastname }}
                    @endif
                </p>
                <p class="text-xs text-gray-500">{{ $conversation->building->name }}</p>
            </div>
        </div>

        {{-- Messages --}}
        <div
            class="flex-1 overflow-y-auto p-4 space-y-3"
            x-data
            x-init="$el.scrollTop = $el.scrollHeight"
            x-on:message-received.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
        >
            @foreach($messages as $message)
                <div class="flex {{ $message['is_mine'] ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-xs lg:max-w-md">
                        @if(! $message['is_mine'])
                            <p class="text-xs text-gray-500 mb-1">{{ $message['sender_name'] }}</p>
                        @endif
                        <div class="px-4 py-2 rounded-2xl {{ $message['is_mine'] ? 'bg-primary-600 text-white rounded-br-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-bl-sm' }}">
                            <p class="text-sm">{{ $message['body'] }}</p>
                        </div>
                        <p class="text-xs text-gray-400 mt-1 {{ $message['is_mine'] ? 'text-right' : '' }}">
                            {{ \Carbon\Carbon::parse($message['created_at'])->format('H:i') }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Input --}}
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            <form wire:submit="sendMessage" class="flex gap-2">
                <input
                    wire:model="newMessage"
                    type="text"
                    placeholder="Schrijf een bericht..."
                    class="flex-1 rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                    autocomplete="off"
                />
                <button
                    type="submit"
                    class="rounded-full bg-primary-600 p-2 text-white hover:bg-primary-700 transition"
                >
                    <x-heroicon-s-paper-airplane class="w-5 h-5" />
                </button>
            </form>
        </div>
    @else
        <div class="flex-1 flex items-center justify-center text-gray-400">
            <p>Selecteer een gesprek</p>
        </div>
    @endif

    @script
    <script>
        const conversationId = @js($conversation?->id);

        if (conversationId) {
            Echo.private(`conversation.${conversationId}`)
                .listen('MessageSent', (e) => {
                    // Only append if the message came from the other person —
                    // our own messages are already added optimistically via sendMessage()
                    if (e.sender_id !== @js(auth()->id())) {
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
    </script>
    @endscript
</div>
