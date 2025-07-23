<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'session_id','applied_coupon_id'];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function appliedCoupon()
    {
        return $this->belongsTo(Coupon::class, 'applied_coupon_id');
    }
}
