# Chat Improvements Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix real-time message delivery, improve scroll behaviour, move the broadcast form to a pinned chat tab, add auto-expanding textarea input, and make the chat layout responsive on phones and tablets.

**Architecture:** All changes are confined to three Blade views (`chat.blade.php`, `chat-window.blade.php`, `conversation-list.blade.php`) and two Livewire components (`ChatWindow.php`, `ConversationList.php`). Alpine.js handles all pure UI state (mobile panel visibility, textarea height). Livewire handles all server-side state and event dispatch. No new routes, models, or migrations are required.

**Tech Stack:** Laravel 11, Livewire v3, Alpine.js, Tailwind CSS v3, Laravel Echo (Pusher/Reverb), Filament v3.

---

## File Map

| File | Change |
|------|--------|
| `app/Livewire/Chat/ChatWindow.php` | Add `$isBroadcastMode`, `$broadcastBuildingId`, `$broadcastMessage`, `$buildings`; add `sendBroadcast()`, `broadcastSelected()` handler; fix scroll dispatch in `messageReceived()` |
| `app/Livewire/Chat/ConversationList.php` | Remove `sendToAll`, `broadcastMessage`, `selectedBuildingId`; add `$broadcastActive`, `selectBroadcast()` |
| `app/Events/MessageSent.php` | Add `sender_name` to `broadcastWith()` |
| `resources/views/livewire/chat/chat-window.blade.php` | Add broadcast mode UI, back button, textarea input, fix scroll listener |
| `resources/views/livewire/chat/conversation-list.blade.php` | Remove broadcast form section, add pinned "Alle huurders" entry |
| `resources/views/filament/dashboard/pages/chat.blade.php` | Responsive grid with Alpine view-state |

---

## Task 1: Debug and fix real-time message delivery

**Files:**
- Modify: `app/Events/MessageSent.php:28-37`
- Modify: `app/Livewire/Chat/ChatWindow.php:95-100`
- Modify: `resources/views/livewire/chat/chat-window.blade.php:74-113`

**Context:** Messages from the other party sometimes only appear after a page refresh. The most likely causes are (a) `sender_name` missing from the broadcast payload causing a silent JS error, (b) the Echo listener not connecting due to a private channel auth issue, or (c) the Livewire `$wire.dispatch` call not triggering a server round-trip.

- [ ] **Step 1: Add `sender_name` to the broadcast payload**

Open `app/Events/MessageSent.php`. The `broadcastWith()` method does not include `sender_name`, but the Echo listener in the blade reads `e.sender_name`. The `?? ''` fallback means it silently sends an empty name. Fix it so the name is always present:

```php
public function broadcastWith(): array
{
    return [
        'id'             => $this->message->id,
        'body'           => $this->message->body,
        'sender_id'      => $this->message->sender_id,
        'sender_name'    => trim($this->message->sender->name.' '.$this->message->sender->lastname),
        'conversation_id'=> $this->message->conversation_id,
        'created_at'     => $this->message->created_at->toISOString(),
    ];
}
```

Note: `$this->message` is eager-loaded with `sender` in `ChatWindow::sendMessage()` via `$message->load('sender')`, so this is safe.

- [ ] **Step 2: Verify Echo is connecting — add temporary console diagnostics**

Open `resources/views/livewire/chat/chat-window.blade.php`. Inside the `@script` block, add temporary logging around the Echo subscription to make the connection visible in the browser console:

```javascript
function subscribeToConversation(conversationId) {
    if (currentChannelName) {
        Echo.leave(currentChannelName);
        currentChannelName = null;
    }
    if (!conversationId) return;

    currentChannelName = `conversation.${conversationId}`;
    console.log('[Chat] Subscribing to', currentChannelName);

    Echo.private(currentChannelName)
        .listen('MessageSent', (e) => {
            console.log('[Chat] MessageSent received', e);
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
```

- [ ] **Step 3: Open the chat page in two browser tabs and send a message**

Open the chat in two browser tabs logged in as different users (one verhuurder, one huurder). In Tab 1 (DevTools → Console open), send a message from Tab 2. Check Tab 1's console:

- If you see `[Chat] Subscribing to conversation.X` → Echo connected.
- If you see `[Chat] MessageSent received {...}` → event arrived; the bug is in the Livewire dispatch or re-render.
- If you see a 403 error in the Network tab for `/broadcasting/auth` → private channel auth is failing.
- If nothing appears → Echo is not connecting; check that your `.env` has correct `PUSHER_*` or `REVERB_*` keys and that the queue/websocket server is running.

- [ ] **Step 4: Fix scroll timing — dispatch `scroll-to-bottom` from PHP after re-render**

The current blade uses `x-on:message-received.window` to scroll. This fires at the moment `$wire.dispatch` is called (before the Livewire HTTP response has updated the DOM). Replace it with a server-dispatched event that fires after Livewire morphs the DOM.

