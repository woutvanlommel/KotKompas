<?php

namespace App\Concerns;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Add to any model that needs image uploads with automatic WebP conversion.
 *
 * Usage:
 *   class Building extends Model implements HasMedia
 *   {
 *       use HasImages;
 *   }
 *
 * Override registerMediaCollections() to add extra collections (e.g. 'avatar').
 */
trait HasImages
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Full-size WebP — replaces the original for web delivery
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(85)
            ->performOnCollections('images');

        // Small thumbnail in WebP — used for previews, cards, galleries
        $this->addMediaConversion('thumb')
            ->format('webp')
            ->width(400)
            ->quality(80)
            ->performOnCollections('images');
    }
}
