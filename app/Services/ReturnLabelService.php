<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Exception;

class ReturnLabelService
{
    /**
     * Generate return pickup label and schedule pickup
     */
    public function generateReturnLabel(Order $order, $returnItems = [])
    {
        try {
            Log::info('Generating return label', [
                'order_id' => $order->id,
                'return_items' => $returnItems
            ]);

            // Choose shipping partner based on original delivery
            $shippingPartner = $this->selectShippingPartner($order);
            
            switch ($shippingPartner) {
                case 'shiprocket':
                    return $this->generateShiprocketReturnLabel($order, $returnItems);
                
                case 'delhivery':
                    return $this->generateDelhiveryReturnLabel($order, $returnItems);
                
                case 'bluedart':
                    return $this->generateBluedartReturnLabel($order, $returnItems);
                
                default:
                    return $this->generateGenericReturnLabel($order, $returnItems);
            }

        } catch (Exception $e) {
            Log::error('Return label generation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to generate return label: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate Shiprocket return label
     */
    private function generateShiprocketReturnLabel($order, $returnItems)
    {
        try {
            $shiprocketToken = $this->getShiprocketToken();
            
            if (!$shiprocketToken) {
                throw new Exception('Failed to authenticate with Shiprocket');
            }

            // Prepare return shipment data
            $returnData = $this->prepareShiprocketReturnData($order, $returnItems);

            // Create return order with Shiprocket
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $shiprocketToken,
                'Content-Type' => 'application/json'
            ])->post('https://apiv2.shiprocket.in/v1/external/orders/create/return', $returnData);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Generate pickup request
                $pickupResult = $this->scheduleShiprocketPickup($responseData['order_id'], $shiprocketToken);

                // Generate and download label
                $labelResult = $this->downloadShiprocketLabel($responseData['shipment_id'], $shiprocketToken);

                // Update order with return information
                $this->updateOrderWithReturnData($order, [
                    'carrier' => 'shiprocket',
                    'return_order_id' => $responseData['order_id'],
                    'return_shipment_id' => $responseData['shipment_id'],
                    'awb_code' => $responseData['awb_code'] ?? null,
                    'pickup_scheduled' => $pickupResult['success'] ?? false,
                    'pickup_date' => $pickupResult['pickup_date'] ?? null,
                    'label_url' => $labelResult['label_url'] ?? null,
                    'tracking_url' => "https://shiprocket.in/tracking/{$responseData['awb_code']}"
                ]);

                return [
                    'success' => true,
                    'message' => 'Return label generated and pickup scheduled successfully',
                    'return_order_id' => $responseData['order_id'],
                    'awb_code' => $responseData['awb_code'],
                    'pickup_date' => $pickupResult['pickup_date'] ?? 'Within 1-2 business days',
                    'label_url' => $labelResult['label_url'],
                    'tracking_url' => "https://shiprocket.in/tracking/{$responseData['awb_code']}",
                    'instructions' => $this->getReturnInstructions()
                ];

            } else {
                throw new Exception('Shiprocket API error: ' . $response->body());
            }

        } catch (Exception $e) {
            Log::error('Shiprocket return label generation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate Delhivery return label
     */
    private function generateDelhiveryReturnLabel($order, $returnItems)
    {
        try {
            $delhiveryToken = config('services.delhivery.token');
            
            $returnData = $this->prepareDelhiveryReturnData($order, $returnItems);

            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $delhiveryToken,
                'Content-Type' => 'application/json'
            ])->post('https://track.delhivery.com/api/cmu/create.json', $returnData);

            if ($response->successful()) {
                $responseData = $response->json();

                // Schedule pickup
                $pickupResult = $this->scheduleDelhiveryPickup($responseData['waybill'], $delhiveryToken);

                $this->updateOrderWithReturnData($order, [
                    'carrier' => 'delhivery',
                    'awb_code' => $responseData['waybill'],
                    'pickup_scheduled' => $pickupResult['success'] ?? false,
                    'pickup_date' => $pickupResult['pickup_date'] ?? null,
                    'tracking_url' => "https://www.delhivery.com/track/package/{$responseData['waybill']}"
                ]);

                return [
                    'success' => true,
                    'message' => 'Delhivery return label generated successfully',
                    'awb_code' => $responseData['waybill'],
                    'pickup_date' => $pickupResult['pickup_date'] ?? 'Within 1-2 business days',
                    'tracking_url' => "https://www.delhivery.com/track/package/{$responseData['waybill']}",
                    'instructions' => $this->getReturnInstructions()
                ];
            } else {
                throw new Exception('Delhivery API error: ' . $response->body());
            }

        } catch (Exception $e) {
            Log::error('Delhivery return label generation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate generic return label (for manual processing)
     */
    private function generateGenericReturnLabel($order, $returnItems)
    {
        try {
            // Create a generic return label with order information
            $labelData = [
                'return_reference' => 'RET' . $order->id . time(),
                'customer_details' => [
                    'name' => $order->user->name,
                    'phone' => $order->user->phone ?? $order->address->phone_number,
                    'email' => $order->user->email,
                    'address' => $this->formatAddress($order->address)
                ],
                'return_address' => $this->getCompanyReturnAddress(),
                'items' => $returnItems,
                'instructions' => $this->getReturnInstructions()
            ];

            // Generate PDF label (you would use a PDF library like DomPDF)
            $labelUrl = $this->generatePDFLabel($labelData);

            $this->updateOrderWithReturnData($order, [
                'carrier' => 'manual',
                'return_reference' => $labelData['return_reference'],
                'label_url' => $labelUrl,
                'pickup_scheduled' => false
            ]);

            return [
                'success' => true,
                'message' => 'Return label generated. Please contact customer service to schedule pickup.',
                'return_reference' => $labelData['return_reference'],
                'label_url' => $labelUrl,
                'pickup_required' => true,
                'contact_number' => config('app.support_phone', '+91-1800-123-4567'),
                'instructions' => $this->getReturnInstructions()
            ];

        } catch (Exception $e) {
            Log::error('Generic return label generation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Helper methods
     */
    private function selectShippingPartner($order)
    {
        // Logic to select shipping partner based on original delivery
        $shipment = $order->latestShipment;
        
        if ($shipment && $shipment->carrier) {
            return strtolower($shipment->carrier->name);
        }

        // Default to configured primary shipping partner
        return config('shipping.primary_partner', 'shiprocket');
    }

    private function getShiprocketToken()
    {
        try {
            $response = Http::post('https://apiv2.shiprocket.in/v1/external/auth/login', [
                'email' => config('services.shiprocket.email'),
                'password' => config('services.shiprocket.password')
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['token'];
            }

        } catch (Exception $e) {
            Log::error('Shiprocket authentication failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    private function prepareShiprocketReturnData($order, $returnItems)
    {
        return [
            'order_id' => 'RET' . $order->id . time(),
            'order_date' => now()->format('Y-m-d H:i'),
            'pickup_location' => 'Primary',
            'channel_id' => config('services.shiprocket.channel_id', ''),
            'comment' => 'Return pickup for Order #' . $order->order_number,
            'billing_customer_name' => $order->user->name,
            'billing_address' => $order->address->address_line_1,
            'billing_city' => $order->address->city->name ?? '',
            'billing_pincode' => $order->address->postal_code,
            'billing_state' => $order->address->state->name ?? '',
            'billing_country' => 'India',
            'billing_email' => $order->user->email,
            'billing_phone' => $order->user->phone ?? $order->address->phone_number,
            'shipping_is_billing' => true,
            'order_items' => $this->formatReturnItems($returnItems),
            'payment_method' => 'Prepaid',
            'sub_total' => collect($returnItems)->sum('total'),
            'length' => 15,
            'breadth' => 10,
            'height' => 5,
            'weight' => 0.5
        ];
    }

    private function formatReturnItems($returnItems)
    {
        return collect($returnItems)->map(function ($item) {
            return [
                'name' => $item['name'],
                'sku' => $item['sku'] ?? 'RET-' . $item['id'],
                'units' => $item['quantity'],
                'selling_price' => $item['price']
            ];
        })->toArray();
    }

    private function scheduleShiprocketPickup($orderId, $token)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ])->post('https://apiv2.shiprocket.in/v1/external/orders/create/pickup', [
                'shipment_id' => $orderId,
                'pickup_date' => now()->addDay()->format('Y-m-d')
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'pickup_date' => now()->addDay()->format('M d, Y')
                ];
            }

        } catch (Exception $e) {
            Log::error('Shiprocket pickup scheduling failed', ['error' => $e->getMessage()]);
        }

        return ['success' => false];
    }

    private function downloadShiprocketLabel($shipmentId, $token)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token
            ])->get("https://apiv2.shiprocket.in/v1/external/courier/generate/label", [
                'shipment_id' => $shipmentId
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Download and store the label
                $labelContent = Http::get($data['label_url'])->body();
                $labelPath = 'return-labels/' . $shipmentId . '.pdf';
                Storage::disk('public')->put($labelPath, $labelContent);

                return [
                    'success' => true,
                    'label_url' => Storage::disk('public')->url($labelPath)
                ];
            }

        } catch (Exception $e) {
            Log::error('Shiprocket label download failed', ['error' => $e->getMessage()]);
        }

        return ['success' => false];
    }

    private function updateOrderWithReturnData($order, $returnData)
    {
        $currentNotes = $order->notes ?? [];
        $currentNotes['return_shipping'] = array_merge(
            $currentNotes['return_shipping'] ?? [],
            [
                'label_generated_at' => now()->toISOString(),
                'carrier_data' => $returnData
            ]
        );

        $order->update(['notes' => $currentNotes]);
    }

    private function formatAddress($address)
    {
        return implode(', ', array_filter([
            $address->address_line_1,
            $address->address_line_2,
            $address->city->name ?? '',
            $address->state->name ?? '',
            $address->postal_code
        ]));
    }

    private function getCompanyReturnAddress()
    {
        return [
            'name' => config('app.name') . ' Returns',
            'address' => config('company.return_address', '123 Business Park, Tech City'),
            'city' => config('company.city', 'Mumbai'),
            'state' => config('company.state', 'Maharashtra'),
            'postal_code' => config('company.postal_code', '400001'),
            'phone' => config('company.phone', '+91-1800-123-4567')
        ];
    }

    private function getReturnInstructions()
    {
        return [
            '1. Pack the item(s) securely in original packaging if available',
            '2. Include all accessories, manuals, and warranty cards',
            '3. Attach the return label to the package',
            '4. Keep the item ready for pickup at the scheduled time',
            '5. Ensure someone is present to hand over the package',
            '6. Get pickup confirmation receipt from delivery person',
            '7. Track return shipment using provided tracking number'
        ];
    }

    private function generatePDFLabel($labelData)
    {
        // This would use a PDF library like DomPDF to generate the label
        // For now, return a placeholder URL
        return url('/storage/return-labels/manual-' . $labelData['return_reference'] . '.pdf');
    }

    /**
     * Track return shipment
     */
    public function trackReturnShipment($order)
    {
        $returnShipping = $order->notes['return_shipping'] ?? null;
        
        if (!$returnShipping) {
            return ['status' => 'no_return_shipping_found'];
        }

        $carrierData = $returnShipping['carrier_data'] ?? [];
        $carrier = $carrierData['carrier'] ?? 'unknown';

        switch ($carrier) {
            case 'shiprocket':
                return $this->trackShiprocketReturn($carrierData['awb_code'] ?? null);
            
            case 'delhivery':
                return $this->trackDelhiveryReturn($carrierData['awb_code'] ?? null);
            
            default:
                return [
                    'status' => 'manual_tracking',
                    'message' => 'Please contact customer service for return status',
                    'reference' => $carrierData['return_reference'] ?? 'N/A'
                ];
        }
    }

    private function trackShiprocketReturn($awbCode)
    {
        if (!$awbCode) {
            return ['status' => 'no_tracking_code'];
        }

        try {
            $token = $this->getShiprocketToken();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token
            ])->get('https://apiv2.shiprocket.in/v1/external/courier/track/awb/' . $awbCode);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => 'tracked',
                    'tracking_data' => $data,
                    'current_status' => $data['tracking_data']['track_status'] ?? 'In Transit',
                    'last_update' => $data['tracking_data']['shipment_track'][0]['date'] ?? now()
                ];
            }

        } catch (Exception $e) {
            Log::error('Shiprocket return tracking failed', ['error' => $e->getMessage()]);
        }

        return ['status' => 'tracking_failed'];
    }
}