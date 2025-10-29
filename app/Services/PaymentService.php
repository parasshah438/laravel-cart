<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Create a new payment record
     */
    public function createPayment(Order $order, array $paymentData): Payment
    {
        $request = request();
        
        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'gateway' => $paymentData['gateway'] ?? 'razorpay',
            'amount' => $paymentData['amount'] ?? $order->grand_total,
            'currency' => $paymentData['currency'] ?? 'INR',
            'method' => $paymentData['method'] ?? null,
            'payment_method' => $paymentData['payment_method'] ?? null,
            'status' => $paymentData['status'] ?? 'pending',
            'payment_status' => $paymentData['payment_status'] ?? 'pending',
            'gateway_order_id' => $paymentData['gateway_order_id'] ?? null,
            'gateway_payment_id' => $paymentData['gateway_payment_id'] ?? null,
            'transaction_id' => $paymentData['transaction_id'] ?? null,
            'metadata' => $paymentData['metadata'] ?? null,
            'ip_address' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'billing_details' => $this->getBillingDetails($order),
        ]);

        Log::info('Payment record created', [
            'payment_id' => $payment->payment_id,
            'order_id' => $order->id,
            'gateway' => $payment->gateway,
            'amount' => $payment->amount,
            'status' => $payment->status
        ]);

        return $payment;
    }

    /**
     * Create payment for COD order
     */
    public function createCODPayment(Order $order): Payment
    {
        return $this->createPayment($order, [
            'gateway' => 'cod',
            'method' => 'cod',
            'payment_method' => 'cash_on_delivery',
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }

    /**
     * Create payment for Razorpay order
     */
    public function createRazorpayPayment(Order $order, string $razorpayOrderId): Payment
    {
        return $this->createPayment($order, [
            'gateway' => 'razorpay',
            'status' => 'pending',
            'payment_status' => 'pending',
            'gateway_order_id' => $razorpayOrderId,
            'metadata' => [
                'razorpay_order_id' => $razorpayOrderId,
                'payment_initiated_at' => now()->toISOString(),
            ]
        ]);
    }

    /**
     * Update payment with success data
     */
    public function markPaymentAsSuccessful(Payment $payment, array $successData): Payment
    {
        $updateData = [
            'status' => 'completed',
            'payment_status' => 'paid',
            'paid_at' => now(),
            'gateway_payment_id' => $successData['gateway_payment_id'] ?? $payment->gateway_payment_id,
            'transaction_id' => $successData['transaction_id'] ?? null,
            'method' => $successData['method'] ?? $payment->method,
            'payment_method' => $successData['payment_method'] ?? $payment->payment_method,
            'gateway_response' => $successData['gateway_response'] ?? null,
        ];

        // Add metadata
        $metadata = $payment->metadata ?? [];
        $metadata['payment_completed_at'] = now()->toISOString();
        $metadata['success_data'] = $successData;
        $updateData['metadata'] = $metadata;

        $payment->update($updateData);

        Log::info('Payment marked as successful', [
            'payment_id' => $payment->payment_id,
            'gateway_payment_id' => $payment->gateway_payment_id,
            'amount' => $payment->amount
        ]);

        return $payment->fresh();
    }

    /**
     * Update payment with failure data
     */
    public function markPaymentAsFailed(Payment $payment, array $failureData): Payment
    {
        $updateData = [
            'status' => 'failed',
            'payment_status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $failureData['reason'] ?? 'Payment failed',
            'gateway_response' => $failureData['gateway_response'] ?? null,
        ];

        // Add metadata
        $metadata = $payment->metadata ?? [];
        $metadata['payment_failed_at'] = now()->toISOString();
        $metadata['failure_data'] = $failureData;
        $updateData['metadata'] = $metadata;

        $payment->update($updateData);

        Log::warning('Payment marked as failed', [
            'payment_id' => $payment->payment_id,
            'reason' => $updateData['failure_reason'],
            'amount' => $payment->amount
        ]);

        return $payment->fresh();
    }

    /**
     * Update payment with refund data
     */
    public function markPaymentAsRefunded(Payment $payment, array $refundData): Payment
    {
        $updateData = [
            'status' => 'refunded',
            'payment_status' => 'refunded',
            'refunded_at' => now(),
            'gateway_response' => $refundData['gateway_response'] ?? null,
        ];

        // Add metadata
        $metadata = $payment->metadata ?? [];
        $metadata['payment_refunded_at'] = now()->toISOString();
        $metadata['refund_data'] = $refundData;
        $updateData['metadata'] = $metadata;

        $payment->update($updateData);

        Log::info('Payment marked as refunded', [
            'payment_id' => $payment->payment_id,
            'refund_amount' => $refundData['amount'] ?? $payment->amount
        ]);

        return $payment->fresh();
    }

    /**
     * Find payment by gateway payment ID
     */
    public function findByGatewayPaymentId(string $gatewayPaymentId): ?Payment
    {
        return Payment::where('gateway_payment_id', $gatewayPaymentId)->first();
    }

    /**
     * Find payment by gateway order ID
     */
    public function findByGatewayOrderId(string $gatewayOrderId): ?Payment
    {
        return Payment::where('gateway_order_id', $gatewayOrderId)->first();
    }

    /**
     * Get payment analytics for date range
     */
    public function getPaymentAnalytics(string $startDate, string $endDate): array
    {
        $payments = Payment::dateRange($startDate, $endDate)->get();

        return [
            'total_payments' => $payments->count(),
            'successful_payments' => $payments->where('payment_status', 'paid')->count(),
            'failed_payments' => $payments->where('payment_status', 'failed')->count(),
            'pending_payments' => $payments->where('payment_status', 'pending')->count(),
            'refunded_payments' => $payments->where('payment_status', 'refunded')->count(),
            'total_amount' => $payments->where('payment_status', 'paid')->sum('amount'),
            'average_amount' => $payments->where('payment_status', 'paid')->avg('amount'),
            'gateway_breakdown' => $payments->groupBy('gateway')->map->count(),
            'method_breakdown' => $payments->where('payment_status', 'paid')->groupBy('method')->map->count(),
        ];
    }

    /**
     * Get billing details from order
     */
    private function getBillingDetails(Order $order): array
    {
        $address = $order->address;
        
        if (!$address) {
            return [];
        }

        return [
            'name' => $address->full_name,
            'email' => $order->user->email,
            'phone' => $address->phone_number,
            'address_line_1' => $address->address_line_1,
            'address_line_2' => $address->address_line_2,
            'city' => $address->city->name ?? null,
            'state' => $address->state->name ?? null,
            'country' => $address->country->name ?? null,
            'postal_code' => $address->postal_code,
        ];
    }

    /**
     * Update payment method from gateway response
     */
    public function updatePaymentMethodFromGateway(Payment $payment, array $gatewayResponse): void
    {
        $method = null;
        $paymentMethod = null;

        // Extract method from Razorpay response
        if ($payment->gateway === 'razorpay' && isset($gatewayResponse['method'])) {
            $method = $gatewayResponse['method'];
            
            // Get more specific payment method details
            if (isset($gatewayResponse['card'])) {
                $paymentMethod = $gatewayResponse['card']['network'] ?? 'card';
            } elseif (isset($gatewayResponse['upi'])) {
                $paymentMethod = $gatewayResponse['upi']['type'] ?? 'upi';
            } elseif (isset($gatewayResponse['bank'])) {
                $paymentMethod = $gatewayResponse['bank']['name'] ?? 'netbanking';
            } elseif (isset($gatewayResponse['wallet'])) {
                $paymentMethod = $gatewayResponse['wallet']['name'] ?? 'wallet';
            }
        }

        if ($method || $paymentMethod) {
            $payment->update([
                'method' => $method ?? $payment->method,
                'payment_method' => $paymentMethod ?? $payment->payment_method,
            ]);

            Log::info('Payment method updated from gateway response', [
                'payment_id' => $payment->payment_id,
                'method' => $method,
                'payment_method' => $paymentMethod
            ]);
        }
    }
}