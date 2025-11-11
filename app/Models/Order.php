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
        'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature',
        // Shipping fields
        'shipping_carrier_id', 'shipping_method_id', 'estimated_delivery_date',
        'package_weight', 'package_dimensions', 'requires_signature', 'delivery_notes'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'delivery_date' => 'datetime',
        'estimated_delivery_date' => 'datetime',
        'notes' => 'array', // Cast notes to array for JSON data
        'package_dimensions' => 'array',
        'requires_signature' => 'boolean',
        'package_weight' => 'decimal:2'
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
     * Get the shipping carrier for this order
     */
    public function shippingCarrier()
    {
        return $this->belongsTo(ShippingCarrier::class, 'shipping_carrier_id');
    }

    /**
     * Get the shipping method for this order
     */
    public function shippingMethodRelation()
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    /**
     * Get all shipments for this order
     */
    public function shipments()
    {
        return $this->hasMany(OrderShipment::class);
    }

    /**
     * Get the latest shipment for this order
     */
    public function latestShipment()
    {
        return $this->hasOne(OrderShipment::class)->latest();
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
     * Get comprehensive tracking steps - combines shipment tracking with order status
     */
    public function getTrackingSteps()
    {
        $shipment = $this->latestShipment;
        
        // If we have a shipment with tracking events, use detailed tracking
        if ($shipment && $shipment->trackingEvents()->exists()) {
            return $this->getDetailedTrackingSteps($shipment);
        }
        
        // Otherwise, use basic order status tracking
        return $this->getBasicTrackingSteps();
    }

    /**
     * Get detailed tracking steps from shipment tracking events
     */
    private function getDetailedTrackingSteps($shipment)
    {
        $steps = [];
        $trackingEvents = $shipment->trackingEvents()->orderBy('event_time')->get();
        
        foreach ($trackingEvents as $index => $event) {
            $steps[] = [
                'title' => $this->formatTrackingTitle($event->status),
                'description' => $event->description,
                'location' => $event->location,
                'icon' => $this->getTrackingIcon($event->status),
                'completed' => true,
                'date' => $event->event_time,
                'is_current' => $index === $trackingEvents->count() - 1, // Last event is current
                'class' => $this->getTrackingClass($event->status)
            ];
        }

        return $steps;
    }

    /**
     * Get basic tracking steps based on order status
     */
    private function getBasicTrackingSteps()
    {
        $steps = [
            [
                'title' => 'Order Placed',
                'description' => 'Your order has been placed successfully',
                'icon' => 'fas fa-shopping-cart',
                'completed' => true,
                'date' => $this->created_at,
                'is_current' => $this->status === 'pending'
            ],
            [
                'title' => 'Order Confirmed',
                'description' => 'Your order has been confirmed and is being prepared',
                'icon' => 'fas fa-check-circle',
                'completed' => in_array($this->status, ['confirmed', 'shipped', 'delivered']),
                'date' => in_array($this->status, ['confirmed', 'shipped', 'delivered']) ? $this->updated_at : null,
                'is_current' => $this->status === 'confirmed'
            ],
            [
                'title' => 'Shipped',
                'description' => 'Your order is on the way',
                'icon' => 'fas fa-truck',
                'completed' => in_array($this->status, ['shipped', 'delivered']),
                'date' => in_array($this->status, ['shipped', 'delivered']) ? $this->updated_at : null,
                'is_current' => $this->status === 'shipped'
            ],
            [
                'title' => 'Delivered',
                'description' => 'Your order has been delivered successfully',
                'icon' => 'fas fa-home',
                'completed' => $this->status === 'delivered',
                'date' => $this->status === 'delivered' ? $this->updated_at : null,
                'is_current' => $this->status === 'delivered'
            ]
        ];

        // Handle cancelled orders
        if ($this->status === 'cancelled') {
            $steps[] = [
                'title' => 'Order Cancelled',
                'description' => 'Your order has been cancelled',
                'icon' => 'fas fa-times-circle',
                'completed' => true,
                'date' => $this->updated_at,
                'is_current' => true,
                'class' => 'text-danger'
            ];
        }

        return $steps;
    }

    /**
     * Format tracking status into readable title
     */
    private function formatTrackingTitle($status)
    {
        return match($status) {
            'pending' => 'Order Confirmed',
            'picked_up' => 'Package Picked Up',
            'in_transit' => 'In Transit',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'exception' => 'Delivery Exception',
            'returned' => 'Returned to Sender',
            default => ucfirst(str_replace('_', ' ', $status))
        };
    }

    /**
     * Get icon for tracking status
     */
    private function getTrackingIcon($status)
    {
        return match($status) {
            'pending' => 'fas fa-clock',
            'picked_up' => 'fas fa-hand-paper',
            'in_transit' => 'fas fa-truck',
            'out_for_delivery' => 'fas fa-shipping-fast',
            'delivered' => 'fas fa-check-circle',
            'exception' => 'fas fa-exclamation-triangle',
            'returned' => 'fas fa-undo',
            default => 'fas fa-circle'
        };
    }

    /**
     * Get CSS class for tracking status
     */
    private function getTrackingClass($status)
    {
        return match($status) {
            'delivered' => 'text-success',
            'exception' => 'text-danger',
            'returned' => 'text-warning',
            default => 'text-primary'
        };
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

    /**
     * Calculate shipping cost based on shipping method
     */
    public function calculateShippingCost()
    {
        if (!$this->shippingMethodRelation || !$this->address) {
            return 0;
        }

        $totalWeight = $this->calculateTotalWeight();
        $distance = $this->calculateDeliveryDistance();
        
        return $this->shippingMethodRelation->calculateCost($totalWeight, $distance);
    }

    /**
     * Calculate total weight of order items
     */
    public function calculateTotalWeight()
    {
        return $this->items->sum(function ($item) {
            $productWeight = $item->product->weight ?? 0.5; // Default weight if not set
            return $productWeight * $item->quantity;
        });
    }

    /**
     * Calculate delivery distance (placeholder - implement with actual distance calculation)
     */
    private function calculateDeliveryDistance()
    {
        // This could use Google Maps API or a similar service
        // For now, return a default distance
        return 10; // 10km default
    }



    /**
     * Check if order has a shipment
     */
    public function hasShipment()
    {
        return $this->shipments()->exists();
    }

    /**
     * Get current shipping status
     */
    public function getShippingStatusAttribute()
    {
        $shipment = $this->latestShipment;
        return $shipment ? $shipment->status : 'pending';
    }

    /**
     * Check if order is ready to create shipment
     */
    public function canCreateShipment()
    {
        // For COD orders, payment is collected on delivery, so allow shipment creation when confirmed
        $paymentCondition = ($this->payment_method === 'cod') 
            ? $this->payment_status === 'unpaid'  // COD should be unpaid until delivery
            : $this->payment_status === 'paid';   // Other methods should be paid
            
        return $this->status === 'confirmed' && 
               $paymentCondition && 
               !$this->hasShipment();
    }

    // ================================================================================================
    // 🎯 PROFESSIONAL STATUS MANAGEMENT (Amazon/Flipkart Style)
    // ================================================================================================

    /**
     * Get available status transitions for current order status
     * Professional e-commerce flow: pending → confirmed → shipped → delivered
     */
    public function getAvailableStatusTransitions()
    {
        $currentStatus = $this->status;
        
        return match($currentStatus) {
            'pending' => [
                'confirmed' => 'Confirmed',
                'cancelled' => 'Cancelled'
            ],
            'confirmed', 'processing' => [
                'shipped' => 'Shipped',
                'cancelled' => 'Cancelled (with conditions)'
            ],
            'shipped' => [
                'delivered' => 'Delivered'
            ],
            'delivered', 'cancelled' => [
                // Final statuses - no transitions allowed
            ],
            default => [
                'confirmed' => 'Confirmed',
                'cancelled' => 'Cancelled'
            ]
        };
    }

    /**
     * Check if status can be changed
     */
    public function canChangeStatus()
    {
        return !in_array($this->status, ['delivered', 'cancelled']);
    }

    /**
     * Check if status transition is valid
     */
    public function canTransitionTo($newStatus)
    {
        $availableTransitions = $this->getAvailableStatusTransitions();
        return array_key_exists($newStatus, $availableTransitions);
    }

    /**
     * Get status transition message for admin
     */
    public function getStatusTransitionMessage($newStatus)
    {
        $messages = [
            'confirmed' => 'Order will be marked as confirmed and ready for processing',
            'shipped' => 'Order will be marked as shipped and customer will be notified',
            'delivered' => 'Order will be marked as delivered and completed',
            'cancelled' => 'Order will be cancelled and cannot be processed further'
        ];

        return $messages[$newStatus] ?? 'Status will be updated';
    }

    /**
     * Professional status badge classes (like Amazon)
     */
    public function getStatusBadgeClassProfessional()
    {
        return match($this->status) {
            'pending' => 'bg-warning text-dark',
            'confirmed', 'processing' => 'bg-info text-white',
            'shipped' => 'bg-primary text-white',
            'delivered' => 'bg-success text-white',
            'cancelled' => 'bg-danger text-white',
            default => 'bg-secondary text-white'
        };
    }
}
