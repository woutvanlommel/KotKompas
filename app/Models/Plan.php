<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use App\Enums\Plan as PlanEnum;

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

    public function pirceId(): ?string
    {
        return $this->planEnum()?->priceId();
    }
}
