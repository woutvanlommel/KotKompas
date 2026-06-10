# Chat Page Responsive Design

**Date:** 2026-06-10  
**Scope:** Improve responsiveness of the chat page for phones (≤640px) and tablets (768–1024px)

---

## Problem

The verhuurder (landlord) chat page uses a hard-coded `grid grid-cols-3` layout with no responsive breakpoints. On phones and tablets both panels (conversation list + chat window) are squeezed into a single row, making the UI unusable at small widths. The huurder (tenant) view is already full-width and is largely fine.

---

## Approach

Use Alpine.js view-state on the page wrapper to toggle between the conversation list and the chat window on small screens. On medium screens and up (`md:`), the existing two-column grid is preserved. A single set of Livewire components is rendered — Alpine adds/removes a `hidden` class to show or hide each panel on mobile, while `md:block` ensures both panels are always visible on desktop (Tailwind responsive variants take precedence over the `hidden` class). No new files, no Livewire backend changes.

---

## Changes

### 1. `resources/views/filament/dashboard/pages/chat.blade.php`

Replace the hard-coded `grid grid-cols-3` wrapper with:

```html
<div
    x-data="{ mobileView: 'list' }"
    @conversation-selected.window="mobileView = 'chat'"
    @back-to-list.window="mobileView = 'list'"
    class="grid grid-cols-1 md:grid-cols-3 gap-4 h-[calc(100vh-12rem)]"
>
    <!-- Conversation list -->
    <div
        class="col-span-1 md:block border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm"
        :class="{ 'hidden': mobileView !== 'list' }"
    >
        <livewire:chat.conversation-list />
    </div>

    <!-- Chat window -->
    <div
        class="col-span-1 md:col-span-2 md:block border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm"
        :class="{ 'hidden': mobileView !== 'chat' }"
    >
        <livewire:chat.chat-window />
    </div>
</div>
```

Key points:
- `grid-cols-1` on mobile → each panel fills the full width.
- `md:grid-cols-3` + `md:col-span-2` on desktop → original 1/3 + 2/3 split.
- `md:block` on each panel overrides Alpine's `hidden` class on desktop (Tailwind places responsive variants after base variants in the stylesheet, so `md:block` wins at ≥768px regardless of whether `hidden` is present).
- `@conversation-selected.window` — Livewire v3's `$this->dispatch()` fires a browser CustomEvent on `window`, so Alpine can listen to it directly.
- `@back-to-list.window` — fired by a new back button in the chat header (see below).

### 2. `resources/views/livewire/chat/chat-window.blade.php`

Two changes to the header block:

**Back button** — inserted left of the avatar, visible only on mobile (`md:hidden`). Dispatches a native browser event to `window` so the Alpine listener in `chat.blade.php` picks it up:

```html
<button
    type="button"
    class="md:hidden mr-1 p-1 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
    aria-label="Terug naar gesprekken"
    x-on:click="window.dispatchEvent(new CustomEvent('back-to-list'))"
>
    <x-heroicon-o-arrow-left class="w-5 h-5" />
</button>
```

**Header padding** — `px-6 py-4` → `px-3 sm:px-6 py-4` to give the back button room on narrow screens.

### 3. `resources/views/livewire/chat/conversation-list.blade.php`

Minor tweaks to the broadcast section:

- **Section padding**: `p-4` → `px-3 py-4 sm:px-4`.
- **Send button**: Replace the text label with an icon + visually-hidden text to prevent row overflow on narrow screens:
  ```html
  <button type="submit" class="rounded-lg bg-accent-500 px-3 py-2 text-white text-sm font-medium hover:bg-accent-600 transition shrink-0">
      <x-heroicon-s-paper-airplane class="w-4 h-4 sm:hidden" />
      <span class="hidden sm:inline">Verstuur</span>
  </button>
  ```

---

## What is not changing

- Message bubble widths (`max-w-[75%]`) — fine on all screen sizes.
- Message area padding (`px-4 sm:px-6`) — already responsive.
- Input row padding (`px-4 sm:px-6`) — already responsive.
- Huurder view — already full-width, no layout changes needed.
- All Livewire PHP classes — zero backend changes.

---

## Event flow (verhuurder mobile)

```
User taps conversation in list
  → Livewire ConversationList::selectConversation() fires
  → $this->dispatch('conversation-selected') → browser CustomEvent on window
  → Alpine @conversation-selected.window: mobileView = 'chat'
  → Chat panel becomes visible (hidden class removed), list panel hidden
  → Livewire ChatWindow::conversationSelected() also fires (via #[On]) → messages loaded

User taps back button in chat header
  → x-on:click: window.dispatchEvent(new CustomEvent('back-to-list'))
  → Alpine @back-to-list.window: mobileView = 'list'
  → List panel becomes visible, chat panel hidden
```

---

## Why no duplicate Livewire instances

Only one `<livewire:chat.conversation-list>` and one `<livewire:chat.chat-window>` are rendered. Alpine manages visibility with `hidden` / `md:block` on the wrapper divs, not by conditionally rendering separate components. This avoids duplicate Echo channel subscriptions and duplicate DB queries.
