<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Concerns\HasImages;
use Database\Factories\UserFactory;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'lastname', 'email', 'phone', 'date_of_birth', 'password', 'provider', 'provider_id', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar, HasMedia
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasFactory, HasRoles, Notifiable, SoftDeletes;

    use HasImages {
        HasImages::registerMediaCollections as registerBaseMediaCollections;
        HasImages::registerMediaConversions as registerBaseMediaConversions;
    }

    public function registerMediaCollections(): void
    {
        // Single avatar image
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

        // General images collection from HasImages
        $this->registerBaseMediaCollections();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // WebP + thumb conversions from HasImages
        $this->registerBaseMediaConversions($media);

        // 200×200 square crop for avatar thumbnails
        $this->addMediaConversion('avatar_thumb')
            ->performOnCollections('avatar')
            ->format('webp')
            ->fit(Fit::Crop, 200, 200)
            ->quality(80);
    }

    /**
     * Reviews in which this user was rated as the landlord
     * (snapshot at submission — feeds the landlord score).
     *
     * @return HasMany<RoomReview, $this>
     */
    public function landlordReviews(): HasMany
    {
        return $this->hasMany(RoomReview::class, 'landlord_id');
    }

    /** @return HasMany<RentalPeriod, $this> */
    public function rentalPeriods(): HasMany
    {
        return $this->hasMany(RentalPeriod::class);
    }

    /** @return HasMany<Document, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->hasRole('admin'),
            'dashboard' => $this->hasAnyRole(['huurder', 'verhuurder']),
            default => false,
        };
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
            'landlord_score' => 'float',
            'landlord_reviews_count' => 'integer',
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->name} {$this->lastname}",
        );
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            // Uploaded avatar (media library) takes precedence over the OAuth provider picture.
            get: fn () => $this->getFirstMediaUrl('avatar', 'avatar_thumb')
                ?: $this->avatar  // Google/OAuth avatar URL stored as a plain string
                ?: null,
        );
    }

    /**
     * Avatar shown across Filament (topbar, profile nav). An uploaded image wins;
     * otherwise fall back to the social provider photo URL stored on the `avatar` column.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        if ($uploaded = $this->getFirstMediaUrl('avatar', 'avatar_thumb')) {
            return $uploaded;
        }

        if ($this->avatar) {
            return Str::startsWith($this->avatar, ['http://', 'https://'])
                ? $this->avatar
                : Storage::url($this->avatar);
        }

        return null;
    }

    public function sendPasswordResetNotification($token): void
    {
        ResetPasswordNotification::createUrlUsing(
            fn ($notifiable, $token) => Filament::getPanel('dashboard')->getResetPasswordUrl($token, $notifiable)
        );

        $this->notify(new ResetPasswordNotification($token));
    }
}
