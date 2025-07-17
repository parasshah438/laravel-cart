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

    public function isLowStock(int $threshold = 3): bool
    {
        return $this->qty > 0 && $this->qty <= $threshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->qty <= 0;
    }
}
