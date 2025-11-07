<?php

namespace App\Services;

use App\Models\OrderShipment;
use App\Models\Order;
use App\Jobs\UpdateShippingStatusJob;
use App\Jobs\SendTrackingNotificationJob;
use App\Services\ShippingExceptionHandler;
use App\Services\TrackingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShippingStatusManager
{
    protected $exceptionHandler;
    protected $trackingService;

    /**
     * Status transition rules
     */
    protected $statusTransitions = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['picked_up', 'cancelled'],
        'picked_up' => ['in_transit', 'exception'],
        'in_transit' => ['out_for_delivery', 'delivered', 'exception', 'returned'],
        'out_for_delivery' => ['delivered', 'exception', 'returned'],
        'delivered' => [], // Terminal state
        'exception' => ['picked_up', 'in_transit', 'returned', 'cancelled'],
        'returned' => ['delivered'], // When return is completed
        'cancelled' => [] // Terminal state
    ];

    /**
     * Status notification requirements
     */
    protected $notificationRequirements = [
        'confirmed' => ['customer', 'admin'],
        'picked_up' => ['customer'],
        'in_transit' => ['customer'],
        'out_for_delivery' => ['customer', 'sms'],
        'delivered' => ['customer', 'admin', 'sms'],
        'exception' => ['customer', 'admin', 'priority'],
        'returned' => ['customer', 'admin'],
        'cancelled' => ['customer', 'admin']
    ];

    public function __construct(
        ShippingExceptionHandler $exceptionHandler,
        TrackingService $trackingService
    ) {
        $this->exceptionHandler = $exceptionHandler;
        $this->trackingService = $trackingService;
    }

    /**
     * Update shipment status with comprehensive workflow
     */
    public function updateStatus(
        OrderShipment $shipment, 
        string $newStatus, 
        array $trackingData = [],
        bool $bypassValidation = false
    ): array {
        try {
            DB::beginTransaction();

            Log::info('Status update initiated', [
                'shipment_id' => $shipment->id,
                'current_status' => $shipment->status,
                'new_status' => $newStatus,
                'bypass_validation' => $bypassValidation
            ]);

            // Validate status transition
            if (!$bypassValidation && !$this->canTransitionTo($shipment->status, $newStatus)) {
                throw new \InvalidArgumentException(
                    "Invalid status transition from {$shipment->status} to {$newStatus}"
                );
            }

            // Pre-update validations
            $this->validateStatusUpdate($shipment, $newStatus, $trackingData);

            // Store previous status for rollback
            $previousStatus = $shipment->status;
            $previousMetadata = $shipment->metadata;

            // Update shipment with new status
            $updateData = $this->prepareStatusUpdateData($shipment, $newStatus, $trackingData);
            $shipment->update($updateData);

            // Handle status-specific logic
            $this->handleStatusSpecificLogic($shipment, $newStatus, $trackingData);

            // Send notifications
            $this->handleStatusNotifications($shipment, $newStatus, $trackingData);

            // Handle exceptions if status is exception
            if ($newStatus === 'exception') {
                $this->handleException($shipment, $trackingData);
            }

            // Update related order status if needed
            $this->updateOrderStatus($shipment, $newStatus);

            // Log status transition
            $this->logStatusTransition($shipment, $previousStatus, $newStatus, $trackingData);

            DB::commit();

            Log::info('Status update completed successfully', [
                'shipment_id' => $shipment->id,
                'status_changed' => "{$previousStatus} -> {$newStatus}"
            ]);

            return [
                'success' => true,
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'notification_sent' => true
            ];

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Status update failed', [
                'shipment_id' => $shipment->id,
                'current_status' => $shipment->status,
                'attempted_status' => $newStatus,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'current_status' => $shipment->status
            ];
        }
    }

    /**
     * Bulk status update for multiple shipments
     */
    public function bulkUpdateStatus(
        array $shipmentIds, 
        string $newStatus, 
        array $commonData = []
    ): array {
        $results = [
            'successful' => [],
            'failed' => [],
            'total' => count($shipmentIds)
        ];

        foreach ($shipmentIds as $shipmentId) {
            try {
                $shipment = OrderShipment::findOrFail($shipmentId);
                $result = $this->updateStatus($shipment, $newStatus, $commonData);
                
                if ($result['success']) {
                    $results['successful'][] = $shipmentId;
                } else {
                    $results['failed'][] = [
                        'id' => $shipmentId,
                        'error' => $result['error']
                    ];
                }

            } catch (\Exception $e) {
                $results['failed'][] = [
                    'id' => $shipmentId,
                    'error' => $e->getMessage()
                ];
            }
        }

        Log::info('Bulk status update completed', [
            'total_shipments' => $results['total'],
            'successful' => count($results['successful']),
            'failed' => count($results['failed']),
            'new_status' => $newStatus
        ]);

        return $results;
    }

    /**
     * Check if status transition is valid
     */
    public function canTransitionTo(string $currentStatus, string $newStatus): bool
    {
        if (!isset($this->statusTransitions[$currentStatus])) {
            return false;
        }

        return in_array($newStatus, $this->statusTransitions[$currentStatus]);
    }

    /**
     * Get valid next statuses for current status
     */
    public function getValidNextStatuses(string $currentStatus): array
    {
        return $this->statusTransitions[$currentStatus] ?? [];
    }

    /**
     * Get status workflow for shipment
     */
    public function getStatusWorkflow(OrderShipment $shipment): array
    {
        $currentStatus = $shipment->status;
        $history = $this->getStatusHistory($shipment);
        $nextStatuses = $this->getValidNextStatuses($currentStatus);

        return [
            'current_status' => $currentStatus,
            'valid_next_statuses' => $nextStatuses,
            'status_history' => $history,
            'workflow_completion' => $this->calculateWorkflowCompletion($currentStatus),
            'estimated_delivery' => $this->estimateDelivery($shipment)
        ];
    }

    /**
     * Validate status update
     */
    protected function validateStatusUpdate(
        OrderShipment $shipment, 
        string $newStatus, 
        array $trackingData
    ): void {
        // Validate shipment state
        if (!$shipment->exists) {
            throw new \InvalidArgumentException('Shipment does not exist');
        }

        // Validate status value
        if (!in_array($newStatus, $this->getAllValidStatuses())) {
            throw new \InvalidArgumentException("Invalid status: {$newStatus}");
        }

        // Status-specific validations
        switch ($newStatus) {
            case 'picked_up':
                $this->validatePickupRequirements($shipment, $trackingData);
                break;
                
            case 'delivered':
                $this->validateDeliveryRequirements($shipment, $trackingData);
                break;
                
            case 'exception':
                $this->validateExceptionRequirements($shipment, $trackingData);
                break;
        }
    }

    /**
     * Prepare status update data
     */
    protected function prepareStatusUpdateData(
        OrderShipment $shipment, 
        string $newStatus, 
        array $trackingData
    ): array {
        $updateData = [
            'status' => $newStatus,
            'updated_at' => now()
        ];

        // Status-specific data updates
        switch ($newStatus) {
            case 'picked_up':
                $updateData['shipped_at'] = now();
                if (isset($trackingData['pickup_location'])) {
                    $updateData['pickup_location'] = $trackingData['pickup_location'];
                }
                break;

            case 'delivered':
                $updateData['delivered_at'] = now();
                if (isset($trackingData['delivered_to'])) {
                    $updateData['delivered_to'] = $trackingData['delivered_to'];
                }
                if (isset($trackingData['delivery_signature'])) {
                    $updateData['delivery_signature'] = $trackingData['delivery_signature'];
                }
                break;

            case 'exception':
                $updateData['exception_reason'] = $trackingData['reason'] ?? 'Exception occurred';
                break;

            case 'returned':
                $updateData['returned_at'] = now();
                $updateData['return_reason'] = $trackingData['return_reason'] ?? 'Package returned';
                break;
        }

        // Update metadata with tracking information
        $metadata = $shipment->metadata ?? [];
        $metadata['status_updates'] = $metadata['status_updates'] ?? [];
        $metadata['status_updates'][] = [
            'status' => $newStatus,
            'timestamp' => now(),
            'tracking_data' => $trackingData,
            'updated_by' => auth()->id() ?? 'system'
        ];

        $updateData['metadata'] = $metadata;

        return $updateData;
    }

    /**
     * Handle status-specific logic
     */
    protected function handleStatusSpecificLogic(
        OrderShipment $shipment, 
        string $newStatus, 
        array $trackingData
    ): void {
        switch ($newStatus) {
            case 'confirmed':
                $this->handleConfirmedStatus($shipment, $trackingData);
                break;

            case 'picked_up':
                $this->handlePickedUpStatus($shipment, $trackingData);
                break;

            case 'delivered':
                $this->handleDeliveredStatus($shipment, $trackingData);
                break;

            case 'exception':
                $this->handleExceptionStatus($shipment, $trackingData);
                break;

            case 'returned':
                $this->handleReturnedStatus($shipment, $trackingData);
                break;
        }
    }

    /**
     * Handle status notifications
     */
    protected function handleStatusNotifications(
        OrderShipment $shipment, 
        string $newStatus, 
        array $trackingData
    ): void {
        $requirements = $this->notificationRequirements[$newStatus] ?? [];

        if (empty($requirements)) {
            return;
        }

        $channels = ['email'];
        $recipients = ['customer'];

        // Determine notification channels and recipients
        if (in_array('sms', $requirements)) {
            $channels[] = 'sms';
        }

        if (in_array('admin', $requirements)) {
            $recipients[] = 'admin';
        }

        // Set priority for exception notifications
        $priority = in_array('priority', $requirements) ? 'high' : 'normal';

        // Dispatch notification job
        SendTrackingNotificationJob::dispatch(
            $shipment,
            array_merge($trackingData, [
                'status' => $newStatus,
                'priority' => $priority
            ]),
            $channels,
            'customer'
        );

        // Send admin notification separately if required
        if (in_array('admin', $requirements)) {
            SendTrackingNotificationJob::dispatch(
                $shipment,
                array_merge($trackingData, [
                    'status' => $newStatus,
                    'priority' => $priority
                ]),
                ['email'],
                'admin'
            );
        }
    }

    /**
     * Handle exception status
     */
    protected function handleException(OrderShipment $shipment, array $trackingData): void
    {
        $this->exceptionHandler->handleException($shipment, $trackingData);
    }

    /**
     * Update related order status
     */
    protected function updateOrderStatus(OrderShipment $shipment, string $newStatus): void
    {
        $order = $shipment->order;

        switch ($newStatus) {
            case 'confirmed':
                if ($order->status === 'pending') {
                    $order->update(['status' => 'confirmed']);
                }
                break;

            case 'picked_up':
                if ($order->status === 'confirmed') {
                    $order->update(['status' => 'shipped']);
                }
                break;

            case 'delivered':
                // Check if all shipments for this order are delivered
                $totalShipments = $order->shipments()->count();
                $deliveredShipments = $order->shipments()->where('status', 'delivered')->count();
                
                if ($totalShipments === $deliveredShipments) {
                    $order->update([
                        'status' => 'delivered',
                        'delivered_at' => now()
                    ]);
                }
                break;

            case 'exception':
                // Don't change order status - let admin decide
                break;

            case 'cancelled':
                // Check if all shipments are cancelled
                $totalShipments = $order->shipments()->count();
                $cancelledShipments = $order->shipments()->where('status', 'cancelled')->count();
                
                if ($totalShipments === $cancelledShipments) {
                    $order->update(['status' => 'cancelled']);
                }
                break;
        }
    }

    /**
     * Get status history for shipment
     */
    protected function getStatusHistory(OrderShipment $shipment): array
    {
        $metadata = $shipment->metadata ?? [];
        return $metadata['status_updates'] ?? [];
    }

    /**
     * Calculate workflow completion percentage
     */
    protected function calculateWorkflowCompletion(string $currentStatus): int
    {
        $statusOrder = [
            'pending' => 0,
            'confirmed' => 20,
            'picked_up' => 40,
            'in_transit' => 60,
            'out_for_delivery' => 80,
            'delivered' => 100,
            'exception' => 50, // Depends on resolution
            'returned' => 25,
            'cancelled' => 0
        ];

        return $statusOrder[$currentStatus] ?? 0;
    }

    /**
     * Estimate delivery date
     */
    protected function estimateDelivery(OrderShipment $shipment): ?Carbon
    {
        if ($shipment->status === 'delivered') {
            return $shipment->delivered_at;
        }

        if ($shipment->expected_delivery_date) {
            return $shipment->expected_delivery_date;
        }

        // Calculate based on current status and carrier performance
        return $this->trackingService->estimateDeliveryDate($shipment);
    }

    /**
     * Log status transition
     */
    protected function logStatusTransition(
        OrderShipment $shipment, 
        string $previousStatus, 
        string $newStatus, 
        array $trackingData
    ): void {
        Log::info('Status transition logged', [
            'shipment_id' => $shipment->id,
            'order_id' => $shipment->order_id,
            'tracking_number' => $shipment->tracking_number,
            'status_transition' => "{$previousStatus} -> {$newStatus}",
            'tracking_data' => $trackingData,
            'timestamp' => now(),
            'user_id' => auth()->id() ?? 'system'
        ]);
    }

    /**
     * Get all valid statuses
     */
    protected function getAllValidStatuses(): array
    {
        return array_keys($this->statusTransitions);
    }

    // Status-specific handler methods
    protected function handleConfirmedStatus(OrderShipment $shipment, array $trackingData) {}
    protected function handlePickedUpStatus(OrderShipment $shipment, array $trackingData) {}
    protected function handleDeliveredStatus(OrderShipment $shipment, array $trackingData) {}
    protected function handleExceptionStatus(OrderShipment $shipment, array $trackingData) {}
    protected function handleReturnedStatus(OrderShipment $shipment, array $trackingData) {}

    // Validation methods
    protected function validatePickupRequirements(OrderShipment $shipment, array $trackingData) {}
    protected function validateDeliveryRequirements(OrderShipment $shipment, array $trackingData) {}
    protected function validateExceptionRequirements(OrderShipment $shipment, array $trackingData) {}
}