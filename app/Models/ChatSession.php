<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_type',
        'is_active',
        'started_at',
        'ended_at',
        'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * Get the user that owns the chat session
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the messages for the chat session
     */
    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Scope for active sessions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for session type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('session_type', $type);
    }

    /**
     * Get the latest message in the session
     */
    public function getLatestMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Get message count for the session
     */
    public function getMessageCountAttribute()
    {
        return $this->messages()->count();
    }
}