In `app/Livewire/Chat/ChatWindow.php`, update `messageReceived()`:

```php
#[On('message-received')]
public function messageReceived(array $message): void
{
    $this->messages[] = $message;
    $this->markAsRead();
    $this->dispatch('scroll-to-bottom');
}
```

Also update `sendMessage()` to scroll after the sender's own message:

```php
public function sendMessage(): void
{
    $this->validate(['newMessage' => 'required|string|max:5000']);

    if (! $this->conversation) {
        return;
    }

    $message = Message::create([
        'conversation_id' => $this->conversation->id,
        'sender_id'       => auth()->id(),
        'body'            => strip_tags($this->newMessage),
    ]);

    $this->conversation->update(['last_message_at' => now()]);

    MessageSent::dispatch($message->load('sender'));

    $this->messages[] = [
        'id'          => $message->id,
        'body'        => $message->body,
        'sender_id'   => $message->sender_id,
        'sender_name' => trim(auth()->user()->name.' '.auth()->user()->lastname),
        'created_at'  => $message->created_at->toISOString(),
        'is_mine'     => true,
    ];

    $this->newMessage = '';
    $this->dispatch('scroll-to-bottom');
}
```

- [ ] **Step 5: Update the scroll listener in the blade**

In `resources/views/livewire/chat/chat-window.blade.php`, replace the `x-on:message-received.window` scroll with `x-on:scroll-to-bottom.window`. Also remove the `x-data` (empty) since the scroll listener is now on the window:

Replace lines 21–26:
```html
{{-- Messages --}}
<div
    class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900/40"
    x-data
    x-init="$el.scrollTop = $el.scrollHeight"
    x-on:scroll-to-bottom.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
>
```

- [ ] **Step 6: Remove the temporary console.log lines added in Step 2**

Clean up the two `console.log` lines from the `@script` block. The final `subscribeToConversation` function should look like:

```javascript
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
```

- [ ] **Step 7: Verify in browser**

Open chat in two tabs. Send a message from one tab. Confirm the other tab shows the message immediately without a page refresh and that the view scrolls to it.

---

## Task 2: Broadcast pinned tab (move "Alle huurders" form)

**Files:**
- Modify: `app/Livewire/Chat/ConversationList.php`
- Modify: `app/Livewire/Chat/ChatWindow.php`
- Modify: `resources/views/livewire/chat/conversation-list.blade.php`
- Modify: `resources/views/livewire/chat/chat-window.blade.php`

**Context:** The "Stuur naar alle huurders" form currently occupies the top of the conversation list sidebar. Move it into the chat window as a dedicated "broadcast" mode, accessible via a pinned entry at the top of the conversation list. Only visible to verhuurders.

### ConversationList.php changes

- [ ] **Step 1: Remove broadcast properties and `sendToAll`, add `$broadcastActive` and `selectBroadcast()`**

Replace the full `ConversationList.php` with:

```php
<?php

namespace App\Livewire\Chat;

use App\Models\Building;
use App\Models\Conversation;
use Livewire\Component;

class ConversationList extends Component
{
    public ?int $activeConversationId = null;

    public bool $broadcastActive = false;

    public function selectConversation(int $conversationId): void
    {
        $this->activeConversationId = $conversationId;
        $this->broadcastActive = false;
        $this->dispatch('conversation-selected', conversationId: $conversationId);
    }

    public function selectBroadcast(): void
    {
        $this->activeConversationId = null;
        $this->broadcastActive = true;
        $this->dispatch('broadcast-selected');
    }

    public function render()
    {
        $conversations = Conversation::where('landlord_id', auth()->id())
            ->with(['tenant', 'building', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(fn (Conversation $c) => [
                'id'             => $c->id,
                'tenant_name'    => trim($c->tenant->name.' '.$c->tenant->lastname),
                'building_name'  => $c->building->name,
                'last_message'   => $c->messages->first()?->body,
                'last_message_at'=> $c->last_message_at?->diffForHumans(),
                'unread'         => $c->unreadFor(auth()->id()),
            ]);

        $buildings = Building::where('landlord_id', auth()->id())->get();

        return view('livewire.chat.conversation-list', compact('conversations', 'buildings'));
    }
}
```

### conversation-list.blade.php changes

- [ ] **Step 2: Remove broadcast form, add pinned "Alle huurders" entry**

Replace the entire file with:

```blade
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
```

### ChatWindow.php changes

- [ ] **Step 3: Add broadcast mode properties and methods**

Add to `ChatWindow.php`:

