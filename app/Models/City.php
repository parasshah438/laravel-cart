<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name',
        'state_id',
        'country_id',
        'postal_code_pattern',
        'is_major',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_major' => 'boolean',
    ];

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function postalCodes()
    {
        return $this->hasMany(PostalCode::class);
    }

    public function userAddresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMajor($query)
    {
        return $query->where('is_major', true);
    }

    public function scopeForState($query, $stateId)
    {
        return $query->where('state_id', $stateId);
    }

    public function scopeForCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }
}
