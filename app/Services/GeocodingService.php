<?php

// Service die een adres omzet naar geografische coördinaten (lat/lng) en
// adres-suggesties levert voor autocomplete.
//
// Primaire bron: Photon (https://photon.komoot.io) — wereldwijd, gratis, geen
// API-key, gebouwd voor autocomplete én geocoding. Nominatim (OpenStreetMap)
// dient enkel nog als fallback voor de geocoding wanneer Photon niets vindt.

namespace App\Services;

use App\Models\Building;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    private const PHOTON_URL = 'https://photon.komoot.io/api/';

    private const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/search';

    /** Aantal pogingen voor de Nominatim-fallback (vangt rate limit op). */
    private const MAX_ATTEMPTS = 3;

    /**
     * Geocode een volledig adres naar lat/lng. Photon eerst, Nominatim als
     * fallback.
     *
     * @return array{latitude: float, longitude: float}|null
     */
    public function geocode(string $address): ?array
    {
        return $this->photonGeocode($address)
            ?? $this->nominatimGeocode(['q' => $address]);
    }

    /**
     * Geocode het adres van een Building. Bouwt een volledige adresstring en
     * laat geocode() de bronnen afhandelen.
     *
     * @return array{latitude: float, longitude: float}|null
     */
    public function geocodeBuilding(Building $building): ?array
    {
        $bus = $building->bus ? " bus {$building->bus}" : '';
        $country = $building->country ?: 'BE';

        $address = trim("{$building->street} {$building->house_number}{$bus}, {$building->postal_code} {$building->city}, {$country}");

        return $this->geocode($address);
    }

    /**
     * Adres-suggesties voor autocomplete via Photon. Geeft per suggestie de
     * losse adresvelden + coördinaten terug, zodat een formulier ze meteen kan
     * invullen.
     *
     * @return list<array{street: string, house_number: string, postal_code: string, city: string, country_code: string, latitude: float|null, longitude: float|null, label: string}>
     */
    public function suggest(string $query, int $limit = 6): array
    {
        $query = trim($query);

        if (mb_strlen($query) < 3) {
            return [];
        }

        try {
            // Let op: Photon ondersteunt enkel default/en/de/fr/it — GEEN 'nl'.
            // Een niet-ondersteunde taal geeft HTTP 400, dus laten we 'lang' weg.
            $response = Http::timeout(8)->get(self::PHOTON_URL, [
                'q' => $query,
                'limit' => $limit,
            ]);

            if (! $response->ok()) {
                Log::warning('Photon autocomplete mislukt', ['status' => $response->status()]);

                return [];
            }

            $suggestions = [];

            foreach ($response->json('features') ?? [] as $feature) {
                $p = $feature['properties'] ?? [];
                $coords = $feature['geometry']['coordinates'] ?? null;

                // Enkel resultaten met een straat of duidelijke naam tonen.
                $street = $p['street'] ?? $p['name'] ?? null;

                if (! $street) {
                    continue;
                }

                $city = $p['city'] ?? $p['town'] ?? $p['village'] ?? $p['district'] ?? $p['county'] ?? '';
                $houseNumber = $p['housenumber'] ?? '';
                $postcode = $p['postcode'] ?? '';

                $suggestions[] = [
                    'street' => $street,
                    'house_number' => $houseNumber,
                    'postal_code' => $postcode,
                    'city' => $city,
                    'country_code' => strtoupper($p['countrycode'] ?? ''),
                    'latitude' => isset($coords[1]) ? (float) $coords[1] : null,
                    'longitude' => isset($coords[0]) ? (float) $coords[0] : null,
                    'label' => $this->formatLabel($street, $houseNumber, $postcode, $city, $p['country'] ?? ''),
                ];
            }

            return $suggestions;
        } catch (\Throwable $e) {
            Log::warning('Photon autocomplete verbinding mislukt', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Eén Photon-geocode (vrije tekst). Photon geeft GeoJSON terug met
     * coördinaten als [lon, lat].
     *
     * @return array{latitude: float, longitude: float}|null
     */
    private function photonGeocode(string $query): ?array
    {
        try {
            // Geen 'lang'-parameter: Photon ondersteunt 'nl' niet (zou HTTP 400 geven).
            $response = Http::timeout(10)->get(self::PHOTON_URL, [
                'q' => $query,
                'limit' => 1,
            ]);

            if (! $response->ok()) {
                Log::warning('Photon geocode mislukt', ['query' => $query, 'status' => $response->status()]);

                return null;
            }

            $coords = $response->json('features.0.geometry.coordinates');

            if (! is_array($coords) || count($coords) < 2) {
                Log::info('Photon gaf geen resultaat', ['query' => $query]);

                return null;
            }

            return [
                'latitude' => (float) $coords[1],
                'longitude' => (float) $coords[0],
            ];
        } catch (\Throwable $e) {
            Log::warning('Photon geocode verbinding mislukt', ['query' => $query, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Fallback-geocode via Nominatim. Robuust tegen de ±1 request/seconde
     * limiet: 429/503 of verbindingsfouten worden met spacing opnieuw
     * geprobeerd.
     *
     * @param  array<string, string>  $query
     * @return array{latitude: float, longitude: float}|null
     */
    private function nominatimGeocode(array $query): ?array
    {
        $params = array_merge($query, [
            'format' => 'json',
            'limit' => 1,
            'addressdetails' => 0,
        ]);

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'KotKompas/1.0 (kotkompas@gmail.com)',
                    'Accept-Language' => 'nl',
                ])
                    ->timeout(10)
                    ->get(self::NOMINATIM_URL, $params);
            } catch (\Throwable $e) {
                Log::warning('Nominatim verbinding mislukt', ['query' => $query, 'attempt' => $attempt, 'error' => $e->getMessage()]);
                $this->respectRateLimit();

                continue;
            }

            if (in_array($response->status(), [429, 503], true)) {
                Log::info('Nominatim rate limit/tijdelijk niet beschikbaar', ['query' => $query, 'attempt' => $attempt]);
                $this->respectRateLimit();

                continue;
            }

            if (! $response->ok()) {
                Log::warning('Nominatim request mislukt', ['query' => $query, 'status' => $response->status()]);

                return null;
            }

            $results = $response->json();

            if (empty($results)) {
                return null;
            }

            return [
                'latitude' => (float) $results[0]['lat'],
                'longitude' => (float) $results[0]['lon'],
            ];
        }

        Log::warning('Nominatim na retries niet beschikbaar', ['query' => $query]);

        return null;
    }

    private function formatLabel(string $street, string $houseNumber, string $postcode, string $city, string $country): string
    {
        $line1 = trim("{$street} {$houseNumber}");
        $line2 = trim("{$postcode} {$city}");

        return implode(', ', array_filter([$line1, $line2, $country]));
    }

    /** Wacht net iets meer dan 1 seconde om de Nominatim usage policy te respecteren. */
    private function respectRateLimit(): void
    {
        usleep(1_100_000);
    }
}
