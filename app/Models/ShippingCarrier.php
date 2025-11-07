<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingCarrier extends Model
{
    protected $fillable = [
        'name', 'code', 'api_endpoint', 'api_key', 'api_secret',
        'tracking_url_template', 'is_active', 'supports_cod',
        'supports_express', 'base_rate', 'per_kg_rate',
        'free_shipping_threshold', 'settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'supports_cod' => 'boolean',
        'supports_express' => 'boolean',
        'settings' => 'array',
        'base_rate' => 'decimal:2',
        'per_kg_rate' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2'
    ];

    public function shippingMethods(): HasMany
    {
        return $this->hasMany(ShippingMethod::class, 'carrier_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'shipping_carrier_id');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(OrderShipment::class, 'carrier_id');
    }

    public function calculateRate($weight, $distance = 0)
    {
        return $this->base_rate + ($weight * $this->per_kg_rate);
    }

    public function getTrackingUrl($trackingNumber)
    {
        return str_replace('{tracking_number}', $trackingNumber, $this->tracking_url_template ?? '');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSupportsCod($query)
    {
        return $query->where('supports_cod', true);
    }

    public function scopeSupportsExpress($query)
    {
        return $query->where('supports_express', true);
    }
}
