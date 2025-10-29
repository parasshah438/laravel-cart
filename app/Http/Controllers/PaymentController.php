<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RazorpayService;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class PaymentController extends Controller
{
    protected $razorpayService;

    public function __construct(RazorpayService $razorpayService)
    {
        $this->razorpayService = $razorpayService;
    }

    /**
     * Get Razorpay configuration for frontend
     */
    public function getRazorpayConfig()
    {
        try {
            $config = $this->razorpayService->getConfig();
            
            return response()->json([
                'success' => true,
                'config' => $config
            ]);
            
        } catch (Exception $e) {
            Log::error('Failed to get Razorpay config', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load payment configuration'
            ], 500);
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(Request $request)
    {
        try {
            $request->validate([
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id' => 'required|string',
                'razorpay_signature' => 'required|string',
            ]);

            // Verify signature
            $isValid = $this->razorpayService->verifyPaymentSignature(
                $request->razorpay_order_id,
                $request->razorpay_payment_id,
                $request->razorpay_signature
            );

            if ($isValid) {
                // Get payment details
                $paymentDetails = $this->razorpayService->fetchPayment($request->razorpay_payment_id);
                
                return response()->json([
                    'success' => true,
                    'verified' => true,
                    'payment_details' => $paymentDetails
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'verified' => false,
                    'message' => 'Payment signature verification failed'
                ], 400);
            }

        } catch (Exception $e) {
            Log::error('Payment verification failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment status for an order
     */
    public function getPaymentStatus($orderId)
    {
        try {
            $user = Auth::user();
            $order = $user->orders()->findOrFail($orderId);

            $status = [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'razorpay_order_id' => $order->razorpay_order_id,
                'razorpay_payment_id' => $order->razorpay_payment_id,
                'total' => $order->grand_total,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ];

            // If payment is successful and we have payment ID, get details from Razorpay
            if ($order->payment_status === 'paid' && $order->razorpay_payment_id) {
                try {
                    $paymentDetails = $this->razorpayService->fetchPayment($order->razorpay_payment_id);
                    $status['razorpay_payment_details'] = $paymentDetails;
                } catch (Exception $e) {
                    Log::warning('Failed to fetch Razorpay payment details', [
                        'order_id' => $orderId,
                        'payment_id' => $order->razorpay_payment_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'payment_status' => $status
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get payment status', [
                'order_id' => $orderId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment status'
            ], 500);
        }
    }

    /**
     * Initiate refund for an order
     */
    public function initiateRefund(Request $request, $orderId)
    {
        try {
            $request->validate([
                'amount' => 'nullable|numeric|min:1',
                'reason' => 'nullable|string|max:500',
            ]);

            $user = Auth::user();
            $order = $user->orders()->findOrFail($orderId);

            // Check if order is eligible for refund
            if ($order->payment_method !== 'razorpay' || $order->payment_status !== 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is not eligible for refund'
                ], 400);
            }

            if (!$order->razorpay_payment_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment ID not found for this order'
                ], 400);
            }

            // Initiate refund
            $refundAmount = $request->amount ?? $order->grand_total;
            $notes = [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'reason' => $request->reason ?? 'Customer requested refund',
                'initiated_by' => $user->id,
                'initiated_at' => now()->toISOString()
            ];

            $refundData = $this->razorpayService->refundPayment(
                $order->razorpay_payment_id,
                $refundAmount,
                $notes
            );

            // Update order with refund information
            $order->update([
                'payment_status' => 'refunded',
                'status' => 'cancelled',
                'notes' => json_encode([
                    'refund_data' => $refundData,
                    'refund_initiated_at' => now()->toISOString(),
                    'refund_amount' => $refundAmount,
                    'refund_reason' => $request->reason
                ])
            ]);

            Log::info('Refund initiated successfully', [
                'order_id' => $order->id,
                'payment_id' => $order->razorpay_payment_id,
                'refund_id' => $refundData['id'] ?? null,
                'amount' => $refundAmount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Refund initiated successfully',
                'refund_data' => $refundData
            ]);

        } catch (Exception $e) {
            Log::error('Refund initiation failed', [
                'order_id' => $orderId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Refund initiation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all payment methods and their status
     */
    public function getPaymentMethods()
    {
        try {
            $methods = [
                'cod' => [
                    'name' => 'Cash on Delivery',
                    'enabled' => true,
                    'description' => 'Pay when your order is delivered',
                    'icon' => 'fas fa-money-bill-wave',
                    'fees' => 0
                ],
                'razorpay' => [
                    'name' => 'Online Payment',
                    'enabled' => !empty(config('services.razorpay.key')),
                    'description' => 'Pay securely with Credit/Debit Card, Net Banking, UPI, Wallets',
                    'icon' => 'fas fa-credit-card',
                    'fees' => 0,
                    'methods' => [
                        'card' => 'Credit/Debit Cards',
                        'netbanking' => 'Net Banking',
                        'upi' => 'UPI',
                        'wallet' => 'Digital Wallets'
                    ]
                ]
            ];

            return response()->json([
                'success' => true,
                'payment_methods' => $methods
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get payment methods', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load payment methods'
            ], 500);
        }
    }

    /**
     * Test Razorpay connection
     */
    public function testConnection()
    {
        try {
            // Test by creating a small test order
            $testOrder = $this->razorpayService->createOrder(
                1, // ₹1 test amount
                'INR',
                'TEST_' . time(),
                ['test' => true]
            );

            Log::info('Razorpay connection test successful', $testOrder);

            return response()->json([
                'success' => true,
                'message' => 'Razorpay connection is working',
                'test_order' => $testOrder
            ]);

        } catch (Exception $e) {
            Log::error('Razorpay connection test failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Razorpay connection failed: ' . $e->getMessage()
            ], 500);
        }
    }
}