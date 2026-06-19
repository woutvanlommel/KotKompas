<?php

// Observer op het Building-model dat de geocoding aanstuurt:
// - bij aanmaken (created)
// - bij bewerken als minstens één adresveld wijzigde (updated)
//
// De geocoding draait ASYNC via de GeocodeBuilding-job, zodat het opslaan niet
// hoeft te wachten op de externe API. Werden er al verse coördinaten meegegeven
// (bv. gekozen via autocomplete), dan slaan we de geocoding over en verversen
// we enkel de POI-cache.

namespace App\Observers;

use App\Jobs\GeocodeBuilding;
use App\Jobs\RefreshBuildingPoiCache;
use App\Models\Building;

class BuildingObserver
{
    private const ADDRESS_FIELDS = ['street', 'house_number', 'bus', 'postal_code', 'city', 'country'];

    public function created(Building $building): void
    {
        if ($this->hasCoordinates($building)) {
            // Coördinaten al bekend (bv. via autocomplete) → enkel POI verversen.
            RefreshBuildingPoiCache::dispatch($building);

            return;
        }

        GeocodeBuilding::dispatch($building);
    }

    public function updated(Building $building): void
    {
        $addressChanged = collect(self::ADDRESS_FIELDS)
            ->contains(fn (string $field) => $building->wasChanged($field));

        if (! $addressChanged) {
            return;
        }

        // Werden lat/lng in dezelfde opslag meegegeven (autocomplete)? Dan zijn
        // de coördinaten al correct → geen geocode, enkel POI verversen.
        $coordinatesProvided = $building->wasChanged('latitude') || $building->wasChanged('longitude');

        if ($coordinatesProvided && $this->hasCoordinates($building)) {
            RefreshBuildingPoiCache::dispatch($building);

            return;
        }

        GeocodeBuilding::dispatch($building);
    }

    private function hasCoordinates(Building $building): bool
    {
        return $building->latitude !== null && $building->longitude !== null;
    }
}
