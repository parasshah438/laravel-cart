<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class BundleDeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'bundle_type',
        'min_products',
        'max_products',
        'discount_type',
        'discount_value',
        'bundle_price',
        'sale_event_id',
        'category_ids',
        'brand_ids',
        'image',
        'is_active',
        'is_featured',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'bundle_price' => 'decimal:2',
        'category_ids' => 'array',
        'brand_ids' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the sale event this bundle belongs to
     */
    public function saleEvent(): BelongsTo
    {
        return $this->belongsTo(SaleEvent::class);
    }

    /**
     * Get bundle products
     */
    public function bundleProducts(): HasMany
    {
        return $this->hasMany(BundleProduct::class);
    }

    /**
     * Get products in this bundle
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'bundle_products')
            ->withPivot([
                'is_primary',
                'is_optional',
                'min_quantity',
                'max_quantity',
                'bundle_product_price'
            ])
            ->withTimestamps();
    }

    /**
     * Get primary products in bundle
     */
    public function primaryProducts(): BelongsToMany
    {
        return $this->products()->wherePivot('is_primary', true);
    }

    /**
     * Get optional products in bundle
     */
    public function optionalProducts(): BelongsToMany
    {
        return $this->products()->wherePivot('is_optional', true);
    }

    /**
     * Scope: Active bundles
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Scope: Featured bundles
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: By bundle type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('bundle_type', $type);
    }

    /**
     * Check if bundle is currently active
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at > $now) {
            return false;
        }

        if ($this->ends_at && $this->ends_at < $now) {
            return false;
        }

        return true;
    }

    /**
     * Calculate bundle discount for given products
     */
    public function calculateDiscount(array $productPrices): float
    {
        $totalPrice = array_sum($productPrices);

        if ($this->bundle_price) {
            return max(0, $totalPrice - $this->bundle_price);
        }

        if ($this->discount_type === 'percentage') {
            return ($totalPrice * $this->discount_value) / 100;
        }

        if ($this->discount_type === 'fixed_amount') {
            return min($this->discount_value, $totalPrice);
        }

        return 0;
    }

    /**
     * Calculate final bundle price
     */
    public function calculateFinalPrice(array $productPrices): float
    {
        if ($this->bundle_price) {
            return $this->bundle_price;
        }

        $totalPrice = array_sum($productPrices);
        $discount = $this->calculateDiscount($productPrices);

        return max(0, $totalPrice - $discount);
    }

    /**
     * Check if products qualify for this bundle
     */
    public function qualifiesForBundle(array $productIds): bool
    {
        $bundleProductIds = $this->products()->pluck('products.id')->toArray();
        $matchingProducts = array_intersect($productIds, $bundleProductIds);

        if (count($matchingProducts) < $this->min_products) {
            return false;
        }

        if ($this->max_products > 0 && count($matchingProducts) > $this->max_products) {
            return false;
        }

        // Check if all primary products are included
        $primaryProductIds = $this->primaryProducts()->pluck('products.id')->toArray();
        $includedPrimary = array_intersect($productIds, $primaryProductIds);

        return count($includedPrimary) === count($primaryProductIds);
    }

    /**
     * Get bundle savings percentage
     */
    public function getSavingsPercentage(array $productPrices): float
    {
        $totalPrice = array_sum($productPrices);
        
        if ($totalPrice <= 0) {
            return 0;
        }

        $discount = $this->calculateDiscount($productPrices);
        
        return ($discount / $totalPrice) * 100;
    }

    /**
     * Check if bundle has category restrictions
     */
    public function hasCategoryRestrictions(): bool
    {
        return !empty($this->category_ids);
    }

    /**
     * Check if bundle has brand restrictions
     */
    public function hasBrandRestrictions(): bool
    {
        return !empty($this->brand_ids);
    }

    /**
     * Check if product categories match bundle restrictions
     */
    public function matchesCategoryRestrictions(array $productCategoryIds): bool
    {
        if (!$this->hasCategoryRestrictions()) {
            return true;
        }

        return !empty(array_intersect($this->category_ids, $productCategoryIds));
    }

    /**
     * Check if product brands match bundle restrictions
     */
    public function matchesBrandRestrictions(array $productBrandIds): bool
    {
        if (!$this->hasBrandRestrictions()) {
            return true;
        }

        return !empty(array_intersect($this->brand_ids, $productBrandIds));
    }
}