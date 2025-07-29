<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'is_default',
        'label',
        'full_name',
        'phone_number',
        'alternate_phone',
        'address_line_1',
        'address_line_2',
        'landmark',
        'state_id',
        'city_id',
        'postal_code',
        'country_id',
        'gst_number',
        'is_default_shipping',
        'is_default_billing',
        'latitude',
        'longitude',
        'is_active',
        'delivery_instructions',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_default_shipping' => 'boolean',
        'is_default_billing' => 'boolean',
        'is_active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function scopeDefaultShipping($query)
    {
        return $query->where('is_default_shipping', true);
    }

    public function scopeDefaultBilling($query)
    {
        return $query->where('is_default_billing', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeWithUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWithLabel($query, $label)
    {
        return $query->where('label', $label);
    }

    public function scopeWithPostalCode($query, $postalCode)
    {
        return $query->where('postal_code', $postalCode);
    }
}
