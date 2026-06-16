<?php

namespace App\Filament\Dashboard\Widgets;

use App\Filament\Dashboard\Pages\Chat;
use App\Models\Message;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class MessagesStats extends StatsOverviewWidget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Berichten';

    public static function canView(): bool
    {
        return Chat::canAccess();
    }

    protected function getStats(): array
    {
        $unread = $this->unreadCount();

        return [
            Stat::make('Ongelezen berichten', $unread)
                ->description($unread > 0 ? 'Open je chat' : 'Je bent helemaal bij')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($unread > 0 ? 'warning' : 'gray')
                ->url(Chat::getUrl()),
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
