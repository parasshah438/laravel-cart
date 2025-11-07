<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    protected $fillable = [
        'carrier_id', 'name', 'code', 'description', 'delivery_time',
        'is_active', 'base_cost', 'per_km_cost', 'weight_based_pricing',
        'zone_based_pricing', 'settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'weight_based_pricing' => 'array',
        'zone_based_pricing' => 'array',
        'settings' => 'array',
        'base_cost' => 'decimal:2',
        'per_km_cost' => 'decimal:2'
    ];

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(ShippingCarrier::class, 'carrier_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'shipping_method_id');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(OrderShipment::class, 'shipping_method_id');
    }

    public function calculateCost($weight, $distance, $zone = null)
    {
        $cost = $this->base_cost + ($distance * $this->per_km_cost);
        
        // Apply weight-based pricing
        if ($this->weight_based_pricing && is_array($this->weight_based_pricing)) {
            foreach ($this->weight_based_pricing as $tier) {
                if ($weight >= $tier['min_weight'] && $weight <= $tier['max_weight']) {
                    $cost += $tier['additional_cost'];
                    break;
                }
            }
        }

        // Apply zone-based pricing
        if ($zone && $this->zone_based_pricing && is_array($this->zone_based_pricing)) {
            $zonePricing = collect($this->zone_based_pricing)->firstWhere('zone', $zone);
            if ($zonePricing) {
                $cost += $zonePricing['additional_cost'] ?? 0;
            }
        }

        return $cost;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFullNameAttribute()
    {
        return $this->carrier->name . ' - ' . $this->name;
    }
}
