<?php

namespace App\Models;

use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['building_id', 'room_number', 'type', 'title', 'description', 'price_per_month', 'costs_included', 'extra_costs', 'surface_m2', 'is_furnished', 'available_from', 'status'])]
class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory;

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
                    $total += array_sum($this->extra_costs);
                }
                return $total;
            },
        );
    }
}
