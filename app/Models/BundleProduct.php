<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BundleProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'bundle_deal_id',
        'product_id',
        'is_primary',
        'is_optional',
        'min_quantity',
        'max_quantity',
        'bundle_product_price',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_optional' => 'boolean',
        'bundle_product_price' => 'decimal:2',
    ];

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * Get the bundle deal
     */
    public function bundleDeal(): BelongsTo
    {
        return $this->belongsTo(BundleDeal::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the effective price for this product in the bundle
     */
    public function getEffectivePrice(): float
    {
        return $this->bundle_product_price ?? $this->product->price;
    }

    /**
     * Get discount amount for this product in bundle
     */
    public function getDiscountAmount(): float
    {
        if (!$this->bundle_product_price) {
            return 0;
        }

        return max(0, $this->product->price - $this->bundle_product_price);
    }

    /**
     * Check if quantity is within allowed range
     */
    public function isQuantityValid(int $quantity): bool
    {
        if ($quantity < $this->min_quantity) {
            return false;
        }

        if ($this->max_quantity > 0 && $quantity > $this->max_quantity) {
            return false;
        }

        return true;
    }
}