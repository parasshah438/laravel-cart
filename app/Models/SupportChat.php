<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'agent_id',
        'status',
        'subject',
        'started_at',
        'ended_at',
        'rating',
        'feedback',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'rating' => 'integer',
    ];

    // Chat statuses
    const STATUS_WAITING = 'waiting';
    const STATUS_ACTIVE = 'active';
    const STATUS_ENDED = 'ended';
    const STATUS_ABANDONED = 'abandoned';

    /**
     * Get the user (customer) for this chat
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the assigned agent for this chat
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get all messages for this chat
     */
    public function messages(): HasMany
    {
        return $this->hasMany(SupportChatMessage::class)->orderBy('created_at');
    }

    /**
     * Get the latest message
     */
    public function latestMessage()
    {
        return $this->hasOne(SupportChatMessage::class)->latest();
    }

    /**
     * Scope to get active chats
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to get waiting chats
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', self::STATUS_WAITING);
    }

    /**
     * Scope to get ended chats
     */
    public function scopeEnded($query)
    {
        return $query->where('status', self::STATUS_ENDED);
    }

    /**
     * Check if chat is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if chat is waiting for agent
     */
    public function isWaiting(): bool
    {
        return $this->status === self::STATUS_WAITING;
    }

    /**
     * Check if chat has ended
     */
    public function hasEnded(): bool
    {
        return in_array($this->status, [self::STATUS_ENDED, self::STATUS_ABANDONED]);
    }

    /**
     * Start the chat session
     */
    public function start($agentId = null): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'agent_id' => $agentId,
            'started_at' => now(),
        ]);
    }

    /**
     * End the chat session
     */
    public function end(): void
    {
        $this->update([
            'status' => self::STATUS_ENDED,
            'ended_at' => now(),
        ]);
    }

    /**
     * Mark chat as abandoned
     */
    public function abandon(): void
    {
        $this->update([
            'status' => self::STATUS_ABANDONED,
            'ended_at' => now(),
        ]);
    }

    /**
     * Get chat duration in minutes
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->started_at || !$this->ended_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->ended_at);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration) {
            return 'N/A';
        }

        $hours = intval($this->duration / 60);
        $minutes = $this->duration % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_WAITING => 'yellow',
            self::STATUS_ACTIVE => 'green',
            self::STATUS_ENDED => 'blue',
            self::STATUS_ABANDONED => 'red',
            default => 'gray'
        };
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute(): string
    {
        return ucwords($this->status);
    }

    /**
     * Rate the chat session
     */
    public function rate(int $rating, ?string $feedback = null): void
    {
        $this->update([
            'rating' => $rating,
            'feedback' => $feedback,
        ]);
    }

    /**
     * Get chat session number
     */
    public function getChatNumberAttribute(): string
    {
        return 'CHAT-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }
}