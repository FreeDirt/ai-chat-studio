<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Conversation extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'provider',
        'model',
        'is_pinned',
        'visibility',
        'share_token',
        'last_active_at',
    ];

    protected $casts = [
        'is_pinned'      => 'boolean',
        'last_active_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class)->latest();
    }

    public function shares(): HasMany
    {
        return $this->hasMany(ConversationShare::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function totalTokens(): int
    {
        return $this->messages()->sum('tokens_used') ?? 0;
    }

    public function isAccessibleBy(?User $user): bool
    {
        if ($this->visibility === 'team' || $this->visibility === 'link') {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($this->user_id === $user->id || $user->isSuperAdmin()) {
            return true;
        }

        return $this->shares()->where('user_id', $user->id)->exists();
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->share_token)) {
                $model->share_token = Str::random(32);
            }
        });
    }

    public static function generateTitle(string $message): string
    {
        $title = substr($message, 0, 60);
        return strlen($message) > 60 ? $title . '...' : $title;
    }
}
