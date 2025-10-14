<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\UserNotificationPreference;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        
        // Get filter parameters
        $filter = $request->get('filter', 'all'); // all, unread, important
        $type = $request->get('type');
        
        // Build query
        $query = $user->notifications()->latest();
        
        // Apply filters
        switch ($filter) {
            case 'unread':
                $query->unread();
                break;
            case 'important':
                $query->important();
                break;
        }
        
        if ($type) {
            $query->ofType($type);
        }
        
        // Paginate results
        $notifications = $query->paginate(15);
        
        // Get statistics
        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->notifications()->unread()->count(),
            'important' => $user->notifications()->important()->count(),
        ];
        
        // Get notification types for filter dropdown
        $types = $user->notifications()
            ->select('type')
            ->distinct()
            ->pluck('type')
            ->mapWithKeys(function ($type) {
                return [$type => ucwords(str_replace('_', ' ', $type))];
            });
        
        if ($request->ajax()) {
            return response()->json([
                'html' => view('notifications.partials.notification-list', compact('notifications'))->render(),
                'hasMorePages' => $notifications->hasMorePages(),
                'stats' => $stats
            ]);
        }
        
        return view('notifications.index', compact('notifications', 'stats', 'types', 'filter', 'type'));
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        // Ensure the notification belongs to the authenticated user
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to notification.'
            ], 403);
        }
        
        $notification->markAsRead();
        
        // Get updated unread count
        $unreadCount = Auth::user()->notifications()->unread()->count();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
            'unread_count' => $unreadCount,
            'action_url' => $notification->action_url
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        
        $updated = $user->notifications()
            ->unread()
            ->update(['read_at' => now()]);
        
        return response()->json([
            'success' => true,
            'message' => "Marked {$updated} notifications as read.",
            'unread_count' => 0
        ]);
    }

    /**
     * Delete a specific notification.
     */
    public function destroy(Notification $notification): JsonResponse
    {
        // Ensure the notification belongs to the authenticated user
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to notification.'
            ], 403);
        }
        
        $notification->delete();
        
        // Get updated counts
        $user = Auth::user();
        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->notifications()->unread()->count(),
            'important' => $user->notifications()->important()->count(),
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully.',
            'stats' => $stats
        ]);
    }

    /**
     * Get notification preferences.
     */
    public function preferences(): View
    {
        $user = Auth::user();
        $preferences = $user->getNotificationPreferences();
        
        return view('notifications.preferences', compact('preferences'));
    }

    /**
     * Update notification preferences.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'order_updates' => 'boolean',
            'payment_alerts' => 'boolean',
            'review_reminders' => 'boolean',
            'review_responses' => 'boolean',
            'promotional_emails' => 'boolean',
            'wishlist_sales' => 'boolean',
            'email_frequency' => 'in:instant,daily,weekly,never',
            'quiet_hours' => 'in:none,evening,night,weekend',
        ]);
        
        $user = Auth::user();
        
        // Get or create user notification preferences
        $preferences = $user->notificationPreferences;
        
        if ($preferences) {
            // Update existing preferences
            $preferences->update($validated);
        } else {
            // Create new preferences
            $user->notificationPreferences()->create($validated);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated successfully.',
            'preferences' => $user->fresh()->notificationPreferences
        ]);
    }

    /**
     * Get unread notifications count for header/navbar.
     */
    public function getUnreadCount(): JsonResponse
    {
        $count = Auth::user()->notifications()->unread()->count();
        
        return response()->json([
            'count' => $count
        ]);
    }

    /**
     * Get recent notifications for dropdown/popup.
     */
    public function getRecent(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        
        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->limit($limit)
            ->get();
        
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => Auth::user()->notifications()->unread()->count()
        ]);
    }

    /**
     * Create a new notification (used internally by the system).
     */
    public static function create($userId, $type, $title, $message, $data = null, $actionUrl = null, $isImportant = false)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
            'is_important' => $isImportant,
            'channel' => 'web'
        ]);
    }

    /**
     * Send order-related notifications.
     */
    public static function sendOrderNotification($orderId, $type, $userId = null)
    {
        // This would be called when order status changes
        $notifications = [
            'order_placed' => [
                'title' => 'Order Placed Successfully!',
                'message' => "Your order #{$orderId} has been placed and is being processed.",
                'url' => "/order/{$orderId}/track"
            ],
            'order_shipped' => [
                'title' => 'Order Shipped!',
                'message' => "Great news! Your order #{$orderId} has been shipped and is on its way.",
                'url' => "/order/{$orderId}/track"
            ],
            'order_delivered' => [
                'title' => 'Order Delivered!',
                'message' => "Your order #{$orderId} has been delivered. Please consider leaving a review.",
                'url' => "/order/{$orderId}"
            ],
            'order_cancelled' => [
                'title' => 'Order Cancelled',
                'message' => "Your order #{$orderId} has been cancelled. Refund will be processed within 3-5 business days.",
                'url' => "/order/{$orderId}"
            ]
        ];

        if (isset($notifications[$type])) {
            $notification = $notifications[$type];
            return self::create(
                $userId,
                $type,
                $notification['title'],
                $notification['message'],
                ['order_id' => $orderId],
                $notification['url'],
                in_array($type, ['order_cancelled', 'payment_failed'])
            );
        }
    }
}
