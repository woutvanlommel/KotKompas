<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $conversation_id
 * @property int $sender_id
 * @property string $body
 * @property Carbon|null $read_at
 * @property bool $is_broadcast
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $sender
 * @property-read Conversation $conversation
 */
#[Fillable(['conversation_id', 'sender_id', 'body', 'read_at', 'is_broadcast'])]
class Message extends Model
{
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'is_broadcast' => 'boolean',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
