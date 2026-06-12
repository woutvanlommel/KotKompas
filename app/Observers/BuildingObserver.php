<?php

// Observer on the Building model that automatically triggers geocoding via GeocodingService:
// - when a building is created (created)
// - when editing if at least one address field changed (updated)

namespace App\Observers;

use App\Jobs\RefreshBuildingPoiCache;
use App\Models\Building;
use App\Services\GeocodingService;

class BuildingObserver
{
    private const ADDRESS_FIELDS = ['street', 'house_number', 'box', 'postal_code', 'city', 'country'];

    public function __construct(private GeocodingService $geocodingService) {}

    public function created(Building $building): void
    {
        $this->updateCoordinates($building);

        RefreshBuildingPoiCache::dispatch($building);
    }

    public function updated(Building $building): void
    {
        $addressChanged = collect(self::ADDRESS_FIELDS)
            ->contains(fn (string $field) => $building->wasChanged($field));

        if ($addressChanged) {
            $this->updateCoordinates($building);

            RefreshBuildingPoiCache::dispatch($building);
        }
    }

    private function updateCoordinates(Building $building): void
    {
        $coordinates = $this->geocodingService->geocodeBuilding($building);

        if ($coordinates) {
            // No withoutEvents needed: the observer only triggers on address field
            // changes, and lat/lng are not among them, so no infinite loop is possible.
            $building->update([
                'latitude' => $coordinates['latitude'],
                'longitude' => $coordinates['longitude'],
            ]);
        }
    }
}
