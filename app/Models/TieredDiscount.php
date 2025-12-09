<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class TieredDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'tier_type',
        'product_ids',
        'category_ids',
        'brand_ids',
        'sale_event_id',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'product_ids' => 'array',
        'category_ids' => 'array',
        'brand_ids' => 'array',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the sale event this tiered discount belongs to
     */
    public function saleEvent(): BelongsTo
    {
        return $this->belongsTo(SaleEvent::class);
    }

    /**
     * Get tier rules for this discount
     */
    public function tierRules(): HasMany
    {
        return $this->hasMany(TierRule::class)->orderBy('tier_order');
    }

    /**
     * Scope: Active tiered discounts
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
     * Scope: By tier type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('tier_type', $type);
    }

    /**
     * Check if tiered discount is currently active
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
     * Get applicable tier for given quantity or amount
     */
    public function getApplicableTier(int $quantity = 0, float $amount = 0): ?TierRule
    {
        if (!$this->isCurrentlyActive()) {
            return null;
        }

        $tierRules = $this->tierRules;

        if ($this->tier_type === 'quantity') {
            return $tierRules->where('min_quantity', '<=', $quantity)
                ->sortByDesc('min_quantity')
                ->first();
        }

        if ($this->tier_type === 'amount') {
            return $tierRules->where('min_amount', '<=', $amount)
                ->sortByDesc('min_amount')
                ->first();
        }

        return null;
    }

    /**
     * Calculate discount for given parameters
     */
    public function calculateDiscount(int $quantity = 0, float $amount = 0): float
    {
        $applicableTier = $this->getApplicableTier($quantity, $amount);

        if (!$applicableTier) {
            return 0;
        }

        if ($applicableTier->discount_type === 'percentage') {
            if ($this->tier_type === 'quantity') {
                return ($amount * $applicableTier->discount_value) / 100;
            } else {
                return ($amount * $applicableTier->discount_value) / 100;
            }
        }

        if ($applicableTier->discount_type === 'fixed_amount') {
            return $applicableTier->discount_value;
        }

        return 0;
    }

    /**
     * Check if products match this tiered discount
     */
    public function matchesProducts(array $productIds): bool
    {
        // If no restrictions, applies to all products
        if (empty($this->product_ids) && empty($this->category_ids) && empty($this->brand_ids)) {
            return true;
        }

        // Check specific products
        if (!empty($this->product_ids)) {
            $matchingProducts = array_intersect($this->product_ids, $productIds);
            if (!empty($matchingProducts)) {
                return true;
            }
        }

        // Check categories
        if (!empty($this->category_ids)) {
            $productCategories = Product::whereIn('id', $productIds)
                ->pluck('category_id')
                ->toArray();
            
            $matchingCategories = array_intersect($this->category_ids, $productCategories);
            if (!empty($matchingCategories)) {
                return true;
            }
        }

        // Check brands
        if (!empty($this->brand_ids)) {
            $productBrands = Product::whereIn('id', $productIds)
                ->pluck('brand_id')
                ->filter()
                ->toArray();
            
            $matchingBrands = array_intersect($this->brand_ids, $productBrands);
            if (!empty($matchingBrands)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get next tier requirements
     */
    public function getNextTierRequirement(int $currentQuantity = 0, float $currentAmount = 0): ?array
    {
        $tierRules = $this->tierRules;
        
        if ($this->tier_type === 'quantity') {
            $nextTier = $tierRules->where('min_quantity', '>', $currentQuantity)
                ->sortBy('min_quantity')
                ->first();
                
            if ($nextTier) {
                return [
                    'tier' => $nextTier,
                    'required_quantity' => $nextTier->min_quantity,
                    'quantity_needed' => $nextTier->min_quantity - $currentQuantity,
                ];
            }
        }

        if ($this->tier_type === 'amount') {
            $nextTier = $tierRules->where('min_amount', '>', $currentAmount)
                ->sortBy('min_amount')
                ->first();
                
            if ($nextTier) {
                return [
                    'tier' => $nextTier,
                    'required_amount' => $nextTier->min_amount,
                    'amount_needed' => $nextTier->min_amount - $currentAmount,
                ];
            }
        }

        return null;
    }

    /**
     * Get all available tiers
     */
    public function getAllTiers(): array
    {
        return $this->tierRules->map(function ($tier) {
            return [
                'id' => $tier->id,
                'name' => $tier->tier_name,
                'min_quantity' => $tier->min_quantity,
                'min_amount' => $tier->min_amount,
                'discount_type' => $tier->discount_type,
                'discount_value' => $tier->discount_value,
                'tier_order' => $tier->tier_order,
            ];
        })->toArray();
    }
}