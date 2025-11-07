<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingTrackingEvent extends Model
{
    protected $fillable = [
        'shipment_id', 'status', 'description', 'location',
        'event_time', 'is_delivered', 'is_exception', 'metadata'
    ];

    protected $casts = [
        'event_time' => 'datetime',
        'is_delivered' => 'boolean',
        'is_exception' => 'boolean',
        'metadata' => 'array'
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(OrderShipment::class, 'shipment_id');
    }

    public function getFormattedStatusAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getStatusIconAttribute()
    {
        return match($this->status) {
            'pending' => 'fas fa-clock',
            'picked_up' => 'fas fa-box',
            'in_transit' => 'fas fa-truck',
            'out_for_delivery' => 'fas fa-shipping-fast',
            'delivered' => 'fas fa-check-circle',
            'exception' => 'fas fa-exclamation-triangle',
            'returned' => 'fas fa-undo',
            default => 'fas fa-info-circle'
        };
    }

    public function getStatusIcon()
    {
        return $this->getStatusIconAttribute();
    }

    public function getStatusColor()
    {
        return match($this->status) {
            'pending' => 'warning',
            'picked_up' => 'info',
            'in_transit' => 'primary',
            'out_for_delivery' => 'primary',
            'delivered' => 'success',
            'exception' => 'danger',
            'returned' => 'secondary',
            default => 'secondary'
        };
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('event_time', 'desc');
    }

    public function scopeDelivered($query)
    {
        return $query->where('is_delivered', true);
    }

    public function scopeExceptions($query)
    {
        return $query->where('is_exception', true);
    }
}
