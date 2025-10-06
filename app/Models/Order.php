<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'address_id', 'order_number', 'total', 'discount', 'grand_total', 
        'coupon_code', 'coupon_title', 'coupon_discount',
        'status', 'payment_method', 'payment_status', 'notes'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
}
