<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\NotificationService;

class OrderShipment extends Model
{
    protected $fillable = [
        'order_id', 'shipment_number', 'carrier_id', 'shipping_method_id',
        'tracking_number', 'shiprocket_order_id', 'shiprocket_shipment_id',
        'status', 'shipped_at', 'estimated_delivery', 'delivered_at',
        'shipped_from_address', 'shipped_to_address', 'package_weight',
        'package_dimensions', 'shipping_cost', 'insurance_cost',
        'cod_amount', 'notes', 'metadata'
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'estimated_delivery' => 'datetime',
        'delivered_at' => 'datetime',
        'shipped_from_address' => 'array',
        'shipped_to_address' => 'array',
        'package_dimensions' => 'array',
        'notes' => 'array',
        'metadata' => 'array',
        'package_weight' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'insurance_cost' => 'decimal:2',
        'cod_amount' => 'decimal:2'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(ShippingCarrier::class, 'carrier_id');
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(ShippingTrackingEvent::class, 'shipment_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class, 'shipment_id');
    }

    public function getTrackingUrlAttribute()
    {
        return $this->carrier?->getTrackingUrl($this->tracking_number);
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-warning text-dark',
            'picked_up' => 'bg-info text-white',
            'in_transit' => 'bg-primary text-white',
            'out_for_delivery' => 'bg-success text-white',
            'delivered' => 'bg-success text-white',
            'exception' => 'bg-danger text-white',
            'returned' => 'bg-secondary text-white',
            default => 'bg-secondary text-white'
        };
    }

    public function updateStatus($status, $description = null, $location = null, $eventTime = null)
    {
        $oldStatus = $this->status;
        
        $this->update([
            'status' => $status,
            'shipped_at' => ($status === 'picked_up' && !$this->shipped_at) ? now() : $this->shipped_at,
            'delivered_at' => ($status === 'delivered' && !$this->delivered_at) ? now() : $this->delivered_at,
        ]);
        
        // Create tracking event
        $this->trackingEvents()->create([
            'status' => $status,
            'description' => $description,
            'location' => $location,
            'event_time' => $eventTime ?? now(),
            'is_delivered' => $status === 'delivered',
            'is_exception' => $status === 'exception'
        ]);

        // Update order status based on shipment status
        $this->updateOrderStatus($oldStatus, $status);

        // Send notifications
        $this->sendStatusNotification($status);
    }

    private function updateOrderStatus($oldStatus, $newStatus)
    {
        $order = $this->order;
        
        switch ($newStatus) {
            case 'picked_up':
                if ($order->status !== 'shipped') {
                    $order->update(['status' => 'shipped']);
                }
                break;
            case 'delivered':
                $order->update(['status' => 'delivered']);
                break;
            case 'exception':
                // Keep order status as is, but notify admin
                break;
            case 'returned':
                $order->update(['status' => 'returned']);
                break;
        }
    }

    private function sendStatusNotification($status)
    {
        // Send customer notification based on status
        try {
            if (class_exists(NotificationService::class)) {
                app(NotificationService::class)->sendShippingUpdate($this, $status);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send shipping notification: ' . $e->getMessage());
        }
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeInTransit($query)
    {
        return $query->whereIn('status', ['picked_up', 'in_transit', 'out_for_delivery']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function generateShipmentNumber()
    {
        return 'SHP' . date('Ymd') . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getStatusColor()
    {
        return match($this->status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'picked_up' => 'primary',
            'in_transit' => 'primary',
            'out_for_delivery' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            'returned' => 'secondary',
            'exception' => 'danger',
            default => 'secondary'
        };
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($shipment) {
            if (!$shipment->shipment_number) {
                $shipment->shipment_number = 'SHP' . date('Ymd') . rand(100000, 999999);
            }
        });
    }
}
