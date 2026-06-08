<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Delete orphaned tmp-media files older than 2 hours.
// These are uploads where the user abandoned the form before saving.
Schedule::call(function () {
    $disk = Storage::disk('public');
    $cutoff = now()->subHours(2)->timestamp;

    foreach ($disk->allFiles('tmp-media') as $file) {
        if ($disk->lastModified($file) < $cutoff) {
            $disk->delete($file);
        }
    }

    // Remove empty subdirectories left behind
    foreach ($disk->directories('tmp-media') as $dir) {
        if (empty($disk->allFiles($dir))) {
            $disk->deleteDirectory($dir);
        }
    }
})->hourly()->name('cleanup-tmp-media')->withoutOverlapping();
