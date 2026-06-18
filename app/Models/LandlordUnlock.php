<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'landlord_id', 'credit_transaction_id', 'unlocked_at'])]
class LandlordUnlock extends Model
{
    protected $casts = [
        'unlocked_at' => 'datetime',
    ];

    /** De huurder die de verhuurder unlocked heeft. */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /** De verhuurder die ge-unlocked is. */
    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    /** De ledgerrij waarmee dit betaald is (kan null zijn). */
    public function creditTransaction(): BelongsTo
    {
        return $this->belongsTo(CreditTransaction::class);
    }
}
