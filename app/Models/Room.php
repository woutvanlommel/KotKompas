<?php

namespace App\Models;

use App\Concerns\HasImages;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

#[Fillable(['building_id', 'room_number', 'type', 'title', 'description', 'price_per_month', 'costs_included', 'extra_costs', 'surface_m2', 'is_furnished', 'available_from', 'status'])]
class Room extends Model implements HasMedia
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory, HasImages;

    protected function casts(): array
    {
        return [
            'price_per_month' => 'decimal:2',
            'extra_costs' => 'json',
            'available_from' => 'date',
            'costs_included' => 'boolean',
            'is_furnished' => 'boolean',
        ];
    }

    protected function totalPrice(): Attribute
    {
        return Attribute::make(
            get: function () {
                $total = $this->price_per_month;
                if ($this->extra_costs) {
                    $total += array_sum((array) $this->extra_costs);
                }

                return $total;
            },
        );
    }
}
