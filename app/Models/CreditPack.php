<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'credits', 'price', 'is_active', 'sort_order'])]
class CreditPack extends Model
{
    protected $casts = [
        'credits' => 'integer',
        'price' => 'integer', // in cents
        'is_active' => 'boolean',
    ];

    /** Prijs in euro's (afgeleid van cents). */
    protected function priceInEuros(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price / 100,
        );
    }
}
