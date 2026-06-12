<?php

namespace App\Jobs;

use App\Models\Building;
use App\Models\BuildingPoiCache;
use App\Services\OverpassService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RefreshBuildingPoiCache implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public readonly Building $building) {}

    public function handle(OverpassService $overpass): void
    {
        if ($this->building->latitude === null || $this->building->longitude === null) {
            Log::info('RefreshBuildingPoiCache skipped: no coordinates', ['building_id' => $this->building->id]);

            return;
        }

        $pois = $overpass->fetchNearby(
            (float) $this->building->latitude,
            (float) $this->building->longitude,
        );

        if (empty($pois)) {
            Log::info('RefreshBuildingPoiCache: no POIs returned', ['building_id' => $this->building->id]);

            return;
        }

        BuildingPoiCache::where('building_id', $this->building->id)->delete();

        $now = now();

        BuildingPoiCache::insert(
            array_map(fn (array $poi) => [
                'building_id' => $this->building->id,
                'category' => $poi['category'],
                'name' => $poi['name'],
                'latitude' => $poi['latitude'],
                'longitude' => $poi['longitude'],
                'created_at' => $now,
                'updated_at' => $now,
            ], $pois)
        );

        Log::info('RefreshBuildingPoiCache completed', [
            'building_id' => $this->building->id,
            'poi_count' => count($pois),
        ]);
    }
}
