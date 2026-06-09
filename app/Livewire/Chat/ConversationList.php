<?php

namespace App\Livewire\Chat;

use App\Events\MessageSent;
use App\Models\Building;
use App\Models\Conversation;
use App\Models\Message;
use Filament\Notifications\Notification;
use Livewire\Component;

class ConversationList extends Component
{
    public ?int $activeConversationId = null;
    public ?int $selectedBuildingId = null;
    public string $broadcastMessage = '';

    public function selectConversation(int $conversationId): void
    {
        $this->activeConversationId = $conversationId;
        $this->dispatch('conversation-selected', conversationId: $conversationId);
    }

    public function sendToAll(): void
    {
        $this->validate([
            'broadcastMessage'  => 'required|string|max:5000',
            'selectedBuildingId' => 'required|exists:buildings,id',
        ]);

        $conversations = Conversation::where('building_id', $this->selectedBuildingId)
            ->where('landlord_id', auth()->id())
            ->get();

        foreach ($conversations as $conversation) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id'       => auth()->id(),
                'body'            => $this->broadcastMessage,
                'is_broadcast'    => true,
            ]);

            $conversation->update(['last_message_at' => now()]);

            MessageSent::dispatch($message->load('sender'));
        }

        $this->broadcastMessage = '';
        $this->selectedBuildingId = null;

        Notification::make()
            ->title('Bericht verstuurd naar alle huurders')
            ->success()
            ->send();
    }

    public function render()
    {
        $conversations = Conversation::where('landlord_id', auth()->id())
            ->with(['tenant', 'building', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(fn ($c) => [
                'id'             => $c->id,
                'tenant_name'    => $c->tenant->name.' '.$c->tenant->lastname,
                'building_name'  => $c->building->name,
                'last_message'   => $c->messages->first()?->body,
                'last_message_at' => $c->last_message_at?->diffForHumans(),
                'unread'         => $c->unreadFor(auth()->id()),
            ]);

        $buildings = Building::where('landlord_id', auth()->id())->get();

        return view('livewire.chat.conversation-list', compact('conversations', 'buildings'));
    }
}
