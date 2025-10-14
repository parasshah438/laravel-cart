<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a new notification
     */
    public static function create($userId, $type, $title, $message, $data = null, $actionUrl = null, $isImportant = false, $channel = 'web')
    {
        try {
            return Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'action_url' => $actionUrl,
                'is_important' => $isImportant,
                'channel' => $channel
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create notification: ' . $e->getMessage(), [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title
            ]);
            return null;
        }
    }

    /**
     * Send order-related notifications
     */
    public static function sendOrderNotification($orderId, $type, $userId, $additionalData = [])
    {
        $notifications = [
            Notification::TYPE_ORDER_PLACED => [
                'title' => 'Order Placed Successfully! 🛍️',
                'message' => "Your order #{$orderId} has been placed and is being processed. You'll receive updates as it progresses.",
                'url' => "/order/{$orderId}/track",
                'important' => false
            ],
            Notification::TYPE_ORDER_SHIPPED => [
                'title' => 'Great News! Your Order is Shipped 🚚',
                'message' => "Your order #{$orderId} has been shipped and is on its way to you. Track your package for real-time updates.",
                'url' => "/order/{$orderId}/track",
                'important' => true
            ],
            Notification::TYPE_ORDER_DELIVERED => [
                'title' => 'Order Delivered Successfully! 📦',
                'message' => "Your order #{$orderId} has been delivered. We hope you love your purchase! Please consider leaving a review.",
                'url' => "/order/{$orderId}",
                'important' => false
            ],
            Notification::TYPE_ORDER_CANCELLED => [
                'title' => 'Order Cancelled ❌',
                'message' => "Your order #{$orderId} has been cancelled. Refund will be processed within 3-5 business days.",
                'url' => "/order/{$orderId}",
                'important' => true
            ]
        ];

        if (isset($notifications[$type])) {
            $notification = $notifications[$type];
            
            $data = array_merge(['order_id' => $orderId], $additionalData);
            
            return self::create(
                $userId,
                $type,
                $notification['title'],
                $notification['message'],
                $data,
                $notification['url'],
                $notification['important']
            );
        }

        return null;
    }

    /**
     * Send payment-related notifications
     */
    public static function sendPaymentNotification($orderId, $type, $userId, $amount = null, $additionalData = [])
    {
        $notifications = [
            Notification::TYPE_PAYMENT_SUCCESS => [
                'title' => 'Payment Successful! ✅',
                'message' => $amount 
                    ? "Your payment of {$amount} for order #{$orderId} has been processed successfully. Thank you!"
                    : "Your payment for order #{$orderId} has been processed successfully. Thank you!",
                'url' => "/order/{$orderId}",
                'important' => false
            ],
            Notification::TYPE_PAYMENT_FAILED => [
                'title' => 'Payment Failed ❌',
                'message' => "We couldn't process your payment for order #{$orderId}. Please try again or use a different payment method.",
                'url' => "/order/{$orderId}/payment",
                'important' => true
            ]
        ];

        if (isset($notifications[$type])) {
            $notification = $notifications[$type];
            
            $data = array_merge([
                'order_id' => $orderId,
                'amount' => $amount
            ], $additionalData);
            
            return self::create(
                $userId,
                $type,
                $notification['title'],
                $notification['message'],
                $data,
                $notification['url'],
                $notification['important']
            );
        }

        return null;
    }

    /**
     * Send review-related notifications
     */
    public static function sendReviewNotification($type, $userId, $productName, $orderId = null, $additionalData = [])
    {
        $notifications = [
            Notification::TYPE_REVIEW_REQUEST => [
                'title' => 'How was your recent purchase? ⭐',
                'message' => "We'd love to hear about your experience with {$productName}. Your review helps other customers!",
                'url' => $orderId ? "/order/{$orderId}/review" : "/product/{$productName}/review",
                'important' => false
            ],
            Notification::TYPE_REVIEW_RESPONSE => [
                'title' => 'Someone responded to your review! 💬',
                'message' => "Your review for {$productName} received a response. Check it out and continue the conversation!",
                'url' => "/product/{$productName}/reviews",
                'important' => false
            ]
        ];

        if (isset($notifications[$type])) {
            $notification = $notifications[$type];
            
            $data = array_merge([
                'product_name' => $productName,
                'order_id' => $orderId
            ], $additionalData);
            
            return self::create(
                $userId,
                $type,
                $notification['title'],
                $notification['message'],
                $data,
                $notification['url'],
                $notification['important']
            );
        }

        return null;
    }

    /**
     * Send wishlist-related notifications
     */
    public static function sendWishlistNotification($userId, $productName, $discount = null, $productSlug = null, $additionalData = [])
    {
        $title = '🔥 Wishlist Item on Sale!';
        $message = $discount 
            ? "Great news! {$productName} from your wishlist is now {$discount} off. Limited time offer!"
            : "Great news! {$productName} from your wishlist is now on sale. Check it out!";
        
        $url = $productSlug ? "/product/{$productSlug}" : "/wishlist";
        
        $data = array_merge([
            'product_name' => $productName,
            'discount' => $discount
        ], $additionalData);
        
        return self::create(
            $userId,
            Notification::TYPE_WISHLIST_SALE,
            $title,
            $message,
            $data,
            $url,
            true // Wishlist sales are important
        );
    }

    /**
     * Send promotional notifications
     */
    public static function sendPromotionalNotification($userIds, $title, $message, $actionUrl = null, $additionalData = [])
    {
        $notifications = [];
        
        // Convert single user ID to array
        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }
        
        foreach ($userIds as $userId) {
            $notification = self::create(
                $userId,
                Notification::TYPE_PROMOTION,
                $title,
                $message,
                $additionalData,
                $actionUrl,
                false
            );
            
            if ($notification) {
                $notifications[] = $notification;
            }
        }
        
        return $notifications;
    }

    /**
     * Send welcome notification to new users
     */
    public static function sendWelcomeNotification($userId, $userName = null)
    {
        $title = 'Welcome to Laravel Cart! 🎉';
        $message = $userName 
            ? "Hi {$userName}! Thank you for joining our community. Enjoy free shipping on your first order!"
            : "Thank you for joining our community! Enjoy free shipping on your first order and explore our amazing products.";
        
        return self::create(
            $userId,
            Notification::TYPE_WELCOME,
            $title,
            $message,
            ['welcome_offer' => 'free_shipping'],
            '/shop',
            false
        );
    }

    /**
     * Send system notifications (security, updates, etc.)
     */
    public static function sendSystemNotification($userId, $title, $message, $actionUrl = null, $isImportant = true, $additionalData = [])
    {
        return self::create(
            $userId,
            Notification::TYPE_SYSTEM,
            $title,
            $message,
            $additionalData,
            $actionUrl,
            $isImportant
        );
    }

    /**
     * Send bulk notifications to multiple users
     */
    public static function sendBulkNotifications($userIds, $type, $title, $message, $data = null, $actionUrl = null, $isImportant = false)
    {
        $notifications = [];
        
        foreach ($userIds as $userId) {
            $notification = self::create(
                $userId,
                $type,
                $title,
                $message,
                $data,
                $actionUrl,
                $isImportant
            );
            
            if ($notification) {
                $notifications[] = $notification;
            }
        }
        
        return $notifications;
    }

    /**
     * Mark all notifications as read for a user
     */
    public static function markAllAsRead($userId)
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get unread count for a user
     */
    public static function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Clean up old notifications (older than specified days)
     */
    public static function cleanupOldNotifications($daysOld = 90)
    {
        $cutoffDate = now()->subDays($daysOld);
        
        return Notification::where('created_at', '<', $cutoffDate)
            ->whereNotNull('read_at') // Only delete read notifications
            ->delete();
    }

    /**
     * Get notification statistics for a user
     */
    public static function getNotificationStats($userId)
    {
        $user = User::find($userId);
        
        if (!$user) {
            return null;
        }
        
        return [
            'total' => $user->notifications()->count(),
            'unread' => $user->notifications()->unread()->count(),
            'important' => $user->notifications()->important()->count(),
            'today' => $user->notifications()->whereDate('created_at', today())->count(),
            'this_week' => $user->notifications()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
    }
}