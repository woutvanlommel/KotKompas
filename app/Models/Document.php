<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable(['user_id', 'name', 'rental_period_id', 'type', 'is_public', 'blocks', 'status', 'ocr_text'])]
class Document extends Model implements HasMedia
{
    use InteractsWithMedia;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<RentalPeriod, $this> */
    public function rentalPeriod(): BelongsTo
    {
        return $this->belongsTo(RentalPeriod::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('document')
            ->acceptsMimeTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/webp',
            ])
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->performOnCollections('document')
            ->width(400)
            ->height(566)   // A4 verhouding
            ->quality(80)
            ->format('webp')
            ->nonQueued();  // voor snelle preview, kan later naar queued
    }

    public function isContract(): bool
    {
        return $this->type === 'contract';
    }

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'blocks' => 'array',
        ];
    }
}
