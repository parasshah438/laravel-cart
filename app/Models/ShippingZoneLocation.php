<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingZoneLocation extends Model
{
    protected $fillable = [
        'zone_id',
        'country_id',
        'state_id', 
        'city_id',
        'postal_code',
        'postal_code_range_start',
        'postal_code_range_end',
        'additional_rate',
        'is_active'
    ];

    protected $casts = [
        'additional_rate' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'zone_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function matchesLocation($countryId = null, $stateId = null, $cityId = null, $postalCode = null)
    {
        // Check country match
        if ($this->country_id && $countryId && $this->country_id != $countryId) {
            return false;
        }

        // Check state match
        if ($this->state_id && $stateId && $this->state_id != $stateId) {
            return false;
        }

        // Check city match
        if ($this->city_id && $cityId && $this->city_id != $cityId) {
            return false;
        }

        // Check postal code match
        if ($postalCode) {
            // Exact postal code match
            if ($this->postal_code && $this->postal_code == $postalCode) {
                return true;
            }

            // Postal code range match
            if ($this->postal_code_range_start && $this->postal_code_range_end) {
                return $postalCode >= $this->postal_code_range_start && 
                       $postalCode <= $this->postal_code_range_end;
            }
        }

        // If no postal code restrictions, consider it a match for broader areas
        return !$this->postal_code && !$this->postal_code_range_start;
    }

    public function getLocationNameAttribute()
    {
        $parts = [];

        if ($this->city) {
            $parts[] = $this->city->name;
        }

        if ($this->state) {
            $parts[] = $this->state->name;
        }

        if ($this->country) {
            $parts[] = $this->country->name;
        }

        if ($this->postal_code) {
            $parts[] = $this->postal_code;
        } elseif ($this->postal_code_range_start && $this->postal_code_range_end) {
            $parts[] = $this->postal_code_range_start . '-' . $this->postal_code_range_end;
        }

        return implode(', ', $parts) ?: 'All Locations';
    }
}
