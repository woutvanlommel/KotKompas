<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $landlord_id
 * @property int $building_id
 * @property Carbon|null $last_message_at
 * @property Carbon|null $notification_sent_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $tenant
 * @property-read User $landlord
 * @property-read Building $building
 * @property-read Collection<int, Message> $messages
 */
#[Fillable(['tenant_id', 'landlord_id', 'building_id', 'last_message_at', 'notification_sent_at', 'tenant_unlocked_until'])]
class Conversation extends Model
{
    protected $casts = [
        'last_message_at' => 'datetime',
        'notification_sent_at' => 'datetime',
        'tenant_unlocked_until' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest()->limit(1);
    }

    public function unreadFor(int $userId): int
    {
        return $this->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $userId)
            ->count();
    }

    /**
     * Whether the tenant may no longer send messages in this conversation.
     *
     * The lock is derived in real time from the tenant's rental periods. A
     * temporary reply window (granted when the landlord messages a locked
     * tenant) keeps the conversation open until it expires; otherwise the
     * lock follows the rental grace window.
     */
    public function isTenantMessagingLocked(): bool
    {
        // Active reply window granted by a landlord message.
        if ($this->tenant_unlocked_until && $this->tenant_unlocked_until->isFuture()) {
            return false;
        }

        return $this->tenantGracePeriodExpired();
    }

    /**
     * Whether the tenant's rental grace window has elapsed, ignoring any
     * temporary reply window. An active (or renewed) rental period in this
     * building never counts as expired, even alongside older ended periods.
     */
    public function tenantGracePeriodExpired(): bool
    {
        $periods = RentalPeriod::whereHas('room', fn ($q) => $q->where('building_id', $this->building_id))
            ->whereHas('tenants', fn ($q) => $q->where('users.id', $this->tenant_id))
            ->get();

        if ($periods->isEmpty()) {
            return false;
        }

        if ($periods->contains(fn (RentalPeriod $period) => $period->isActive())) {
            return false;
        }

        $latestEnded = $periods->sortByDesc('end_date')->first();

        return $latestEnded->end_date
            ->addDays(config('chat.tenant_messaging_window_days'))
            ->isPast();
    }
}
