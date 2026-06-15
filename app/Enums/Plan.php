<?php

namespace App\Enums;

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
}
