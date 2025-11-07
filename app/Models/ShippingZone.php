<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    protected $fillable = [
        'name', 
        'description', 
        'is_active',
        'base_rate',
        'per_kg_rate',
        'free_shipping_threshold',
        'settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'base_rate' => 'decimal:2',
        'per_kg_rate' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'settings' => 'array'
    ];

    public function locations(): HasMany
    {
        return $this->hasMany(ShippingZoneLocation::class, 'zone_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function calculateShippingRate($weight, $orderValue = 0)
    {
        // Free shipping threshold check
        if ($orderValue >= $this->free_shipping_threshold && $this->free_shipping_threshold > 0) {
            return 0.00;
        }

        // Calculate base rate + weight-based rate
        return $this->base_rate + ($weight * $this->per_kg_rate);
    }

    public function coversLocation($countryId = null, $stateId = null, $cityId = null, $postalCode = null)
    {
        return $this->locations()
            ->where('is_active', true)
            ->where(function ($query) use ($countryId, $stateId, $cityId, $postalCode) {
                // Check for exact matches
                $query->where(function ($q) use ($countryId, $stateId, $cityId, $postalCode) {
                    if ($countryId) $q->where('country_id', $countryId);
                    if ($stateId) $q->where('state_id', $stateId);
                    if ($cityId) $q->where('city_id', $cityId);
                    if ($postalCode) $q->where('postal_code', $postalCode);
                })
                // Or check postal code ranges
                ->orWhere(function ($q) use ($postalCode) {
                    if ($postalCode) {
                        $q->where('postal_code_range_start', '<=', $postalCode)
                          ->where('postal_code_range_end', '>=', $postalCode);
                    }
                });
            })
            ->exists();
    }

    public function getLocationSpecificRate($countryId = null, $stateId = null, $cityId = null, $postalCode = null)
    {
        $location = $this->locations()
            ->where('is_active', true)
            ->where(function ($query) use ($countryId, $stateId, $cityId, $postalCode) {
                if ($countryId) $query->where('country_id', $countryId);
                if ($stateId) $query->where('state_id', $stateId);
                if ($cityId) $query->where('city_id', $cityId);
                if ($postalCode) $query->where('postal_code', $postalCode);
            })
            ->first();

        return $location ? $location->additional_rate : 0.00;
    }
}
