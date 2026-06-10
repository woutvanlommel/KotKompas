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
#[Fillable(['tenant_id', 'landlord_id', 'building_id', 'last_message_at', 'notification_sent_at'])]
class Conversation extends Model
{
    protected $casts = [
        'last_message_at' => 'datetime',
        'notification_sent_at' => 'datetime',
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
}
