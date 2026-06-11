<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\RoomReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $room_id
 * @property int $landlord_id
 * @property int|null $tenant_id
 * @property int $score_hygiene
 * @property int $score_size
 * @property int $score_value
 * @property int $score_communication
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read float $room_score
 * @property-read Room $room
 * @property-read User $landlord
 */
#[Fillable(['room_id', 'landlord_id', 'tenant_id', 'score_hygiene', 'score_size', 'score_value', 'score_communication'])]
class RoomReview extends Model
{
    /** @use HasFactory<RoomReviewFactory> */
    use HasFactory;

    /** @return BelongsTo<Room, $this> */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /** @return BelongsTo<User, $this> */
    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    /**
     * De kotscore van deze beoordeling: gemiddelde van de kot-criteria.
     * Communicatie hoort bij de verhuurder en telt hier bewust niet mee.
     */
    protected function roomScore(): Attribute
    {
        return Attribute::make(
            get: fn () => round(($this->score_hygiene + $this->score_size + $this->score_value) / 3, 2),
        );
    }
}
