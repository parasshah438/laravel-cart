<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'address_id', 'order_number', 'total', 'discount', 'shipping_cost', 'grand_total', 
        'delivery_date', 'shipping_method', 'time_slot', 'delivery_instructions',
        'coupon_code', 'coupon_title', 'coupon_discount',
        'status', 'payment_method', 'payment_status', 'notes',
        // Razorpay payment fields
        'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'delivery_date' => 'datetime',
        'notes' => 'array', // Cast notes to array for JSON data
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the payments for this order
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the latest payment for this order
     */
    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latest();
    }

    /**
     * Get the status badge class for display
     */
    public function getStatusBadgeClassAttribute()
    {
        return [
            'pending' => 'bg-warning text-dark',
            'confirmed' => 'bg-info text-white',
            'shipped' => 'bg-primary text-white',
            'delivered' => 'bg-success text-white',
            'cancelled' => 'bg-danger text-white',
        ][$this->status] ?? 'bg-secondary text-white';
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Check if order can be reordered
     */
    public function canBeReordered()
    {
        return $this->status === 'delivered';
    }

    /**
     * Get estimated delivery date
     */
    public function getEstimatedDeliveryAttribute()
    {
        return $this->created_at->addDays(5);
    }

    /**
     * Get tracking steps based on current status
     */
    public function getTrackingSteps()
    {
        $steps = [
            'pending' => [
                'title' => 'Order Placed',
                'description' => 'Your order has been placed successfully',
                'icon' => 'fas fa-shopping-cart',
                'completed' => true,
                'date' => $this->created_at
            ],
            'confirmed' => [
                'title' => 'Order Confirmed',
                'description' => 'Your order has been confirmed and is being prepared',
                'icon' => 'fas fa-check-circle',
                'completed' => in_array($this->status, ['confirmed', 'shipped', 'delivered']),
                'date' => in_array($this->status, ['confirmed', 'shipped', 'delivered']) ? $this->updated_at : null
            ],
            'shipped' => [
                'title' => 'Shipped',
                'description' => 'Your order is on the way',
                'icon' => 'fas fa-truck',
                'completed' => in_array($this->status, ['shipped', 'delivered']),
                'date' => in_array($this->status, ['shipped', 'delivered']) ? $this->updated_at : null
            ],
            'delivered' => [
                'title' => 'Delivered',
                'description' => 'Your order has been delivered successfully',
                'icon' => 'fas fa-home',
                'completed' => $this->status === 'delivered',
                'date' => $this->status === 'delivered' ? $this->updated_at : null
            ]
        ];

        // Handle cancelled orders
        if ($this->status === 'cancelled') {
            $steps['cancelled'] = [
                'title' => 'Order Cancelled',
                'description' => 'Your order has been cancelled',
                'icon' => 'fas fa-times-circle',
                'completed' => true,
                'date' => $this->updated_at,
                'class' => 'text-danger'
            ];
        }

        return $steps;
    }

    // ================================================================================================
    // 💳 PAYMENT RELATED METHODS
    // ================================================================================================

    /**
     * Check if order uses Razorpay payment
     */
    public function isRazorpayPayment(): bool
    {
        return $this->payment_method === 'razorpay';
    }

    /**
     * Check if payment is completed/paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if payment is pending
     */
    public function isPaymentPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    /**
     * Check if payment failed
     */
    public function isPaymentFailed(): bool
    {
        return $this->payment_status === 'failed';
    }

    /**
     * Get payment method display name
     */
    public function getPaymentMethodDisplayAttribute(): string
    {
        return match($this->payment_method) {
            'cod' => 'Cash on Delivery',
            'razorpay' => 'Online Payment (Razorpay)',
            'stripe' => 'Online Payment (Stripe)',
            'paypal' => 'PayPal',
            default => ucfirst($this->payment_method ?? 'Unknown'),
        };
    }

    /**
     * Get payment status badge class
     */
    public function getPaymentStatusBadgeClassAttribute(): string
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

    // ================================================================================================
    // 💳 RAZORPAY PAYMENT HELPER METHODS
    // ================================================================================================
    /**
     * Check if order is COD
     */
    public function isCOD(): bool
    {
        return $this->payment_method === 'cod';
    }

    /**
     * Check if order can be refunded
     */
    public function canBeRefunded(): bool
    {
        return $this->isRazorpayPayment() && 
               $this->isPaid() && 
               in_array($this->status, ['confirmed', 'shipped']) &&
               $this->created_at->diffInDays(now()) <= 30; // 30 days refund policy
    }

    /**
     * Get Razorpay payment details from notes
     */
    public function getRazorpayPaymentDetails()
    {
        if (!$this->isRazorpayPayment() || !is_array($this->notes)) {
            return null;
        }

        return $this->notes['razorpay_payment_data'] ?? null;
    }

    /**
     * Get payment failure reason
     */
    public function getPaymentFailureReason()
    {
        if (!$this->isPaymentFailed() || !is_array($this->notes)) {
            return null;
        }

        return $this->notes['payment_failure_reason'] ?? 'Unknown payment failure';
    }

    /**
     * Scope to filter by payment method
     */
    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope to filter by payment status
     */
    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope to get paid orders
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope to get pending payment orders
     */
    public function scopePaymentPending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope to get failed payment orders
     */
    public function scopePaymentFailed($query)
    {
        return $query->where('payment_status', 'failed');
    }

    /**
     * Scope to get Razorpay orders
     */
    public function scopeRazorpay($query)
    {
        return $query->where('payment_method', 'razorpay');
    }

    /**
     * Scope to get COD orders
     */
    public function scopeCOD($query)
    {
        return $query->where('payment_method', 'cod');
    }
}
