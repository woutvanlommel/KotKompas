<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'content',
        'sort',
        'is_active',
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Locale-aware accessors. Each field is a nested JSON map:
     * {"vraag": {"nl": "...", "en": "..."}, "antwoord": {"nl": "...", "en": "..."}}.
     * Falls back current locale -> nl -> en -> first available, and still
     * supports a plain string value (backward compatible).
     */
    public function getVraagAttribute(): string
    {
        return $this->localized('vraag');
    }

    public function getAntwoordAttribute(): string
    {
        return $this->localized('antwoord');
    }

    protected function localized(string $key): string
    {
        $value = $this->content[$key] ?? '';

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
