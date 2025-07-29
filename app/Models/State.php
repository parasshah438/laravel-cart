<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class State extends Model
{   
    protected $fillable = [
        'name',
        'code',
        'country_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
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

    public function scopeForCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
