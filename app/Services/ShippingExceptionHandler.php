<?php

namespace App\Services;

use App\Models\OrderShipment;
use App\Models\Order;
use App\Models\User;
use App\Jobs\SendTrackingNotificationJob;
use App\Jobs\UpdateShippingStatusJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ShippingExceptionAlert;
use App\Mail\ShippingExceptionReport;

class ShippingExceptionHandler
{
    /**
     * Handle shipping exception
     */
    public function handleException(OrderShipment $shipment, array $exceptionData)
    {
        try {
            Log::warning('Shipping exception detected', [
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'exception_data' => $exceptionData
            ]);

            // Update shipment status
            $this->updateShipmentException($shipment, $exceptionData);

            // Classify exception severity
            $severity = $this->classifyExceptionSeverity($exceptionData);

            // Handle based on severity
            switch ($severity) {
                case 'critical':
                    $this->handleCriticalException($shipment, $exceptionData);
                    break;
                
                case 'high':
                    $this->handleHighPriorityException($shipment, $exceptionData);
                    break;
                
                case 'medium':
                    $this->handleMediumPriorityException($shipment, $exceptionData);
                    break;
                
                case 'low':
                    $this->handleLowPriorityException($shipment, $exceptionData);
                    break;
                
                default:
                    $this->handleUnknownException($shipment, $exceptionData);
            }

            // Log exception handling
            $this->logExceptionHandling($shipment, $exceptionData, $severity);

            return ['success' => true, 'severity' => $severity];

        } catch (\Exception $e) {
            Log::error('Failed to handle shipping exception', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update shipment with exception details
     */
    protected function updateShipmentException(OrderShipment $shipment, array $exceptionData)
    {
        $exceptionHistory = $shipment->metadata['exceptions'] ?? [];
        $exceptionHistory[] = [
            'timestamp' => now(),
            'type' => $exceptionData['type'] ?? 'unknown',
            'reason' => $exceptionData['reason'] ?? 'No reason provided',
            'location' => $exceptionData['location'] ?? null,
            'carrier_message' => $exceptionData['carrier_message'] ?? null,
            'resolution_attempted' => false
        ];

        $shipment->update([
            'status' => 'exception',
            'exception_reason' => $exceptionData['reason'] ?? 'Shipping exception occurred',
            'metadata' => array_merge($shipment->metadata ?? [], [
                'exceptions' => $exceptionHistory,
                'last_exception' => now(),
                'exception_count' => count($exceptionHistory)
            ])
        ]);
    }

    /**
     * Classify exception severity
     */
    protected function classifyExceptionSeverity(array $exceptionData): string
    {
        $type = strtolower($exceptionData['type'] ?? '');
        $reason = strtolower($exceptionData['reason'] ?? '');

        // Critical exceptions - immediate intervention required
        if (in_array($type, ['lost', 'damaged', 'theft', 'fraud'])) {
            return 'critical';
        }

        if (str_contains($reason, 'lost') || str_contains($reason, 'damaged') || 
            str_contains($reason, 'theft') || str_contains($reason, 'stolen')) {
            return 'critical';
        }

        // High priority - requires quick resolution
        if (in_array($type, ['undeliverable', 'return_to_sender', 'refused'])) {
            return 'high';
        }

        if (str_contains($reason, 'undeliverable') || str_contains($reason, 'refused') ||
            str_contains($reason, 'return')) {
            return 'high';
        }

        // Medium priority - needs attention within business hours
        if (in_array($type, ['delay', 'missed_delivery', 'no_attempt'])) {
            return 'medium';
        }

        if (str_contains($reason, 'delay') || str_contains($reason, 'missed') ||
            str_contains($reason, 'attempt')) {
            return 'medium';
        }

        // Low priority - can be handled routinely
        if (in_array($type, ['address_correction', 'contact_customer'])) {
            return 'low';
        }

        // Unknown - treat as medium priority for safety
        return 'medium';
    }

    /**
     * Handle critical exceptions
     */
    protected function handleCriticalException(OrderShipment $shipment, array $exceptionData)
    {
        // Immediate admin notification
        $this->notifyAdminTeam($shipment, $exceptionData, 'critical');

        // Customer notification
        $this->notifyCustomer($shipment, $exceptionData, 'critical');

        // Create internal support ticket
        $this->createInternalTicket($shipment, $exceptionData, 'critical');

        // Automatic insurance claim if applicable
        $this->initiateInsuranceClaim($shipment, $exceptionData);

        // Escalate to manager
        $this->escalateToManager($shipment, $exceptionData);

        Log::critical('Critical shipping exception handled', [
            'shipment_id' => $shipment->id,
            'exception_type' => $exceptionData['type'] ?? 'unknown'
        ]);
    }

    /**
     * Handle high priority exceptions
     */
    protected function handleHighPriorityException(OrderShipment $shipment, array $exceptionData)
    {
        // Admin notification
        $this->notifyAdminTeam($shipment, $exceptionData, 'high');

        // Customer notification with resolution options
        $this->notifyCustomerWithOptions($shipment, $exceptionData);

        // Attempt automatic resolution
        $this->attemptAutomaticResolution($shipment, $exceptionData);

        // Schedule follow-up
        $this->scheduleFollowUp($shipment, $exceptionData, '2 hours');

        Log::warning('High priority shipping exception handled', [
            'shipment_id' => $shipment->id,
            'exception_type' => $exceptionData['type'] ?? 'unknown'
        ]);
    }

    /**
     * Handle medium priority exceptions
     */
    protected function handleMediumPriorityException(OrderShipment $shipment, array $exceptionData)
    {
        // Standard admin notification
        $this->notifyAdminTeam($shipment, $exceptionData, 'medium');

        // Customer notification
        $this->notifyCustomer($shipment, $exceptionData, 'medium');

        // Add to daily review queue
        $this->addToDailyReviewQueue($shipment, $exceptionData);

        // Schedule follow-up
        $this->scheduleFollowUp($shipment, $exceptionData, '4 hours');

        Log::info('Medium priority shipping exception handled', [
            'shipment_id' => $shipment->id,
            'exception_type' => $exceptionData['type'] ?? 'unknown'
        ]);
    }

    /**
     * Handle low priority exceptions
     */
    protected function handleLowPriorityException(OrderShipment $shipment, array $exceptionData)
    {
        // Batch notification to admin
        $this->addToBatchNotification($shipment, $exceptionData);

        // Customer notification if needed
        if ($this->shouldNotifyCustomer($exceptionData)) {
            $this->notifyCustomer($shipment, $exceptionData, 'low');
        }

        // Add to weekly review
        $this->addToWeeklyReview($shipment, $exceptionData);

        Log::info('Low priority shipping exception handled', [
            'shipment_id' => $shipment->id,
            'exception_type' => $exceptionData['type'] ?? 'unknown'
        ]);
    }

    /**
     * Handle unknown exceptions
     */
    protected function handleUnknownException(OrderShipment $shipment, array $exceptionData)
    {
        // Treat as medium priority for safety
        $this->handleMediumPriorityException($shipment, $exceptionData);

        // Flag for manual review
        $this->flagForManualReview($shipment, $exceptionData);

        Log::warning('Unknown shipping exception handled as medium priority', [
            'shipment_id' => $shipment->id,
            'exception_data' => $exceptionData
        ]);
    }

    /**
     * Notify admin team
     */
    protected function notifyAdminTeam(OrderShipment $shipment, array $exceptionData, string $priority)
    {
        $adminUsers = User::role('admin')->get();
        
        foreach ($adminUsers as $admin) {
            Notification::send($admin, new ShippingExceptionAlert(
                $shipment, 
                $exceptionData, 
                $priority
            ));
        }

        // Send email report for critical/high priority
        if (in_array($priority, ['critical', 'high'])) {
            Mail::to(config('shipping.admin_email'))
                ->send(new ShippingExceptionReport($shipment, $exceptionData, $priority));
        }
    }

    /**
     * Notify customer
     */
    protected function notifyCustomer(OrderShipment $shipment, array $exceptionData, string $priority)
    {
        $channels = ['email'];
        
        // Add SMS for critical/high priority
        if (in_array($priority, ['critical', 'high'])) {
            $channels[] = 'sms';
        }

        SendTrackingNotificationJob::dispatch(
            $shipment, 
            [
                'type' => 'exception',
                'priority' => $priority,
                'reason' => $exceptionData['reason'] ?? 'Shipping exception',
                'resolution_eta' => $this->getResolutionETA($priority)
            ],
            $channels,
            'customer'
        );
    }

    /**
     * Notify customer with resolution options
     */
    protected function notifyCustomerWithOptions(OrderShipment $shipment, array $exceptionData)
    {
        $options = $this->getResolutionOptions($shipment, $exceptionData);
        
        SendTrackingNotificationJob::dispatch(
            $shipment, 
            [
                'type' => 'exception_with_options',
                'reason' => $exceptionData['reason'] ?? 'Shipping exception',
                'resolution_options' => $options,
                'contact_info' => config('shipping.customer_support')
            ],
            ['email', 'sms'],
            'customer'
        );
    }

    /**
     * Create internal support ticket
     */
    protected function createInternalTicket(OrderShipment $shipment, array $exceptionData, string $priority)
    {
        // Implementation would create a support ticket
        Log::info('Internal support ticket would be created', [
            'shipment_id' => $shipment->id,
            'priority' => $priority,
            'exception_type' => $exceptionData['type'] ?? 'unknown'
        ]);
    }

    /**
     * Attempt automatic resolution
     */
    protected function attemptAutomaticResolution(OrderShipment $shipment, array $exceptionData)
    {
        $type = $exceptionData['type'] ?? '';

        switch ($type) {
            case 'address_correction':
                $this->attemptAddressCorrection($shipment, $exceptionData);
                break;
                
            case 'missed_delivery':
                $this->rescheduleDelivery($shipment, $exceptionData);
                break;
                
            case 'delay':
                $this->updateExpectedDelivery($shipment, $exceptionData);
                break;
                
            default:
                Log::info('No automatic resolution available', [
                    'shipment_id' => $shipment->id,
                    'exception_type' => $type
                ]);
        }
    }

    /**
     * Get resolution ETA based on priority
     */
    protected function getResolutionETA(string $priority): string
    {
        switch ($priority) {
            case 'critical':
                return '2-4 hours';
            case 'high':
                return '4-8 hours';
            case 'medium':
                return '1-2 business days';
            case 'low':
                return '3-5 business days';
            default:
                return '1-2 business days';
        }
    }

    /**
     * Get resolution options for customer
     */
    protected function getResolutionOptions(OrderShipment $shipment, array $exceptionData): array
    {
        $type = $exceptionData['type'] ?? '';
        
        switch ($type) {
            case 'undeliverable':
                return [
                    'update_address' => 'Update delivery address',
                    'pickup_location' => 'Pickup from nearest location',
                    'refund' => 'Request full refund'
                ];
                
            case 'missed_delivery':
                return [
                    'reschedule' => 'Reschedule delivery',
                    'pickup_location' => 'Pickup from depot',
                    'leave_safe_place' => 'Leave in safe place'
                ];
                
            default:
                return [
                    'contact_support' => 'Contact customer support',
                    'track_updates' => 'Receive tracking updates'
                ];
        }
    }

    /**
     * Log exception handling
     */
    protected function logExceptionHandling(OrderShipment $shipment, array $exceptionData, string $severity)
    {
        $logData = [
            'shipment_id' => $shipment->id,
            'order_id' => $shipment->order_id,
            'tracking_number' => $shipment->tracking_number,
            'exception_type' => $exceptionData['type'] ?? 'unknown',
            'exception_reason' => $exceptionData['reason'] ?? 'No reason provided',
            'severity' => $severity,
            'handled_at' => now(),
            'actions_taken' => $this->getActionsTaken($severity)
        ];

        Log::info('Shipping exception handling completed', $logData);

        // Store in database for reporting
        $this->storeExceptionReport($shipment, $logData);
    }

    /**
     * Get actions taken based on severity
     */
    protected function getActionsTaken(string $severity): array
    {
        switch ($severity) {
            case 'critical':
                return [
                    'admin_notified',
                    'customer_notified',
                    'support_ticket_created',
                    'manager_escalated',
                    'insurance_initiated'
                ];
                
            case 'high':
                return [
                    'admin_notified',
                    'customer_notified_with_options',
                    'automatic_resolution_attempted',
                    'follow_up_scheduled'
                ];
                
            case 'medium':
                return [
                    'admin_notified',
                    'customer_notified',
                    'daily_review_queued',
                    'follow_up_scheduled'
                ];
                
            case 'low':
                return [
                    'batch_notification_queued',
                    'weekly_review_queued'
                ];
                
            default:
                return ['handled_as_medium_priority'];
        }
    }

    /**
     * Store exception report for analytics
     */
    protected function storeExceptionReport(OrderShipment $shipment, array $logData)
    {
        // This would store in a shipping_exceptions table for reporting
        Log::info('Exception report stored for analytics', [
            'shipment_id' => $shipment->id,
            'report_data' => $logData
        ]);
    }

    // Additional helper methods would be implemented here
    protected function shouldNotifyCustomer(array $exceptionData): bool { return true; }
    protected function addToBatchNotification(OrderShipment $shipment, array $exceptionData) {}
    protected function addToWeeklyReview(OrderShipment $shipment, array $exceptionData) {}
    protected function addToDailyReviewQueue(OrderShipment $shipment, array $exceptionData) {}
    protected function scheduleFollowUp(OrderShipment $shipment, array $exceptionData, string $timeframe) {}
    protected function flagForManualReview(OrderShipment $shipment, array $exceptionData) {}
    protected function initiateInsuranceClaim(OrderShipment $shipment, array $exceptionData) {}
    protected function escalateToManager(OrderShipment $shipment, array $exceptionData) {}
    protected function attemptAddressCorrection(OrderShipment $shipment, array $exceptionData) {}
    protected function rescheduleDelivery(OrderShipment $shipment, array $exceptionData) {}
    protected function updateExpectedDelivery(OrderShipment $shipment, array $exceptionData) {}
}