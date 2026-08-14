<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'parent_id',
        'user_id',
        'role',
        'content',
        'model',
        'tokens_used',
        'response_time_ms',
    ];

    protected $casts = [
        'tokens_used'       => 'integer',
        'response_time_ms'  => 'integer',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Message::class, 'parent_id');
    }

    public function siblings()
    {
        if ($this->parent_id) {
            return Message::where('parent_id', $this->parent_id)->orderBy('id');
        }
        return Message::where('conversation_id', $this->conversation_id)
            ->whereNull('parent_id')
            ->orderBy('id');
    }

    public function getFormattedResponseTimeAttribute(): ?string
    {
        if (!$this->response_time_ms) return null;
        return $this->response_time_ms < 1000
            ? $this->response_time_ms . 'ms'
            : round($this->response_time_ms / 1000, 1) . 's';
    }
}
