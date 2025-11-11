<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class RefundProcessingService
{
    /**
     * Process refund based on original payment method
     */
    public function processRefund(Order $order, $refundAmount = null, $reason = 'Return processed')
    {
        try {
            $refundAmount = $refundAmount ?? $order->grand_total;
            $payment = $order->payments()->where('status', 'completed')->first();

            if (!$payment) {
                throw new Exception('No completed payment found for this order');
            }

            Log::info('Processing refund', [
                'order_id' => $order->id,
                'payment_method' => $payment->method,
                'refund_amount' => $refundAmount,
                'reason' => $reason
            ]);

            switch ($payment->method) {
                case 'razorpay':
                case 'card':
                case 'upi':
                case 'netbanking':
                    return $this->processRazorpayRefund($payment, $refundAmount, $reason);
                
                case 'cod':
                    return $this->processCODRefund($order, $payment, $refundAmount, $reason);
                
                case 'wallet':
                    return $this->processWalletRefund($payment, $refundAmount, $reason);
                
                default:
                    throw new Exception('Unsupported payment method for refund: ' . $payment->method);
            }

        } catch (Exception $e) {
            Log::error('Refund processing failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Refund processing failed: ' . $e->getMessage(),
                'error_code' => 'REFUND_FAILED'
            ];
        }
    }

    /**
     * Process Razorpay refund for card/UPI/netbanking payments
     */
    private function processRazorpayRefund($payment, $refundAmount, $reason)
    {
        try {
            $razorpayKey = config('services.razorpay.key');
            $razorpaySecret = config('services.razorpay.secret');

            if (!$payment->gateway_payment_id) {
                throw new Exception('Gateway payment ID not found');
            }

            // Create refund via Razorpay API
            $response = Http::withBasicAuth($razorpayKey, $razorpaySecret)
                ->post("https://api.razorpay.com/v1/payments/{$payment->gateway_payment_id}/refund", [
                    'amount' => $refundAmount * 100, // Amount in paise
                    'speed' => 'normal', // normal or optimum
                    'notes' => [
                        'reason' => $reason,
                        'order_id' => $payment->order_id,
                        'processed_at' => now()->toISOString()
                    ],
                    'receipt' => 'refund_' . $payment->order_id . '_' . time()
                ]);

            if ($response->successful()) {
                $refundData = $response->json();
                
                // Update payment record
                $payment->update([
                    'refund_amount' => $refundAmount,
                    'refund_status' => 'processing',
                    'refund_id' => $refundData['id'],
                    'refunded_at' => now(),
                    'gateway_response' => array_merge($payment->gateway_response ?? [], [
                        'refund_data' => $refundData
                    ])
                ]);

                Log::info('Razorpay refund initiated successfully', [
                    'payment_id' => $payment->id,
                    'refund_id' => $refundData['id'],
                    'amount' => $refundAmount
                ]);

                return [
                    'success' => true,
                    'message' => 'Refund initiated successfully. It will be processed within 5-7 business days.',
                    'refund_id' => $refundData['id'],
                    'estimated_completion' => now()->addBusinessDays(7)->format('M d, Y'),
                    'method' => 'gateway_refund'
                ];

            } else {
                throw new Exception('Razorpay API error: ' . $response->body());
            }

        } catch (Exception $e) {
            Log::error('Razorpay refund failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Process COD refund - Multiple methods available
     */
    private function processCODRefund($order, $payment, $refundAmount, $reason)
    {
        // Get customer's preferred refund method from order or default options
        $refundMethod = $this->determineCODRefundMethod($order);

        switch ($refundMethod) {
            case 'bank_transfer':
                return $this->processBankTransferRefund($order, $payment, $refundAmount, $reason);
            
            case 'upi_transfer':
                return $this->processUPIRefund($order, $payment, $refundAmount, $reason);
            
            case 'store_credit':
                return $this->processStoreCreditRefund($order, $payment, $refundAmount, $reason);
            
            case 'cheque':
                return $this->processChequeRefund($order, $payment, $refundAmount, $reason);
            
            default:
                return $this->processBankTransferRefund($order, $payment, $refundAmount, $reason);
        }
    }

    /**
     * Bank Transfer Refund (Most common for COD)
     */
    private function processBankTransferRefund($order, $payment, $refundAmount, $reason)
    {
        try {
            // Get customer bank details from user profile or order notes
            $bankDetails = $this->getCustomerBankDetails($order->user);

            if (!$bankDetails) {
                // Create a pending refund request for manual processing
                $payment->update([
                    'refund_amount' => $refundAmount,
                    'refund_status' => 'pending_bank_details',
                    'refunded_at' => now(),
                    'gateway_response' => array_merge($payment->gateway_response ?? [], [
                        'refund_method' => 'bank_transfer',
                        'refund_reason' => $reason,
                        'requires_bank_details' => true
                    ])
                ]);

                return [
                    'success' => true,
                    'message' => 'Refund initiated. Please provide your bank details to complete the refund process.',
                    'requires_action' => true,
                    'action_type' => 'bank_details_required',
                    'method' => 'bank_transfer_pending'
                ];
            }

            // Process bank transfer via payment gateway or banking API
            $transferResult = $this->executeBankTransfer($bankDetails, $refundAmount, $order);

            $payment->update([
                'refund_amount' => $refundAmount,
                'refund_status' => 'processing',
                'refunded_at' => now(),
                'gateway_response' => array_merge($payment->gateway_response ?? [], [
                    'refund_method' => 'bank_transfer',
                    'bank_details' => $bankDetails,
                    'transfer_reference' => $transferResult['reference'] ?? null
                ])
            ]);

            return [
                'success' => true,
                'message' => 'Bank transfer refund initiated. Amount will be credited within 3-5 business days.',
                'reference_number' => $transferResult['reference'] ?? 'REF' . time(),
                'estimated_completion' => now()->addBusinessDays(5)->format('M d, Y'),
                'method' => 'bank_transfer'
            ];

        } catch (Exception $e) {
            throw new Exception('Bank transfer refund failed: ' . $e->getMessage());
        }
    }

    /**
     * UPI Refund Processing
     */
    private function processUPIRefund($order, $payment, $refundAmount, $reason)
    {
        try {
            $upiId = $order->user->upi_id ?? $this->getCustomerUPIId($order->user);

            if (!$upiId) {
                $payment->update([
                    'refund_amount' => $refundAmount,
                    'refund_status' => 'pending_upi_id',
                    'refunded_at' => now()
                ]);

                return [
                    'success' => true,
                    'message' => 'Please provide your UPI ID to receive the refund.',
                    'requires_action' => true,
                    'action_type' => 'upi_id_required',
                    'method' => 'upi_pending'
                ];
            }

            // Process UPI transfer
            $upiResult = $this->executeUPITransfer($upiId, $refundAmount, $order);

            $payment->update([
                'refund_amount' => $refundAmount,
                'refund_status' => 'completed',
                'refunded_at' => now(),
                'gateway_response' => array_merge($payment->gateway_response ?? [], [
                    'refund_method' => 'upi_transfer',
                    'upi_id' => $upiId,
                    'upi_reference' => $upiResult['reference'] ?? null
                ])
            ]);

            return [
                'success' => true,
                'message' => 'UPI refund processed successfully.',
                'reference_number' => $upiResult['reference'] ?? 'UPI' . time(),
                'estimated_completion' => 'Instant',
                'method' => 'upi_transfer'
            ];

        } catch (Exception $e) {
            throw new Exception('UPI refund failed: ' . $e->getMessage());
        }
    }

    /**
     * Store Credit Refund
     */
    private function processStoreCreditRefund($order, $payment, $refundAmount, $reason)
    {
        try {
            // Add store credit to user's wallet
            $user = $order->user;
            $user->increment('store_credit', $refundAmount);

            // Create store credit transaction record
            $user->storeCreditTransactions()->create([
                'type' => 'credit',
                'amount' => $refundAmount,
                'description' => "Refund for Order #{$order->order_number}",
                'reference_type' => 'order_refund',
                'reference_id' => $order->id,
                'balance_after' => $user->fresh()->store_credit
            ]);

            $payment->update([
                'refund_amount' => $refundAmount,
                'refund_status' => 'completed',
                'refunded_at' => now(),
                'gateway_response' => array_merge($payment->gateway_response ?? [], [
                    'refund_method' => 'store_credit',
                    'credit_added' => $refundAmount
                ])
            ]);

            return [
                'success' => true,
                'message' => "₹{$refundAmount} store credit added to your account.",
                'credit_amount' => $refundAmount,
                'estimated_completion' => 'Instant',
                'method' => 'store_credit'
            ];

        } catch (Exception $e) {
            throw new Exception('Store credit refund failed: ' . $e->getMessage());
        }
    }

    /**
     * Cheque Refund Processing
     */
    private function processChequeRefund($order, $payment, $refundAmount, $reason)
    {
        try {
            $address = $order->address;
            
            $payment->update([
                'refund_amount' => $refundAmount,
                'refund_status' => 'processing',
                'refunded_at' => now(),
                'gateway_response' => array_merge($payment->gateway_response ?? [], [
                    'refund_method' => 'cheque',
                    'delivery_address' => [
                        'name' => $address->full_name,
                        'address' => $address->address_line_1,
                        'city' => $address->city->name ?? '',
                        'state' => $address->state->name ?? '',
                        'postal_code' => $address->postal_code
                    ]
                ])
            ]);

            return [
                'success' => true,
                'message' => 'Refund cheque will be dispatched to your registered address within 7-10 business days.',
                'cheque_number' => 'CHQ' . time(),
                'estimated_completion' => now()->addBusinessDays(10)->format('M d, Y'),
                'method' => 'cheque'
            ];

        } catch (Exception $e) {
            throw new Exception('Cheque refund failed: ' . $e->getMessage());
        }
    }

    /**
     * Helper methods
     */
    private function determineCODRefundMethod($order)
    {
        // Check user preferences or order notes for preferred refund method
        $userPreference = $order->user->preferred_refund_method ?? 'bank_transfer';
        
        // You can also check order-specific preferences
        $notes = $order->notes ?? [];
        $refundPreference = $notes['refund_preference'] ?? null;
        
        return $refundPreference ?? $userPreference;
    }

    private function getCustomerBankDetails($user)
    {
        // Get from user profile or separate bank_details table
        return $user->bank_details ?? null;
    }

    private function getCustomerUPIId($user)
    {
        return $user->upi_id ?? null;
    }

    private function executeBankTransfer($bankDetails, $amount, $order)
    {
        // Integration with banking API (IMPS/NEFT)
        // This would integrate with services like Razorpay Payouts, Cashfree Payouts, etc.
        
        // Simulated response
        return [
            'reference' => 'BANK' . time(),
            'status' => 'initiated'
        ];
    }

    private function executeUPITransfer($upiId, $amount, $order)
    {
        // Integration with UPI transfer API
        // This would integrate with UPI payment gateways
        
        // Simulated response
        return [
            'reference' => 'UPI' . time(),
            'status' => 'completed'
        ];
    }

    /**
     * Check refund status from payment gateway
     */
    public function checkRefundStatus($payment)
    {
        if ($payment->method === 'razorpay' && $payment->refund_id) {
            return $this->checkRazorpayRefundStatus($payment);
        }

        return [
            'status' => $payment->refund_status,
            'last_updated' => $payment->updated_at
        ];
    }

    private function checkRazorpayRefundStatus($payment)
    {
        try {
            $razorpayKey = config('services.razorpay.key');
            $razorpaySecret = config('services.razorpay.secret');

            $response = Http::withBasicAuth($razorpayKey, $razorpaySecret)
                ->get("https://api.razorpay.com/v1/refunds/{$payment->refund_id}");

            if ($response->successful()) {
                $refundData = $response->json();
                
                // Update payment status based on Razorpay response
                $payment->update([
                    'refund_status' => $refundData['status'] === 'processed' ? 'completed' : 'processing'
                ]);

                return [
                    'status' => $refundData['status'],
                    'gateway_status' => $refundData,
                    'last_updated' => now()
                ];
            }

        } catch (Exception $e) {
            Log::error('Failed to check Razorpay refund status', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }

        return [
            'status' => $payment->refund_status,
            'last_updated' => $payment->updated_at
        ];
    }
}