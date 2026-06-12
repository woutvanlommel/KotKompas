<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\ReviewInvitationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Token invitation for the kotscore survey, created when a rental ends.
 * The link is shared manually until the email exists (#28).
 *
 * @property int $id
 * @property int $room_id
 * @property int $landlord_id
 * @property int|null $tenant_id
 * @property string $token
 * @property Carbon $expires_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Room $room
 * @property-read User $landlord
 * @property-read User|null $tenant
 */
#[Fillable(['room_id', 'landlord_id', 'tenant_id', 'token', 'expires_at', 'completed_at'])]
class ReviewInvitation extends Model
{
    /** @use HasFactory<ReviewInvitationFactory> */
    use HasFactory;

    public const VALID_DAYS = 30;

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

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

    /** @return BelongsTo<User, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /**
     * Create an invitation for the tenant whose rental just ended.
     * Returns null when that tenant already reviewed this room —
     * room_reviews allows only one review per tenant per room.
     */
    public static function issueFor(Room $room, int $tenantId): ?self
    {
        $alreadyReviewed = RoomReview::query()
            ->where('room_id', $room->id)
            ->where('tenant_id', $tenantId)
            ->exists();

        if ($alreadyReviewed) {
            return null;
        }

        return DB::transaction(function () use ($room, $tenantId): self {
            // One link per room + tenant: old uncompleted ones expire.
            static::query()
                ->where('room_id', $room->id)
                ->where('tenant_id', $tenantId)
                ->whereNull('completed_at')
                ->delete();

            return static::create([
                'room_id' => $room->id,
                'landlord_id' => $room->building->landlord_id,
                'tenant_id' => $tenantId,
                'token' => Str::random(64),
                'expires_at' => now()->addDays(self::VALID_DAYS),
            ]);
        });
    }

    public function isOpen(): bool
    {
        return $this->completed_at === null && $this->expires_at->isFuture();
    }

    public function url(): string
    {
        return route('reviews.create', $this);
    }
}
