<?php

namespace App\Filament\Dashboard\Widgets;

use App\Filament\Dashboard\Pages\Chat;
use App\Models\Message;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class MessagesStats extends Widget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.dashboard.widgets.messages-stats';

    public static function canView(): bool
    {
        return Chat::canAccess();
    }

    protected function getViewData(): array
    {
        return [
            'unread' => $this->unreadCount(),
            'chatUrl' => Chat::getUrl(),
        ];
    }

    protected function unreadCount(): int
    {
        $userId = auth()->id();

        return Message::query()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $userId)
            ->whereHas('conversation', fn (Builder $query) => $query
                ->where(fn (Builder $query) => $query
                    ->where('tenant_id', $userId)
                    ->orWhere('landlord_id', $userId)))
            ->count();
    }
}
