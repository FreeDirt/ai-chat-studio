<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'token_quota',
        'avatar',
        'last_seen_at',
        'active_conversation_id',
        'is_typing',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
        'token_quota'       => 'integer',
        'last_seen_at'      => 'datetime',
        'is_typing'         => 'boolean',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'super_admin' || $this->role === 'admin';
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class)->latest();
    }

    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subSeconds(20));
    }
}
