<?php

namespace App\Models;

use App\Enums\Plan as PlanEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'name', 'description', 'features', 'is_active', 'sort_order'])]
class Plan extends Model
{
    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function planEnum(): ?PlanEnum
    {
        return PlanEnum::tryFrom($this->slug);
    }

    public function priceId(): ?string
    {
        return $this->planEnum()?->priceId();
    }
}
