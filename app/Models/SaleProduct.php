<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class SaleProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_event_id',
        'product_id',
        'discount_type',
        'discount_value',
        'sale_price',
        'original_price',
        'max_discount_amount',
        'sale_quantity_limit',
        'sold_quantity',
        'per_user_limit',
        'flash_sale_duration_minutes',
        'sort_order',
        'is_featured_in_sale',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'is_featured_in_sale' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the sale event this product belongs to
     */
    public function saleEvent(): BelongsTo
    {
        return $this->belongsTo(SaleEvent::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope: Active sale products
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereHas('saleEvent', function ($q) {
            $q->where('status', 'active')
              ->where('starts_at', '<=', now())
              ->where('ends_at', '>=', now());
        });
    }

    /**
     * Scope: Featured in sale
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured_in_sale', true);
    }

    /**
     * Scope: Flash sale products
     */
    public function scopeFlashSale(Builder $query): Builder
    {
        return $query->where('flash_sale_duration_minutes', '>', 0);
    }

    /**
     * Scope: Available products (not sold out)
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('sale_quantity_limit')
              ->orWhereRaw('sold_quantity < sale_quantity_limit');
        });
    }

    /**
     * Check if product is currently on sale
     */
    public function isOnSale(): bool
    {
        $saleActive = $this->saleEvent && $this->saleEvent->isActive();
        
        if ($this->starts_at && $this->ends_at) {
            return $saleActive && $this->starts_at <= now() && $this->ends_at >= now();
        }
        
        return $saleActive;
    }

    /**
     * Check if product is available for purchase
     */
    public function isAvailable(): bool
    {
        if (!$this->sale_quantity_limit) {
            return true;
        }

        return $this->sold_quantity < $this->sale_quantity_limit;
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentage(): float
    {
        if ($this->original_price <= 0) {
            return 0;
        }

        return (($this->original_price - $this->sale_price) / $this->original_price) * 100;
    }

    /**
     * Get discount amount
     */
    public function getDiscountAmount(): float
    {
        return $this->original_price - $this->sale_price;
    }

    /**
     * Get remaining quantity
     */
    public function getRemainingQuantity(): ?int
    {
        if (!$this->sale_quantity_limit) {
            return null;
        }

        return max(0, $this->sale_quantity_limit - $this->sold_quantity);
    }

    /**
     * Check if flash sale is active
     */
    public function isFlashSaleActive(): bool
    {
        if ($this->flash_sale_duration_minutes <= 0) {
            return false;
        }

        if (!$this->isOnSale()) {
            return false;
        }

        $flashEndTime = $this->starts_at ? 
            $this->starts_at->addMinutes($this->flash_sale_duration_minutes) : 
            $this->saleEvent->starts_at->addMinutes($this->flash_sale_duration_minutes);

        return now() <= $flashEndTime;
    }

    /**
     * Get flash sale time remaining (in seconds)
     */
    public function getFlashSaleTimeRemaining(): int
    {
        if (!$this->isFlashSaleActive()) {
            return 0;
        }

        $flashEndTime = $this->starts_at ? 
            $this->starts_at->addMinutes($this->flash_sale_duration_minutes) : 
            $this->saleEvent->starts_at->addMinutes($this->flash_sale_duration_minutes);

        return max(0, $flashEndTime->diffInSeconds(now()));
    }

    /**
     * Increment sold quantity
     */
    public function incrementSoldQuantity(int $quantity = 1): bool
    {
        return $this->increment('sold_quantity', $quantity);
    }

    /**
     * Check if user can purchase this quantity
     */
    public function canUserPurchase(int $userId, int $quantity): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        if ($this->per_user_limit <= 0) {
            return true;
        }

        // Check how many this user has already purchased
        $userPurchased = OrderItem::whereHas('order', function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered']);
        })->where('product_id', $this->product_id)
          ->whereHas('order.saleOrder', function ($q) {
            $q->where('sale_event_id', $this->sale_event_id);
        })->sum('quantity');

        return ($userPurchased + $quantity) <= $this->per_user_limit;
    }
}