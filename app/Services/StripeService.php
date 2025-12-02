<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use Stripe\Refund;
use Stripe\Webhook;

class StripeService
{
    private $secretKey;
    private $publishableKey;

    public function __construct()
    {
        $this->secretKey = config('services.stripe.secret_key');
        $this->publishableKey = config('services.stripe.publishable_key');
        
        // Set Stripe API key
        Stripe::setApiKey($this->secretKey);
    }

    /**
     * Create a Payment Intent
     */
    public function createPaymentIntent($amount, $currency = 'inr', $metadata = [])
    {
        try {
            $paymentIntentData = [
                'amount' => $amount * 100, // Amount in paise for INR
                'currency' => $currency,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => $metadata
            ];

            Log::info('Creating Stripe Payment Intent', $paymentIntentData);

            $paymentIntent = PaymentIntent::create($paymentIntentData);

            Log::info('Stripe Payment Intent created successfully', [
                'id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret
            ]);

            return $paymentIntent;

        } catch (Exception $e) {
            Log::error('Exception in creating Stripe Payment Intent', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Retrieve Payment Intent
     */
    public function retrievePaymentIntent($paymentIntentId)
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            
            Log::info('Stripe Payment Intent retrieved', [
                'id' => $paymentIntent->id,
                'status' => $paymentIntent->status
            ]);

            return $paymentIntent;

        } catch (Exception $e) {
            Log::error('Exception in retrieving Stripe Payment Intent', [
                'payment_intent_id' => $paymentIntentId,
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Confirm Payment Intent
     */
    public function confirmPaymentIntent($paymentIntentId, $paymentMethod = null)
    {
        try {
            $params = [];
            if ($paymentMethod) {
                $params['payment_method'] = $paymentMethod;
            }

            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            $paymentIntent->confirm($params);

            Log::info('Stripe Payment Intent confirmed', [
                'id' => $paymentIntent->id,
                'status' => $paymentIntent->status
            ]);

            return $paymentIntent;

        } catch (Exception $e) {
            Log::error('Exception in confirming Stripe Payment Intent', [
                'payment_intent_id' => $paymentIntentId,
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create or retrieve customer
     */
    public function createCustomer($email, $name = null, $metadata = [])
    {
        try {
            // Check if customer already exists
            $existingCustomers = Customer::all(['email' => $email, 'limit' => 1]);
            
            if ($existingCustomers->data && count($existingCustomers->data) > 0) {
                return $existingCustomers->data[0];
            }

            // Create new customer
            $customerData = [
                'email' => $email,
                'metadata' => $metadata
            ];

            if ($name) {
                $customerData['name'] = $name;
            }

            $customer = Customer::create($customerData);

            Log::info('Stripe customer created', [
                'id' => $customer->id,
                'email' => $customer->email
            ]);

            return $customer;

        } catch (Exception $e) {
            Log::error('Exception in creating Stripe customer', [
                'email' => $email,
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Refund payment
     */
    public function refundPayment($paymentIntentId, $amount = null, $reason = null)
    {
        try {
            $refundData = [
                'payment_intent' => $paymentIntentId,
            ];

            if ($amount) {
                $refundData['amount'] = $amount * 100; // Amount in paise
            }

            if ($reason) {
                $refundData['reason'] = $reason;
            }

            $refund = Refund::create($refundData);

            Log::info('Stripe refund created', [
                'id' => $refund->id,
                'amount' => $refund->amount,
                'status' => $refund->status
            ]);

            return $refund;

        } catch (Exception $e) {
            Log::error('Exception in creating Stripe refund', [
                'payment_intent_id' => $paymentIntentId,
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
            $webhookSecret = config('services.stripe.webhook_secret');
            
            $event = Webhook::constructEvent(
                $payload, 
                $signature, 
                $webhookSecret
            );

            return $event;

        } catch (Exception $e) {
            Log::error('Exception in verifying Stripe webhook signature', [
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get Stripe configuration for frontend
     */
    public function getConfig()
    {
        return [
            'publishable_key' => $this->publishableKey,
            'currency' => 'inr',
            'country' => 'IN',
            'name' => config('app.name'),
            'description' => 'Payment for order',
            'image' => asset('images/logo.png'), // Update with your logo path
        ];
    }

    /**
     * Calculate application fee (if using Stripe Connect)
     */
    public function calculateApplicationFee($amount, $feePercentage = 2.9)
    {
        return round(($amount * $feePercentage) / 100, 2);
    }

    /**
     * Get payment methods for a customer
     */
    public function getCustomerPaymentMethods($customerId, $type = 'card')
    {
        try {
            $paymentMethods = \Stripe\PaymentMethod::all([
                'customer' => $customerId,
                'type' => $type,
            ]);

            return $paymentMethods;

        } catch (Exception $e) {
            Log::error('Exception in getting customer payment methods', [
                'customer_id' => $customerId,
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}