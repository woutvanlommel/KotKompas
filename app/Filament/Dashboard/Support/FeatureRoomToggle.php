<?php

namespace App\Filament\Dashboard\Support;

use App\Filament\Dashboard\Pages\Subscription;
use App\Models\Room;
use App\Services\FeaturedListingService;
use App\Services\FilamentNotificationService;
use Filament\Actions\Action;

/**
 * Shared handler behind every "Uitlichten" control (rooms table + building
 * view): toggles a room's featured state and notifies the landlord, nudging
 * them to the Abonnement tab when they're out of slots. One home so the nudge
 * stays consistent everywhere.
 */
class FeatureRoomToggle
{
    public static function handle(Room $room): void
    {
        $service = app(FeaturedListingService::class);

        if ($room->isFeatured()) {
            $service->unfeature($room);
            FilamentNotificationService::info('Niet meer uitgelicht', 'Je kot staat niet langer bovenaan.', icon: 'heroicon-o-star');

            return;
        }

        if (! $service->feature($room)) {
            FilamentNotificationService::warning(
                'Geen uitlicht-slots beschikbaar',
                'Je hebt geen vrije uitlicht-slots meer. Upgrade je abonnement om meer koten uit te lichten.',
                icon: 'heroicon-o-star',
                actions: [
                    Action::make('upgrade')
                        ->label('Bekijk abonnementen')
                        ->url(Subscription::getUrl())
                        ->button(),
                ],
            );

            return;
        }

        FilamentNotificationService::success('Kot uitgelicht', 'Je kot staat nu bovenaan de zoekresultaten.', icon: 'heroicon-o-star');
    }
}
