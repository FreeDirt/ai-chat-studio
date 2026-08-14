<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Persona extends Model
{
    protected $fillable = ['name', 'icon', 'system_prompt', 'color', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class)->latest();
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get HTML representation of the icon (supports Emoji text or GIF/Image URLs).
     */
    public function getFormattedIconAttribute(): string
    {
        $icon = trim($this->icon ?? '🤖');

        if (
            str_starts_with($icon, 'http://') ||
            str_starts_with($icon, 'https://') ||
            str_starts_with($icon, '/') ||
            str_starts_with($icon, 'data:image/')
        ) {
            return '<img src="' . e($icon) . '" alt="icon" style="width:100%;height:100%;object-fit:cover;border-radius:inherit">';
        }

        return e($icon);
    }
}
