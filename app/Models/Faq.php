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
     * Convenience accessors for the JSON pair.
     */
    public function getVraagAttribute(): string
    {
        return $this->content['vraag'] ?? '';
    }

    public function getAntwoordAttribute(): string
    {
        return $this->content['antwoord'] ?? '';
    }
}
