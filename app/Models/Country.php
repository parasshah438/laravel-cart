<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\State;
use App\Models\City;
use App\Models\PostalCode;
use App\Models\UserAddress;

class Country extends Model
{
    protected $fillable = [
        'name',
        'code',
        'iso3',
        'phone_code',
        'currency',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function states()
    {
        return $this->hasMany(State::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function userAddresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function postalCodes()
    {
        return $this->hasMany(PostalCode::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
