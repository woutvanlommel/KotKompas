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

    /** Tier-rang: hoger = duurder/hoger plan. Bepaalt upgrade vs downgrade. */
    public function rank(): int
    {
        return match ($this) {
            self::Starter => 1,
            self::Pro => 2,
            self::Premium => 3,
        };
    }

    /** Zoek het plan dat bij een Stripe price-ID hoort, of null. */
    public static function fromPriceId(?string $priceId): ?self
    {
        if ($priceId === null) {
            return null;
        }

        foreach (self::cases() as $case) {
            if ($case->priceId() === $priceId) {
                return $case;
            }
        }

        return null;
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
