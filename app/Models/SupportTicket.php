<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'description',
        'status',
        'priority',
        'category',
        'order_id',
        'product_id',
        'assigned_agent_id',
        'closed_at',
        'first_response_at',
        'last_activity_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'first_response_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    // Ticket statuses
    const STATUS_OPEN = 'open';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_WAITING_CUSTOMER = 'waiting_customer';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED = 'closed';

    // Ticket priorities
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // Ticket categories
    const CATEGORY_GENERAL = 'general';
    const CATEGORY_ORDER = 'order';
    const CATEGORY_PRODUCT = 'product';
    const CATEGORY_BILLING = 'billing';
    const CATEGORY_TECHNICAL = 'technical';
    const CATEGORY_COMPLAINT = 'complaint';

    /**
     * Get the user that owns the ticket
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the assigned agent
     */
    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    /**
     * Get the related order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the related product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get all replies for this ticket
     */
    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class)->orderBy('created_at');
    }

    /**
     * Get the latest reply
     */
    public function latestReply()
    {
        return $this->hasOne(SupportTicketReply::class)->latest();
    }

    /**
     * Scope to get tickets by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get open tickets
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', [
            self::STATUS_OPEN,
            self::STATUS_IN_PROGRESS,
            self::STATUS_WAITING_CUSTOMER
        ]);
    }

    /**
     * Scope to get closed tickets
     */
    public function scopeClosed($query)
    {
        return $query->whereIn('status', [
            self::STATUS_RESOLVED,
            self::STATUS_CLOSED
        ]);
    }

    /**
     * Scope to get tickets by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to get urgent tickets
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_URGENT);
    }

    /**
     * Check if ticket is open
     */
    public function isOpen(): bool
    {
        return in_array($this->status, [
            self::STATUS_OPEN,
            self::STATUS_IN_PROGRESS,
            self::STATUS_WAITING_CUSTOMER
        ]);
    }

    /**
     * Check if ticket is closed
     */
    public function isClosed(): bool
    {
        return in_array($this->status, [
            self::STATUS_RESOLVED,
            self::STATUS_CLOSED
        ]);
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_OPEN => 'blue',
            self::STATUS_IN_PROGRESS => 'yellow',
            self::STATUS_WAITING_CUSTOMER => 'orange',
            self::STATUS_RESOLVED => 'green',
            self::STATUS_CLOSED => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get priority badge color
     */
    public function getPriorityBadgeColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'green',
            self::PRIORITY_NORMAL => 'blue',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_URGENT => 'red',
            default => 'gray'
        };
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    /**
     * Get formatted priority
     */
    public function getFormattedPriorityAttribute(): string
    {
        return ucwords($this->priority);
    }

    /**
     * Get ticket number (formatted ID)
     */
    public function getTicketNumberAttribute(): string
    {
        return 'TICK-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Close the ticket
     */
    public function close(): void
    {
        $this->update([
            'status' => self::STATUS_CLOSED,
            'closed_at' => now(),
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Mark ticket as resolved
     */
    public function resolve(): void
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Update last activity
     */
    public function updateLastActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }
}