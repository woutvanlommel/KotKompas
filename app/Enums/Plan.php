<?php

namespace App\Enums;

use App\Models\Plan as PlanModel;

enum Plan: string
{
    case Starter = 'starter';
    case Pro = 'pro';
    case Premium = 'premium';

    public function priceId(): string
    {
        return config("subscriptions.plans.{$this->value}");
    }

    public function label(): string
    {
        return match ($this) {
            self::Starter => 'Starter',
            self::Pro => 'Pro',
            self::Premium => 'Premium',
        };
    }

    public function model(): ?PlanModel
    {
        return PlanModel::where('slug', $this->value)->first();
    }
}
