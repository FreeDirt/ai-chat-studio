<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromptTemplate extends Model
{
    protected $fillable = ['title', 'shortcut', 'content', 'category', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
