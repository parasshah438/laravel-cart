<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_name', 'price', 'quantity', 'total',
        'item_status', 'cancellation_reason', 'cancelled_at',
    ];

    protected $casts = [
        'cancelled_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Can this specific item be cancelled by the customer?
     * Requires effective status to be pending/confirmed AND product to allow returns.
     */
    public function canBeCancelled(): bool
    {
        $statusAllowed = in_array($this->effective_status, ['pending', 'confirmed']);
        $productAllows = $this->product ? (bool) $this->product->is_return : true;

        return $statusAllowed && $productAllows;
    }

    /**
     * Can this specific item be returned by the customer?
     * Requires effective status to be delivered AND product to allow returns.
     */
    public function canBeReturned(): bool
    {
        return $this->effective_status === 'delivered'
            && $this->product
            && (bool) $this->product->is_return;
    }

    /**
     * Effective item status — falls back to the parent order's status for
     * existing rows that were created before per-item tracking was added.
     */
    public function getEffectiveStatusAttribute(): string
    {
        $status = $this->item_status ?? 'pending';

        // If still at default and the parent order is loaded + advanced, mirror it
        if ($status === 'pending' && $this->relationLoaded('order') && $this->order) {
            $orderStatus = $this->order->status;
            $map = ['confirmed' => 'confirmed', 'shipped' => 'shipped',
                    'delivered' => 'delivered',  'cancelled' => 'cancelled'];
            if (isset($map[$orderStatus])) {
                return $map[$orderStatus];
            }
        }

        return $status;
    }

    /**
     * Human-readable label for item_status.
     */
    public function getItemStatusLabelAttribute(): string
    {
        return match ($this->effective_status) {
            'pending'          => 'Pending',
            'confirmed'        => 'Confirmed',
            'shipped'          => 'Shipped',
            'delivered'        => 'Delivered',
            'cancelled'        => 'Cancelled',
            'return_requested' => 'Return Requested',
            'returned'         => 'Returned',
            default            => ucfirst($this->effective_status),
        };
    }

    /**
     * Bootstrap colour class for item_status badge.
     */
    public function getItemStatusBadgeClassAttribute(): string
    {
        return match ($this->effective_status) {
            'pending'          => 'bg-warning text-dark',
            'confirmed'        => 'bg-info text-white',
            'shipped'          => 'bg-primary text-white',
            'delivered'        => 'bg-success text-white',
            'cancelled'        => 'bg-danger text-white',
            'return_requested' => 'bg-warning text-dark',
            'returned'         => 'bg-secondary text-white',
            default            => 'bg-secondary text-white',
        };
    }
}
