<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'payment_id',
        'gateway',
        'gateway_payment_id',
        'gateway_order_id',
        'transaction_id',
        'status',
        'amount',
        'currency',
        'method',
        'payment_method',
        'payment_status',
        'gateway_response',
        'metadata',
        'failure_reason',
        'receipt_number',
        'paid_at',
        'failed_at',
        'cancelled_at',
        'refunded_at',
        'ip_address',
        'user_agent',
        'billing_details',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'metadata' => 'array',
        'billing_details' => 'array',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Boot method to auto-generate payment ID
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($payment) {
            if (empty($payment->payment_id)) {
                $payment->payment_id = 'PAY_' . strtoupper(Str::random(16));
            }
        });
    }

    /**
     * Get the order that owns this payment
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user that owns this payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this->payment_status === 'paid' && $this->status === 'completed';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->payment_status === 'pending' || $this->status === 'pending';
    }

    /**
     * Check if payment failed
     */
    public function isFailed(): bool
    {
        return $this->payment_status === 'failed' || $this->status === 'failed';
    }

    /**
     * Check if payment is refunded
     */
    public function isRefunded(): bool
    {
        return $this->payment_status === 'refunded' || $this->status === 'refunded';
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted($gatewayResponse = null): void
    {
        $this->update([
            'status' => 'completed',
            'payment_status' => 'paid',
            'paid_at' => now(),
            'gateway_response' => $gatewayResponse,
        ]);
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed($reason = null, $gatewayResponse = null): void
    {
        $this->update([
            'status' => 'failed',
            'payment_status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
            'gateway_response' => $gatewayResponse,
        ]);
    }

    /**
     * Mark payment as refunded
     */
    public function markAsRefunded($gatewayResponse = null): void
    {
        $this->update([
            'status' => 'refunded',
            'payment_status' => 'refunded',
            'refunded_at' => now(),
            'gateway_response' => $gatewayResponse,
        ]);
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->payment_status) {
            'paid' => 'bg-success text-white',
            'pending' => 'bg-warning text-dark',
            'failed' => 'bg-danger text-white',
            'refunded' => 'bg-info text-white',
            'cancelled' => 'bg-secondary text-white',
            default => 'bg-secondary text-white',
        };
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return '₹' . number_format($this->amount, 2);
    }

    /**
     * Get gateway display name
     */
    public function getGatewayDisplayNameAttribute(): string
    {
        return match($this->gateway) {
            'razorpay' => 'Razorpay',
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            'paytm' => 'Paytm',
            'cod' => 'Cash on Delivery',
            default => ucfirst($this->gateway),
        };
    }

    /**
     * Get method display name
     */
    public function getMethodDisplayNameAttribute(): string
    {
        return match($this->method) {
            'card' => 'Credit/Debit Card',
            'upi' => 'UPI',
            'netbanking' => 'Net Banking',
            'wallet' => 'Digital Wallet',
            'emi' => 'EMI',
            'cod' => 'Cash on Delivery',
            default => ucfirst($this->method ?? 'Unknown'),
        };
    }

    /**
     * Scope for successful payments
     */
    public function scopeSuccessful($query)
    {
        return $query->where('payment_status', 'paid')->where('status', 'completed');
    }

    /**
     * Scope for failed payments
     */
    public function scopeFailed($query)
    {
        return $query->where('payment_status', 'failed');
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope for refunded payments
     */
    public function scopeRefunded($query)
    {
        return $query->where('payment_status', 'refunded');
    }

    /**
     * Scope for specific gateway
     */
    public function scopeGateway($query, $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
