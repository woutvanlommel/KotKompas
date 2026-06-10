<?php

namespace App\Livewire\Chat;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatWindow extends Component
{
    public ?Conversation $conversation = null;

    public string $newMessage = '';

    public array $messages = [];

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
                'id' => $m->id,
                'body' => $m->body,
                'sender_id' => $m->sender_id,
                'sender_name' => trim($m->sender->name.' '.$m->sender->lastname),
                'created_at' => $m->created_at->toISOString(),
                'is_mine' => $m->sender_id === auth()->id(),
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
            'sender_id' => auth()->id(),
            'body' => strip_tags($this->newMessage),
        ]);

        $this->conversation->update(['last_message_at' => now()]);

        MessageSent::dispatch($message->load('sender'));

        $this->messages[] = [
            'id' => $message->id,
            'body' => $message->body,
            'sender_id' => $message->sender_id,
            'sender_name' => trim(auth()->user()->name.' '.auth()->user()->lastname),
            'created_at' => $message->created_at->toISOString(),
            'is_mine' => true,
        ];

        $this->newMessage = '';
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

    /**
     * Called from the Echo listener in the blade via $wire.dispatch().
     * Appends the incoming message to the local array without a full re-render.
     */
    #[On('message-received')]
    public function messageReceived(array $message): void
    {
        $this->messages[] = $message;
        $this->markAsRead();
    }

    /**
     * Fired by ConversationList when the verhuurder selects a different conversation.
     */
    #[On('conversation-selected')]
    public function conversationSelected(int $conversationId): void
    {
        $this->conversation = Conversation::findOrFail($conversationId);
        $this->loadMessages();
        $this->markAsRead();
    }

    public function render()
    {
        return view('livewire.chat.chat-window');
    }
}
