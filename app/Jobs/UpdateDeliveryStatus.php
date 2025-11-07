<?php

namespace App\Jobs;

use App\Models\OrderShipment;
use App\Services\TrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateDeliveryStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shipment;
    protected $newStatus;
    protected $eventData;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     */
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(OrderShipment $shipment, string $newStatus, array $eventData = [])
    {
        $this->shipment = $shipment;
        $this->newStatus = $newStatus;
        $this->eventData = $eventData;
    }

    /**
     * Execute the job.
     */
    public function handle(TrackingService $trackingService)
    {
        try {
            Log::info('Updating delivery status', [
                'shipment_id' => $this->shipment->id,
                'current_status' => $this->shipment->status,
                'new_status' => $this->newStatus
            ]);

            // Validate status transition
            if (!$this->isValidStatusTransition()) {
                Log::warning('Invalid status transition attempted', [
                    'shipment_id' => $this->shipment->id,
                    'from_status' => $this->shipment->status,
                    'to_status' => $this->newStatus
                ]);
                return;
            }

            // Update shipment status
            $oldStatus = $this->shipment->status;
            $this->shipment->update([
                'status' => $this->newStatus,
                'delivered_at' => $this->newStatus === 'delivered' ? now() : $this->shipment->delivered_at,
                'metadata' => array_merge($this->shipment->metadata ?? [], [
                    'status_updated_at' => now(),
                    'status_updated_by' => 'system',
                    'previous_status' => $oldStatus
                ])
            ]);

            // Add tracking event
            if (!empty($this->eventData)) {
                $trackingService->addTrackingEvent($this->shipment, array_merge([
                    'status' => $this->newStatus,
                    'event_time' => now()
                ], $this->eventData));
            }

            // Update order status if needed
            $this->updateOrderStatus();

            // Trigger post-status-update actions
            $this->executeStatusSpecificActions();

            Log::info('Delivery status updated successfully', [
                'shipment_id' => $this->shipment->id,
                'old_status' => $oldStatus,
                'new_status' => $this->newStatus
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update delivery status', [
                'shipment_id' => $this->shipment->id,
                'new_status' => $this->newStatus,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Check if the status transition is valid
     */
    protected function isValidStatusTransition(): bool
    {
        $validTransitions = [
            'pending' => ['picked_up', 'exception', 'cancelled'],
            'picked_up' => ['in_transit', 'exception', 'returned'],
            'in_transit' => ['out_for_delivery', 'delivered', 'exception', 'returned'],
            'out_for_delivery' => ['delivered', 'exception', 'returned'],
            'delivered' => [], // Terminal state
            'exception' => ['in_transit', 'out_for_delivery', 'delivered', 'returned'],
            'returned' => [], // Terminal state
            'cancelled' => [] // Terminal state
        ];

        $currentStatus = $this->shipment->status;
        $allowedNextStatuses = $validTransitions[$currentStatus] ?? [];

        return in_array($this->newStatus, $allowedNextStatuses);
    }

    /**
     * Update order status based on shipment status
     */
    protected function updateOrderStatus()
    {
        $order = $this->shipment->order;
        $allShipments = $order->shipments;

        // Determine new order status based on all shipments
        if ($allShipments->every(fn($s) => $s->status === 'delivered')) {
            $newOrderStatus = 'delivered';
        } elseif ($allShipments->some(fn($s) => in_array($s->status, ['picked_up', 'in_transit', 'out_for_delivery']))) {
            $newOrderStatus = 'shipped';
        } elseif ($allShipments->every(fn($s) => in_array($s->status, ['exception', 'returned']))) {
            $newOrderStatus = 'failed';
        } elseif ($allShipments->every(fn($s) => $s->status === 'cancelled')) {
            $newOrderStatus = 'cancelled';
        } else {
            $newOrderStatus = $order->status; // Keep current status
        }

        if ($order->status !== $newOrderStatus) {
            $order->update([
                'status' => $newOrderStatus,
                'delivered_at' => $newOrderStatus === 'delivered' ? now() : $order->delivered_at
            ]);

            Log::info('Order status updated', [
                'order_id' => $order->id,
                'new_status' => $newOrderStatus,
                'trigger_shipment' => $this->shipment->id
            ]);
        }
    }

    /**
     * Execute actions specific to the new status
     */
    protected function executeStatusSpecificActions()
    {
        switch ($this->newStatus) {
            case 'delivered':
                $this->handleDeliveryComplete();
                break;
                
            case 'exception':
                $this->handleException();
                break;
                
            case 'returned':
                $this->handleReturn();
                break;
                
            case 'out_for_delivery':
                $this->handleOutForDelivery();
                break;
        }
    }

    /**
     * Handle delivery completion
     */
    protected function handleDeliveryComplete()
    {
        // Dispatch notification job
        SendShippingNotifications::dispatch($this->shipment, 'delivered');

        // Update delivery metrics
        $this->updateDeliveryMetrics();

        // Trigger any post-delivery workflows
        $this->triggerPostDeliveryWorkflows();

        Log::info('Delivery completion actions executed', [
            'shipment_id' => $this->shipment->id
        ]);
    }

    /**
     * Handle delivery exception
     */
    protected function handleException()
    {
        // Notify customer and admin
        SendShippingNotifications::dispatch($this->shipment, 'exception');

        // Create support ticket if configured
        $this->createSupportTicketForException();

        // Schedule retry tracking
        ProcessShipmentTracking::dispatch($this->shipment)->delay(now()->addHours(2));

        Log::info('Exception handling actions executed', [
            'shipment_id' => $this->shipment->id
        ]);
    }

    /**
     * Handle return
     */
    protected function handleReturn()
    {
        // Notify customer and admin
        SendShippingNotifications::dispatch($this->shipment, 'returned');

        // Update inventory if applicable
        $this->updateInventoryForReturn();

        // Process refund if applicable
        $this->processRefundForReturn();

        Log::info('Return handling actions executed', [
            'shipment_id' => $this->shipment->id
        ]);
    }

    /**
     * Handle out for delivery status
     */
    protected function handleOutForDelivery()
    {
        // Send delivery notification
        SendShippingNotifications::dispatch($this->shipment, 'out_for_delivery');

        // Set expected delivery time
        if (!$this->shipment->estimated_delivery) {
            $this->shipment->update([
                'estimated_delivery' => now()->addHours(8) // Assume delivery within 8 hours
            ]);
        }

        Log::info('Out for delivery actions executed', [
            'shipment_id' => $this->shipment->id
        ]);
    }

    /**
     * Update delivery metrics
     */
    protected function updateDeliveryMetrics()
    {
        if ($this->shipment->shipped_at && $this->shipment->delivered_at) {
            $deliveryTime = $this->shipment->shipped_at->diffInHours($this->shipment->delivered_at);
            
            $this->shipment->update([
                'metadata' => array_merge($this->shipment->metadata ?? [], [
                    'actual_delivery_time_hours' => $deliveryTime,
                    'delivery_performance' => $this->calculateDeliveryPerformance()
                ])
            ]);
        }
    }

    /**
     * Calculate delivery performance score
     */
    protected function calculateDeliveryPerformance(): string
    {
        if (!$this->shipment->estimated_delivery || !$this->shipment->delivered_at) {
            return 'unknown';
        }

        $estimatedDelivery = $this->shipment->estimated_delivery;
        $actualDelivery = $this->shipment->delivered_at;

        if ($actualDelivery <= $estimatedDelivery) {
            return 'on_time';
        } elseif ($actualDelivery <= $estimatedDelivery->addDay()) {
            return 'delayed_minor';
        } else {
            return 'delayed_major';
        }
    }

    /**
     * Trigger post-delivery workflows
     */
    protected function triggerPostDeliveryWorkflows()
    {
        $order = $this->shipment->order;

        // Schedule review request
        if (config('shipping.send_review_requests', true)) {
            // Dispatch review request job with delay
            // ReviewRequestJob::dispatch($order)->delay(now()->addDays(3));
        }

        // Update customer loyalty points
        if (config('shipping.award_loyalty_points', true)) {
            // LoyaltyPointsJob::dispatch($order);
        }

        // Trigger reorder suggestions
        if (config('shipping.send_reorder_suggestions', true)) {
            // ReorderSuggestionsJob::dispatch($order)->delay(now()->addWeeks(2));
        }
    }

    /**
     * Create support ticket for exception
     */
    protected function createSupportTicketForException()
    {
        // This would integrate with your support ticket system
        Log::info('Support ticket should be created for shipment exception', [
            'shipment_id' => $this->shipment->id,
            'customer_id' => $this->shipment->order->user_id
        ]);
    }

    /**
     * Update inventory for return
     */
    protected function updateInventoryForReturn()
    {
        // This would integrate with your inventory management system
        foreach ($this->shipment->items as $shipmentItem) {
            $orderItem = $shipmentItem->orderItem;
            Log::info('Inventory should be updated for returned item', [
                'product_id' => $orderItem->product_id,
                'quantity' => $shipmentItem->quantity
            ]);
        }
    }

    /**
     * Process refund for return
     */
    protected function processRefundForReturn()
    {
        $order = $this->shipment->order;
        
        // Only process refund if order was paid
        if ($order->payment_status === 'paid' && $order->payment_method !== 'cod') {
            Log::info('Refund should be processed for returned shipment', [
                'shipment_id' => $this->shipment->id,
                'order_id' => $order->id,
                'refund_amount' => $this->calculateRefundAmount()
            ]);
            
            // RefundProcessingJob::dispatch($order, $this->calculateRefundAmount());
        }
    }

    /**
     * Calculate refund amount for return
     */
    protected function calculateRefundAmount(): float
    {
        $itemTotal = 0;
        
        foreach ($this->shipment->items as $shipmentItem) {
            $orderItem = $shipmentItem->orderItem;
            $itemTotal += $orderItem->price * $shipmentItem->quantity;
        }

        // Include shipping cost if full order is returned
        $order = $this->shipment->order;
        if ($order->shipments()->count() === 1) {
            $itemTotal += $this->shipment->shipping_cost;
        }

        return $itemTotal;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('UpdateDeliveryStatus job failed permanently', [
            'shipment_id' => $this->shipment->id,
            'new_status' => $this->newStatus,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Mark shipment as having status update issues
        $this->shipment->update([
            'metadata' => array_merge($this->shipment->metadata ?? [], [
                'status_update_failed' => true,
                'status_update_error' => $exception->getMessage(),
                'failed_at' => now()
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
            'status_update',
            'status:' . $this->newStatus
        ];
    }
}