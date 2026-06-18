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

        $owned = Building::where('id', $value)
            ->where('landlord_id', auth()->id())
            ->exists();

        if (! $owned) {
            return;
        }

        $this->availableTenants = Room::where('building_id', $value)
            ->whereNotNull('tenant_id')
            ->with('tenant')
            ->get()
            ->map(fn (Room $room) => [
                'id' => (int) $room->tenant_id,
                'name' => $room->tenant
                    ? trim($room->tenant->getAttribute('name').' '.$room->tenant->getAttribute('lastname'))
                    : '',
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

        $validRoom = Room::where('building_id', $this->filterBuildingId)
            ->where('tenant_id', $this->filterTenantId)
            ->whereHas('building', fn ($q) => $q->where('landlord_id', auth()->id()))
            ->exists();

        if (! $validRoom) {
            return;
        }

        $conversation = Conversation::firstOrCreate([
            'tenant_id' => $this->filterTenantId,
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
        $isLandlord = auth()->user()?->hasRole('verhuurder') ?? false;

        $conversations = Conversation::where($isLandlord ? 'landlord_id' : 'tenant_id', auth()->id())
            ->with(['tenant', 'landlord', 'building', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->withCount(['messages as unread_count' => fn ($q) => $q
                ->whereNull('read_at')
                ->where('sender_id', '!=', auth()->id()),
            ])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function (Conversation $c) use ($isLandlord) {
                // The list always names the *other* party in the conversation.
                $counterpart = $isLandlord ? $c->tenant : $c->landlord;

                return [
                    'id' => $c->id,
                    'name' => trim($counterpart->name.' '.$counterpart->lastname),
                    'building_name' => $c->building->name,
                    'last_message' => $c->messages->first()?->body,
                    'last_message_at' => $c->last_message_at?->diffForHumans(),
                    'unread' => (int) $c->getAttribute('unread_count'),
                ];
            });

        // The broadcast tool + tenant finder are landlord-only.
        $buildings = $isLandlord
            ? Building::where('landlord_id', auth()->id())->get()
            : collect();

        return view('livewire.chat.conversation-list', compact('conversations', 'buildings', 'isLandlord'));
    }
}
