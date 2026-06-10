<?php

// Observer op het Building model die automatisch geocoding triggert via GeocodingService:
// - bij aanmaken van een gebouw (created)
// - bij bewerken als minstens één adresveld gewijzigd is (updated)

namespace App\Observers;

use App\Models\Building;
use App\Services\GeocodingService;

class BuildingObserver
{
    private const ADDRESS_FIELDS = ['street', 'house_number', 'box', 'postal_code', 'city', 'country'];

    public function __construct(private GeocodingService $geocodingService) {}

    public function created(Building $building): void
    {
        $this->updateCoordinates($building);
    }

    public function updated(Building $building): void
    {
        $addressChanged = collect(self::ADDRESS_FIELDS)
            ->contains(fn (string $field) => $building->wasChanged($field));

        if ($addressChanged) {
            $this->updateCoordinates($building);
        }
    }

    private function updateCoordinates(Building $building): void
    {
        $coordinates = $this->geocodingService->geocodeBuilding($building);

        if ($coordinates) {
            // Geen withoutEvents nodig: observer triggert enkel bij adresveldwijzigingen,
            // lat/lng vallen daar niet onder, dus geen oneindige loop mogelijk.
            $building->update([
                'latitude' => $coordinates['latitude'],
                'longitude' => $coordinates['longitude'],
            ]);
        }
    }
}
