# Chat Page — Responsive Design & Functional Improvements

**Date:** 2026-06-10  
**Scope:** Responsive layout for phones/tablets + four functional improvements to the chat components

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

---

## Functional Improvements

### F1 — Debug & fix: messages not appearing without a page refresh

**Symptom:** When the other party sends a message, it often does not appear in the chat window until the page is refreshed.

**Suspected cause:** The Echo listener in `chat-window.blade.php` calls `$wire.dispatch('message-received', { message })`, which triggers the PHP `messageReceived()` method that appends to `$this->messages`. However, Livewire may not be re-rendering the component after the array append, or the Echo subscription may be silently failing.

**Debugging approach:** A dedicated debugging subagent should:
1. Verify Echo is connecting and receiving events (check console errors, channel auth, `MessageSent` event structure).
2. Verify `$wire.dispatch('message-received', ...)` actually calls the PHP method (add temporary logging).
3. Verify Livewire re-renders after `$this->messages[] = $message` — if not, switch to `$this->dispatch` followed by a full `loadMessages()` reload, or use `$this->js(...)` to trigger a client-side scroll after re-render.
4. Fix root cause and confirm messages appear in real time without a refresh.

### F2 — Auto-scroll to bottom on new message

**Current state:** `x-on:message-received.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)"` in the messages container. This fires when the Livewire event reaches the browser window, but Livewire's DOM update may not yet be complete when the scroll runs, so the new message is outside the scroll area.

**Fix:** After F1's root cause is established, ensure the scroll fires reliably AFTER the DOM update. Options in priority order:
- If Livewire re-renders the component, use `x-on:livewire:updated` scoped to the messages container, guarded by a flag set when a new message arrives.
- Or dispatch a dedicated `scroll-to-bottom` browser event from inside `messageReceived()` PHP method using `$this->js("document.dispatchEvent(new CustomEvent('scroll-to-bottom'))")` — this fires after the Livewire re-render cycle.

### F3 — "Stuur naar alle huurders" becomes a pinned broadcast tab

**Current state:** A broadcast form (building selector + message input + send button) sits at the top of the conversation list sidebar. It takes up permanent space and is visually disconnected from the chat UX.

**New behaviour:**
- Remove the broadcast form section from the top of `conversation-list.blade.php`.
- Add a pinned entry at the very top of the conversation list (above the scrollable list, visually separated by a thin divider below it). This entry looks like a regular conversation row but uses a group/megaphone icon and the label "Alle huurders". It is always present regardless of the conversation list.
- When this pinned entry is clicked, `selectConversation` is called with a special sentinel value (e.g. `'broadcast'` or `0`).
- In `ChatWindow`, detect this sentinel value and render a **broadcast mode** view instead of a regular conversation: show a building selector dropdown and a message textarea with a send button. On submit, call the existing `sendToAll` logic (move it from `ConversationList` to `ChatWindow`, or keep it in `ConversationList` and dispatch an event).
- The back button (responsive) and desktop panel layout work identically — broadcast mode is just another "active view" in the chat window.

**Data flow:**
```
User clicks "Alle huurders" pinned entry
  → ConversationList::selectConversation('broadcast')
  → dispatch('conversation-selected', conversationId: 'broadcast')
  → ChatWindow receives event, sets $isBroadcastMode = true, $conversation = null
  → Chat window renders broadcast UI

User submits broadcast form
  → ChatWindow::sendBroadcast(buildingId, message)
  → Same logic as current ConversationList::sendToAll()
```

**Model/PHP changes needed:**
- `ConversationList.php`: remove `sendToAll`, `broadcastMessage`, `selectedBuildingId`. Add pinned "broadcast" entry to the rendered conversations data (or render it statically in the blade).
- `ChatWindow.php`: add `$isBroadcastMode`, `$broadcastBuildingId`, `$broadcastMessage` properties. Add `sendBroadcast()` method with the same logic as the removed `sendToAll()`. Load buildings list in `mount()` or `render()` for broadcast mode.
- `conversation-selected` event payload: `conversationId` is currently `int`; update type to `int|string` and handle `'broadcast'` sentinel.

### F4 — Auto-expanding textarea for message input

**Current state:** `<input type="text">` for the message input — text overflows horizontally without wrapping.

**Fix:** Replace the `<input>` with a `<textarea>` that auto-expands vertically as the user types, capped at a max height.

- Use Alpine.js to resize: `x-on:input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 160) + 'px'"`.
- Default height: single line (`rows="1"`, `resize-none`).
- Max height: ~160px (approx 6 lines) — beyond this the textarea scrolls internally.
- Submit behaviour: **Enter** sends, **Shift+Enter** inserts a newline. Handle with `@keydown.enter.prevent="if (!$event.shiftKey) { $wire.sendMessage(); $el.style.height = 'auto' }"`.
- After `sendMessage()` the `newMessage` model clears — also reset height via the keydown handler.
- The `wire:submit` form handler continues to work for button-click submission.
- Styling: match existing `rounded-full` input — switch to `rounded-2xl` since `rounded-full` looks odd on multi-line. Keep `border`, `bg`, `px-5 py-2.5`, `text-sm`, `focus:ring` classes. Add `overflow-y-auto` so internal scroll works when capped.

---

## Implementation order

1. **F1 (debug messages)** — must be first; F2 depends on knowing the correct re-render hook.
2. **F2 (auto-scroll)** — depends on F1's outcome.
3. **F3 (broadcast tab)** — independent of F1/F2.
4. **F4 (textarea)** — independent, purely frontend.
5. **Responsive layout** — independent, purely frontend.
