<?php

namespace App\Filament\Components;

use Filament\Forms\Components\FileUpload;
use Illuminate\Http\UploadedFile;
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
    /** MIME types that are accepted for upload. */
    private const ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    /** MIME types that are commonly tried but cannot be converted server-side. */
    private const UNSUPPORTED_TYPES = ['image/heic', 'image/heif', 'image/heic-sequence', 'image/heif-sequence'];

    private const HEIC_MESSAGE = 'HEIC/HEIF images (used by iPhones by default) are not supported. '
        .'On your iPhone, go to Settings → Camera → Formats and choose "Most Compatible" to shoot in JPEG instead. '
        .'Alternatively, convert the image to JPEG or PNG before uploading.';

    public static function make(string $collection = 'images', bool $multiple = true): FileUpload
    {
        return FileUpload::make($collection)
            ->label(ucfirst($collection))
            ->image()
            ->multiple($multiple)
            ->disk('public')
            ->directory('tmp-media')
            ->visibility('public')
            ->imageEditor()
            ->maxSize(10240) // 10 MB
            ->acceptedFileTypes(self::ACCEPTED_TYPES)
            ->maxFiles($multiple ? 20 : 1)
            ->panelLayout('grid')
            ->hint('HEIC/HEIF not supported — use JPEG, PNG, or WebP.')
            ->hintColor('warning')
            ->hintIcon('heroicon-o-exclamation-triangle')
            ->rules([static::heicValidationRule()])
            ->dehydrated(true); // keep paths in form data so SyncsMediaUploads can read them
    }

    /**
     * Validation rule that catches HEIC/HEIF uploads and returns a clear,
     * actionable error instead of a generic "invalid file type" message.
     */
    public static function heicValidationRule(): \Closure
    {
        return function ($attribute, $value, $fail) {
            $files = is_array($value) ? $value : [$value];

            foreach ($files as $file) {
                if (! ($file instanceof UploadedFile)) {
                    continue;
                }

                if (in_array($file->getMimeType(), self::UNSUPPORTED_TYPES, true)
                    || in_array(strtolower($file->getClientOriginalExtension()), ['heic', 'heif'], true)
                ) {
                    $fail(self::HEIC_MESSAGE);

                    return;
                }
            }
        };
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
        $existingMedia = $record->getMedia($collection);
        $existingByPath = $existingMedia->keyBy(fn ($m) => $m->getPathRelativeToRoot());

        $keptPaths = [];

        foreach ($formPaths as $path) {
            if (! is_string($path)) {
                continue;
            }

            if (isset($existingByPath[$path])) {
                // Already in media library — keep it
                $keptPaths[] = $path;

                continue;
            }

            // Guard against path traversal: only process files in our temp directory
            if (! str_starts_with($path, 'tmp-media/')) {
                continue;
            }

            // Reject any path containing directory traversal sequences
            if (str_contains($path, '..') || str_contains($path, "\0")) {
                continue;
            }

            $fullPath = Storage::disk('public')->path($path);

            if (! file_exists($fullPath)) {
                continue;
            }

            // Verify the resolved path is actually inside the storage root
            // to prevent symlink or edge-case traversal
            $storagePath = realpath(Storage::disk('public')->path('tmp-media'));
            $resolvedPath = realpath($fullPath);

            if ($storagePath === false || $resolvedPath === false) {
                continue;
            }

            if (! str_starts_with($resolvedPath, $storagePath . DIRECTORY_SEPARATOR)) {
                continue;
            }

            $record->addMedia($fullPath)
                ->toMediaCollection($collection);
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
