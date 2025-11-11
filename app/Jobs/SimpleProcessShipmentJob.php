<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\OrderShipment;
use App\Models\ShipmentItem;
use App\Models\ShippingCarrier;
use App\Models\ShippingMethod;
use App\Models\ShippingTrackingEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SimpleProcessShipmentJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected $order;
    
    public $tries = 3;
    public $timeout = 120;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(): void
    {
        try {
            Log::info('SimpleProcessShipmentJob started', ['order_id' => $this->order->id]);

            // Check if order is eligible for shipment
            if (!$this->order->canCreateShipment()) {
                Log::warning('Order not eligible for shipment', [
                    'order_id' => $this->order->id,
                    'status' => $this->order->status,
                    'payment_status' => $this->order->payment_status
                ]);
                return;
            }

            // Get default carrier and shipping method
            $carrier = $this->getDefaultCarrier();
            $shippingMethod = $this->getDefaultShippingMethod();

            // Create shipment record
            $shipment = $this->createShipment($carrier, $shippingMethod);

            // Create shipment items
            $this->createShipmentItems($shipment);

            // Create initial tracking event
            $this->createInitialTrackingEvent($shipment);

            // Update order status and notes
            $this->updateOrderAfterShipmentCreation($shipment);

            Log::info('SimpleProcessShipmentJob completed successfully', [
                'order_id' => $this->order->id,
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number
            ]);

        } catch (\Exception $e) {
            Log::error('SimpleProcessShipmentJob failed', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Update order with error information
            $this->order->update([
                'notes' => array_merge($this->order->notes ?? [], [
                    'shipment_creation_error' => [
                        'error' => $e->getMessage(),
                        'failed_at' => now(),
                        'job_attempts' => $this->attempts()
                    ]
                ])
            ]);

            throw $e;
        }
    }

    /**
     * Get default shipping carrier (create if not exists)
     */
    protected function getDefaultCarrier(): ShippingCarrier
    {
        $carrier = ShippingCarrier::where('code', 'local_courier')->first();
        
        if (!$carrier) {
            $carrier = ShippingCarrier::create([
                'name' => 'Local Courier',
                'code' => 'local_courier',
                'website' => 'https://local-courier.com',
                'tracking_url' => 'https://local-courier.com/track/{tracking_number}',
                'api_enabled' => false,
                'is_active' => true,
                'settings' => [
                    'supports_cod' => true,
                    'max_weight' => 50,
                    'delivery_days' => '3-5',
                    'coverage' => 'Pan India'
                ]
            ]);
        }

        return $carrier;
    }

    /**
     * Get default shipping method (create if not exists)
     */
    protected function getDefaultShippingMethod(): ShippingMethod
    {
        $method = ShippingMethod::where('code', 'standard')->first();
        
        if (!$method) {
            $method = ShippingMethod::create([
                'name' => 'Standard Delivery',
                'code' => 'standard',
                'description' => 'Standard delivery within 3-5 business days',
                'estimated_days' => 5,
                'base_cost' => 50.00,
                'is_active' => true,
                'settings' => [
                    'cod_available' => true,
                    'tracking_available' => true,
                    'insurance_available' => false
                ]
            ]);
        }

        return $method;
    }

    /**
     * Create shipment record
     */
    protected function createShipment(ShippingCarrier $carrier, ShippingMethod $shippingMethod): OrderShipment
    {
        // Generate unique shipment number
        $shipmentNumber = 'SHP-' . strtoupper(Str::random(8)) . '-' . $this->order->id;
        
        // Generate tracking number
        $trackingNumber = 'TRK' . date('Ymd') . strtoupper(Str::random(6));

        // Calculate estimated delivery (add estimated days to current date)
        $estimatedDelivery = now()->addDays($shippingMethod->estimated_days ?? 5);

        $shipment = OrderShipment::create([
            'order_id' => $this->order->id,
            'shipment_number' => $shipmentNumber,
            'carrier_id' => $carrier->id,
            'shipping_method_id' => $shippingMethod->id,
            'tracking_number' => $trackingNumber,
            'status' => 'pending',
            'shipped_at' => null, // Will be set when actually shipped
            'estimated_delivery' => $estimatedDelivery,
            'shipped_from_address' => $this->getShippedFromAddress(),
            'shipped_to_address' => $this->order->shipping_address,
            'package_weight' => $this->calculatePackageWeight(),
            'package_dimensions' => $this->getDefaultPackageDimensions(),
            'shipping_cost' => $this->order->shipping_cost ?? $shippingMethod->base_cost,
            'cod_amount' => $this->order->payment_method === 'cod' ? $this->order->grand_total : 0,
            'notes' => [
                'created_by_job' => 'SimpleProcessShipmentJob',
                'created_at' => now(),
                'order_type' => $this->order->payment_method
            ]
        ]);

        return $shipment;
    }

    /**
     * Create shipment items from order items
     */
    protected function createShipmentItems(OrderShipment $shipment): void
    {
        // Load order items if not already loaded
        if (!$this->order->relationLoaded('items')) {
            $this->order->load('items.product');
        }
        
        foreach ($this->order->items as $orderItem) {
            ShipmentItem::create([
                'shipment_id' => $shipment->id,
                'order_item_id' => $orderItem->id,
                'product_id' => $orderItem->product_id,
                'quantity' => $orderItem->quantity,
                'weight' => $orderItem->product->weight ?? 0.5, // Default 0.5kg if not set
                'dimensions' => [
                    'length' => 20,
                    'width' => 15,
                    'height' => 10
                ]
            ]);
        }
    }

    /**
     * Create initial tracking event
     */
    protected function createInitialTrackingEvent(OrderShipment $shipment): void
    {
        ShippingTrackingEvent::create([
            'shipment_id' => $shipment->id,
            'status' => 'pending',
            'description' => 'Order confirmed and ready for pickup',
            'location' => 'Warehouse',
            'event_time' => now(),
            'metadata' => [
                'event_type' => 'order_confirmed',
                'automated' => true,
                'source' => 'SimpleProcessShipmentJob'
            ]
        ]);
    }

    /**
     * Update order after shipment creation
     */
    protected function updateOrderAfterShipmentCreation(OrderShipment $shipment): void
    {
        $this->order->update([
            'status' => 'processing',
            'notes' => array_merge($this->order->notes ?? [], [
                'shipment_created' => [
                    'shipment_id' => $shipment->id,
                    'shipment_number' => $shipment->shipment_number,
                    'tracking_number' => $shipment->tracking_number,
                    'carrier' => $shipment->carrier->name,
                    'shipping_method' => $shipment->shippingMethod->name,
                    'created_at' => now(),
                    'estimated_delivery' => $shipment->estimated_delivery
                ]
            ])
        ]);
    }

    /**
     * Get default shipped from address
     */
    protected function getShippedFromAddress(): array
    {
        return [
            'name' => 'Your Store Name',
            'company' => 'Your Company',
            'address_line_1' => 'Your Store Address',
            'address_line_2' => 'Store Address Line 2',
            'city' => 'Your City',
            'state' => 'Your State',
            'postal_code' => '123456',
            'country' => 'India',
            'phone' => '+91-9999999999',
            'email' => 'store@yourstore.com'
        ];
    }

    /**
     * Calculate total package weight
     */
    protected function calculatePackageWeight(): float
    {
        $totalWeight = 0;
        
        // Load order items if not already loaded
        if (!$this->order->relationLoaded('items')) {
            $this->order->load('items.product');
        }
        
        foreach ($this->order->items as $item) {
            $productWeight = $item->product->weight ?? 0.5; // Default 0.5kg if not set
            $totalWeight += $productWeight * $item->quantity;
        }

        // Add packaging weight (200g)
        $totalWeight += 0.2;

        return round($totalWeight, 2);
    }

    /**
     * Get default package dimensions
     */
    protected function getDefaultPackageDimensions(): array
    {
        return [
            'length' => 25, // cm
            'width' => 20,  // cm  
            'height' => 15  // cm
        ];
    }
}
