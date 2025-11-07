<?php

namespace App\Services;

use App\Models\ShippingCarrier;
use App\Models\OrderShipment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class CarrierIntegrationService
{
    /**
     * Create shipment with carrier
     */
    public function createShipment(ShippingCarrier $carrier, OrderShipment $shipment): array
    {
        try {
            return match($carrier->code) {
                'shiprocket' => $this->createShipRocketShipment($carrier, $shipment),
                'delhivery' => $this->createDelhiveryShipment($carrier, $shipment),
                'bluedart' => $this->createBlueDartShipment($carrier, $shipment),
                'dtdc' => $this->createDTDCShipment($carrier, $shipment),
                'fedex' => $this->createFedExShipment($carrier, $shipment),
                'ups' => $this->createUPSShipment($carrier, $shipment),
                default => $this->createGenericShipment($carrier, $shipment)
            };
        } catch (Exception $e) {
            Log::error('Carrier shipment creation failed', [
                'carrier' => $carrier->code,
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get tracking information from carrier
     */
    public function getTrackingInfo(ShippingCarrier $carrier, string $trackingNumber): array
    {
        try {
            return match($carrier->code) {
                'shiprocket' => $this->getShipRocketTracking($carrier, $trackingNumber),
                'delhivery' => $this->getDelhiveryTracking($carrier, $trackingNumber),
                'bluedart' => $this->getBlueDartTracking($carrier, $trackingNumber),
                'dtdc' => $this->getDTDCTracking($carrier, $trackingNumber),
                'fedex' => $this->getFedExTracking($carrier, $trackingNumber),
                'ups' => $this->getUPSTracking($carrier, $trackingNumber),
                default => $this->getGenericTracking($carrier, $trackingNumber)
            };
        } catch (Exception $e) {
            Log::error('Carrier tracking failed', [
                'carrier' => $carrier->code,
                'tracking_number' => $trackingNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Cancel shipment with carrier
     */
    public function cancelShipment(ShippingCarrier $carrier, OrderShipment $shipment): array
    {
        try {
            return match($carrier->code) {
                'shiprocket' => $this->cancelShipRocketShipment($carrier, $shipment),
                'delhivery' => $this->cancelDelhiveryShipment($carrier, $shipment),
                'bluedart' => $this->cancelBlueDartShipment($carrier, $shipment),
                'dtdc' => $this->cancelDTDCShipment($carrier, $shipment),
                default => $this->cancelGenericShipment($carrier, $shipment)
            };
        } catch (Exception $e) {
            Log::error('Carrier shipment cancellation failed', [
                'carrier' => $carrier->code,
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * ShipRocket Integration
     */
    protected function createShipRocketShipment(ShippingCarrier $carrier, OrderShipment $shipment): array
    {
        $order = $shipment->order;
        $shippingAddress = $order->shippingAddress;

        // Get authentication token
        $token = $this->getShipRocketToken($carrier);
        
        if (!$token) {
            throw new Exception('Failed to authenticate with ShipRocket');
        }

        $payload = [
            'order_id' => $order->order_number,
            'order_date' => $order->created_at->format('Y-m-d H:i'),
            'pickup_location' => 'Primary',
            'billing_customer_name' => $order->billingAddress->first_name . ' ' . $order->billingAddress->last_name,
            'billing_last_name' => $order->billingAddress->last_name,
            'billing_address' => $order->billingAddress->address_line_1,
            'billing_address_2' => $order->billingAddress->address_line_2 ?? '',
            'billing_city' => $order->billingAddress->city->name ?? $order->billingAddress->city,
            'billing_pincode' => $order->billingAddress->postal_code,
            'billing_state' => $order->billingAddress->state->name ?? $order->billingAddress->state,
            'billing_country' => $order->billingAddress->country->name ?? $order->billingAddress->country,
            'billing_email' => $order->user->email,
            'billing_phone' => $order->billingAddress->phone,
            'shipping_is_billing' => false,
            'shipping_customer_name' => $shippingAddress->first_name . ' ' . $shippingAddress->last_name,
            'shipping_last_name' => $shippingAddress->last_name,
            'shipping_address' => $shippingAddress->address_line_1,
            'shipping_address_2' => $shippingAddress->address_line_2 ?? '',
            'shipping_city' => $shippingAddress->city->name ?? $shippingAddress->city,
            'shipping_pincode' => $shippingAddress->postal_code,
            'shipping_country' => $shippingAddress->country->name ?? $shippingAddress->country,
            'shipping_state' => $shippingAddress->state->name ?? $shippingAddress->state,
            'shipping_email' => $order->user->email,
            'shipping_phone' => $shippingAddress->phone,
            'order_items' => $this->formatShipRocketOrderItems($order),
            'payment_method' => $order->payment_method === 'cod' ? 'COD' : 'Prepaid',
            'shipping_charges' => $shipment->shipping_cost,
            'giftwrap_charges' => 0,
            'transaction_charges' => 0,
            'total_discount' => $order->discount_amount ?? 0,
            'sub_total' => $order->subtotal,
            'length' => $shipment->package_dimensions['length'] ?? 10,
            'breadth' => $shipment->package_dimensions['width'] ?? 10,
            'height' => $shipment->package_dimensions['height'] ?? 5,
            'weight' => $shipment->package_weight ?? 0.5
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ])->post($carrier->api_endpoint . '/v1/external/orders/create/adhoc', $payload);

        if ($response->successful()) {
            $data = $response->json();
            
            return [
                'success' => true,
                'order_id' => $data['order_id'],
                'shipment_id' => $data['shipment_id'],
                'tracking_number' => $data['awb_code'] ?? null,
                'estimated_delivery' => null,
                'carrier_response' => $data
            ];
        }

        throw new Exception('ShipRocket API error: ' . $response->body());
    }

    protected function getShipRocketToken(ShippingCarrier $carrier): ?string
    {
        $response = Http::post($carrier->api_endpoint . '/v1/external/auth/login', [
            'email' => $carrier->api_key,
            'password' => $carrier->api_secret
        ]);

        if ($response->successful()) {
            return $response->json()['token'];
        }

        return null;
    }

    protected function formatShipRocketOrderItems($order): array
    {
        $items = [];
        
        foreach ($order->items as $item) {
            $items[] = [
                'name' => $item->product->name,
                'sku' => $item->product->sku ?? $item->product->id,
                'units' => $item->quantity,
                'selling_price' => $item->price,
                'discount' => 0,
                'tax' => 0,
                'hsn' => $item->product->hsn_code ?? 0
            ];
        }

        return $items;
    }

    protected function getShipRocketTracking(ShippingCarrier $carrier, string $trackingNumber): array
    {
        $token = $this->getShipRocketToken($carrier);
        
        if (!$token) {
            throw new Exception('Failed to authenticate with ShipRocket');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get($carrier->api_endpoint . '/v1/external/courier/track/awb/' . $trackingNumber);

        if ($response->successful()) {
            $data = $response->json();
            $trackingData = $data['tracking_data'] ?? [];
            
            $events = [];
            foreach ($trackingData['shipment_track'] ?? [] as $track) {
                $events[] = [
                    'status' => $this->normalizeShipRocketStatus($track['current_status']),
                    'description' => $track['activity'],
                    'location' => $track['location'] ?? '',
                    'event_time' => $track['date'] . ' ' . $track['time']
                ];
            }

            return [
                'success' => true,
                'current_status' => $this->normalizeShipRocketStatus($trackingData['track_status'] ?? 'pending'),
                'events' => $events,
                'estimated_delivery' => null
            ];
        }

        throw new Exception('ShipRocket tracking API error: ' . $response->body());
    }

    protected function normalizeShipRocketStatus(string $status): string
    {
        return match(strtolower($status)) {
            'pickup scheduled' => 'pending',
            'shipped' => 'picked_up',
            'in transit' => 'in_transit',
            'out for delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
            'rto' => 'returned',
            'exception' => 'exception',
            default => 'pending'
        };
    }

    protected function cancelShipRocketShipment(ShippingCarrier $carrier, OrderShipment $shipment): array
    {
        $token = $this->getShipRocketToken($carrier);
        
        if (!$token) {
            throw new Exception('Failed to authenticate with ShipRocket');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->post($carrier->api_endpoint . '/v1/external/orders/cancel', [
            'ids' => [$shipment->metadata['order_id'] ?? $shipment->id]
        ]);

        if ($response->successful()) {
            return ['success' => true, 'message' => 'Shipment cancelled successfully'];
        }

        throw new Exception('ShipRocket cancellation API error: ' . $response->body());
    }

    /**
     * Delhivery Integration
     */
    protected function createDelhiveryShipment(ShippingCarrier $carrier, OrderShipment $shipment): array
    {
        $order = $shipment->order;
        $shippingAddress = $order->shippingAddress;

        $payload = [
            'shipments' => [[
                'name' => $shippingAddress->first_name . ' ' . $shippingAddress->last_name,
                'add' => $shippingAddress->address_line_1 . ' ' . ($shippingAddress->address_line_2 ?? ''),
                'pin' => $shippingAddress->postal_code,
                'city' => $shippingAddress->city->name ?? $shippingAddress->city,
                'state' => $shippingAddress->state->name ?? $shippingAddress->state,
                'country' => $shippingAddress->country->name ?? $shippingAddress->country,
                'phone' => $shippingAddress->phone,
                'order' => $order->order_number,
                'payment_mode' => $order->payment_method === 'cod' ? 'COD' : 'Prepaid',
                'return_pin' => config('shipping.origin.postal_code', '400001'),
                'return_city' => config('shipping.origin.city', 'Mumbai'),
                'return_phone' => config('shipping.origin.phone', '1234567890'),
                'return_add' => config('shipping.origin.address_line_1', 'Business Address'),
                'return_state' => config('shipping.origin.state', 'Maharashtra'),
                'return_country' => config('shipping.origin.country', 'India'),
                'products_desc' => implode(', ', $order->items->pluck('product.name')->toArray()),
                'hsn_code' => '123456',
                'cod_amount' => $order->payment_method === 'cod' ? $order->total : 0,
                'order_date' => $order->created_at->format('Y-m-d H:i:s'),
                'total_amount' => $order->total,
                'seller_add' => config('shipping.origin.address_line_1', 'Business Address'),
                'seller_name' => config('app.name'),
                'seller_inv' => $order->order_number,
                'quantity' => $order->items->sum('quantity'),
                'waybill' => '',
                'shipment_width' => $shipment->package_dimensions['width'] ?? 10,
                'shipment_height' => $shipment->package_dimensions['height'] ?? 5,
                'weight' => $shipment->package_weight ?? 0.5,
                'seller_gst_tin' => config('shipping.gst_number', ''),
                'shipping_mode' => 'Surface',
                'address_type' => 'home'
            ]]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Token ' . $carrier->api_key
        ])->post($carrier->api_endpoint . '/api/cmu/create.json', [
            'format' => 'json',
            'data' => json_encode($payload)
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            if (isset($data['packages'][0]['waybill'])) {
                return [
                    'success' => true,
                    'tracking_number' => $data['packages'][0]['waybill'],
                    'estimated_delivery' => null,
                    'carrier_response' => $data
                ];
            }
        }

        throw new Exception('Delhivery API error: ' . $response->body());
    }

    protected function getDelhiveryTracking(ShippingCarrier $carrier, string $trackingNumber): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Token ' . $carrier->api_key
        ])->get($carrier->api_endpoint . '/api/v1/packages/json/', [
            'waybill' => $trackingNumber
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            // Process Delhivery tracking response
            $events = [];
            // Implementation depends on Delhivery API response format
            
            return [
                'success' => true,
                'current_status' => 'in_transit', // Normalize based on response
                'events' => $events,
                'estimated_delivery' => null
            ];
        }

        throw new Exception('Delhivery tracking API error: ' . $response->body());
    }

    protected function cancelDelhiveryShipment(ShippingCarrier $carrier, OrderShipment $shipment): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Token ' . $carrier->api_key
        ])->post($carrier->api_endpoint . '/api/p/edit', [
            'waybill' => $shipment->tracking_number,
            'cancellation' => 'true'
        ]);

        if ($response->successful()) {
            return ['success' => true, 'message' => 'Shipment cancelled successfully'];
        }

        throw new Exception('Delhivery cancellation API error: ' . $response->body());
    }

    /**
     * BlueDart Integration (placeholder)
     */
    protected function createBlueDartShipment(ShippingCarrier $carrier, OrderShipment $shipment): array
    {
        // BlueDart API integration would go here
        throw new Exception('BlueDart integration not implemented yet');
    }

    protected function getBlueDartTracking(ShippingCarrier $carrier, string $trackingNumber): array
    {
        // BlueDart tracking API would go here
        throw new Exception('BlueDart tracking not implemented yet');
    }

    protected function cancelBlueDartShipment(ShippingCarrier $carrier, OrderShipment $shipment): array
    {
        // BlueDart cancellation API would go here
        throw new Exception('BlueDart cancellation not implemented yet');
    }

    /**
     * DTDC Integration (placeholder)
     */
    protected function createDTDCShipment(ShippingCarrier $carrier, OrderShipment $shipment): array
    {
        // DTDC API integration would go here
        throw new Exception('DTDC integration not implemented yet');
    }

    protected function getDTDCTracking(ShippingCarrier $carrier, string $trackingNumber): array
    {
        // DTDC tracking API would go here
        throw new Exception('DTDC tracking not implemented yet');
    }

    protected function cancelDTDCShipment(ShippingCarrier $carrier, OrderShipment $shipment): array
    {
        // DTDC cancellation API would go here
        throw new Exception('DTDC cancellation not implemented yet');
    }

    /**
     * FedEx Integration (placeholder)
     */
    protected function createFedExShipment(ShippingCarrier $carrier, OrderShipment $shipment): array
    {
        // FedEx API integration would go here
        throw new Exception('FedEx integration not implemented yet');
    }

    protected function getFedExTracking(ShippingCarrier $carrier, string $trackingNumber): array
    {
        // FedEx tracking API would go here
        throw new Exception('FedEx tracking not implemented yet');
    }

    /**
     * UPS Integration (placeholder)
     */
    protected function createUPSShipment(ShippingCarrier $carrier, OrderShipment $shipment): array
    {
        // UPS API integration would go here
        throw new Exception('UPS integration not implemented yet');
    }

    protected function getUPSTracking(ShippingCarrier $carrier, string $trackingNumber): array
    {
        // UPS tracking API would go here
        throw new Exception('UPS tracking not implemented yet');
    }

    /**
     * Generic carrier methods (fallback)
     */
    protected function createGenericShipment(ShippingCarrier $carrier, OrderShipment $shipment): array
    {
        // Generate a dummy tracking number for testing
        $trackingNumber = strtoupper($carrier->code) . date('Ymd') . sprintf('%06d', $shipment->id);
        
        return [
            'success' => true,
            'tracking_number' => $trackingNumber,
            'estimated_delivery' => now()->addDays(3),
            'carrier_response' => [
                'note' => 'Generic carrier - tracking number generated locally'
            ]
        ];
    }

    protected function getGenericTracking(ShippingCarrier $carrier, string $trackingNumber): array
    {
        // Return dummy tracking data for testing
        return [
            'success' => true,
            'current_status' => 'in_transit',
            'events' => [
                [
                    'status' => 'picked_up',
                    'description' => 'Package picked up from sender',
                    'location' => 'Origin Hub',
                    'event_time' => now()->subDays(1)->format('Y-m-d H:i:s')
                ],
                [
                    'status' => 'in_transit',
                    'description' => 'Package in transit',
                    'location' => 'Sorting Facility',
                    'event_time' => now()->format('Y-m-d H:i:s')
                ]
            ],
            'estimated_delivery' => now()->addDays(2)
        ];
    }

    protected function cancelGenericShipment(ShippingCarrier $carrier, OrderShipment $shipment): array
    {
        return [
            'success' => true,
            'message' => 'Generic carrier shipment cancelled locally'
        ];
    }

    /**
     * Validate carrier credentials
     */
    public function validateCarrierCredentials(ShippingCarrier $carrier): array
    {
        try {
            return match($carrier->code) {
                'shiprocket' => $this->validateShipRocketCredentials($carrier),
                'delhivery' => $this->validateDelhiveryCredentials($carrier),
                default => ['success' => true, 'message' => 'Generic carrier - validation skipped']
            };
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    protected function validateShipRocketCredentials(ShippingCarrier $carrier): array
    {
        $token = $this->getShipRocketToken($carrier);
        
        if ($token) {
            return ['success' => true, 'message' => 'ShipRocket credentials valid'];
        }
        
        return ['success' => false, 'error' => 'Invalid ShipRocket credentials'];
    }

    protected function validateDelhiveryCredentials(ShippingCarrier $carrier): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Token ' . $carrier->api_key
        ])->get($carrier->api_endpoint . '/api/backend/clientwarehouse/');

        if ($response->successful()) {
            return ['success' => true, 'message' => 'Delhivery credentials valid'];
        }
        
        return ['success' => false, 'error' => 'Invalid Delhivery credentials'];
    }

    /**
     * Get available services from carrier
     */
    public function getCarrierServices(ShippingCarrier $carrier): array
    {
        try {
            return match($carrier->code) {
                'shiprocket' => $this->getShipRocketServices($carrier),
                'delhivery' => $this->getDelhiveryServices($carrier),
                default => $this->getGenericServices($carrier)
            };
        } catch (Exception $e) {
            Log::error('Failed to get carrier services', [
                'carrier' => $carrier->code,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    protected function getShipRocketServices(ShippingCarrier $carrier): array
    {
        $token = $this->getShipRocketToken($carrier);
        
        if (!$token) {
            return [];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get($carrier->api_endpoint . '/v1/external/courier/serviceability/');

        if ($response->successful()) {
            return $response->json()['data']['available_courier_companies'] ?? [];
        }

        return [];
    }

    protected function getDelhiveryServices(ShippingCarrier $carrier): array
    {
        // Delhivery services API call would go here
        return [
            ['name' => 'Surface', 'code' => 'surface'],
            ['name' => 'Express', 'code' => 'express']
        ];
    }

    protected function getGenericServices(ShippingCarrier $carrier): array
    {
        return [
            ['name' => 'Standard', 'code' => 'standard'],
            ['name' => 'Express', 'code' => 'express']
        ];
    }
}