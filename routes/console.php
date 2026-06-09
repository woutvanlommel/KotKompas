<?php

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
