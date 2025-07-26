<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'slug', // Ensure slug is fillable for mass assignment
        'status', // active, inactive, out_of_stock
    ];

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function media()
    {
        return $this->hasMany(ProductMedia::class)->orderBy('sort_order');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function views()
    {
        return $this->hasMany(RecentlyViewedProduct::class);
    }
}
