<?php

namespace App\Livewire;

use App\Models\Room;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class FavouriteButton extends Component
{
    #[Locked]
    public int $roomId;

    public bool $isFavourited = false;

    public function mount(int $roomId): void
    {
        $this->roomId = $roomId;
        $this->isFavourited = $this->checkIsFavourited();
    }

    public function toggle(): void
    {
        $user = auth()->user();

        if (! $user) {
            $this->redirect(route('filament.dashboard.auth.login'), navigate: true);

            return;
        }

        if (! $user->hasRole('huurder')) {
            return;
        }

        // Derive the real state server-side — never trust $this->isFavourited,
        // which is client-controllable. Detach is allowed regardless of status
        // so stale favourites (room no longer available) can still be removed.
        $alreadyFavourited = $user->favouriteRooms()
            ->where('room_id', $this->roomId)
            ->exists();

        if ($alreadyFavourited) {
            $user->favouriteRooms()->detach($this->roomId);
            $this->isFavourited = false;
        } elseif (Room::where('id', $this->roomId)->where('status', 'available')->exists()) {
            // syncWithoutDetaching is idempotent — avoids a unique-constraint
            // violation if the row somehow already exists.
            $user->favouriteRooms()->syncWithoutDetaching([$this->roomId]);
            $this->isFavourited = true;
        }
    }

    private function checkIsFavourited(): bool
    {
        $user = auth()->user();

        if (! $user || ! $user->hasRole('huurder')) {
            return false;
        }

        return $user->availableFavouriteRoomIds()->contains($this->roomId);
    }

    public function render(): View
    {
        return view('livewire.favourite-button');
    }
}
