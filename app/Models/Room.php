<?php

namespace App\Models;

use App\Concerns\HasImages;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable(['building_id', 'tenant_id', 'bus', 'room_number', 'type', 'title', 'description', 'price_per_month', 'deposit_amount', 'costs_included', 'surface_m2', 'is_furnished', 'available_from', 'status'])]
class Room extends Model implements HasMedia
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory;

    use HasImages;

    /** @return BelongsTo<Building, $this> */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /** @return BelongsToMany<Facility, $this> */
    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class)
            ->withPivot('description')
            ->withTimestamps();
    }

    /** @return BelongsToMany<CostType, $this> */
    public function costTypes(): BelongsToMany
    {
        return $this->belongsToMany(CostType::class)
            ->withPivot('amount', 'is_variable', 'frequency', 'description')
            ->withTimestamps();
    }

    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: function () {
                $building = $this->building;
                $bus = $building->bus ?? $this->bus;
                $busStr = $bus ? " bus {$bus}" : '';

                return "{$building->street} {$building->house_number}{$busStr}, {$building->postal_code} {$building->city}";
            },
        );
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->singleFile();

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->performOnCollections('cover', 'gallery')
            ->format('webp')
            ->quality(85);

        $this->addMediaConversion('thumb')
            ->performOnCollections('cover', 'gallery')
            ->format('webp')
            ->width(400)
            ->quality(80);
    }

    protected function casts(): array
    {
        return [
            'price_per_month' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'available_from' => 'date',
            'costs_included' => 'boolean',
            'is_furnished' => 'boolean',
        ];
    }

    protected function totalMonthlyPrice(): Attribute
    {
        return Attribute::make(
            get: function () {
                $total = (float) $this->price_per_month;

                $this->costTypes
                    ->where('pivot.frequency', 'monthly')
                    ->whereNotNull('pivot.amount')
                    ->each(function (CostType $costType) use (&$total) {
                        /** @phpstan-ignore-next-line */
                        $total += (float) $costType->pivot->amount;
                    });

                return round($total, 2);
            },
        );
    }
}
