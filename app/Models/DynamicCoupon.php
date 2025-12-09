<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DynamicCoupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'usage_limit',
        'used_count',
        'per_user_limit',
        'min_order_amount',
        'max_discount_amount',
        'applicable_products',
        'applicable_categories',
        'applicable_brands',
        'user_groups',
        'first_order_only',
        'payment_methods',
        'starts_at',
        'ends_at',
        'sale_event_id',
        'is_active',
        'is_auto_apply',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'applicable_products' => 'array',
        'applicable_categories' => 'array',
        'applicable_brands' => 'array',
        'user_groups' => 'array',
        'payment_methods' => 'array',
        'first_order_only' => 'boolean',
        'is_active' => 'boolean',
        'is_auto_apply' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the sale event this coupon belongs to
     */
    public function saleEvent(): BelongsTo
    {
        return $this->belongsTo(SaleEvent::class);
    }

    /**
     * Scope: Active coupons
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->where(function ($q) {
                $q->where('usage_limit', 0)
                  ->orWhereRaw('used_count < usage_limit');
            });
    }

    /**
     * Scope: Auto-apply coupons
     */
    public function scopeAutoApply(Builder $query): Builder
    {
        return $query->where('is_auto_apply', true);
    }

    /**
     * Scope: By coupon type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: For specific payment method
     */
    public function scopeForPaymentMethod(Builder $query, string $paymentMethod): Builder
    {
        return $query->where(function ($q) use ($paymentMethod) {
            $q->whereNull('payment_methods')
              ->orWhereJsonContains('payment_methods', $paymentMethod);
        });
    }

    /**
     * Check if coupon is currently valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at > $now || $this->ends_at < $now) {
            return false;
        }

        if ($this->usage_limit > 0 && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Check if coupon can be used by user
     */
    public function canBeUsedBy(int $userId, float $orderAmount, array $productIds = [], string $paymentMethod = null): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check minimum order amount
        if ($orderAmount < $this->min_order_amount) {
            return false;
        }

        // Check per-user usage limit
        if ($this->per_user_limit > 0) {
            $userUsageCount = $this->getUserUsageCount($userId);
            if ($userUsageCount >= $this->per_user_limit) {
                return false;
            }
        }

        // Check if first order only
        if ($this->first_order_only) {
            $hasOrders = Order::where('user_id', $userId)
                ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
                ->exists();
            
            if ($hasOrders) {
                return false;
            }
        }

        // Check user groups
        if (!empty($this->user_groups)) {
            $user = User::find($userId);
            if (!$user || !$this->userBelongsToGroups($user)) {
                return false;
            }
        }

        // Check payment method
        if ($paymentMethod && !empty($this->payment_methods)) {
            if (!in_array($paymentMethod, $this->payment_methods)) {
                return false;
            }
        }

        // Check product restrictions
        if (!empty($this->applicable_products) && !empty($productIds)) {
            $hasApplicableProducts = !empty(array_intersect($this->applicable_products, $productIds));
            if (!$hasApplicableProducts) {
                return false;
            }
        }

        // Check category restrictions
        if (!empty($this->applicable_categories) && !empty($productIds)) {
            $productCategories = Product::whereIn('id', $productIds)
                ->pluck('category_id')
                ->toArray();
            
            $hasApplicableCategories = !empty(array_intersect($this->applicable_categories, $productCategories));
            if (!$hasApplicableCategories) {
                return false;
            }
        }

        // Check brand restrictions
        if (!empty($this->applicable_brands) && !empty($productIds)) {
            $productBrands = Product::whereIn('id', $productIds)
                ->pluck('brand_id')
                ->filter()
                ->toArray();
            
            $hasApplicableBrands = !empty(array_intersect($this->applicable_brands, $productBrands));
            if (!$hasApplicableBrands) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount(float $orderAmount, array $productIds = []): float
    {
        $applicableAmount = $this->getApplicableAmount($orderAmount, $productIds);

        switch ($this->type) {
            case 'percentage':
                $discount = ($applicableAmount * $this->value) / 100;
                break;

            case 'fixed_cart':
                $discount = min($this->value, $applicableAmount);
                break;

            case 'fixed_product':
                $applicableProducts = $this->getApplicableProducts($productIds);
                $discount = min($this->value * count($applicableProducts), $applicableAmount);
                break;

            case 'free_shipping':
                $discount = 0; // Handle shipping discount separately
                break;

            case 'cashback':
                $discount = ($applicableAmount * $this->value) / 100;
                break;

            default:
                $discount = 0;
        }

        // Apply maximum discount limit
        if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
            $discount = $this->max_discount_amount;
        }

        return round($discount, 2);
    }

    /**
     * Get applicable amount for discount calculation
     */
    protected function getApplicableAmount(float $orderAmount, array $productIds): float
    {
        // If no product restrictions, apply to full order
        if (empty($this->applicable_products) && empty($this->applicable_categories) && empty($this->applicable_brands)) {
            return $orderAmount;
        }

        if (empty($productIds)) {
            return $orderAmount;
        }

        $applicableProducts = $this->getApplicableProducts($productIds);
        
        return Product::whereIn('id', $applicableProducts)->sum('price');
    }

    /**
     * Get applicable product IDs
     */
    protected function getApplicableProducts(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $query = Product::whereIn('id', $productIds);

        // Filter by applicable products
        if (!empty($this->applicable_products)) {
            $query->whereIn('id', $this->applicable_products);
        }

        // Filter by applicable categories
        if (!empty($this->applicable_categories)) {
            $query->whereIn('category_id', $this->applicable_categories);
        }

        // Filter by applicable brands
        if (!empty($this->applicable_brands)) {
            $query->whereIn('brand_id', $this->applicable_brands);
        }

        return $query->pluck('id')->toArray();
    }

    /**
     * Get user usage count for this coupon
     */
    public function getUserUsageCount(int $userId): int
    {
        return Order::where('user_id', $userId)
            ->whereJsonContains('coupon_codes', $this->code)
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->count();
    }

    /**
     * Check if user belongs to required groups
     */
    protected function userBelongsToGroups(User $user): bool
    {
        // This would depend on how you implement user groups
        // Example implementation:
        foreach ($this->user_groups as $group) {
            switch ($group) {
                case 'premium':
                    if ($user->isPremium()) return true;
                    break;
                case 'vip':
                    if ($user->isVip()) return true;
                    break;
                case 'new_users':
                    if ($user->created_at->diffInDays(now()) <= 30) return true;
                    break;
            }
        }

        return false;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): bool
    {
        return $this->increment('used_count');
    }

    /**
     * Check if coupon provides free shipping
     */
    public function providesFreeShipping(): bool
    {
        return $this->type === 'free_shipping';
    }

    /**
     * Check if coupon is a cashback type
     */
    public function isCashback(): bool
    {
        return $this->type === 'cashback';
    }
}