<?php

use App\Jobs\RefreshBuildingPoiCache;
use App\Models\Building;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Delete orphaned tmp-media uploads older than 24 hours.
// Run manually: php artisan app:prune-tmp-media
// Dry run:      php artisan app:prune-tmp-media --dry-run
Schedule::command('app:prune-tmp-media')
    ->daily()
    ->withoutOverlapping()
    ->runInBackground();

// Cached room scores drift even without new reviews: reviews drop to
// half weight after 2 years. Daily recompute catches that drift.
Schedule::command('app:recompute-kotscores')
    ->daily()
    ->withoutOverlapping()
    ->runInBackground();
// Ververs de POI-cache van elk gebouw maandelijks.
Schedule::call(function () {
    Building::whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->each(fn (Building $building) => RefreshBuildingPoiCache::dispatch($building));
})->monthly();
