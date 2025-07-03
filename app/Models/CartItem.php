<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'quantity', 'price_at_time', 'options'];
    protected $casts = ['options' => 'array'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
