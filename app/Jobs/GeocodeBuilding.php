<?php

namespace App\Jobs;

use App\Models\Building;
use App\Services\GeocodingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Geocodeert een gebouw asynchroon, zodat het opslaan-request niet hoeft te
 * wachten op de externe geocoding-API. Schrijft de coördinaten weg en ververst
 * daarna de POI-cache. Wordt enkel gebruikt wanneer er nog geen verse
 * coördinaten zijn (bv. handmatig adres ingetikt zonder autocomplete).
 */
class GeocodeBuilding implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public readonly Building $building) {}

    public function handle(GeocodingService $geocoder): void
    {
        $coordinates = $geocoder->geocodeBuilding($this->building);

        if (! $coordinates) {
            Log::info('GeocodeBuilding: geen coördinaten gevonden', ['building_id' => $this->building->id]);

            return;
        }

        // lat/lng zijn geen adresvelden, dus dit triggert geen nieuwe geocode
        // in de BuildingObserver (geen oneindige lus).
        $this->building->update($coordinates);

        RefreshBuildingPoiCache::dispatch($this->building);
    }
}