```php
<?php

namespace App\Livewire\Chat;

use App\Events\MessageSent;
use App\Models\Building;
use App\Models\Conversation;
use App\Models\Message;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatWindow extends Component
{
    public ?Conversation $conversation = null;

    public string $newMessage = '';

    public array $messages = [];

    public bool $isBroadcastMode = false;

    public ?int $broadcastBuildingId = null;

    public string $broadcastMessage = '';

    public function mount(?int $conversationId = null): void
    {
        if ($conversationId) {
            $this->conversation = Conversation::findOrFail($conversationId);
            $this->loadMessages();
            $this->markAsRead();
        }
    }

    public function loadMessages(): void
    {
        if (! $this->conversation) {
            return;
        }

        $this->messages = Message::where('conversation_id', $this->conversation->id)
            ->with('sender')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => [
                'id'          => $m->id,
                'body'        => $m->body,
                'sender_id'   => $m->sender_id,
                'sender_name' => trim($m->sender->name.' '.$m->sender->lastname),
                'created_at'  => $m->created_at->toISOString(),
                'is_mine'     => $m->sender_id === auth()->id(),
            ])
            ->toArray();
    }

    public function sendMessage(): void
    {
        $this->validate(['newMessage' => 'required|string|max:5000']);

        if (! $this->conversation) {
            return;
        }

        $message = Message::create([
            'conversation_id' => $this->conversation->id,
            'sender_id'       => auth()->id(),
            'body'            => strip_tags($this->newMessage),
        ]);

        $this->conversation->update(['last_message_at' => now()]);

        MessageSent::dispatch($message->load('sender'));

        $this->messages[] = [
            'id'          => $message->id,
            'body'        => $message->body,
            'sender_id'   => $message->sender_id,
            'sender_name' => trim(auth()->user()->name.' '.auth()->user()->lastname),
            'created_at'  => $message->created_at->toISOString(),
            'is_mine'     => true,
        ];

        $this->newMessage = '';
        $this->dispatch('scroll-to-bottom');
    }

    public function sendBroadcast(): void
    {
        $this->validate([
            'broadcastMessage'   => 'required|string|max:5000',
            'broadcastBuildingId'=> 'required|exists:buildings,id',
        ]);

        $conversations = Conversation::where('building_id', $this->broadcastBuildingId)
            ->where('landlord_id', auth()->id())
            ->get();

        foreach ($conversations as $conversation) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id'       => auth()->id(),
                'body'            => strip_tags($this->broadcastMessage),
                'is_broadcast'    => true,
            ]);

            $conversation->update(['last_message_at' => now()]);

            MessageSent::dispatch($message->load('sender'));
        }

        $this->broadcastMessage = '';
        $this->broadcastBuildingId = null;

        Notification::make()
            ->title('Bericht verstuurd naar alle huurders')
            ->success()
            ->send();
    }

    public function markAsRead(): void
    {
        if (! $this->conversation) {
            return;
        }

        Message::where('conversation_id', $this->conversation->id)
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    #[On('message-received')]
    public function messageReceived(array $message): void
    {
        $this->messages[] = $message;
        $this->markAsRead();
        $this->dispatch('scroll-to-bottom');
    }

    #[On('conversation-selected')]
    public function conversationSelected(int $conversationId): void
    {
        $this->isBroadcastMode = false;
        $this->conversation = Conversation::findOrFail($conversationId);
        $this->loadMessages();
        $this->markAsRead();
    }

    #[On('broadcast-selected')]
    public function broadcastSelected(): void
    {
        $this->isBroadcastMode = true;
        $this->conversation = null;
        $this->messages = [];
        $this->broadcastMessage = '';
        $this->broadcastBuildingId = null;
    }

    public function render()
    {
        $buildings = $this->isBroadcastMode
            ? Building::where('landlord_id', auth()->id())->get()
            : collect();

        return view('livewire.chat.chat-window', compact('buildings'));
    }
}
```

### chat-window.blade.php — broadcast mode UI

- [ ] **Step 4: Add broadcast mode view to the blade**

The blade currently has `@if($conversation) ... @else ... @endif`. Add a third branch for broadcast mode. Replace the top-level `@if/$else/@endif`:

```blade
<div class="flex flex-col h-full">
    @if($isBroadcastMode)

        {{-- Broadcast header --}}
        <div class="flex items-center gap-3 px-3 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
            <button
                type="button"
                class="md:hidden mr-1 p-1 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                aria-label="Terug naar gesprekken"
                x-on:click="window.dispatchEvent(new CustomEvent('back-to-list'))"
            >
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </button>
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
                        wire:model="broadcastMessage"
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
            <button
                type="button"
                class="md:hidden mr-1 p-1 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                aria-label="Terug naar gesprekken"
                x-on:click="window.dispatchEvent(new CustomEvent('back-to-list'))"
            >
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </button>
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
            class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900/40"
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

        {{-- Input (see Task 3 for textarea version) --}}
        <div class="border-t border-gray-200 dark:border-gray-700 shrink-0">
            <form wire:submit="sendMessage" class="max-w-3xl mx-auto w-full flex items-end gap-3 px-4 sm:px-6 py-4">
                <textarea
                    wire:model="newMessage"
                    rows="1"
                    placeholder="Schrijf een bericht..."
                    class="flex-1 rounded-2xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none overflow-y-auto"
                    style="max-height: 160px;"
                    autocomplete="off"
                    x-data
                    x-on:input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 160) + 'px'"
                    x-on:keydown.enter.prevent="if (!$event.shiftKey) { $wire.sendMessage(); $el.style.height = 'auto' }"
                ></textarea>
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
```

