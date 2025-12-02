<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Models\OrderShipment;
use App\Models\Order;
use App\Services\ShipRocketService;
use App\Jobs\UpdateShippingTrackingJob;
use Exception;

class WebhookController extends Controller
{
    /**
     * Handle ShipRocket webhook for tracking updates
     */
    public function shiprocket(Request $request)
    {
        try {
            Log::info('ShipRocket webhook received', $request->all());

            // Validate webhook signature if configured
            if (!$this->validateShipRocketSignature($request)) {
                Log::warning('Invalid ShipRocket webhook signature');
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $trackingNumber = $request->input('awb');
            $status = $request->input('current_status');
            $statusCode = $request->input('status');
            $location = $request->input('location');
            $eventTime = $request->input('date_time');

            if (!$trackingNumber) {
                Log::warning('ShipRocket webhook missing tracking number');
                return response()->json(['error' => 'Missing tracking number'], 400);
            }

            // Find shipment by tracking number
            $shipment = OrderShipment::where('tracking_number', $trackingNumber)->first();
            
            if (!$shipment) {
                Log::warning('Shipment not found for tracking number', ['tracking_number' => $trackingNumber]);
                return response()->json(['error' => 'Shipment not found'], 404);
            }

            // Map ShipRocket status to internal status
            $internalStatus = $this->mapShipRocketStatus($statusCode, $status);
            
            if ($internalStatus && $shipment->status !== $internalStatus) {
                // Update shipment status
                $shipment->update([
                    'status' => $internalStatus,
                    'last_updated_at' => now()
                ]);

                // Create tracking event
                $shipment->trackingEvents()->create([
                    'status' => $internalStatus,
                    'description' => $status,
                    'location' => $location,
                    'event_time' => $eventTime ? \Carbon\Carbon::parse($eventTime) : now(),
                    'metadata' => [
                        'shiprocket_status_code' => $statusCode,
                        'webhook_data' => $request->all()
                    ]
                ]);

                // Update order status based on shipment status
                $this->updateOrderStatusFromShipment($shipment);

                Log::info('ShipRocket webhook processed successfully', [
                    'tracking_number' => $trackingNumber,
                    'old_status' => $shipment->status,
                    'new_status' => $internalStatus
                ]);
            }

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            Log::error('ShipRocket webhook processing failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle Razorpay webhook for payment events
     */
    public function razorpay(Request $request)
    {
        try {
            Log::info('Razorpay webhook received', $request->all());

            // Validate webhook signature
            if (!$this->validateRazorpaySignature($request)) {
                Log::warning('Invalid Razorpay webhook signature');
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $event = $request->input('event');
            $payload = $request->input('payload');

            switch ($event) {
                case 'payment.captured':
                    $this->handlePaymentCaptured($payload);
                    break;
                    
                case 'payment.failed':
                    $this->handlePaymentFailed($payload);
                    break;
                    
                case 'order.paid':
                    $this->handleOrderPaid($payload);
                    break;
                    
                default:
                    Log::info('Unhandled Razorpay webhook event', ['event' => $event]);
            }

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            Log::error('Razorpay webhook processing failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Validate ShipRocket webhook signature
     */
    private function validateShipRocketSignature(Request $request): bool
    {
        // Add signature validation logic if ShipRocket provides webhook signatures
        // For now, we'll skip validation but log the attempt
        Log::info('ShipRocket webhook signature validation skipped (not implemented)');
        return true;
    }

    /**
     * Validate Razorpay webhook signature
     */
    private function validateRazorpaySignature(Request $request): bool
    {
        $webhookSecret = config('services.razorpay.webhook_secret');
        
        if (!$webhookSecret) {
            Log::warning('Razorpay webhook secret not configured');
            return false;
        }

        $signature = $request->header('X-Razorpay-Signature');
        $payload = $request->getContent();
        
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Map ShipRocket status to internal status
     */
    private function mapShipRocketStatus($statusCode, $statusText): ?string
    {
        $statusMap = [
            '6' => 'picked_up',      // Shipped
            '7' => 'in_transit',     // In Transit
            '8' => 'out_for_delivery', // Out for Delivery
            '10' => 'delivered',     // Delivered
            '14' => 'returned',      // RTO Initiated
            '15' => 'returned',      // RTO Delivered
            '17' => 'exception',     // Pickup Error
            '20' => 'exception',     // Pickup Rescheduled
        ];

        return $statusMap[$statusCode] ?? null;
    }

    /**
     * Update order status based on shipment status
     */
    private function updateOrderStatusFromShipment(OrderShipment $shipment): void
    {
        $order = $shipment->order;
        
        switch ($shipment->status) {
            case 'picked_up':
                if ($order->status === 'processing') {
                    $order->update(['status' => 'shipped']);
                }
                break;
                
            case 'delivered':
                $order->update([
                    'status' => 'delivered',
                    'delivered_at' => now()
                ]);
                
                // For COD orders, mark payment as collected
                if ($order->payment_method === 'cod') {
                    $order->update(['payment_status' => 'paid']);
                }
                break;
                
            case 'returned':
                $order->update(['status' => 'returned']);
                break;
        }
    }

    /**
     * Handle payment captured event
     */
    private function handlePaymentCaptured($payload): void
    {
        $paymentId = $payload['payment']['entity']['id'];
        $orderId = $payload['payment']['entity']['order_id'];
        
        Log::info('Payment captured via webhook', [
            'payment_id' => $paymentId,
            'order_id' => $orderId
        ]);
        
        // Additional processing if needed
    }

    /**
     * Handle payment failed event
     */
    private function handlePaymentFailed($payload): void
    {
        $paymentId = $payload['payment']['entity']['id'];
        $orderId = $payload['payment']['entity']['order_id'];
        
        Log::warning('Payment failed via webhook', [
            'payment_id' => $paymentId,
            'order_id' => $orderId
        ]);
        
        // Update order status if needed
    }

    /**
     * Handle order paid event
     */
    private function handleOrderPaid($payload): void
    {
        $orderId = $payload['order']['entity']['id'];
        
        Log::info('Order paid via webhook', ['order_id' => $orderId]);
        
        // Additional processing if needed
    }
}