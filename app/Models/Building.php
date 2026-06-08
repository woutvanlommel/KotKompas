<?php

namespace App\Models;

use Database\Factories\BuildingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['landlord_id', 'name', 'description', 'street', 'house_number', 'bus', 'postal_code', 'city', 'country', 'longitude', 'latitude'])]
class Building extends Model
{
    /** @use HasFactory<BuildingFactory> */
    use HasFactory;

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    protected function casts(): array
    {
        return [
            'longitude' => 'decimal:8',
            'latitude' => 'decimal:8',
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
}
