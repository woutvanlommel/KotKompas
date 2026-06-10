# Verhuurder Tenant Finder Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add cascading building → huurder dropdowns to the conversation list so a verhuurder can open (or create) a conversation with any tenant, including those with no prior messages.

**Architecture:** Two Livewire `updated*` lifecycle hooks in `ConversationList` handle cascading state — selecting a building loads its tenants, selecting a tenant calls `Conversation::firstOrCreate()` and dispatches `conversation-selected`. Buildings are restored to `render()` for the dropdowns. The blade gets a compact filter section between the pinned broadcast entry and the scrollable list.

**Tech Stack:** Laravel 11, Livewire v3, Tailwind CSS v3.

---

## File Map

| File | Change |
|------|--------|
| `app/Livewire/Chat/ConversationList.php` | Add 3 properties, 2 lifecycle hooks, 1 method, restore buildings query |
| `resources/views/livewire/chat/conversation-list.blade.php` | Add tenant finder section between broadcast entry and list |

---

### Task 1: Add tenant finder to ConversationList

**Files:**
- Modify: `app/Livewire/Chat/ConversationList.php`

- [ ] **Step 1: Add imports**

At the top of `app/Livewire/Chat/ConversationList.php`, the current imports are:

```php
use App\Models\Conversation;
use Livewire\Component;
```

Replace with:

```php
use App\Models\Building;
use App\Models\Conversation;
use App\Models\Room;
use Livewire\Component;
```

- [ ] **Step 2: Add the three filter properties**

After `public bool $broadcastActive = false;` (currently line 12), add:

```php
public ?int $filterBuildingId = null;

public ?int $filterTenantId = null;

public array $availableTenants = [];
```

- [ ] **Step 3: Add the `updatedFilterBuildingId` lifecycle hook**

Add after the `selectBroadcast()` method (after line 26):

```php
public function updatedFilterBuildingId(?int $value): void
{
    $this->filterTenantId = null;
    $this->availableTenants = [];

    if (! $value) {
        return;
    }

    $this->availableTenants = Room::where('building_id', $value)
        ->whereNotNull('tenant_id')
        ->with('tenant')
        ->get()
        ->map(fn ($room) => [
            'id'   => $room->tenant->id,
            'name' => trim($room->tenant->name.' '.$room->tenant->lastname),
        ])
        ->toArray();
}
```

- [ ] **Step 4: Add the `updatedFilterTenantId` lifecycle hook**

Add directly after `updatedFilterBuildingId`:

```php
public function updatedFilterTenantId(?int $value): void
{
    if ($value) {
        $this->startConversation();
    }
}
```

- [ ] **Step 5: Add the `startConversation` method**

Add directly after `updatedFilterTenantId`:

```php
public function startConversation(): void
{
    if (! $this->filterTenantId || ! $this->filterBuildingId) {
        return;
    }

    $conversation = Conversation::firstOrCreate([
        'tenant_id'   => $this->filterTenantId,
        'landlord_id' => auth()->id(),
        'building_id' => $this->filterBuildingId,
    ]);

    $this->activeConversationId = $conversation->id;
    $this->broadcastActive = false;

    $this->filterBuildingId = null;
    $this->filterTenantId = null;
    $this->availableTenants = [];

    $this->dispatch('conversation-selected', conversationId: $conversation->id);
}
```

- [ ] **Step 6: Restore `$buildings` in `render()`**

The current `render()` method ends with:

```php
return view('livewire.chat.conversation-list', compact('conversations'));
```

Replace the full `render()` method with:

```php
public function render()
{
    $conversations = Conversation::where('landlord_id', auth()->id())
        ->with(['tenant', 'building', 'messages' => fn ($q) => $q->latest()->limit(1)])
        ->withCount(['messages as unread_count' => fn ($q) => $q
            ->whereNull('read_at')
            ->where('sender_id', '!=', auth()->id())
        ])
        ->orderByDesc('last_message_at')
        ->get()
        ->map(fn (Conversation $c) => [
            'id'             => $c->id,
            'tenant_name'    => trim($c->tenant->name.' '.$c->tenant->lastname),
            'building_name'  => $c->building->name,
            'last_message'   => $c->messages->first()?->body,
            'last_message_at'=> $c->last_message_at?->diffForHumans(),
            'unread'         => $c->unread_count,
        ]);

    $buildings = Building::where('landlord_id', auth()->id())->get();

    return view('livewire.chat.conversation-list', compact('conversations', 'buildings'));
}
```

- [ ] **Step 7: Verify the full file looks correct**

The final `app/Livewire/Chat/ConversationList.php` should look like:

