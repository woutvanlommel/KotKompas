# Verhuurder Tenant Finder Design

**Date:** 2026-06-10  
**Scope:** Add cascading building → huurder dropdowns to the conversation list so a verhuurder can open (or create) a conversation with any tenant, including those with no prior messages.

---

## Problem

A verhuurder can only start or find conversations by scrolling the conversation list. Tenants who have never sent a message don't appear there at all. There is no way to initiate a conversation with a specific tenant from the sidebar.

---

## Approach

Add two cascading selects to the top of the conversation list sidebar (below the "Alle huurders" pinned entry, above the scrollable list). Selecting a building populates a huurder dropdown. Selecting a huurder immediately runs `Conversation::firstOrCreate()` and dispatches `conversation-selected` — opening the conversation in the chat window and ensuring it appears in the list. Both dropdowns reset after use. Verhuurder-only feature.

---

## Changes

### `app/Livewire/Chat/ConversationList.php`

**New properties:**
```php
public ?int $filterBuildingId = null;
public ?int $filterTenantId = null;
public array $availableTenants = [];
```

**New lifecycle hook — `updatedFilterBuildingId`:**
Fires automatically when `$filterBuildingId` changes (Livewire `updated*` convention). Resets `$filterTenantId = null` and `$availableTenants = []`, then if a value is set:
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

**New lifecycle hook — `updatedFilterTenantId`:**
Fires when `$filterTenantId` changes. Calls `startConversation()` if a value is set:
```php
public function updatedFilterTenantId(?int $value): void
{
    if ($value) {
        $this->startConversation();
    }
}
```

**New method — `startConversation()`:**
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

**`render()` — restore buildings query:**
Buildings were removed from `render()` in the previous task (T2). Add them back, needed for the filter dropdowns:
```php
$buildings = Building::where('landlord_id', auth()->id())->get();
return view('livewire.chat.conversation-list', compact('conversations', 'buildings'));
```
Also add `use App\Models\Building;` and `use App\Models\Room;` imports.

---

### `resources/views/livewire/chat/conversation-list.blade.php`

Add a filter section between the pinned "Alle huurders" entry and the scrollable conversation list:

```blade
{{-- Tenant finder --}}
<div class="px-3 py-3 border-b border-gray-200 dark:border-gray-700 space-y-2">
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

---

## Data flow

```
Verhuurder selects building
  → wire:model.live updates $filterBuildingId
  → updatedFilterBuildingId() loads Room tenants for that building into $availableTenants
  → Livewire re-renders: huurder select appears

Verhuurder selects huurder
  → wire:model.live updates $filterTenantId
  → updatedFilterTenantId() → startConversation()
  → Conversation::firstOrCreate() → conversation guaranteed to exist
  → $activeConversationId set, filter state cleared
  → dispatch('conversation-selected') → ChatWindow loads conversation
  → Conversation appears in list (re-render includes it via query)
```

---

## What is not changing

- `ChatWindow.php` — no changes needed; `conversation-selected` event already handled.
- `chat.blade.php` — no changes.
- `chat-window.blade.php` — no changes.
- Huurder view — unaffected; this section only renders for verhuurders.

---

## Edge cases

- **Tenant has no room in building:** Cannot appear — query filters by `Room.building_id` and `tenant_id IS NOT NULL`.
- **Conversation already exists:** `firstOrCreate` returns the existing one; verhuurder lands on the existing conversation normally.
- **Building with no tenants:** `$availableTenants` is `[]`, huurder select stays hidden, nothing opens.
