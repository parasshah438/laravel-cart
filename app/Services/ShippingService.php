<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderShipment;
use App\Models\ShippingCarrier;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Jobs\ProcessShipmentTracking;
use App\Jobs\SendShippingNotifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class ShippingService
{
    protected $rateCalculator;
    protected $carrierIntegration;
    protected $trackingService;

    public function __construct(
        RateCalculatorService $rateCalculator,
        CarrierIntegrationService $carrierIntegration,
        TrackingService $trackingService
    ) {
        $this->rateCalculator = $rateCalculator;
        $this->carrierIntegration = $carrierIntegration;
        $this->trackingService = $trackingService;
    }

    /**
     * Create shipment for an order
     */
    public function createShipment(Order $order, array $options = [])
    {
        DB::beginTransaction();

        try {
            // Determine best carrier and method
            $shippingOptions = $this->getShippingOptions($order);
            
            $selectedCarrier = $options['carrier_id'] 
                ? ShippingCarrier::findOrFail($options['carrier_id'])
                : $shippingOptions['recommended']['carrier'];
                
            $selectedMethod = $options['method_id']
                ? ShippingMethod::findOrFail($options['method_id'])
                : $shippingOptions['recommended']['method'];

            // Calculate package details
            $packageDetails = $this->calculatePackageDetails($order);
            
            // Calculate shipping cost
            $shippingCost = $this->rateCalculator->calculateRate(
                $order,
                $selectedCarrier,
                $selectedMethod,
                $packageDetails
            );

            // Create shipment record
            $shipment = OrderShipment::create([
                'order_id' => $order->id,
                'shipment_number' => $this->generateShipmentNumber(),
                'carrier_id' => $selectedCarrier->id,
                'shipping_method_id' => $selectedMethod->id,
                'status' => 'pending',
                'shipped_from_address' => $this->getOriginAddress(),
                'shipped_to_address' => $this->formatShippingAddress($order),
                'package_weight' => $packageDetails['weight'],
                'package_dimensions' => $packageDetails['dimensions'],
                'shipping_cost' => $shippingCost,
                'cod_amount' => $order->payment_method === 'cod' ? $order->total : 0,
                'metadata' => [
                    'created_by' => auth()->id(),
                    'auto_generated' => !isset($options['manual']),
                    'shipping_options' => $shippingOptions
                ]
            ]);

            // Add shipment items
            $this->addShipmentItems($shipment, $order);

            // Create shipment with carrier
            if ($selectedCarrier->api_endpoint) {
                $carrierResponse = $this->carrierIntegration->createShipment(
                    $selectedCarrier,
                    $shipment
                );
                
                if ($carrierResponse['success']) {
                    $shipment->update([
                        'tracking_number' => $carrierResponse['tracking_number'],
                        'estimated_delivery' => $carrierResponse['estimated_delivery'],
                        'metadata' => array_merge($shipment->metadata, [
                            'carrier_response' => $carrierResponse
                        ])
                    ]);
                }
            }

            // Update order
            $order->update([
                'shipping_carrier_id' => $selectedCarrier->id,
                'shipping_method_id' => $selectedMethod->id,
                'shipping_cost' => $shippingCost,
                'status' => 'confirmed',
                'package_weight' => $packageDetails['weight'],
                'package_dimensions' => $packageDetails['dimensions']
            ]);

            // Schedule tracking updates
            if ($shipment->tracking_number) {
                ProcessShipmentTracking::dispatch($shipment)->delay(now()->addHours(2));
            }

            // Send notifications
            SendShippingNotifications::dispatch($shipment, 'created');

            DB::commit();

            return [
                'success' => true,
                'shipment' => $shipment,
                'message' => 'Shipment created successfully'
            ];

        } catch (Exception $e) {
            DB::rollback();
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to create shipment'
            ];
        }
    }

    /**
     * Get available shipping options for an order
     */
    public function getShippingOptions(Order $order)
    {
        $shippingAddress = $order->shippingAddress;
        $zone = $this->getShippingZone($shippingAddress);
        
        $carriers = ShippingCarrier::active()->get();
        $options = [];
        $recommended = null;
        $lowestCost = PHP_FLOAT_MAX;

        foreach ($carriers as $carrier) {
            foreach ($carrier->methods as $method) {
                if (!$method->is_active) continue;

                $rate = $this->rateCalculator->calculateRate($order, $carrier, $method);
                $deliveryTime = $this->estimateDeliveryTime($carrier, $method, $zone);

                $option = [
                    'carrier' => $carrier,
                    'method' => $method,
                    'rate' => $rate,
                    'delivery_time' => $deliveryTime,
                    'estimated_delivery' => now()->addDays($deliveryTime['max_days']),
                    'supports_cod' => $carrier->supports_cod && $order->payment_method === 'cod'
                ];

                $options[] = $option;

                // Determine recommended option (lowest cost for now)
                if ($rate < $lowestCost) {
                    $lowestCost = $rate;
                    $recommended = $option;
                }
            }
        }

        // Sort by rate
        usort($options, function($a, $b) {
            return $a['rate'] <=> $b['rate'];
        });

        return [
            'options' => $options,
            'recommended' => $recommended,
            'zone' => $zone
        ];
    }

    /**
     * Calculate package details from order items
     */
    protected function calculatePackageDetails(Order $order)
    {
        $totalWeight = 0;
        $totalVolume = 0;
        $maxLength = 0;
        $maxWidth = 0;
        $totalHeight = 0;

        foreach ($order->items as $item) {
            $product = $item->product;
            $quantity = $item->quantity;

            // Weight calculation
            if ($product->weight) {
                $totalWeight += $product->weight * $quantity;
            }

            // Dimension calculation (assuming products have dimensions)
            if ($product->dimensions) {
                $dims = is_string($product->dimensions) 
                    ? json_decode($product->dimensions, true) 
                    : $product->dimensions;
                    
                if ($dims) {
                    $maxLength = max($maxLength, $dims['length'] ?? 0);
                    $maxWidth = max($maxWidth, $dims['width'] ?? 0);
                    $totalHeight += ($dims['height'] ?? 0) * $quantity;
                    $totalVolume += ($dims['length'] ?? 0) * ($dims['width'] ?? 0) * ($dims['height'] ?? 0) * $quantity;
                }
            }
        }

        // Add packaging weight (estimated 10% of product weight)
        $totalWeight = $totalWeight * 1.1;

        // Minimum package dimensions
        $length = max($maxLength, 10); // minimum 10cm
        $width = max($maxWidth, 10);   // minimum 10cm
        $height = max($totalHeight, 5); // minimum 5cm

        return [
            'weight' => round($totalWeight, 2),
            'dimensions' => [
                'length' => round($length, 2),
                'width' => round($width, 2),
                'height' => round($height, 2)
            ],
            'volume' => round($totalVolume, 2)
        ];
    }

    /**
     * Get shipping zone for address
     */
    protected function getShippingZone($address)
    {
        return ShippingZone::whereHas('locations', function($query) use ($address) {
            $query->where(function($q) use ($address) {
                $q->where('country_id', $address->country_id)
                  ->where('state_id', $address->state_id)
                  ->where('city_id', $address->city_id);
            })->orWhere(function($q) use ($address) {
                $q->where('country_id', $address->country_id)
                  ->where('state_id', $address->state_id)
                  ->whereNull('city_id');
            })->orWhere(function($q) use ($address) {
                $q->where('country_id', $address->country_id)
                  ->whereNull('state_id')
                  ->whereNull('city_id');
            });
        })->first();
    }

    /**
     * Estimate delivery time
     */
    protected function estimateDeliveryTime($carrier, $method, $zone)
    {
        $baseTime = [
            'standard' => ['min_days' => 3, 'max_days' => 7],
            'express' => ['min_days' => 1, 'max_days' => 3],
            'overnight' => ['min_days' => 1, 'max_days' => 1]
        ];

        $methodType = strtolower($method->code);
        
        if (str_contains($methodType, 'express') || str_contains($methodType, 'fast')) {
            return $baseTime['express'];
        } elseif (str_contains($methodType, 'overnight') || str_contains($methodType, 'same')) {
            return $baseTime['overnight'];
        }

        return $baseTime['standard'];
    }

    /**
     * Add items to shipment
     */
    protected function addShipmentItems(OrderShipment $shipment, Order $order)
    {
        foreach ($order->items as $item) {
            $shipment->items()->create([
                'order_item_id' => $item->id,
                'quantity' => $item->quantity
            ]);
        }
    }

    /**
     * Generate unique shipment number
     */
    protected function generateShipmentNumber()
    {
        do {
            $number = 'SH' . date('Ymd') . strtoupper(Str::random(6));
        } while (OrderShipment::where('shipment_number', $number)->exists());

        return $number;
    }

    /**
     * Get origin address for shipping
     */
    protected function getOriginAddress()
    {
        return [
            'name' => config('app.name'),
            'company' => config('app.name'),
            'address_line_1' => config('shipping.origin.address_line_1', '123 Business Street'),
            'address_line_2' => config('shipping.origin.address_line_2', ''),
            'city' => config('shipping.origin.city', 'Mumbai'),
            'state' => config('shipping.origin.state', 'Maharashtra'),
            'postal_code' => config('shipping.origin.postal_code', '400001'),
            'country' => config('shipping.origin.country', 'India'),
            'phone' => config('shipping.origin.phone', '+91-1234567890'),
            'email' => config('shipping.origin.email', config('mail.from.address'))
        ];
    }

    /**
     * Format shipping address for carrier
     */
    protected function formatShippingAddress(Order $order)
    {
        $address = $order->shippingAddress;
        
        return [
            'name' => $address->first_name . ' ' . $address->last_name,
            'company' => $address->company ?? '',
            'address_line_1' => $address->address_line_1,
            'address_line_2' => $address->address_line_2 ?? '',
            'city' => $address->city->name ?? $address->city,
            'state' => $address->state->name ?? $address->state,
            'postal_code' => $address->postal_code,
            'country' => $address->country->name ?? $address->country,
            'phone' => $address->phone,
            'email' => $order->user->email
        ];
    }

    /**
     * Update shipment status
     */
    public function updateShipmentStatus(OrderShipment $shipment, string $status, array $data = [])
    {
        $shipment->updateStatus($status, $data['description'] ?? null, $data['location'] ?? null);
        
        // Send notification
        SendShippingNotifications::dispatch($shipment, 'status_update');
        
        return $shipment;
    }

    /**
     * Cancel shipment
     */
    public function cancelShipment(OrderShipment $shipment, string $reason = '')
    {
        DB::beginTransaction();

        try {
            // Cancel with carrier if exists
            if ($shipment->tracking_number && $shipment->carrier->api_endpoint) {
                $this->carrierIntegration->cancelShipment($shipment->carrier, $shipment);
            }

            // Update shipment
            $shipment->update([
                'status' => 'cancelled',
                'notes' => $reason,
                'metadata' => array_merge($shipment->metadata ?? [], [
                    'cancelled_at' => now(),
                    'cancelled_by' => auth()->id(),
                    'cancellation_reason' => $reason
                ])
            ]);

            // Update order status
            $shipment->order->update(['status' => 'cancelled']);

            // Send notification
            SendShippingNotifications::dispatch($shipment, 'cancelled');

            DB::commit();

            return ['success' => true, 'message' => 'Shipment cancelled successfully'];

        } catch (Exception $e) {
            DB::rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get shipment analytics
     */
    public function getShipmentAnalytics($period = '30 days')
    {
        $startDate = now()->sub($period);

        return [
            'total_shipments' => OrderShipment::where('created_at', '>=', $startDate)->count(),
            'delivered_shipments' => OrderShipment::where('status', 'delivered')
                ->where('delivered_at', '>=', $startDate)->count(),
            'in_transit_shipments' => OrderShipment::where('status', 'in_transit')->count(),
            'exception_shipments' => OrderShipment::where('status', 'exception')
                ->where('created_at', '>=', $startDate)->count(),
            'average_delivery_time' => $this->getAverageDeliveryTime($startDate),
            'carrier_performance' => $this->getCarrierPerformance($startDate),
            'shipping_costs' => OrderShipment::where('created_at', '>=', $startDate)
                ->sum('shipping_cost')
        ];
    }

    /**
     * Get average delivery time
     */
    protected function getAverageDeliveryTime($startDate)
    {
        return OrderShipment::where('status', 'delivered')
            ->where('delivered_at', '>=', $startDate)
            ->whereNotNull('shipped_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, shipped_at, delivered_at)) as avg_hours')
            ->value('avg_hours');
    }

    /**
     * Get carrier performance metrics
     */
    protected function getCarrierPerformance($startDate)
    {
        return ShippingCarrier::withCount([
            'shipments as total_shipments' => function($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            },
            'shipments as delivered_shipments' => function($query) use ($startDate) {
                $query->where('status', 'delivered')
                      ->where('delivered_at', '>=', $startDate);
            },
            'shipments as exception_shipments' => function($query) use ($startDate) {
                $query->where('status', 'exception')
                      ->where('created_at', '>=', $startDate);
            }
        ])->get()->map(function($carrier) {
            $carrier->delivery_rate = $carrier->total_shipments > 0 
                ? ($carrier->delivered_shipments / $carrier->total_shipments) * 100 
                : 0;
            $carrier->exception_rate = $carrier->total_shipments > 0 
                ? ($carrier->exception_shipments / $carrier->total_shipments) * 100 
                : 0;
            return $carrier;
        });
    }
}