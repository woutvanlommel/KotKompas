<?php

namespace App\Livewire\Room;

use App\Events\MessageSent;
use App\Exceptions\InsufficientCreditsException;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Room;
use App\Services\CreditService;
use App\Services\LandlordUnlockService;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LandlordCard extends Component
{
    #[Locked]
    public int $roomId;

    public bool $showForm = false;

    #[Validate('required|string|max:5000')]
    public string $body = '';

    // Vluchtige melding (verdwijnt vanzelf in de UI).
    public ?string $flashType = null;

    public ?string $flashMessage = null;

    public int $flashTick = 0;

    public function mount(int $roomId): void
    {
        $this->roomId = $roomId;
    }

    /** Ontgrendel de verhuurder met credits — zonder page refresh. */
    public function unlock(): void
    {
        $user = auth()->user();

        if (! $user) {
            $this->loginToUnlock();

            return;
        }

        if (! $user->hasRole('huurder')) {
            return;
        }

        $room = Room::with('building.landlord')->find($this->roomId);
        $landlord = $room?->building?->landlord;

        if (! $landlord || $landlord->id === $user->id) {
            return;
        }

        try {
            app(LandlordUnlockService::class)->unlock($user, $landlord);
            $this->setFlash('success', 'De gegevens van de verhuurder zijn ontgrendeld.');
        } catch (InsufficientCreditsException) {
            $this->setFlash('error', 'Je hebt niet genoeg credits om deze verhuurder te ontgrendelen.');
        }
    }

    /** Stuur een in-app bericht naar de verhuurder — zonder page refresh. */
    public function sendMessage(): void
    {
        $user = auth()->user();

        abort_unless($user?->hasRole('huurder'), 403);

        $room = Room::with('building.landlord')->find($this->roomId);
        $landlord = $room?->building?->landlord;

        // Berichten kan enkel zodra de verhuurder ontgrendeld is.
        abort_if($landlord === null || $landlord->id === $user->id || ! $user->canViewLandlord($landlord), 403);

        $this->validate();

        $conversation = Conversation::firstOrCreate([
            'tenant_id' => $user->id,
            'landlord_id' => $landlord->id,
            'building_id' => $room->building_id,
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => strip_tags($this->body),
        ]);

        $conversation->update(['last_message_at' => now()]);

        MessageSent::dispatch($message->load('sender'));

        $this->reset('body');
        $this->showForm = false;
        $this->setFlash('success', 'Je bericht is verstuurd naar de verhuurder. Je vindt het terug bij Berichten in je dashboard.');
    }

    /**
     * Gast: bewaar dit kot als terugkeer-URL (Filament's LoginResponse gebruikt
     * redirect()->intended()) en stuur door naar de login. Na inloggen kom je
     * dus terug op deze kotpagina.
     */
    public function loginToUnlock()
    {
        session()->put('url.intended', route('rooms.show', $this->roomId));

        return $this->redirect(route('filament.dashboard.auth.login'), navigate: false);
    }

    protected function setFlash(string $type, string $message): void
    {
        $this->flashType = $type;
        $this->flashMessage = $message;
        $this->flashTick++;
    }

    public function render(): View
    {
        $room = Room::with('building.landlord')->find($this->roomId);
        $landlord = $room?->building?->landlord;
        $user = auth()->user();

        $isOwn = $user && $landlord && $user->id === $landlord->id;
        $isHuurder = $user?->hasRole('huurder') ?? false;
        $canView = $user && $landlord ? $user->canViewLandlord($landlord) : false;

        $cost = app(LandlordUnlockService::class)->cost();
        $balance = $isHuurder ? app(CreditService::class)->balance($user) : null;

        return view('livewire.room.landlord-card', [
            'room' => $room,
            'landlord' => $landlord,
            'isOwn' => $isOwn,
            'isHuurder' => $isHuurder,
            'canView' => $canView,
            'cost' => $cost,
            'balance' => $balance,
        ]);
    }
}
