<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $fillable = ['user_id', 'product_id'];

    // Assuming Wishlist is associated with a Product
    // If you have a product_id in the wishlist table
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    // Assuming Wishlist is associated with a User
    // If you have a user_id in the wishlist table
    // you can define the relationship like this:    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
