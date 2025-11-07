<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderShipment;
use Illuminate\Support\Facades\Log;

class ShipRocketWebhookController extends Controller
{
    /**
     * Handle ShipRocket webhook events
     */
    public function handle(Request $request)
    {
        try {
            $data = $request->all();
            
            Log::info('ShipRocket webhook received', ['data' => $data]);

            // Validate webhook data
            if (!isset($data['order_id']) && !isset($data['awb'])) {
                Log::warning('Invalid ShipRocket webhook data', ['data' => $data]);
                return response()->json(['status' => 'error', 'message' => 'Invalid data'], 400);
            }

            // Find shipment by ShipRocket order ID or AWB
            $shipment = null;
            
            if (isset($data['order_id'])) {
                $shipment = OrderShipment::where('shiprocket_order_id', $data['order_id'])->first();
            }
            
            if (!$shipment && isset($data['awb'])) {
                $shipment = OrderShipment::where('tracking_number', $data['awb'])->first();
            }

            if (!$shipment) {
                Log::warning('Shipment not found for webhook', [
                    'order_id' => $data['order_id'] ?? null,
                    'awb' => $data['awb'] ?? null
                ]);
                return response()->json(['status' => 'error', 'message' => 'Shipment not found'], 404);
            }

            // Update shipment based on webhook data
            $this->updateShipmentFromWebhook($shipment, $data);

            return response()->json(['status' => 'success']);
            
        } catch (\Exception $e) {
            Log::error('ShipRocket webhook error: ' . $e->getMessage(), [
                'data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['status' => 'error', 'message' => 'Internal error'], 500);
        }
    }

    /**
     * Update shipment from webhook data
     */
    private function updateShipmentFromWebhook($shipment, $data)
    {
        // Map ShipRocket status to our internal status
        $status = $this->mapShipRocketStatus($data['current_status'] ?? $data['status'] ?? '');
        
        // Update tracking number if provided
        if (isset($data['awb']) && $data['awb'] !== $shipment->tracking_number) {
            $shipment->update(['tracking_number' => $data['awb']]);
        }

        // Update carrier information if provided
        if (isset($data['courier_company_id'])) {
            $metadata = $shipment->metadata ?? [];
            $metadata['courier_company_id'] = $data['courier_company_id'];
            $metadata['courier_name'] = $data['courier_name'] ?? null;
            $shipment->update(['metadata' => $metadata]);
        }

        // Update estimated delivery if provided
        if (isset($data['expected_delivery_date'])) {
            try {
                $estimatedDelivery = \Carbon\Carbon::parse($data['expected_delivery_date']);
                $shipment->update(['estimated_delivery' => $estimatedDelivery]);
            } catch (\Exception $e) {
                Log::warning('Failed to parse expected delivery date', [
                    'date' => $data['expected_delivery_date'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Update shipment status with tracking event
        if ($status && $status !== $shipment->status) {
            $description = $this->getStatusDescription($data);
            $location = $this->getLocationFromData($data);
            
            $shipment->updateStatus($status, $description, $location);
            
            Log::info('Shipment status updated from webhook', [
                'shipment_id' => $shipment->id,
                'old_status' => $shipment->status,
                'new_status' => $status,
                'description' => $description
            ]);
        }
    }

    /**
     * Map ShipRocket status to internal status
     */
    private function mapShipRocketStatus($shipRocketStatus)
    {
        $statusMap = [
            // ShipRocket status => Internal status
            'NEW' => 'pending',
            'PICKUP_SCHEDULED' => 'pending',
            'PICKUP_GENERATED' => 'pending',
            'PICKUP_COMPLETED' => 'picked_up',
            'SHIPPED' => 'picked_up',
            'IN_TRANSIT' => 'in_transit',
            'OUT_FOR_DELIVERY' => 'out_for_delivery',
            'DELIVERED' => 'delivered',
            'RTO' => 'returned',
            'RTO_DELIVERED' => 'returned',
            'LOST' => 'exception',
            'DAMAGED' => 'exception',
            'CANCELLED' => 'exception',
            'PICKUP_ERROR' => 'exception',
            'PICKUP_CANCELLED' => 'exception',
            'UNDELIVERED' => 'exception',
            'EXCEPTION' => 'exception',
            
            // Alternative formats
            'Shipped' => 'picked_up',
            'In Transit' => 'in_transit',
            'Out for Delivery' => 'out_for_delivery',
            'Delivered' => 'delivered',
            'RTO Initiated' => 'returned',
            'Lost' => 'exception'
        ];

        $normalizedStatus = strtoupper(str_replace([' ', '-'], '_', $shipRocketStatus));
        
        return $statusMap[$normalizedStatus] ?? $statusMap[$shipRocketStatus] ?? null;
    }

    /**
     * Extract description from webhook data
     */
    private function getStatusDescription($data)
    {
        $descriptions = [];
        
        if (isset($data['current_status'])) {
            $descriptions[] = $data['current_status'];
        }
        
        if (isset($data['comment']) && $data['comment']) {
            $descriptions[] = $data['comment'];
        }
        
        if (isset($data['reason']) && $data['reason']) {
            $descriptions[] = 'Reason: ' . $data['reason'];
        }
        
        if (isset($data['courier_name'])) {
            $descriptions[] = 'Courier: ' . $data['courier_name'];
        }

        return implode(' | ', array_filter($descriptions)) ?: 'Status updated via ShipRocket webhook';
    }

    /**
     * Extract location from webhook data
     */
    private function getLocationFromData($data)
    {
        $locations = [];
        
        if (isset($data['location']) && $data['location']) {
            $locations[] = $data['location'];
        }
        
        if (isset($data['city']) && $data['city']) {
            $locations[] = $data['city'];
        }
        
        if (isset($data['state']) && $data['state']) {
            $locations[] = $data['state'];
        }

        return implode(', ', array_filter($locations)) ?: null;
    }

    /**
     * Handle specific webhook events
     */
    public function handlePickup(Request $request)
    {
        return $this->handle($request);
    }

    public function handleDelivery(Request $request)
    {
        return $this->handle($request);
    }

    public function handleReturn(Request $request)
    {
        return $this->handle($request);
    }

    public function handleException(Request $request)
    {
        return $this->handle($request);
    }
}
