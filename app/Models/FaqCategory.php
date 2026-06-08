<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FaqCategory extends Model
{
    protected $fillable = [
        'name',
        'sort',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'is_active' => 'boolean',
    ];

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }

    /**
     * Locale-aware name (nested JSON {nl, en}), fallback locale -> nl -> en.
     */
    public function getNaamAttribute(): string
    {
        $value = $this->name ?? [];

        if (! is_array($value)) {
            return (string) $value;
        }

        return $value[app()->getLocale()]
            ?? $value['nl']
            ?? $value['en']
            ?? (string) reset($value)
            ?: '';
    }
}
