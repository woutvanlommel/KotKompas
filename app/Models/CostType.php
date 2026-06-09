<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'category'])]
class CostType extends Model
{
    /** @return BelongsToMany<Room, $this> */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class)
            ->withPivot('amount', 'is_variable', 'frequency', 'description')
            ->withTimestamps();
    }
}
