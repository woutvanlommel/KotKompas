<?php

namespace App\Filament\Dashboard\Pages;

use App\Models\Conversation;
use App\Models\Room;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Chat extends Page
{
    protected string $view = 'filament.dashboard.pages.chat';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Chat';

    protected static ?string $title = 'Berichten';

    protected static ?int $navigationSort = 2;

    // Huurder: ID of their single conversation, null if not yet assigned to a room
    public ?int $tenantConversationId = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['huurder', 'verhuurder']) ?? false;
    }

    public function mount(): void
    {
        // For huurders: find or create their conversation based on their assigned room.
        // A huurder has one room → one building → one landlord → one conversation.
        if (auth()->user()->hasRole('huurder')) {
            $room = Room::where('tenant_id', auth()->id())
                ->with('building')
                ->first();

            if ($room) {
                $conversation = Conversation::firstOrCreate([
                    'tenant_id' => auth()->id(),
                    'landlord_id' => $room->building->landlord_id,
                    'building_id' => $room->building_id,
                ]);

                $this->tenantConversationId = $conversation->id;
            }
        }
    }
}
