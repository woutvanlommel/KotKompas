<?php

namespace App\Livewire;

use App\Models\Room;
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

        if ($this->isFavourited) {
            $user->favouriteRooms()->detach($this->roomId);
            $this->isFavourited = false;
        } elseif (Room::where('id', $this->roomId)->where('status', 'available')->exists()) {
            $user->favouriteRooms()->attach($this->roomId);
            $this->isFavourited = true;
        }
    }

    private function checkIsFavourited(): bool
    {
        $user = auth()->user();

        if (! $user || ! $user->hasRole('huurder')) {
            return false;
        }

        return $user->favouriteRooms()
            ->where('room_id', $this->roomId)
            ->where('status', 'available')
            ->exists();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.favourite-button');
    }
}
