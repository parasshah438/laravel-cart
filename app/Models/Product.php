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
        'status', // active, inactive, out_of_stock
    ];

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }
    
}
