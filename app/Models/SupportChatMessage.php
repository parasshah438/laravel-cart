<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_chat_id',
        'user_id',
        'message',
        'is_from_agent',
        'message_type',
        'read_at',
    ];

    protected $casts = [
        'is_from_agent' => 'boolean',
        'read_at' => 'datetime',
    ];

    // Message types
    const TYPE_MESSAGE = 'message';
    const TYPE_SYSTEM = 'system';
    const TYPE_FILE = 'file';
    const TYPE_IMAGE = 'image';

    /**
     * Get the chat this message belongs to
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(SupportChat::class, 'support_chat_id');
    }

    /**
     * Get the user who sent this message
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get messages from agents
     */
    public function scopeFromAgent($query)
    {
        return $query->where('is_from_agent', true);
    }

    /**
     * Scope to get messages from customers
     */
    public function scopeFromCustomer($query)
    {
        return $query->where('is_from_agent', false);
    }

    /**
     * Scope to get unread messages
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope to get system messages
     */
    public function scopeSystemMessages($query)
    {
        return $query->where('message_type', self::TYPE_SYSTEM);
    }

    /**
     * Check if message is from agent
     */
    public function isFromAgent(): bool
    {
        return $this->is_from_agent;
    }

    /**
     * Check if message is system message
     */
    public function isSystemMessage(): bool
    {
        return $this->message_type === self::TYPE_SYSTEM;
    }

    /**
     * Check if message is unread
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(): void
    {
        if ($this->isUnread()) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Get the sender type
     */
    public function getSenderTypeAttribute(): string
    {
        if ($this->message_type === self::TYPE_SYSTEM) {
            return 'System';
        }
        
        return $this->is_from_agent ? 'Agent' : 'Customer';
    }

    /**
     * Get formatted message
     */
    public function getFormattedMessageAttribute(): string
    {
        return nl2br(e($this->message));
    }

    /**
     * Boot method to update chat activity when message is created
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($message) {
            // Update chat's updated_at timestamp
            $message->chat->touch();
        });
    }
}