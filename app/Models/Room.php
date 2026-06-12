<?php

namespace App\Models;

use App\Concerns\HasImages;
use App\Observers\RoomObserver;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable(['building_id', 'tenant_id', 'bus', 'room_number', 'type', 'title', 'description', 'price_per_month', 'deposit_amount', 'costs_included', 'surface_m2', 'is_furnished', 'available_from', 'status'])]
#[ObservedBy(RoomObserver::class)]
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

    /** @deprecated Gebruik rentalPeriods() — tenant_id wordt uitgefaseerd */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /** @return HasMany<RentalPeriod, $this> */
    public function rentalPeriods(): HasMany
    {
        return $this->hasMany(RentalPeriod::class);
    }

    public function activeTenant(): ?User
    {
        return $this->rentalPeriods()
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->latest('start_date')
            ->first()
            ?->primaryTenant();
    }

    /** Alle huurders (hoofd + mede) van de actieve huurperiode */
    public function activeTenants(): Collection
    {
        $period = $this->rentalPeriods()
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->latest('start_date')
            ->first();

        return $period->tenants ?? collect();
    }

    /** @return HasMany<RoomReview, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(RoomReview::class);
    }

    /** @return HasMany<ReviewInvitation, $this> */
    public function reviewInvitations(): HasMany
    {
        return $this->hasMany(ReviewInvitation::class);
    }

    /**
     * Survey invitations not yet completed (open or expired, one per
     * ex-tenant) — the dashboard lists them so the landlord can share the
     * link manually or renew an expired one.
     *
     * @return HasMany<ReviewInvitation, $this>
     */
    public function pendingReviewInvitations(): HasMany
    {
        return $this->hasMany(ReviewInvitation::class)
            ->whereNull('completed_at')
            ->latest('id');
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
            'score' => 'float',
            'score_bayesian' => 'float',
            'reviews_count' => 'integer',
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
                    ->where('pivot.is_variable', false)
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
