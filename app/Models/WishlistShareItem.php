<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WishlistShareItem extends Model
{
    protected $fillable = [
        'wishlist_share_id',
        'product_id',
        'added_at'
    ];

    protected $casts = [
        'added_at' => 'datetime'
    ];

    /**
     * Get the shared wishlist that owns this item
     */
    public function wishlistShare(): BelongsTo
    {
        return $this->belongsTo(WishlistShare::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}