- [ ] **Step 5: Verify broadcast tab in browser**

As a verhuurder: click "Alle huurders" in the conversation list. Confirm the chat window shows the broadcast form with a building selector and message textarea. Select a building, type a message, submit. Confirm a success notification appears. Verify clicking a regular conversation row afterwards shows that conversation normally.

---

## Task 3: Auto-expanding textarea (F4)

**Files:**
- Modify: `resources/views/livewire/chat/chat-window.blade.php`

**Note:** The textarea was already introduced in Task 2's blade rewrite. This task verifies correct behaviour and covers the huurder view, which also has an input that needs the same fix.

- [ ] **Step 1: Confirm the textarea input in the verhuurder chat window wraps long messages**

In the blade produced by Task 2, the `<textarea>` already has:
- `rows="1"` — single line default
- `resize-none` — prevents manual resize handle
- `style="max-height: 160px;"` — caps growth at ~6 lines
- `overflow-y-auto` — scrolls internally once capped
- Alpine `x-on:input` auto-resize handler
- `x-on:keydown.enter.prevent` — Enter sends, Shift+Enter inserts newline

If Task 2 is complete, no additional changes are needed for the verhuurder input. Confirm by typing a long message and verifying it wraps instead of overflowing.

- [ ] **Step 2: Verify huurder chat window also uses the textarea**

The huurder view uses `<livewire:chat.chat-window :conversationId="$tenantConversationId" />` which renders the same `chat-window.blade.php`. Since Task 2 replaced the full blade with the textarea, the huurder view inherits the fix automatically. Log in as a huurder and confirm the same textarea behaviour.

---

## Task 4: Responsive layout

**Files:**
- Modify: `resources/views/filament/dashboard/pages/chat.blade.php`
- Modify: `resources/views/livewire/chat/conversation-list.blade.php` (minor padding only — pinned entry was already added in Task 2)

**Note:** The back button and header padding changes were already included in the blade rewrite in Task 2. This task implements the page-level responsive grid.

- [ ] **Step 1: Replace the verhuurder grid in `chat.blade.php`**

Replace lines 3–12 (the verhuurder block) with:

```blade
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
                class="col-span-1 md:block border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm"
                :class="{ 'hidden': mobileView !== 'list' }"
            >
                <livewire:chat.conversation-list />
            </div>
            <div
                class="col-span-1 md:col-span-2 md:block border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm"
                :class="{ 'hidden': mobileView !== 'chat' }"
            >
                <livewire:chat.chat-window />
            </div>
        </div>
```

Note `@broadcast-selected.window` is also added so tapping "Alle huurders" on mobile switches to the chat panel.

- [ ] **Step 2: Verify responsive behaviour**

Open the chat page and resize the browser window to below 768px (or use DevTools device emulation). Confirm:
- Only the conversation list is visible initially.
- Tapping a conversation or "Alle huurders" hides the list and shows the chat panel.
- The back button (arrow icon, top-left of chat header) returns to the list.
- On desktop (≥768px) both panels are visible side-by-side as before.

---

## Self-review checklist

- [x] **F1 (debug/scroll):** Covered in Task 1 — `sender_name` added to broadcast, scroll uses `dispatch('scroll-to-bottom')` fired after re-render.
- [x] **F2 (auto-scroll):** Covered in Task 1 — `x-on:scroll-to-bottom.window` replaces `x-on:message-received.window`.
- [x] **F3 (broadcast tab):** Covered in Task 2 — pinned entry in list, broadcast mode in ChatWindow, `sendBroadcast()` method.
- [x] **F4 (textarea):** Covered in Task 2 (included in blade rewrite) + Task 3 (verification).
- [x] **Responsive layout:** Covered in Task 4.
- [x] **`broadcast-selected` wired on Alpine page wrapper:** Added in Task 4 Step 1.
- [x] **Echo leaves channel when broadcast selected:** Added `Livewire.on('broadcast-selected', ...)` in `@script` block in Task 2 Step 4.
- [x] **No duplicate Livewire instances:** Single `<livewire:chat.*>` per component, Alpine toggles visibility.
- [x] **Huurder view unchanged structurally:** huurder gets textarea fix (Task 3 Step 2) but no layout changes.
