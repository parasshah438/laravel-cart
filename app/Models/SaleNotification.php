<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class SaleNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'sale_event_id',
        'product_id',
        'title',
        'message',
        'action_url',
        'scheduled_for',
        'sent_at',
        'is_read',
        'read_at',
        'sent_via_email',
        'sent_via_push',
        'sent_via_sms',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'is_read' => 'boolean',
        'sent_via_email' => 'boolean',
        'sent_via_push' => 'boolean',
        'sent_via_sms' => 'boolean',
    ];

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sale event
     */
    public function saleEvent(): BelongsTo
    {
        return $this->belongsTo(SaleEvent::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope: Unread notifications
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: Sent notifications
     */
    public function scopeSent(Builder $query): Builder
    {
        return $query->whereNotNull('sent_at');
    }

    /**
     * Scope: Pending notifications
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('sent_at')
            ->where(function ($q) {
                $q->whereNull('scheduled_for')
                  ->orWhere('scheduled_for', '<=', now());
            });
    }

    /**
     * Scope: By notification type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return true;
        }

        $this->is_read = true;
        $this->read_at = now();

        return $this->save();
    }

    /**
     * Mark notification as sent
     */
    public function markAsSent(array $channels = []): bool
    {
        $this->sent_at = now();

        foreach ($channels as $channel) {
            switch ($channel) {
                case 'email':
                    $this->sent_via_email = true;
                    break;
                case 'push':
                    $this->sent_via_push = true;
                    break;
                case 'sms':
                    $this->sent_via_sms = true;
                    break;
            }
        }

        return $this->save();
    }

    /**
     * Check if notification is scheduled for future
     */
    public function isScheduled(): bool
    {
        return $this->scheduled_for && $this->scheduled_for > now();
    }

    /**
     * Check if notification was sent
     */
    public function isSent(): bool
    {
        return $this->sent_at !== null;
    }

    /**
     * Get channels this notification was sent via
     */
    public function getSentChannels(): array
    {
        $channels = [];

        if ($this->sent_via_email) {
            $channels[] = 'email';
        }

        if ($this->sent_via_push) {
            $channels[] = 'push';
        }

        if ($this->sent_via_sms) {
            $channels[] = 'sms';
        }

        return $channels;
    }
}