```php
<?php

namespace App\Livewire\Chat;

use App\Models\Building;
use App\Models\Conversation;
use App\Models\Room;
use Livewire\Component;

class ConversationList extends Component
{
    public ?int $activeConversationId = null;

    public bool $broadcastActive = false;

    public ?int $filterBuildingId = null;

    public ?int $filterTenantId = null;

    public array $availableTenants = [];

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

    public function updatedFilterBuildingId(?int $value): void
    {
        $this->filterTenantId = null;
        $this->availableTenants = [];

        if (! $value) {
            return;
        }

        $this->availableTenants = Room::where('building_id', $value)
            ->whereNotNull('tenant_id')
            ->with('tenant')
            ->get()
            ->map(fn ($room) => [
                'id'   => $room->tenant->id,
                'name' => trim($room->tenant->name.' '.$room->tenant->lastname),
            ])
            ->toArray();
    }

    public function updatedFilterTenantId(?int $value): void
    {
        if ($value) {
            $this->startConversation();
        }
    }

    public function startConversation(): void
    {
        if (! $this->filterTenantId || ! $this->filterBuildingId) {
            return;
        }

        $conversation = Conversation::firstOrCreate([
            'tenant_id'   => $this->filterTenantId,
            'landlord_id' => auth()->id(),
            'building_id' => $this->filterBuildingId,
        ]);

        $this->activeConversationId = $conversation->id;
        $this->broadcastActive = false;

        $this->filterBuildingId = null;
        $this->filterTenantId = null;
        $this->availableTenants = [];

        $this->dispatch('conversation-selected', conversationId: $conversation->id);
    }

    public function render()
    {
        $conversations = Conversation::where('landlord_id', auth()->id())
            ->with(['tenant', 'building', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->withCount(['messages as unread_count' => fn ($q) => $q
                ->whereNull('read_at')
                ->where('sender_id', '!=', auth()->id())
            ])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(fn (Conversation $c) => [
                'id'             => $c->id,
                'tenant_name'    => trim($c->tenant->name.' '.$c->tenant->lastname),
                'building_name'  => $c->building->name,
                'last_message'   => $c->messages->first()?->body,
                'last_message_at'=> $c->last_message_at?->diffForHumans(),
                'unread'         => $c->unread_count,
            ]);

        $buildings = Building::where('landlord_id', auth()->id())->get();

        return view('livewire.chat.conversation-list', compact('conversations', 'buildings'));
    }
}
```

---

### Task 2: Add tenant finder UI to the blade

**Files:**
- Modify: `resources/views/livewire/chat/conversation-list.blade.php`

- [ ] **Step 1: Insert the tenant finder section**

In `resources/views/livewire/chat/conversation-list.blade.php`, insert a new section between the closing `</div>` of the pinned broadcast entry (line 19) and the `{{-- Conversation list --}}` comment (line 21).

The section to insert:

```blade
    {{-- Tenant finder --}}
    <div class="px-3 py-3 border-b border-gray-200 dark:border-gray-700 space-y-2 shrink-0">
        <select
            wire:model.live="filterBuildingId"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
        >
            <option value="">Selecteer gebouw...</option>
            @foreach($buildings as $building)
                <option value="{{ $building->id }}">{{ $building->name }}</option>
            @endforeach
        </select>

        @if($availableTenants)
            <select
                wire:model.live="filterTenantId"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
                <option value="">Selecteer huurder...</option>
                @foreach($availableTenants as $tenant)
                    <option value="{{ $tenant['id'] }}">{{ $tenant['name'] }}</option>
                @endforeach
            </select>
        @endif
    </div>

```

- [ ] **Step 2: Verify the full blade file looks correct**

The final `resources/views/livewire/chat/conversation-list.blade.php` should look like:

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

    {{-- Tenant finder --}}
    <div class="px-3 py-3 border-b border-gray-200 dark:border-gray-700 space-y-2 shrink-0">
        <select
            wire:model.live="filterBuildingId"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
        >
            <option value="">Selecteer gebouw...</option>
            @foreach($buildings as $building)
                <option value="{{ $building->id }}">{{ $building->name }}</option>
            @endforeach
        </select>

        @if($availableTenants)
            <select
                wire:model.live="filterTenantId"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
                <option value="">Selecteer huurder...</option>
                @foreach($availableTenants as $tenant)
                    <option value="{{ $tenant['id'] }}">{{ $tenant['name'] }}</option>
                @endforeach
            </select>
        @endif
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

- [ ] **Step 3: Verify in browser**

Log in as a verhuurder. In the conversation list sidebar:
- Confirm the building dropdown appears below "Alle huurders"
- Select a building → confirm the huurder dropdown appears
- Select a huurder who has an existing conversation → confirm that conversation opens in the chat window and is highlighted in the list
- Select a huurder who has NO existing conversation → confirm a conversation is created, opens in the chat window, and the tenant now appears in the conversation list
- Select a building with no tenants → confirm only the building dropdown shows (huurder dropdown stays hidden)
- After opening a conversation via the finder → confirm both dropdowns reset to their placeholder state

---

## Self-review

- [x] **Spec coverage:** All requirements covered — cascading dropdowns (Task 1+2), `firstOrCreate` (Task 1 Step 5), conversation opens in chat window via `conversation-selected` dispatch (Task 1 Step 5), new conversation appears in list via `render()` re-query (Task 1 Step 6).
- [x] **No placeholders:** All steps contain complete code.
- [x] **Type consistency:** `$filterBuildingId: ?int`, `$filterTenantId: ?int`, `$availableTenants: array` used consistently across all steps. `$tenant['id']` and `$tenant['name']` in blade match the map shape in `updatedFilterBuildingId`.
- [x] **Buildings query:** `Building` and `Room` imports added in Task 1 Step 1, used in Task 1 Steps 3 and 6.
- [x] **`updatedFilterBuildingId` takes `?int`:** Livewire passes the cast value of the model property. Since `$filterBuildingId` is `?int`, an empty string selection casts to `null`, triggering the early return. Correct.
