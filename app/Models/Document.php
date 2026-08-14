<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'conversation_id', 'persona_id', 'original_name', 'stored_name',
        'mime_type', 'file_size', 'chunk_count', 'status', 'error_message',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Persona::class);
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(DocumentChunk::class)->orderBy('chunk_index');
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024)       return $bytes . ' B';
        if ($bytes < 1048576)    return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'ready'      => '#4ade80',
            'processing' => '#facc15',
            'failed'     => '#f87171',
            default      => '#9399ad',
        };
    }

    public function getIconAttribute(): string
    {
        return match(true) {
            str_contains($this->mime_type, 'pdf')  => '📄',
            str_contains($this->mime_type, 'word') => '📝',
            str_contains($this->mime_type, 'text') => '📃',
            str_contains($this->original_name, '.md')  => '📋',
            str_contains($this->original_name, '.csv') => '📊',
            default => '📁',
        };
    }
}
