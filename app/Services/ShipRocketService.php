<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderShipment;
use App\Models\ShippingCarrier;

class ShipRocketService
{
    private $baseUrl = 'https://apiv2.shiprocket.in/v1/external/';
    private $token;
    private $email;
    private $password;

    public function __construct()
    {
        $this->email = config('services.shiprocket.email');
        $this->password = config('services.shiprocket.password');
        
        if ($this->email && $this->password) {
            $this->authenticate();
        }
    }

    /**
     * Authenticate with ShipRocket API
     */
    public function authenticate()
    {
        try {
            $response = Http::timeout(30)->post($this->baseUrl . 'auth/login', [
                'email' => $this->email,
                'password' => $this->password,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->token = $data['token'];
                return true;
            } else {
                Log::error('ShipRocket authentication failed', [
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('ShipRocket authentication error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create order in ShipRocket
     */
    public function createOrder(Order $order)
    {
        if (!$this->token) {
            throw new \Exception('ShipRocket not authenticated');
        }

        $orderData = [
            'order_id' => $order->order_number,
            'order_date' => $order->created_at->format('Y-m-d H:i'),
            'pickup_location' => 'Primary',
            'billing_customer_name' => $order->user->name,
            'billing_last_name' => '',
            'billing_address' => $order->address->address_line_1,
            'billing_address_2' => $order->address->address_line_2 ?? '',
            'billing_city' => $order->address->city->name ?? 'Mumbai',
            'billing_pincode' => $order->address->postal_code,
            'billing_state' => $order->address->state->name ?? 'Maharashtra',
            'billing_country' => $order->address->country->name ?? 'India',
            'billing_email' => $order->user->email,
            'billing_phone' => $order->address->phone,
            'shipping_is_billing' => true,
            'order_items' => $this->formatOrderItems($order),
            'payment_method' => $order->payment_method === 'cod' ? 'COD' : 'Prepaid',
            'sub_total' => $order->total,
            'length' => $this->getPackageDimension($order, 'length', 15),
            'breadth' => $this->getPackageDimension($order, 'width', 10),
            'height' => $this->getPackageDimension($order, 'height', 10),
            'weight' => $this->calculateWeight($order)
        ];

        try {
            $response = Http::timeout(30)
                ->withToken($this->token)
                ->post($this->baseUrl . 'orders/create/adhoc', $orderData);

            if ($response->successful()) {
                $data = $response->json();
                
                // Get or create ShipRocket carrier
                $carrier = $this->getShipRocketCarrier();
                
                // Create shipment record
                $shipment = OrderShipment::create([
                    'order_id' => $order->id,
                    'carrier_id' => $carrier->id,
                    'shipment_number' => $data['shipment_id'] ?? ('SHP' . date('Ymd') . rand(1000, 9999)),
                    'shiprocket_order_id' => $data['order_id'],
                    'shiprocket_shipment_id' => $data['shipment_id'] ?? null,
                    'status' => 'pending',
                    'shipping_cost' => $order->shipping_cost,
                    'package_weight' => $this->calculateWeight($order),
                    'package_dimensions' => [
                        'length' => $orderData['length'],
                        'width' => $orderData['breadth'],
                        'height' => $orderData['height']
                    ],
                    'shipped_to_address' => [
                        'name' => $order->user->name,
                        'address_line_1' => $order->address->address_line_1,
                        'address_line_2' => $order->address->address_line_2,
                        'city' => $order->address->city->name ?? 'Mumbai',
                        'state' => $order->address->state->name ?? 'Maharashtra',
                        'postal_code' => $order->address->postal_code,
                        'country' => $order->address->country->name ?? 'India',
                        'phone' => $order->address->phone
                    ],
                    'cod_amount' => $order->payment_method === 'cod' ? $order->grand_total : 0,
                    'metadata' => $data
                ]);

                Log::info('ShipRocket order created successfully', [
                    'order_id' => $order->id,
                    'shiprocket_order_id' => $data['order_id'],
                    'shipment_id' => $shipment->id
                ]);

                return $shipment;
            } else {
                Log::error('ShipRocket order creation failed', [
                    'order_id' => $order->id,
                    'response' => $response->body(),
                    'order_data' => $orderData
                ]);
                throw new \Exception('Failed to create ShipRocket order: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('ShipRocket order creation error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Track shipment by AWB/tracking number
     */
    public function trackShipment($awbCode)
    {
        if (!$this->token) {
            throw new \Exception('ShipRocket not authenticated');
        }

        try {
            $response = Http::timeout(30)
                ->withToken($this->token)
                ->get($this->baseUrl . "courier/track/awb/{$awbCode}");

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::warning('ShipRocket tracking failed', [
                    'awb' => $awbCode,
                    'response' => $response->body()
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('ShipRocket tracking error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate shipping label
     */
    public function generateLabel($shipmentId)
    {
        if (!$this->token) {
            throw new \Exception('ShipRocket not authenticated');
        }

        try {
            $response = Http::timeout(30)
                ->withToken($this->token)
                ->post($this->baseUrl . 'courier/generate/label', [
                    'shipment_id' => [$shipmentId]
                ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('ShipRocket label generation failed', [
                    'shipment_id' => $shipmentId,
                    'response' => $response->body()
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('ShipRocket label generation error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get available courier services for order
     */
    public function getCourierServices($pickupPostcode, $deliveryPostcode, $weight, $codAmount = 0)
    {
        if (!$this->token) {
            throw new \Exception('ShipRocket not authenticated');
        }

        try {
            $response = Http::timeout(30)
                ->withToken($this->token)
                ->get($this->baseUrl . 'courier/serviceability/', [
                    'pickup_postcode' => $pickupPostcode,
                    'delivery_postcode' => $deliveryPostcode,
                    'weight' => $weight,
                    'cod' => $codAmount > 0 ? 1 : 0
                ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::warning('ShipRocket serviceability check failed', [
                    'pickup_postcode' => $pickupPostcode,
                    'delivery_postcode' => $deliveryPostcode,
                    'response' => $response->body()
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('ShipRocket serviceability error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cancel shipment
     */
    public function cancelShipment($awbCode)
    {
        if (!$this->token) {
            throw new \Exception('ShipRocket not authenticated');
        }

        try {
            $response = Http::timeout(30)
                ->withToken($this->token)
                ->post($this->baseUrl . 'orders/cancel/shipment/awbs', [
                    'awbs' => [$awbCode]
                ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('ShipRocket cancellation failed', [
                    'awb' => $awbCode,
                    'response' => $response->body()
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('ShipRocket cancellation error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Format order items for ShipRocket API
     */
    private function formatOrderItems($order)
    {
        return $order->items->map(function ($item) {
            return [
                'name' => $item->product_name,
                'sku' => $item->product->sku ?? 'DEFAULT-SKU-' . $item->product_id,
                'units' => $item->quantity,
                'selling_price' => $item->price,
                'discount' => 0,
                'tax' => 0,
                'hsn' => $item->product->hsn_code ?? 0
            ];
        })->toArray();
    }

    /**
     * Calculate total weight of order
     */
    private function calculateWeight($order)
    {
        $totalWeight = $order->items->sum(function ($item) {
            $productWeight = $item->product->weight ?? 0.5; // Default 500g if not set
            return $productWeight * $item->quantity;
        });

        // Minimum weight of 0.5kg for ShipRocket
        return max($totalWeight, 0.5);
    }

    /**
     * Get package dimension
     */
    private function getPackageDimension($order, $dimension, $default)
    {
        if ($order->package_dimensions && isset($order->package_dimensions[$dimension])) {
            return $order->package_dimensions[$dimension];
        }
        return $default;
    }

    /**
     * Get or create ShipRocket carrier
     */
    private function getShipRocketCarrier()
    {
        return ShippingCarrier::firstOrCreate(
            ['code' => 'shiprocket'],
            [
                'name' => 'ShipRocket',
                'code' => 'shiprocket',
                'tracking_url_template' => 'https://shiprocket.in/tracking/{tracking_number}',
                'is_active' => true,
                'supports_cod' => true,
                'supports_express' => true,
                'base_rate' => 40.00,
                'per_kg_rate' => 20.00,
                'free_shipping_threshold' => 500.00
            ]
        );
    }

    /**
     * Test API connection
     */
    public function testConnection()
    {
        try {
            $authenticated = $this->authenticate();
            
            if (!$authenticated) {
                return [
                    'success' => false,
                    'message' => 'Authentication failed'
                ];
            }

            // Test with a simple API call
            $response = Http::timeout(10)
                ->withToken($this->token)
                ->get($this->baseUrl . 'settings/company/pickup');

            return [
                'success' => $response->successful(),
                'message' => $response->successful() ? 'Connection successful' : 'API call failed',
                'data' => $response->successful() ? $response->json() : $response->body()
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        }
    }
}