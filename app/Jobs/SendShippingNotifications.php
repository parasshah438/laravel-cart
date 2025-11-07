<?php

namespace App\Jobs;

use App\Models\OrderShipment;
use App\Mail\ShippingConfirmation;
use App\Mail\DeliveryNotification;
use App\Mail\ShippingException;
use App\Mail\ShippingStatusUpdate;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ShippingUpdateNotification;

class SendShippingNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shipment;
    protected $notificationType;
    protected $additionalData;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     */
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(OrderShipment $shipment, string $notificationType, array $additionalData = [])
    {
        $this->shipment = $shipment;
        $this->notificationType = $notificationType;
        $this->additionalData = $additionalData;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            Log::info('Sending shipping notification', [
                'shipment_id' => $this->shipment->id,
                'notification_type' => $this->notificationType,
                'customer_email' => $this->shipment->order->user->email
            ]);

            $user = $this->shipment->order->user;

            // Send customer notifications
            $this->sendCustomerNotifications($user);

            // Send admin notifications if required
            $this->sendAdminNotifications();

            // Send SMS notifications if enabled
            $this->sendSMSNotifications($user);

            // Send push notifications if enabled
            $this->sendPushNotifications($user);

            // Update notification log
            $this->logNotification();

            Log::info('Shipping notification sent successfully', [
                'shipment_id' => $this->shipment->id,
                'notification_type' => $this->notificationType
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send shipping notification', [
                'shipment_id' => $this->shipment->id,
                'notification_type' => $this->notificationType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Send notifications to customer
     */
    protected function sendCustomerNotifications(User $user)
    {
        // Check user notification preferences
        $preferences = $user->notification_preferences ?? [];
        
        if ($this->shouldSendEmailNotification($preferences)) {
            $this->sendEmailNotification($user);
        }

        if ($this->shouldSendInAppNotification($preferences)) {
            $this->sendInAppNotification($user);
        }
    }

    /**
     * Send email notification to customer
     */
    protected function sendEmailNotification(User $user)
    {
        $mailable = $this->getMailableClass();
        
        if ($mailable) {
            Mail::to($user->email)->send(new $mailable($this->shipment, $this->additionalData));
            
            Log::info('Email notification sent', [
                'shipment_id' => $this->shipment->id,
                'email' => $user->email,
                'mailable' => $mailable
            ]);
        }
    }

    /**
     * Get the appropriate mailable class for the notification type
     */
    protected function getMailableClass(): ?string
    {
        return match($this->notificationType) {
            'created', 'confirmed' => ShippingConfirmation::class,
            'delivered' => DeliveryNotification::class,
            'exception' => ShippingException::class,
            'status_update', 'webhook_update', 'out_for_delivery' => ShippingStatusUpdate::class,
            default => ShippingStatusUpdate::class
        };
    }

    /**
     * Send in-app notification
     */
    protected function sendInAppNotification(User $user)
    {
        $notificationData = [
            'shipment_id' => $this->shipment->id,
            'order_number' => $this->shipment->order->order_number,
            'status' => $this->shipment->status,
            'tracking_number' => $this->shipment->tracking_number,
            'notification_type' => $this->notificationType,
            'message' => $this->getNotificationMessage(),
            'action_url' => route('orders.show', $this->shipment->order)
        ];

        Notification::send($user, new ShippingUpdateNotification($notificationData));

        Log::info('In-app notification sent', [
            'shipment_id' => $this->shipment->id,
            'user_id' => $user->id
        ]);
    }

    /**
     * Send admin notifications for important events
     */
    protected function sendAdminNotifications()
    {
        // Send admin notifications for exceptions, delays, or other critical events
        if (in_array($this->notificationType, ['exception', 'returned', 'delayed'])) {
            $adminEmails = config('shipping.admin_notification_emails', []);
            
            foreach ($adminEmails as $email) {
                Mail::to($email)->send(new ShippingException($this->shipment, array_merge($this->additionalData, [
                    'is_admin_notification' => true
                ])));
            }

            Log::info('Admin notification sent for critical event', [
                'shipment_id' => $this->shipment->id,
                'notification_type' => $this->notificationType,
                'admin_emails' => $adminEmails
            ]);
        }
    }

    /**
     * Send SMS notifications
     */
    protected function sendSMSNotifications(User $user)
    {
        // Check if SMS notifications are enabled and user has phone number
        if (!config('shipping.sms_notifications_enabled', false)) {
            return;
        }

        $phoneNumber = $user->phone ?? $this->shipment->order->shippingAddress->phone ?? null;
        
        if (!$phoneNumber) {
            return;
        }

        // Check user preferences
        $preferences = $user->notification_preferences ?? [];
        if (!($preferences['sms'] ?? true)) {
            return;
        }

        // Send SMS for important status updates
        if (in_array($this->notificationType, ['delivered', 'out_for_delivery', 'exception'])) {
            $message = $this->getSMSMessage();
            $this->sendSMS($phoneNumber, $message);
        }
    }

    /**
     * Send SMS message
     */
    protected function sendSMS(string $phoneNumber, string $message)
    {
        try {
            // Integration with SMS provider (Twilio, AWS SNS, etc.)
            // For now, just log the SMS that would be sent
            Log::info('SMS notification would be sent', [
                'shipment_id' => $this->shipment->id,
                'phone' => $phoneNumber,
                'message' => $message
            ]);

            // Actual SMS implementation would go here
            // SMSService::send($phoneNumber, $message);

        } catch (\Exception $e) {
            Log::error('Failed to send SMS notification', [
                'shipment_id' => $this->shipment->id,
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send push notifications
     */
    protected function sendPushNotifications(User $user)
    {
        // Check if push notifications are enabled
        if (!config('shipping.push_notifications_enabled', false)) {
            return;
        }

        // Check user preferences
        $preferences = $user->notification_preferences ?? [];
        if (!($preferences['push'] ?? true)) {
            return;
        }

        // Send push notification for important updates
        if (in_array($this->notificationType, ['delivered', 'out_for_delivery', 'exception'])) {
            $this->sendPushNotification($user);
        }
    }

    /**
     * Send push notification
     */
    protected function sendPushNotification(User $user)
    {
        try {
            $notificationData = [
                'title' => $this->getPushNotificationTitle(),
                'body' => $this->getPushNotificationBody(),
                'data' => [
                    'shipment_id' => $this->shipment->id,
                    'order_id' => $this->shipment->order->id,
                    'type' => 'shipping_update'
                ]
            ];

            // Integration with push notification service (FCM, OneSignal, etc.)
            Log::info('Push notification would be sent', [
                'shipment_id' => $this->shipment->id,
                'user_id' => $user->id,
                'notification_data' => $notificationData
            ]);

            // Actual push notification implementation would go here
            // PushNotificationService::send($user, $notificationData);

        } catch (\Exception $e) {
            Log::error('Failed to send push notification', [
                'shipment_id' => $this->shipment->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log notification for tracking purposes
     */
    protected function logNotification()
    {
        // Update shipment metadata with notification log
        $notifications = $this->shipment->metadata['notifications'] ?? [];
        $notifications[] = [
            'type' => $this->notificationType,
            'sent_at' => now(),
            'channels' => $this->getNotificationChannels(),
            'status' => 'sent'
        ];

        $this->shipment->update([
            'metadata' => array_merge($this->shipment->metadata ?? [], [
                'notifications' => $notifications,
                'last_notification_sent' => now()
            ])
        ]);
    }

    /**
     * Get notification channels used
     */
    protected function getNotificationChannels(): array
    {
        $channels = ['email', 'in_app'];

        if (config('shipping.sms_notifications_enabled', false)) {
            $channels[] = 'sms';
        }

        if (config('shipping.push_notifications_enabled', false)) {
            $channels[] = 'push';
        }

        return $channels;
    }

    /**
     * Check if email notification should be sent
     */
    protected function shouldSendEmailNotification(array $preferences): bool
    {
        return $preferences['email'] ?? true;
    }

    /**
     * Check if in-app notification should be sent
     */
    protected function shouldSendInAppNotification(array $preferences): bool
    {
        return $preferences['in_app'] ?? true;
    }

    /**
     * Get notification message for in-app notifications
     */
    protected function getNotificationMessage(): string
    {
        $orderNumber = $this->shipment->order->order_number;
        
        return match($this->notificationType) {
            'created', 'confirmed' => "Your order #{$orderNumber} has been confirmed and will be shipped soon.",
            'picked_up' => "Your order #{$orderNumber} has been picked up and is on its way.",
            'in_transit' => "Your order #{$orderNumber} is in transit.",
            'out_for_delivery' => "Your order #{$orderNumber} is out for delivery and will be delivered today.",
            'delivered' => "Your order #{$orderNumber} has been delivered successfully.",
            'exception' => "There's an issue with your order #{$orderNumber}. We're working to resolve it.",
            'returned' => "Your order #{$orderNumber} is being returned.",
            default => "There's an update on your order #{$orderNumber}."
        };
    }

    /**
     * Get SMS message
     */
    protected function getSMSMessage(): string
    {
        $orderNumber = $this->shipment->order->order_number;
        $companyName = config('app.name');
        
        return match($this->notificationType) {
            'out_for_delivery' => "{$companyName}: Your order #{$orderNumber} is out for delivery. Track: " . route('orders.track', $this->shipment->tracking_number),
            'delivered' => "{$companyName}: Your order #{$orderNumber} has been delivered. Thank you for shopping with us!",
            'exception' => "{$companyName}: Issue with order #{$orderNumber}. We're resolving it. Check your account for updates.",
            default => "{$companyName}: Update on order #{$orderNumber}. Check your account for details."
        };
    }

    /**
     * Get push notification title
     */
    protected function getPushNotificationTitle(): string
    {
        return match($this->notificationType) {
            'delivered' => 'Order Delivered!',
            'out_for_delivery' => 'Out for Delivery',
            'exception' => 'Delivery Update',
            default => 'Shipping Update'
        };
    }

    /**
     * Get push notification body
     */
    protected function getPushNotificationBody(): string
    {
        $orderNumber = $this->shipment->order->order_number;
        
        return match($this->notificationType) {
            'delivered' => "Your order #{$orderNumber} has been delivered.",
            'out_for_delivery' => "Your order #{$orderNumber} is out for delivery.",
            'exception' => "There's an update on your order #{$orderNumber}.",
            default => "Update on order #{$orderNumber}."
        };
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('SendShippingNotifications job failed permanently', [
            'shipment_id' => $this->shipment->id,
            'notification_type' => $this->notificationType,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Update shipment metadata to track failed notification
        $notifications = $this->shipment->metadata['notifications'] ?? [];
        $notifications[] = [
            'type' => $this->notificationType,
            'attempted_at' => now(),
            'status' => 'failed',
            'error' => $exception->getMessage()
        ];

        $this->shipment->update([
            'metadata' => array_merge($this->shipment->metadata ?? [], [
                'notifications' => $notifications,
                'notification_failed' => true
            ])
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags()
    {
        return [
            'shipment:' . $this->shipment->id,
            'notification',
            'type:' . $this->notificationType
        ];
    }
}