<?php

// Service die via de Overpass API (OpenStreetMap) nabijgelegen POIs ophaalt voor een gebouw.
// Wordt gebruikt door RefreshBuildingPoiCache job (automatisch bij opslaan en maandelijks via scheduler).

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OverpassService
{
    private const OVERPASS_URL = 'https://overpass-api.de/api/interpreter';

    private const RADIUS = 750; // meters

    // Overpass amenity/tag values → onze interne categorienaam
    private const CATEGORY_MAP = [
        'supermarket'   => 'supermarket',
        'convenience'   => 'convenience',
        'pharmacy'      => 'pharmacy',
        'hospital'      => 'hospital',
        'bus_station'   => 'bus_stop',
        'bus_stop'      => 'bus_stop',
        'cafe'          => 'cafe',
        'restaurant'    => 'restaurant',
    ];

    /**
     * Haal alle POIs op binnen RADIUS meter van de gegeven coördinaten.
     *
     * @return array<int, array{category: string, name: string, latitude: float, longitude: float}>
     */
    public function fetchNearby(float $latitude, float $longitude): array
    {
        $query = $this->buildQuery($latitude, $longitude);

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'KotKompas/1.0 (kotkompas@gmail.com)',
            ])->timeout(15)->post(self::OVERPASS_URL, ['data' => $query]);

            if (! $response->ok()) {
                Log::warning('Overpass API request failed', [
                    'status' => $response->status(),
                    'lat'    => $latitude,
                    'lng'    => $longitude,
                ]);

                return [];
            }

            return $this->parseElements($response->json('elements', []));
        } catch (\Throwable $e) {
            Log::error('Overpass API error', [
                'error' => $e->getMessage(),
                'lat'   => $latitude,
                'lng'   => $longitude,
            ]);

            return [];
        }
    }

    /**
     * Bouw de Overpass QL query op.
     */
    private function buildQuery(float $lat, float $lng): string
    {
        $r = self::RADIUS;

        // Haal nodes/ways op voor relevante amenity-waarden + bus stops + trein/tram
        return <<<OVERPASS
        [out:json][timeout:10];
        (
          node["amenity"~"^(supermarket|convenience|pharmacy|hospital|bus_station|bus_stop|cafe|restaurant)$"](around:{$r},{$lat},{$lng});
          node["public_transport"="stop_position"](around:{$r},{$lat},{$lng});
          node["railway"~"^(station|halt|tram_stop)$"](around:{$r},{$lat},{$lng});
        );
        out center;
        OVERPASS;
    }

    /**
     * Zet ruwe Overpass elementen om naar ons formaat.
     *
     * @param  array<int, array<string, mixed>>  $elements
     * @return array<int, array{category: string, name: string, latitude: float, longitude: float}>
     */
    private function parseElements(array $elements): array
    {
        $results = [];

        foreach ($elements as $el) {
            $tags = $el['tags'] ?? [];
            $name = $tags['name'] ?? null;

            // Sla naamloze POIs over — niet nuttig om te tonen
            if (empty($name)) {
                continue;
            }

            $category = $this->resolveCategory($tags);

            if ($category === null) {
                continue;
            }

            $lat = $el['lat'] ?? ($el['center']['lat'] ?? null);
            $lng = $el['lon'] ?? ($el['center']['lon'] ?? null);

            if ($lat === null || $lng === null) {
                continue;
            }

            $results[] = [
                'category'  => $category,
                'name'      => $name,
                'latitude'  => (float) $lat,
                'longitude' => (float) $lng,
            ];
        }

        return $results;
    }

    /**
     * Bepaal de interne categorie op basis van OSM tags.
     */
    private function resolveCategory(array $tags): ?string
    {
        // amenity tag (supermarket, pharmacy, ...)
        if (isset($tags['amenity'])) {
            return self::CATEGORY_MAP[$tags['amenity']] ?? null;
        }

        // public_transport stop → bus_stop
        if (isset($tags['public_transport'])) {
            return 'bus_stop';
        }

        // railway station/halt/tram_stop
        if (isset($tags['railway'])) {
            return match ($tags['railway']) {
                'station', 'halt' => 'train_station',
                'tram_stop'       => 'tram_stop',
                default           => null,
            };
        }

        return null;
    }
}
