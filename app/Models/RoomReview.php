<?php

namespace App\Models;

use App\Observers\RoomReviewObserver;
use Carbon\Carbon;
use Database\Factories\RoomReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read float $room_score
 * @property-read Room $room
 * @property-read User $landlord
 */
#[Fillable(['room_id', 'landlord_id', 'tenant_id', 'score_hygiene', 'score_size', 'score_value', 'score_communication'])]
#[ObservedBy(RoomReviewObserver::class)]
class RoomReview extends Model
{
    /** @use HasFactory<RoomReviewFactory> */
    use HasFactory;

    /**
     * The kotscore criteria, shared by the survey form and the invitation
     * mail so label + question text live in one place. Keys are the
     * score_* columns; communication is the landlord dimension.
     */
    public const CRITERIA = [
        'score_hygiene' => ['label' => 'Hygiëne', 'hint' => 'Staat van de kamer, het sanitair en de gedeelde ruimtes.'],
        'score_size' => ['label' => 'Grootte', 'hint' => 'Was de ruimte wat je ervan verwachtte?'],
        'score_value' => ['label' => 'Prijs-kwaliteit', 'hint' => 'Kreeg je waar voor je huurprijs?'],
        'score_communication' => ['label' => 'Communicatie verhuurder', 'hint' => 'Bereikbaarheid, duidelijke afspraken en opvolging.'],
    ];

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
     * This review's kotscore: the average of the room criteria.
     * Communication belongs to the landlord and deliberately does not count here.
     */
    protected function roomScore(): Attribute
    {
        return Attribute::make(
            get: fn () => round(($this->score_hygiene + $this->score_size + $this->score_value) / 3, 2),
        );
    }
}
