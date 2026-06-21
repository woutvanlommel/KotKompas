<?php

namespace App\Livewire\Chat;

use App\Events\MessageSent;
use App\Models\Building;
use App\Models\Conversation;
use App\Models\Message;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rule;
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
            $this->conversation = Conversation::where('id', $conversationId)
                ->where(fn ($q) => $q
                    ->where('landlord_id', auth()->id())
                    ->orWhere('tenant_id', auth()->id())
                )
                ->firstOrFail();
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
        if ($this->conversation && auth()->id() === $this->conversation->tenant_id
            && $this->conversation->isTenantMessagingLocked()) {
            Notification::make()
                ->title('Berichten geblokkeerd')
                ->body('Je kunt geen berichten meer sturen omdat je huurperiode meer dan '.config('chat.tenant_messaging_window_days').' dagen geleden is afgelopen.')
                ->danger()
                ->send();

            return;
        }

        $this->validate(['newMessage' => 'required|string|max:5000']);

        if (! $this->conversation) {
            return;
        }

        if ($this->containsBlacklistedWord($this->newMessage)) {
            Notification::make()
                ->title('Ongepaste taal')
                ->body('Je bericht bevat woorden die niet zijn toegestaan.')
                ->danger()
                ->send();

            return;
        }

        // A landlord message to a grace-expired tenant (re)opens a temporary
        // reply window, refreshed on every message so the tenant isn't locked
        // out mid-conversation.
        if (auth()->id() === $this->conversation->landlord_id
            && $this->conversation->tenantGracePeriodExpired()) {
            $this->conversation->update([
                'tenant_unlocked_until' => now()->addHours(config('chat.tenant_reply_window_hours')),
            ]);
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
        $this->dispatch('scroll-to-bottom');
    }

    public function sendBroadcast(): void
    {
        $this->validate([
            'broadcastMessage' => 'required|string|max:5000',
            'broadcastBuildingId' => ['required', Rule::exists('buildings', 'id')->where('landlord_id', auth()->id())],
        ]);

        if ($this->containsBlacklistedWord($this->broadcastMessage)) {
            Notification::make()
                ->title('Ongepaste taal')
                ->body('Je bericht bevat woorden die niet zijn toegestaan.')
                ->danger()
                ->send();

            return;
        }

        $conversations = Conversation::where('building_id', $this->broadcastBuildingId)
            ->where('landlord_id', auth()->id())
            ->get();

        foreach ($conversations as $conversation) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => auth()->id(),
                'body' => strip_tags($this->broadcastMessage),
                'is_broadcast' => true,
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

    private function containsBlacklistedWord(string $text): bool
    {
        foreach (config('chat.blacklist', []) as $word) {
            if (preg_match('/\b'.preg_quote($word, '/').'\b/iu', $text)) {
                return true;
            }
        }

        return false;
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
        $this->conversation = Conversation::where('id', $conversationId)
            ->where(fn ($q) => $q
                ->where('landlord_id', auth()->id())
                ->orWhere('tenant_id', auth()->id())
            )
            ->firstOrFail();
        $this->loadMessages();
        $this->markAsRead();
        $this->dispatch('scroll-to-bottom');
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

        $isTenant = $this->conversation && auth()->id() === $this->conversation->tenant_id;

        $isLocked = $isTenant && $this->conversation->isTenantMessagingLocked();

        // Tenant is past their grace window but within a landlord-granted pass.
        $inReplyWindow = $isTenant
            && $this->conversation->tenant_unlocked_until
            && $this->conversation->tenant_unlocked_until->isFuture()
            && $this->conversation->tenantGracePeriodExpired();

        return view('livewire.chat.chat-window', compact('buildings', 'isLocked', 'inReplyWindow'));
    }
}
