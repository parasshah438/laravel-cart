<?php

namespace App\Jobs;

use App\Models\OrderShipment;
use App\Models\User;
use App\Mail\ShippingTrackingUpdate;
use App\Notifications\ShippingTrackingNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class SendTrackingNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shipment;
    protected $trackingEvent;
    protected $channels;
    protected $recipientType;

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
    public function __construct(
        OrderShipment $shipment, 
        array $trackingEvent = null, 
        array $channels = ['email', 'sms'], 
        string $recipientType = 'customer'
    ) {
        $this->shipment = $shipment;
        $this->trackingEvent = $trackingEvent;
        $this->channels = $channels;
        $this->recipientType = $recipientType;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            Log::info('Sending tracking notification', [
                'shipment_id' => $this->shipment->id,
                'tracking_number' => $this->shipment->tracking_number,
                'channels' => $this->channels,
                'recipient_type' => $this->recipientType
            ]);

            // Determine recipients
            $recipients = $this->getRecipients();

            if (empty($recipients)) {
                Log::warning('No recipients found for tracking notification', [
                    'shipment_id' => $this->shipment->id,
                    'recipient_type' => $this->recipientType
                ]);
                return;
            }

            // Send notifications through specified channels
            foreach ($this->channels as $channel) {
                $this->sendNotificationViaChannel($recipients, $channel);
            }

            // Log successful notification
            $this->logNotificationSent();

            Log::info('Tracking notification sent successfully', [
                'shipment_id' => $this->shipment->id,
                'channels' => $this->channels,
                'recipient_count' => count($recipients)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send tracking notification', [
                'shipment_id' => $this->shipment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Get notification recipients
     */
    protected function getRecipients(): array
    {
        $recipients = [];

        switch ($this->recipientType) {
            case 'customer':
                $recipients[] = $this->shipment->order->user;
                break;

            case 'admin':
                // Get admin users with shipping permissions
                $recipients = User::role('admin')
                    ->orWhere('email', config('shipping.admin_notification_email'))
                    ->get()
                    ->toArray();
                break;

            case 'both':
                $recipients[] = $this->shipment->order->user;
                $adminUsers = User::role('admin')->get();
                $recipients = array_merge($recipients, $adminUsers->toArray());
                break;
        }

        return array_filter($recipients);
    }

    /**
     * Send notification via specific channel
     */
    protected function sendNotificationViaChannel(array $recipients, string $channel)
    {
        switch ($channel) {
            case 'email':
                $this->sendEmailNotifications($recipients);
                break;

            case 'sms':
                $this->sendSMSNotifications($recipients);
                break;

            case 'push':
                $this->sendPushNotifications($recipients);
                break;

            case 'in_app':
                $this->sendInAppNotifications($recipients);
                break;

            default:
                Log::warning('Unsupported notification channel', [
                    'channel' => $channel,
                    'shipment_id' => $this->shipment->id
                ]);
        }
    }

    /**
     * Send email notifications
     */
    protected function sendEmailNotifications(array $recipients)
    {
        foreach ($recipients as $recipient) {
            if (!$recipient instanceof User) {
                continue;
            }

            try {
                Mail::to($recipient->email)->send(
                    new ShippingTrackingUpdate($this->shipment, $this->trackingEvent)
                );

                Log::info('Tracking email sent', [
                    'shipment_id' => $this->shipment->id,
                    'recipient_email' => $recipient->email
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to send tracking email', [
                    'shipment_id' => $this->shipment->id,
                    'recipient_email' => $recipient->email,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Send SMS notifications
     */
    protected function sendSMSNotifications(array $recipients)
    {
        if (!config('shipping.sms_notifications_enabled', false)) {
            return;
        }

        foreach ($recipients as $recipient) {
            if (!$recipient instanceof User) {
                continue;
            }

            $phoneNumber = $recipient->phone ?? $this->shipment->order->shippingAddress->phone ?? null;

            if (!$phoneNumber) {
                Log::warning('No phone number for SMS notification', [
                    'shipment_id' => $this->shipment->id,
                    'user_id' => $recipient->id
                ]);
                continue;
            }

            try {
                $message = $this->buildSMSMessage();
                $this->sendSMS($phoneNumber, $message);

                Log::info('Tracking SMS sent', [
                    'shipment_id' => $this->shipment->id,
                    'phone' => $phoneNumber
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to send tracking SMS', [
                    'shipment_id' => $this->shipment->id,
                    'phone' => $phoneNumber,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Send push notifications
     */
    protected function sendPushNotifications(array $recipients)
    {
        if (!config('shipping.push_notifications_enabled', false)) {
            return;
        }

        foreach ($recipients as $recipient) {
            if (!$recipient instanceof User) {
                continue;
            }

            try {
                Notification::send($recipient, new ShippingTrackingNotification(
                    $this->shipment, 
                    $this->trackingEvent
                ));

                Log::info('Tracking push notification sent', [
                    'shipment_id' => $this->shipment->id,
                    'user_id' => $recipient->id
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to send tracking push notification', [
                    'shipment_id' => $this->shipment->id,
                    'user_id' => $recipient->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Send in-app notifications
     */
    protected function sendInAppNotifications(array $recipients)
    {
        foreach ($recipients as $recipient) {
            if (!$recipient instanceof User) {
                continue;
            }

            try {
                Notification::send($recipient, new ShippingTrackingNotification(
                    $this->shipment, 
                    $this->trackingEvent,
                    ['database'] // Force database channel for in-app
                ));

                Log::info('Tracking in-app notification sent', [
                    'shipment_id' => $this->shipment->id,
                    'user_id' => $recipient->id
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to send tracking in-app notification', [
                    'shipment_id' => $this->shipment->id,
                    'user_id' => $recipient->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Build SMS message
     */
    protected function buildSMSMessage(): string
    {
        $orderNumber = $this->shipment->order->order_number;
        $status = ucfirst(str_replace('_', ' ', $this->shipment->status));
        $companyName = config('app.name');

        if ($this->trackingEvent) {
            $location = $this->trackingEvent['location'] ?? '';
            $locationText = $location ? " at {$location}" : '';
            
            return "{$companyName}: Your order #{$orderNumber} is now {$status}{$locationText}. Track: " . 
                   route('shipping.track.public', $this->shipment->tracking_number);
        }

        return "{$companyName}: Update on your order #{$orderNumber} - Status: {$status}. Track: " . 
               route('shipping.track.public', $this->shipment->tracking_number);
    }

    /**
     * Send SMS via provider
     */
    protected function sendSMS(string $phoneNumber, string $message)
    {
        // This would integrate with SMS provider (Twilio, AWS SNS, etc.)
        Log::info('SMS would be sent', [
            'shipment_id' => $this->shipment->id,
            'phone' => $phoneNumber,
            'message' => $message
        ]);

        // Actual SMS implementation would go here
        // Example with Twilio:
        // $twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
        // $twilio->messages->create($phoneNumber, [
        //     'from' => config('services.twilio.from'),
        //     'body' => $message
        // ]);
    }

    /**
     * Log notification sent
     */
    protected function logNotificationSent()
    {
        $notifications = $this->shipment->metadata['tracking_notifications'] ?? [];
        $notifications[] = [
            'sent_at' => now(),
            'channels' => $this->channels,
            'recipient_type' => $this->recipientType,
            'tracking_event' => $this->trackingEvent,
            'status' => 'sent'
        ];

        $this->shipment->update([
            'metadata' => array_merge($this->shipment->metadata ?? [], [
                'tracking_notifications' => $notifications,
                'last_tracking_notification' => now()
            ])
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('SendTrackingNotificationJob failed permanently', [
            'shipment_id' => $this->shipment->id,
            'channels' => $this->channels,
            'recipient_type' => $this->recipientType,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Log failed notification
        $notifications = $this->shipment->metadata['tracking_notifications'] ?? [];
        $notifications[] = [
            'attempted_at' => now(),
            'channels' => $this->channels,
            'recipient_type' => $this->recipientType,
            'status' => 'failed',
            'error' => $exception->getMessage()
        ];

        $this->shipment->update([
            'metadata' => array_merge($this->shipment->metadata ?? [], [
                'tracking_notifications' => $notifications,
                'tracking_notification_failed' => true
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
            'tracking_notification',
            'recipient:' . $this->recipientType
        ];
    }
}