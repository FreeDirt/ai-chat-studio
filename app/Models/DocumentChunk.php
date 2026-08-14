<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentChunk extends Model
{
    protected $fillable = [
        'document_id', 'chunk_index', 'content', 'embedding', 'token_estimate',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get embedding as a PHP float array.
     */
    public function getEmbeddingVectorAttribute(): array
    {
        return $this->embedding ? json_decode($this->embedding, true) : [];
    }
}
