<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'email_notifications',
        'push_notifications',
        'order_updates',
        'payment_alerts',
        'review_reminders',
        'review_responses',
        'promotional_emails',
        'wishlist_sales',
        'email_frequency',
        'quiet_hours',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'order_updates' => 'boolean',
        'payment_alerts' => 'boolean',
        'review_reminders' => 'boolean',
        'review_responses' => 'boolean',
        'promotional_emails' => 'boolean',
        'wishlist_sales' => 'boolean',
    ];

    /**
     * Get the user that owns the notification preferences.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get default preferences for a new user.
     */
    public static function getDefaults(): array
    {
        return [
            'email_notifications' => true,
            'push_notifications' => false,
            'order_updates' => true,
            'payment_alerts' => true,
            'review_reminders' => true,
            'review_responses' => true,
            'promotional_emails' => false,
            'wishlist_sales' => true,
            'email_frequency' => 'daily',
            'quiet_hours' => 'night',
        ];
    }

    /**
     * Check if user wants specific notification type.
     */
    public function wantsNotification(string $type): bool
    {
        return match($type) {
            'order_placed', 'order_shipped', 'order_delivered', 'order_cancelled' => $this->order_updates,
            'payment_success', 'payment_failed' => $this->payment_alerts,
            'review_request' => $this->review_reminders,
            'review_response' => $this->review_responses,
            'promotion', 'promotional' => $this->promotional_emails,
            'wishlist_sale' => $this->wishlist_sales,
            default => true // For system notifications, always send
        };
    }

    /**
     * Check if user is in quiet hours.
     */
    public function isInQuietHours(): bool
    {
        $now = now();
        
        return match($this->quiet_hours) {
            'evening' => $now->hour >= 18 || $now->hour < 9,
            'night' => $now->hour >= 22 || $now->hour < 8,
            'weekend' => $now->isWeekend(),
            default => false
        };
    }

    /**
     * Check if user wants email notifications.
     */
    public function wantsEmailNotification(): bool
    {
        return $this->email_notifications && !$this->isInQuietHours();
    }

    /**
     * Check if user wants push notifications.
     */
    public function wantsPushNotification(): bool
    {
        return $this->push_notifications && !$this->isInQuietHours();
    }
}