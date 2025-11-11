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

class ProcessCODTrackingEventJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected $shipment;
    protected $eventType;
    protected $eventData;
    
    public $tries = 3;
    public $timeout = 60;

    public function __construct(OrderShipment $shipment, string $eventType, array $eventData = [])
    {
        $this->shipment = $shipment;
        $this->eventType = $eventType;
        $this->eventData = $eventData;
    }

    public function handle(): void
    {
        try {
            Log::info('ProcessCODTrackingEventJob started', [
                'shipment_id' => $this->shipment->id,
                'event_type' => $this->eventType
            ]);

            // Process the tracking event based on type
            switch ($this->eventType) {
                case 'admin_ship':
                    $this->processAdminShip();
                    break;
                case 'admin_deliver':
                    $this->processAdminDeliver();
                    break;
                case 'pickup_scheduled':
                    $this->processPickupScheduled();
                    break;
                case 'in_transit':
                    $this->processInTransit();
                    break;
                case 'out_for_delivery':
                    $this->processOutForDelivery();
                    break;
                default:
                    $this->processGenericEvent();
                    break;
            }

            Log::info('ProcessCODTrackingEventJob completed', [
                'shipment_id' => $this->shipment->id,
                'event_type' => $this->eventType
            ]);

        } catch (\Exception $e) {
            Log::error('ProcessCODTrackingEventJob failed', [
                'shipment_id' => $this->shipment->id,
                'event_type' => $this->eventType,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process admin marking order as shipped
     */
    protected function processAdminShip(): void
    {
        // Create tracking event
        ShippingTrackingEvent::create([
            'shipment_id' => $this->shipment->id,
            'status' => 'picked_up',
            'description' => 'Package picked up by courier and shipped',
            'location' => 'Warehouse',
            'event_time' => now(),
            'metadata' => [
                'event_type' => 'admin_ship',
                'automated' => false,
                'source' => 'admin_action',
                'admin_id' => $this->eventData['admin_id'] ?? null
            ]
        ]);

        // Update shipment
        $this->shipment->update([
            'status' => 'picked_up',
            'shipped_at' => now()
        ]);

        // Update order
        $this->shipment->order->update(['status' => 'shipped']);
    }

    /**
     * Process admin marking order as delivered
     */
    protected function processAdminDeliver(): void
    {
        // Create tracking event
        ShippingTrackingEvent::create([
            'shipment_id' => $this->shipment->id,
            'status' => 'delivered',
            'description' => 'Package delivered successfully to customer',
            'location' => $this->eventData['delivery_location'] ?? 'Customer Address',
            'event_time' => now(),
            'metadata' => [
                'event_type' => 'admin_deliver',
                'automated' => false,
                'source' => 'admin_action',
                'admin_id' => $this->eventData['admin_id'] ?? null,
                'delivery_notes' => $this->eventData['delivery_notes'] ?? null
            ]
        ]);

        // Update shipment
        $this->shipment->update([
            'status' => 'delivered',
            'delivered_at' => now()
        ]);

        // Update order - mark as delivered and payment received for COD
        $order = $this->shipment->order;
        $orderUpdate = ['status' => 'delivered'];
        
        if ($order->payment_method === 'cod') {
            $orderUpdate['payment_status'] = 'paid';
        }
        
        $order->update($orderUpdate);
    }

    /**
     * Process pickup scheduled event
     */
    protected function processPickupScheduled(): void
    {
        ShippingTrackingEvent::create([
            'shipment_id' => $this->shipment->id,
            'status' => 'pickup_scheduled',
            'description' => 'Pickup scheduled with courier partner',
            'location' => 'Warehouse',
            'event_time' => now(),
            'metadata' => [
                'event_type' => 'pickup_scheduled',
                'automated' => true,
                'source' => 'system',
                'scheduled_date' => $this->eventData['scheduled_date'] ?? null
            ]
        ]);
    }

    /**
     * Process in transit event
     */
    protected function processInTransit(): void
    {
        ShippingTrackingEvent::create([
            'shipment_id' => $this->shipment->id,
            'status' => 'in_transit',
            'description' => 'Package is in transit to destination',
            'location' => $this->eventData['current_location'] ?? 'In Transit',
            'event_time' => now(),
            'metadata' => [
                'event_type' => 'in_transit',
                'automated' => true,
                'source' => 'courier_api',
                'current_hub' => $this->eventData['current_hub'] ?? null
            ]
        ]);

        $this->shipment->update(['status' => 'in_transit']);
    }

    /**
     * Process out for delivery event
     */
    protected function processOutForDelivery(): void
    {
        ShippingTrackingEvent::create([
            'shipment_id' => $this->shipment->id,
            'status' => 'out_for_delivery',
            'description' => 'Package is out for delivery',
            'location' => $this->eventData['delivery_hub'] ?? 'Local Delivery Hub',
            'event_time' => now(),
            'metadata' => [
                'event_type' => 'out_for_delivery',
                'automated' => true,
                'source' => 'courier_api',
                'delivery_agent' => $this->eventData['delivery_agent'] ?? null,
                'estimated_delivery' => $this->eventData['estimated_delivery'] ?? null
            ]
        ]);

        $this->shipment->update(['status' => 'out_for_delivery']);
    }

    /**
     * Process generic tracking event
     */
    protected function processGenericEvent(): void
    {
        ShippingTrackingEvent::create([
            'shipment_id' => $this->shipment->id,
            'status' => $this->eventType,
            'description' => $this->eventData['description'] ?? "Tracking event: {$this->eventType}",
            'location' => $this->eventData['location'] ?? 'Unknown',
            'event_time' => $this->eventData['event_time'] ?? now(),
            'metadata' => array_merge([
                'event_type' => $this->eventType,
                'automated' => true,
                'source' => 'generic_event'
            ], $this->eventData['metadata'] ?? [])
        ]);
    }

    /**
     * Static helper methods for common COD tracking events
     */
    public static function adminShipped(OrderShipment $shipment, int $adminId): void
    {
        dispatch(new self($shipment, 'admin_ship', ['admin_id' => $adminId]));
    }

    public static function adminDelivered(OrderShipment $shipment, int $adminId, array $deliveryData = []): void
    {
        dispatch(new self($shipment, 'admin_deliver', array_merge([
            'admin_id' => $adminId
        ], $deliveryData)));
    }

    public static function schedulePickup(OrderShipment $shipment, string $scheduledDate = null): void
    {
        dispatch(new self($shipment, 'pickup_scheduled', [
            'scheduled_date' => $scheduledDate ?? now()->addDay()->format('Y-m-d')
        ]));
    }

    public static function markInTransit(OrderShipment $shipment, string $location = null): void
    {
        dispatch(new self($shipment, 'in_transit', [
            'current_location' => $location ?? 'In Transit'
        ]));
    }

    public static function markOutForDelivery(OrderShipment $shipment, array $deliveryData = []): void
    {
        dispatch(new self($shipment, 'out_for_delivery', $deliveryData));
    }
}
