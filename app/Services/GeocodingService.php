<?php

// Service die via de Nominatim API (OpenStreetMap) een adres omzet naar geografische coördinaten (lat/lng).
// Wordt gebruikt door BuildingObserver (automatisch bij opslaan) en de Filament GeocodeAction (manueel).

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    private const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/search';

    /**
     * Geocode a full address string to lat/lng.
     *
     * @return array{latitude: float, longitude: float}|null
     */
    public function geocode(string $address): ?array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'KotKompas/1.0 (kotkompas@gmail.com)',
                'Accept-Language' => 'nl',
            ])->get(self::NOMINATIM_URL, [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
                'addressdetails' => 0,
            ]);

            if (! $response->ok()) {
                Log::warning('Nominatim geocoding request failed', [
                    'address' => $address,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $results = $response->json();

            if (empty($results)) {
                Log::info('Nominatim returned no results', ['address' => $address]);

                return null;
            }

            return [
                'latitude' => (float) $results[0]['lat'],
                'longitude' => (float) $results[0]['lon'],
            ];
        } catch (\Throwable $e) {
            Log::error('Geocoding failed', ['address' => $address, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Geocode a Building model's address and return the coordinates.
     *
     * @return array{latitude: float, longitude: float}|null
     */
    public function geocodeBuilding(\App\Models\Building $building): ?array
    {
        $bus = $building->box ? " bus {$building->box}" : '';
        $address = "{$building->street} {$building->house_number}{$bus}, {$building->postal_code} {$building->city}, {$building->country}";

        return $this->geocode($address);
    }
}
