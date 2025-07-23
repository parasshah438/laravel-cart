<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'is_active',
        'min_cart_value',
        'starts_at',
        'expires_at',
    ];
    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    //Scope to get active coupons
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    //Check if coupon is currently valid
    public function isValid(float $cartTotal): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        if ($this->min_cart_value && $cartTotal < $this->min_cart_value) {
            return false;
        }
        return true;
    }

    //Calculate discounted amount
    public function calculateDiscount(float $cartTotal): float
    {
        if (!$this->isValid($cartTotal)) {
            return 0;
        }

        if ($this->type === 'percent') {
            return round($cartTotal * ($this->discount / 100), 2);
        }

        //Fixed discount
        return min($this->discount, $cartTotal); //can't exceed cart total
    }
}
