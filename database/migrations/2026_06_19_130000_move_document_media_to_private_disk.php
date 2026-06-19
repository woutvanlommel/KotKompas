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
            ->get()
            ->each(function (Media $media) {
                $from = Storage::disk('public');
                $to = Storage::disk('local');

                foreach ($from->allFiles((string) $media->id) as $path) {
                    if (! $to->exists($path)) {
                        $to->writeStream($path, $from->readStream($path));
                    }
                    $from->delete($path);
                }

                $media->disk = 'local';
                $media->conversions_disk = 'local';
                $media->save();
            });
    }

    public function down(): void
    {
        // One-way data migration; no rollback.
    }
};
