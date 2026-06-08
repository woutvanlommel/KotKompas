<?php

namespace App\Filament\Components;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;

/**
 * Reusable image upload component wired to Spatie Media Library.
 *
 * --- Form usage ---
 *   ImageUpload::make('images')          // multiple images (default)
 *   ImageUpload::make('avatar', false)   // single image
 *
 * --- Sync after save ---
 *   ImageUpload::sync($this->record, $this->pendingMediaUploads['images'] ?? [], 'images');
 *
 * Use the SyncsMediaUploads trait on your CreateRecord / EditRecord page
 * to handle the sync automatically.
 *
 * --- Edit form: show existing images ---
 *   In mutateFormDataBeforeFill(), the SyncsMediaUploads trait populates
 *   the field with existing media paths so they appear in the upload widget.
 */
class ImageUpload
{
    public static function make(string $collection = 'images', bool $multiple = true): FileUpload
    {
        return FileUpload::make($collection)
            ->label(ucfirst($collection))
            ->image()
            ->multiple($multiple)
            ->reorderable($multiple)
            ->disk('public')
            ->directory('tmp-media')
            ->visibility('public')
            ->imageEditor()
            ->maxSize(10240) // 10 MB
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->maxFiles($multiple ? 20 : 1)
            ->panelLayout('grid')
            ->dehydrated(true); // keep paths in form data so SyncsMediaUploads can read them
    }

    /**
     * Sync uploaded temp files to a media library collection.
     *
     * - New paths (under tmp-media/) are added to the collection.
     * - Existing media paths already on the public disk are kept as-is.
     * - Media no longer present in $formPaths is deleted from the collection.
     */
    public static function sync(HasMedia $record, array $formPaths, string $collection = 'images'): void
    {
        $existingMedia   = $record->getMedia($collection);
        $existingByPath  = $existingMedia->keyBy(fn ($m) => $m->getPathRelativeToRoot());

        $keptPaths = [];

        foreach ($formPaths as $path) {
            if (isset($existingByPath[$path])) {
                // Already in media library — keep it
                $keptPaths[] = $path;
                continue;
            }

            // New temp upload — move into media library
            $fullPath = Storage::disk('public')->path($path);

            if (file_exists($fullPath)) {
                $record->addMedia($fullPath)
                    ->toMediaCollection($collection);
            }
        }

        // Delete media the user removed from the upload field
        $existingMedia->each(function ($media) use ($keptPaths) {
            if (! in_array($media->getPathRelativeToRoot(), $keptPaths, true)) {
                $media->delete();
            }
        });
    }

    /**
     * Return existing media paths for a collection, ready to pre-populate
     * a FileUpload field in an edit form via mutateFormDataBeforeFill().
     */
    public static function existingPaths(HasMedia $record, string $collection = 'images'): array
    {
        return $record->getMedia($collection)
            ->map(fn ($media) => $media->getPathRelativeToRoot())
            ->values()
            ->toArray();
    }
}
