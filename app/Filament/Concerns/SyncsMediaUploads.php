<?php

namespace App\Filament\Concerns;

use App\Filament\Components\ImageUpload;
use Spatie\MediaLibrary\HasMedia;

/**
 * Add to any CreateRecord or EditRecord Filament page to automatically
 * sync ImageUpload fields to Spatie Media Library.
 *
 * Usage:
 *   class CreateBuilding extends CreateRecord
 *   {
 *       use SyncsMediaUploads;
 *
 *       // Optional: override to sync multiple collections
 *       protected function getMediaCollections(): array
 *       {
 *           return ['images', 'floor_plans'];
 *       }
 *   }
 *
 * The trait:
 *   - Strips upload paths from form data before the model is created/saved
 *     (preventing mass-assignment errors).
 *   - Moves temp files into media library after the record exists.
 *   - Pre-populates the upload field with existing media on edit forms.
 */
trait SyncsMediaUploads
{
    /** Temporarily holds upload paths extracted before model save. */
    protected array $pendingMediaUploads = [];

    // ── Create ────────────────────────────────────────────────────────────────

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->extractUploadsFromData($data);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->processPendingUploads();
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record instanceof HasMedia) {
            foreach ($this->getMediaCollections() as $collection) {
                $data[$collection] = ImageUpload::existingPaths($this->record, $collection);
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->extractUploadsFromData($data);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->processPendingUploads();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Pull upload paths out of form data so they don't reach the model's
     * create()/update() call and cause mass-assignment errors.
     */
    protected function extractUploadsFromData(array &$data): void
    {
        foreach ($this->getMediaCollections() as $collection) {
            if (array_key_exists($collection, $data)) {
                $this->pendingMediaUploads[$collection] = $data[$collection] ?? [];
                unset($data[$collection]);
            }
        }
    }

    protected function processPendingUploads(): void
    {
        if (! ($this->record instanceof HasMedia)) {
            return;
        }

        foreach ($this->pendingMediaUploads as $collection => $paths) {
            ImageUpload::sync($this->record, $paths ?? [], $collection);
        }

        $this->pendingMediaUploads = [];
    }

    /**
     * Override this in your resource page to sync additional collections.
     *
     * @return string[]
     */
    protected function getMediaCollections(): array
    {
        return ['images'];
    }
}
