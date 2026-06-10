<?php

namespace App\Livewire\Chat;

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

        return view('livewire.chat.conversation-list', compact('conversations'));
    }
}
