<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

return new class extends Migration
{
    public function up(): void
    {
        Media::query()
            ->where('collection_name', 'document')
            ->where('disk', 'public')
            ->chunkById(100, function ($mediaRows) {
                foreach ($mediaRows as $media) {
                    $from = Storage::disk('public');
                    $to = Storage::disk('local');

                    $paths = $from->allFiles((string) $media->id);

                    // 1. Copy every file first (idempotent: skip if already present).
                    foreach ($paths as $path) {
                        if (! $to->exists($path)) {
                            $to->writeStream($path, $from->readStream($path));
                        }
                    }

                    // 2. Point the DB row at the new disk BEFORE removing any source,
                    //    so the row never references files that no longer exist.
                    $media->disk = 'local';
                    $media->conversions_disk = 'local';
                    $media->save();

                    // 3. Only now delete the originals from the public disk.
                    foreach ($paths as $path) {
                        $from->delete($path);
                    }
                }
            });
    }

    public function down(): void
    {
        // One-way data migration; no rollback.
    }
};
