<?php

namespace App\Models;

use App\Concerns\HasImages;
use App\Observers\BuildingObserver;
use Database\Factories\BuildingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;

#[Fillable(['landlord_id', 'name', 'description', 'street', 'house_number', 'box', 'postal_code', 'city', 'country', 'longitude', 'latitude'])]
#[ObservedBy(BuildingObserver::class)]
class Building extends Model implements HasMedia
{
    /** @use HasFactory<BuildingFactory> */
    use HasFactory, HasImages;

    /** @return HasMany<Room, $this> */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /** @return BelongsTo<User, $this> */
    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    protected function casts(): array
    {
        return [
            'longitude' => 'decimal:8',
            'latitude' => 'decimal:8',
            'score' => 'float',
            'reviews_count' => 'integer',
        ];
    }

    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: function () {
                $bus = $this->bus ? " bus {$this->bus}" : '';

                return "{$this->street} {$this->house_number}{$bus}, {$this->postal_code} {$this->city}";
            },
        );
    }

    /**
     * @return HasMany<BuildingPoiCache, $this>
     */
    public function poiCache(): HasMany
    {
        return $this->hasMany(BuildingPoiCache::class);
    }
}
