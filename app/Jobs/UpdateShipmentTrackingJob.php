<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\OrderShipment;
use App\Models\ShippingTrackingEvent;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class UpdateShipmentTrackingJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected $shipment;
    protected $newStatus;
    protected $location;
    protected $description;
    
    public $tries = 3;
    public $timeout = 60;

    public function __construct(OrderShipment $shipment, string $newStatus, string $location = null, string $description = null)
    {
        $this->shipment = $shipment;
        $this->newStatus = $newStatus;
        $this->location = $location ?? 'Processing Center';
        $this->description = $description ?? $this->getDefaultDescription($newStatus);
    }

    public function handle(): void
    {
        try {
            Log::info('UpdateShipmentTrackingJob started', [
                'shipment_id' => $this->shipment->id,
                'new_status' => $this->newStatus
            ]);

            // Create tracking event
            $trackingEvent = ShippingTrackingEvent::create([
                'shipment_id' => $this->shipment->id,
                'status' => $this->newStatus,
                'description' => $this->description,
                'location' => $this->location,
                'occurred_at' => now(),
                'metadata' => [
                    'event_type' => 'status_update',
                    'automated' => true,
                    'source' => 'UpdateShipmentTrackingJob',
                    'previous_status' => $this->shipment->status
                ]
            ]);

            // Update shipment status
            $this->updateShipmentStatus();

            // Update order status if needed
            $this->updateOrderStatus();

            Log::info('UpdateShipmentTrackingJob completed', [
                'shipment_id' => $this->shipment->id,
                'tracking_event_id' => $trackingEvent->id,
                'new_status' => $this->newStatus
            ]);

        } catch (\Exception $e) {
            Log::error('UpdateShipmentTrackingJob failed', [
                'shipment_id' => $this->shipment->id,
                'new_status' => $this->newStatus,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update shipment status and related fields
     */
    protected function updateShipmentStatus(): void
    {
        $updateData = ['status' => $this->newStatus];

        // Set specific timestamps based on status
        switch ($this->newStatus) {
            case 'picked_up':
                $updateData['shipped_at'] = now();
                break;
            case 'delivered':
                $updateData['delivered_at'] = now();
                break;
        }

        $this->shipment->update($updateData);
    }

    /**
     * Update order status based on shipment status
     */
    protected function updateOrderStatus(): void
    {
        $order = $this->shipment->order;
        $currentOrderStatus = $order->status;
        $newOrderStatus = null;

        // Determine new order status based on shipment status
        switch ($this->newStatus) {
            case 'confirmed':
                if ($currentOrderStatus === 'pending') {
                    $newOrderStatus = 'processing';
                }
                break;
            case 'picked_up':
                $newOrderStatus = 'shipped';
                break;
            case 'delivered':
                $newOrderStatus = 'delivered';
                // For COD orders, mark payment as received when delivered
                if ($order->payment_method === 'cod' && $order->payment_status !== 'paid') {
                    $order->update(['payment_status' => 'paid']);
                }
                break;
            case 'returned':
                $newOrderStatus = 'returned';
                break;
            case 'exception':
                // Don't change order status for exceptions, just log
                Log::warning('Shipment exception occurred', [
                    'shipment_id' => $this->shipment->id,
                    'order_id' => $order->id
                ]);
                break;
        }

        // Update order status if needed
        if ($newOrderStatus && $newOrderStatus !== $currentOrderStatus) {
            $order->update([
                'status' => $newOrderStatus,
                'notes' => array_merge($order->notes ?? [], [
                    'status_auto_update' => [
                        'from' => $currentOrderStatus,
                        'to' => $newOrderStatus,
                        'reason' => "Shipment status changed to {$this->newStatus}",
                        'updated_at' => now(),
                        'shipment_id' => $this->shipment->id
                    ]
                ])
            ]);

            Log::info('Order status auto-updated', [
                'order_id' => $order->id,
                'from' => $currentOrderStatus,
                'to' => $newOrderStatus,
                'trigger' => "shipment_status_{$this->newStatus}"
            ]);
        }
    }

    /**
     * Get default description for status
     */
    protected function getDefaultDescription(string $status): string
    {
        $descriptions = [
            'confirmed' => 'Order confirmed and ready for pickup',
            'picked_up' => 'Package picked up by courier',
            'in_transit' => 'Package is in transit',
            'out_for_delivery' => 'Package is out for delivery',
            'delivered' => 'Package delivered successfully',
            'returned' => 'Package returned to sender',
            'exception' => 'Delivery exception occurred'
        ];

        return $descriptions[$status] ?? "Status updated to {$status}";
    }

    /**
     * Create tracking event for COD order lifecycle
     */
    public static function createCODTrackingEvent(OrderShipment $shipment, string $status, array $options = []): void
    {
        $location = $options['location'] ?? null;
        $description = $options['description'] ?? null;

        dispatch(new self($shipment, $status, $location, $description));
    }
}