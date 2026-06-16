<?php

namespace App\Filament\Dashboard\Pages;

use App\Models\Room;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;

class Favourites extends Page
{
    protected string $view = 'filament.dashboard.pages.favourites';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?string $navigationLabel = 'Favorieten';

    protected static ?string $title = 'Mijn favorieten';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('huurder') ?? false;
    }

    /** @return Collection<int, Room> */
    public function getFavouriteRooms(): Collection
    {
        return auth()->user()
            ->favouriteRooms()
            ->with(['building', 'media'])
            ->latest('room_user_favourites.created_at')
            ->get();
    }
}
