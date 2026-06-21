<?php

namespace App\Enums;

enum DocumentVisibility: string
{
    case Private = 'private';
    case Landlord = 'landlord';
    case Building = 'building';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::Private => 'Privé',
            self::Landlord => 'Delen met verhuurder',
            self::Building => 'Hele gebouw',
            self::User => 'Specifieke student',
        };
    }
}
