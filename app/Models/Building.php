<?php

namespace App\Models;

use Database\Factories\BuildingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['landlord_id', 'name', 'description', 'street', 'house_number', 'box', 'postal_code', 'city', 'country', 'longitude', 'latitude'])]
class Building extends Model
{
    /** @use HasFactory<BuildingFactory> */
    use HasFactory;

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
                $box = $this->box ? " bus {$this->box}" : '';
                return "{$this->street} {$this->house_number}{$box}, {$this->postal_code} {$this->city}, {$this->country}";
            },
        );
    }
}
