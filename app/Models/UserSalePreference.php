<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSalePreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'preferred_categories',
        'preferred_brands',
        'max_budget',
        'min_discount_percentage',
        'notification_methods',
        'notification_frequency',
        'flash_sale_alerts',
        'bundle_deal_alerts',
        'wishlist_sale_alerts',
        'early_access_preference',
        'weekend_sale_preference',
    ];

    protected $casts = [
        'preferred_categories' => 'array',
        'preferred_brands' => 'array',
        'max_budget' => 'decimal:2',
        'min_discount_percentage' => 'integer',
        'notification_methods' => 'array',
        'flash_sale_alerts' => 'boolean',
        'bundle_deal_alerts' => 'boolean',
        'wishlist_sale_alerts' => 'boolean',
        'early_access_preference' => 'boolean',
        'weekend_sale_preference' => 'boolean',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if user wants notifications for specific sale type
     */
    public function wantsNotificationFor(string $saleType): bool
    {
        switch ($saleType) {
            case 'flash_sale':
                return $this->flash_sale_alerts;
            case 'bundle_deal':
                return $this->bundle_deal_alerts;
            case 'wishlist_sale':
                return $this->wishlist_sale_alerts;
            default:
                return false;
        }
    }

    /**
     * Check if user has notification method enabled
     */
    public function hasNotificationMethod(string $method): bool
    {
        return in_array($method, $this->notification_methods ?? []);
    }

    /**
     * Check if sale meets user's minimum discount preference
     */
    public function meetsDiscountPreference(float $discountPercentage): bool
    {
        return $discountPercentage >= $this->min_discount_percentage;
    }

    /**
     * Check if sale is within user's budget
     */
    public function isWithinBudget(float $price): bool
    {
        if (!$this->max_budget) {
            return true;
        }

        return $price <= $this->max_budget;
    }

    /**
     * Check if product category is preferred by user
     */
    public function isPreferredCategory(string $category): bool
    {
        if (empty($this->preferred_categories)) {
            return true;
        }

        return in_array($category, $this->preferred_categories);
    }

    /**
     * Check if product brand is preferred by user
     */
    public function isPreferredBrand(string $brand): bool
    {
        if (empty($this->preferred_brands)) {
            return true;
        }

        return in_array($brand, $this->preferred_brands);
    }

    /**
     * Update notification preferences
     */
    public function updateNotificationPreferences(array $preferences): void
    {
        $this->update([
            'notification_methods' => $preferences['methods'] ?? $this->notification_methods,
            'notification_frequency' => $preferences['frequency'] ?? $this->notification_frequency,
            'flash_sale_alerts' => $preferences['flash_sale'] ?? $this->flash_sale_alerts,
            'bundle_deal_alerts' => $preferences['bundle_deals'] ?? $this->bundle_deal_alerts,
            'wishlist_sale_alerts' => $preferences['wishlist_sales'] ?? $this->wishlist_sale_alerts,
        ]);
    }

    /**
     * Get notification preferences summary
     */
    public function getNotificationSummary(): array
    {
        return [
            'methods' => $this->notification_methods ?? [],
            'frequency' => $this->notification_frequency,
            'flash_sales' => $this->flash_sale_alerts,
            'bundle_deals' => $this->bundle_deal_alerts,
            'wishlist_sales' => $this->wishlist_sale_alerts,
            'early_access' => $this->early_access_preference,
            'weekend_sales' => $this->weekend_sale_preference,
        ];
    }

    /**
     * Get shopping preferences summary
     */
    public function getShoppingPreferences(): array
    {
        return [
            'categories' => $this->preferred_categories ?? [],
            'brands' => $this->preferred_brands ?? [],
            'max_budget' => $this->max_budget,
            'min_discount' => $this->min_discount_percentage,
        ];
    }
}