<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    private $key;
    private $secret;
    private $baseUrl = 'https://api.razorpay.com/v1/';

    public function __construct()
    {
        $this->key = config('services.razorpay.key');
        $this->secret = config('services.razorpay.secret');
    }

    /**
     * Get HTTP options for requests
     */
    private function getHttpOptions()
    {
        $options = [];
        
        // Skip SSL verification in local development or when explicitly set
        if (app()->environment('local') || config('services.razorpay.skip_ssl_verification', false)) {
            $options['verify'] = false;
        }
        
        return $options;
    }

    /**
     * Create a Razorpay order
     */
    public function createOrder($amount, $currency = 'INR', $receipt = null, $notes = [])
    {
        try {
            $data = [
                'amount' => $amount * 100, // Amount in paise
                'currency' => $currency,
                'receipt' => $receipt ?: 'order_' . time(),
                'notes' => $notes
            ];

            Log::info('Creating Razorpay order', $data);

            $response = Http::withBasicAuth($this->key, $this->secret)
                ->withOptions($this->getHttpOptions())
                ->post($this->baseUrl . 'orders', $data);

            if ($response->successful()) {
                $orderData = $response->json();
                Log::info('Razorpay order created successfully', $orderData);
                return $orderData;
            } else {
                Log::error('Razorpay order creation failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new Exception('Failed to create Razorpay order: ' . $response->body());
            }
        } catch (Exception $e) {
            Log::error('Exception in creating Razorpay order', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Verify payment signature
     */
    public function verifyPaymentSignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature)
    {
        try {
            $generatedSignature = hash_hmac(
                'sha256',
                $razorpayOrderId . '|' . $razorpayPaymentId,
                $this->secret
            );

            $isValid = hash_equals($generatedSignature, $razorpaySignature);
            
            Log::info('Payment signature verification', [
                'order_id' => $razorpayOrderId,
                'payment_id' => $razorpayPaymentId,
                'is_valid' => $isValid
            ]);

            return $isValid;
        } catch (Exception $e) {
            Log::error('Exception in verifying payment signature', [
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Fetch payment details
     */
    public function fetchPayment($paymentId)
    {
        try {
            $response = Http::withBasicAuth($this->key, $this->secret)
                ->withOptions($this->getHttpOptions())
                ->get($this->baseUrl . 'payments/' . $paymentId);

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new Exception('Failed to fetch payment details: ' . $response->body());
            }
        } catch (Exception $e) {
            Log::error('Exception in fetching payment details', [
                'payment_id' => $paymentId,
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Capture payment
     */
    public function capturePayment($paymentId, $amount)
    {
        try {
            $data = [
                'amount' => $amount * 100, // Amount in paise
                'currency' => 'INR'
            ];

            $response = Http::withBasicAuth($this->key, $this->secret)
                ->withOptions($this->getHttpOptions())
                ->post($this->baseUrl . 'payments/' . $paymentId . '/capture', $data);

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new Exception('Failed to capture payment: ' . $response->body());
            }
        } catch (Exception $e) {
            Log::error('Exception in capturing payment', [
                'payment_id' => $paymentId,
                'amount' => $amount,
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Refund payment
     */
    public function refundPayment($paymentId, $amount = null, $notes = [])
    {
        try {
            $data = [
                'notes' => $notes
            ];

            if ($amount) {
                $data['amount'] = $amount * 100; // Amount in paise
            }

            $response = Http::withBasicAuth($this->key, $this->secret)
                ->withOptions($this->getHttpOptions())
                ->post($this->baseUrl . 'payments/' . $paymentId . '/refund', $data);

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new Exception('Failed to refund payment: ' . $response->body());
            }
        } catch (Exception $e) {
            Log::error('Exception in refunding payment', [
                'payment_id' => $paymentId,
                'amount' => $amount,
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature($payload, $signature)
    {
        try {
            $webhookSecret = config('services.razorpay.webhook_secret');
            $generatedSignature = hash_hmac('sha256', $payload, $webhookSecret);
            
            return hash_equals($generatedSignature, $signature);
        } catch (Exception $e) {
            Log::error('Exception in verifying webhook signature', [
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get all orders
     */
    public function getAllOrders($count = 10, $skip = 0)
    {
        try {
            $response = Http::withBasicAuth($this->key, $this->secret)
                ->withOptions($this->getHttpOptions())
                ->get($this->baseUrl . 'orders', [
                    'count' => $count,
                    'skip' => $skip
                ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new Exception('Failed to fetch orders: ' . $response->body());
            }
        } catch (Exception $e) {
            Log::error('Exception in fetching orders', [
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get Razorpay configuration for frontend
     */
    public function getConfig()
    {
        return [
            'key' => $this->key,
            'currency' => 'INR',
            'name' => config('app.name'),
            'description' => 'Payment for order',
            'image' => asset('images/logo.png'), // Update with your logo path
            'theme' => [
                'color' => '#3399cc'
            ]
        ];
    }
}