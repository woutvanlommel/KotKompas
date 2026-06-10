<?php

// Service die via de Nominatim API (OpenStreetMap) een adres omzet naar geografische coördinaten (lat/lng).
// Wordt gebruikt door BuildingObserver (automatisch bij opslaan) en de Filament GeocodeAction (manueel).

namespace App\Services;

use App\Models\Building;
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
     * Geocode a Building model's address using structured Nominatim parameters.
     * More reliable than free-text search, especially with ISO country codes (e.g. 'BE').
     *
     * @return array{latitude: float, longitude: float}|null
     */
    public function geocodeBuilding(Building $building): ?array
    {
        $bus = $building->bus ? " bus {$building->bus}" : '';

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'KotKompas/1.0 (kotkompas@gmail.com)',
                'Accept-Language' => 'nl',
            ])->get(self::NOMINATIM_URL, [
                'street' => "{$building->street} {$building->house_number}{$bus}",
                'city' => $building->city,
                'postalcode' => $building->postal_code,
                'countrycodes' => strtolower($building->country ?? 'be'),
                'format' => 'json',
                'limit' => 1,
                'addressdetails' => 0,
            ]);

            if (! $response->ok()) {
                Log::warning('Nominatim structured geocoding failed', [
                    'building_id' => $building->id,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $results = $response->json();

            if (! empty($results)) {
                return [
                    'latitude' => (float) $results[0]['lat'],
                    'longitude' => (float) $results[0]['lon'],
                ];
            }

            // Fallback: free-text search with city + postal code (minder specifiek maar breder)
            Log::info('Nominatim structured search returned no results, trying fallback', ['building_id' => $building->id]);

            return $this->geocode("{$building->street} {$building->house_number}{$bus}, {$building->postal_code} {$building->city}");
        } catch (\Throwable $e) {
            Log::error('Geocoding building failed', ['building_id' => $building->id, 'error' => $e->getMessage()]);

            return null;
        }
    }
}
