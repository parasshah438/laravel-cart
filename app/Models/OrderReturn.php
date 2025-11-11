<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class OrderReturn extends Model
{
    protected $fillable = [
        'order_id', 'return_number', 'return_type', 'status', 'return_reason',
        'return_comments', 'return_items', 'refund_amount', 'refund_method',
        'refund_details', 'refund_status', 'pickup_carrier_id', 'pickup_tracking_number',
        'pickup_scheduled_date', 'pickup_completed_date', 'quality_check_notes',
        'quality_check_images', 'approved_refund_amount', 'processed_by',
        'processed_at', 'admin_notes'
    ];

    protected $casts = [
        'return_items' => 'array',
        'refund_details' => 'array',
        'quality_check_images' => 'array',
        'pickup_scheduled_date' => 'datetime',
        'pickup_completed_date' => 'datetime',
        'processed_at' => 'datetime',
        'refund_amount' => 'decimal:2',
        'approved_refund_amount' => 'decimal:2'
    ];

    /**
     * Generate return number
     */
    public static function generateReturnNumber()
    {
        return 'RTN-' . date('Ymd') . '-' . str_pad(self::count() + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get the order that owns this return
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the admin who processed this return
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'requested' => 'bg-warning text-dark',
            'approved' => 'bg-info text-white',
            'rejected' => 'bg-danger text-white',
            'pickup_scheduled' => 'bg-primary text-white',
            'picked_up' => 'bg-primary text-white',
            'in_transit' => 'bg-primary text-white',
            'received' => 'bg-success text-white',
            'quality_check' => 'bg-warning text-dark',
            'quality_passed' => 'bg-success text-white',
            'quality_failed' => 'bg-danger text-white',
            'refund_initiated' => 'bg-info text-white',
            'refund_completed' => 'bg-success text-white',
            'closed' => 'bg-secondary text-white',
            default => 'bg-secondary text-white'
        };
    }

    /**
     * Get refund status badge class
     */
    public function getRefundStatusBadgeClassAttribute()
    {
        return match($this->refund_status) {
            'pending' => 'bg-warning text-dark',
            'initiated' => 'bg-info text-white',
            'processing' => 'bg-primary text-white',
            'completed' => 'bg-success text-white',
            'failed' => 'bg-danger text-white',
            default => 'bg-secondary text-white'
        };
    }

    /**
     * Get formatted return type
     */
    public function getFormattedReturnTypeAttribute()
    {
        return match($this->return_type) {
            'return' => 'Return & Refund',
            'exchange' => 'Exchange',
            'cancel' => 'Cancellation',
            default => ucfirst($this->return_type)
        };
    }

    /**
     * Get return progress percentage
     */
    public function getProgressPercentageAttribute()
    {
        $statusWeights = [
            'requested' => 10,
            'approved' => 20,
            'pickup_scheduled' => 30,
            'picked_up' => 40,
            'in_transit' => 50,
            'received' => 60,
            'quality_check' => 70,
            'quality_passed' => 80,
            'refund_initiated' => 90,
            'refund_completed' => 100,
            'rejected' => 0,
            'quality_failed' => 0,
            'closed' => 100
        ];

        return $statusWeights[$this->status] ?? 0;
    }

    /**
     * Check if return can be cancelled by customer
     */
    public function canBeCancelledByCustomer()
    {
        return in_array($this->status, ['requested', 'approved']);
    }

    /**
     * Check if return requires admin action
     */
    public function requiresAdminAction()
    {
        return in_array($this->status, ['requested', 'received', 'quality_check']);
    }

    /**
     * Get estimated refund completion date
     */
    public function getEstimatedRefundDateAttribute()
    {
        if ($this->status === 'refund_completed') {
            return null;
        }

        $businessDays = match($this->refund_method) {
            'upi_transfer' => 1,
            'bank_transfer' => 5,
            'store_credit' => 0,
            'cheque' => 10,
            default => 3
        };

        return $this->pickup_completed_date 
            ? $this->pickup_completed_date->addWeekdays($businessDays)
            : now()->addWeekdays($businessDays);
    }

    /**
     * Scope for pending returns requiring admin action
     */
    public function scopePendingAdminAction($query)
    {
        return $query->whereIn('status', ['requested', 'received', 'quality_check']);
    }

    /**
     * Scope for active returns
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['rejected', 'closed', 'refund_completed']);
    }
}