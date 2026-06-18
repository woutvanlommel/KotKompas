<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'amount', 'reason', 'stripe_session_id'])]
class CreditTransaction extends Model
{
    protected $casts = [
        'amount' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Leesbare omschrijving van de mutatie (afgeleid uit reason). */
    public function label(): string
    {
        if (str_starts_with($this->reason, 'unlock_landlord:')) {
            return 'Verhuurder ontgrendeld';
        }

        return match ($this->reason) {
            'pack_purchase' => 'Credits gekocht',
            default => ucfirst(str_replace('_', ' ', $this->reason)),
        };
    }
}
