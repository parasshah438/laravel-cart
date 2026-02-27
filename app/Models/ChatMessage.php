<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_session_id',
        'message',
        'sender_type',
        'metadata',
        'is_read'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_read' => 'boolean'
    ];

    /**
     * Get the chat session that owns the message
     */
    public function chatSession()
    {
        return $this->belongsTo(ChatSession::class);
    }

    /**
     * Scope for user messages
     */
    public function scopeFromUser($query)
    {
        return $query->where('sender_type', 'user');
    }

    /**
     * Scope for bot messages
     */
    public function scopeFromBot($query)
    {
        return $query->where('sender_type', 'bot');
    }

    /**
     * Scope for unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Get formatted timestamp
     */
    public function getFormattedTimeAttribute()
    {
        return $this->created_at->format('H:i');
    }

    /**
     * Check if message is from user
     */
    public function getIsFromUserAttribute()
    {
        return $this->sender_type === 'user';
    }

    /**
     * Check if message is from bot
     */
    public function getIsFromBotAttribute()
    {
        return $this->sender_type === 'bot';
    }
}