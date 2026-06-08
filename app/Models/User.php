<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Concerns\HasImages;
use Database\Factories\UserFactory;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'lastname', 'email', 'phone', 'date_of_birth', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    use HasImages {
        HasImages::registerMediaCollections as registerBaseMediaCollections;
        HasImages::registerMediaConversions as registerBaseMediaConversions;
    }

    public function registerMediaCollections(): void
    {
        // Single avatar image
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        // General images collection from HasImages
        $this->registerBaseMediaCollections();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // WebP + thumb conversions from HasImages
        $this->registerBaseMediaConversions($media);

        // Square crop for avatar thumbnails
        $this->addMediaConversion('avatar_thumb')
            ->performOnCollections('avatar')
            ->format('webp')
            ->width(200)
            ->height(200)
            ->quality(80);
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
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->name} {$this->lastname}",
        );
    }

    public function sendPasswordResetNotification($token): void
    {
        ResetPasswordNotification::createUrlUsing(
            fn ($notifiable, $token) => Filament::getPanel('dashboard')->getResetPasswordUrl($token, $notifiable)
        );

        $this->notify(new ResetPasswordNotification($token));
    }
}
