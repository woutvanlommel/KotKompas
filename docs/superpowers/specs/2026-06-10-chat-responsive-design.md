# Chat Page Responsive Design

**Date:** 2026-06-10  
**Scope:** Improve responsiveness of the chat page for phones (≤640px) and tablets (768–1024px)

---

## Problem

The verhuurder (landlord) chat page uses a hard-coded `grid grid-cols-3` layout with no responsive breakpoints. On phones and tablets both panels (conversation list + chat window) are squeezed into a single row, making the UI unusable at small widths. The huurder (tenant) view is already full-width and is largely fine.

---

## Approach

Use Alpine.js view-state on the page wrapper to toggle between the conversation list and the chat window on small screens. On medium screens and up (`md:`), the existing two-column grid is preserved. No new files, no Livewire backend changes.

---

## Changes

### 1. `resources/views/filament/dashboard/pages/chat.blade.php`

- Add `x-data="{ mobileView: 'list' }"` to the verhuurder wrapper.
- The existing desktop grid becomes `hidden md:grid grid-cols-3 gap-4 h-[calc(100vh-12rem)]` — hidden on mobile, shown on md+.
- Add two full-width mobile panels below it (each `block md:hidden h-[calc(100vh-12rem)]`):
  - **List panel**: shown when `mobileView === 'list'`, contains `<livewire:chat.conversation-list />`.
  - **Chat panel**: shown when `mobileView === 'chat'`, contains `<livewire:chat.chat-window />`.
- Add Alpine window event listeners on the wrapper:
  - `@conversation-selected.window="mobileView = 'chat'"` — fires when verhuurder taps a conversation.
  - `@back-to-list.window="mobileView = 'list'"` — fires when the back button in the chat window is tapped.

### 2. `resources/views/livewire/chat/chat-window.blade.php`

- **Back button**: inserted in the header, left of the avatar, visible only on mobile (`md:hidden`). Uses `x-heroicon-o-arrow-left`. On click dispatches `new CustomEvent('back-to-list')` to `window`.
- **Header padding**: `px-6 py-4` → `px-3 sm:px-6 py-4` to accommodate the back button on narrow screens.

### 3. `resources/views/livewire/chat/conversation-list.blade.php`

- **Broadcast section padding**: `p-4` → `px-3 py-4 sm:px-4` for tighter fit on phones.
- **Broadcast send button**: label "Verstuur" hidden on small screens, icon-only fallback: `<span class="hidden sm:inline">Verstuur</span>` with a paper-airplane icon always visible, so the row doesn't overflow on narrow widths.

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
  → Livewire fires `conversation-selected` window event
  → Alpine: mobileView = 'chat'
  → Chat panel becomes visible, list panel hidden

User taps back button in chat header
  → JS: window.dispatchEvent(new CustomEvent('back-to-list'))
  → Alpine: mobileView = 'list'
  → List panel becomes visible, chat panel hidden
```
