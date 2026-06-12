<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['room_id', 'start_date', 'end_date'])]
class RentalPeriod extends Model
{
    /** @return BelongsTo<Room, $this> */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'rental_period_user')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function primaryTenant(): ?User
    {
        return $this->tenants()->wherePivot('is_primary', true)->first();
    }

    /** @return HasMany<Document, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function isActive(): bool
    {
        return $this->end_date === null || $this->end_date >= now();
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
}
