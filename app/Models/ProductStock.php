<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    protected $fillable = ['product_id', 'sku', 'variant', 'price', 'qty', 'image','status'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function isInStock(int $requiredQty = 1): bool
    {
        return $this->qty >= $requiredQty;
    }
}
