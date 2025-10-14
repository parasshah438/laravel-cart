<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'icon',
        'color',
        'action_url',
        'read_at',
        'is_important',
        'channel'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'is_important' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Notification types
     */
    const TYPE_ORDER_PLACED = 'order_placed';
    const TYPE_ORDER_SHIPPED = 'order_shipped';
    const TYPE_ORDER_DELIVERED = 'order_delivered';
    const TYPE_ORDER_CANCELLED = 'order_cancelled';
    const TYPE_PAYMENT_SUCCESS = 'payment_success';
    const TYPE_PAYMENT_FAILED = 'payment_failed';
    const TYPE_REVIEW_REQUEST = 'review_request';
    const TYPE_REVIEW_RESPONSE = 'review_response';
    const TYPE_WISHLIST_SALE = 'wishlist_sale';
    const TYPE_PROMOTION = 'promotion';
    const TYPE_WELCOME = 'welcome';
    const TYPE_SYSTEM = 'system';

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine if the notification has been read.
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): bool
    {
        if ($this->isRead()) {
            return false;
        }

        return $this->update(['read_at' => now()]);
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread(): bool
    {
        return $this->update(['read_at' => null]);
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope for important notifications.
     */
    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }

    /**
     * Scope for specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get time ago string for created_at
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get notification icon with fallback
     */
    public function getIconAttribute($value)
    {
        $iconMap = [
            self::TYPE_ORDER_PLACED => 'shopping-bag',
            self::TYPE_ORDER_SHIPPED => 'truck',
            self::TYPE_ORDER_DELIVERED => 'check-circle',
            self::TYPE_ORDER_CANCELLED => 'x-circle',
            self::TYPE_PAYMENT_SUCCESS => 'credit-card',
            self::TYPE_PAYMENT_FAILED => 'alert-circle',
            self::TYPE_REVIEW_REQUEST => 'star',
            self::TYPE_REVIEW_RESPONSE => 'message-circle',
            self::TYPE_WISHLIST_SALE => 'heart',
            self::TYPE_PROMOTION => 'tag',
            self::TYPE_WELCOME => 'user',
            self::TYPE_SYSTEM => 'settings',
        ];

        return $iconMap[$this->type] ?? $value ?? 'bell';
    }

    /**
     * Get notification color theme
     */
    public function getColorAttribute($value)
    {
        $colorMap = [
            self::TYPE_ORDER_PLACED => 'success',
            self::TYPE_ORDER_SHIPPED => 'info',
            self::TYPE_ORDER_DELIVERED => 'success',
            self::TYPE_ORDER_CANCELLED => 'danger',
            self::TYPE_PAYMENT_SUCCESS => 'success',
            self::TYPE_PAYMENT_FAILED => 'danger',
            self::TYPE_REVIEW_REQUEST => 'warning',
            self::TYPE_REVIEW_RESPONSE => 'info',
            self::TYPE_WISHLIST_SALE => 'pink',
            self::TYPE_PROMOTION => 'purple',
            self::TYPE_WELCOME => 'primary',
            self::TYPE_SYSTEM => 'secondary',
        ];

        return $colorMap[$this->type] ?? $value ?? 'primary';
    }
}
