<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class SaleEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'status',
        'starts_at',
        'ends_at',
        'early_access_starts_at',
        'banner_image',
        'mobile_banner_image',
        'theme_color',
        'landing_page_template',
        'is_featured',
        'is_public',
        'requires_membership',
        'max_discount_percentage',
        'meta_title',
        'meta_description',
        'total_participants',
        'total_orders',
        'total_revenue',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'early_access_starts_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'requires_membership' => 'boolean',
        'max_discount_percentage' => 'decimal:2',
        'total_revenue' => 'decimal:2',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get sale products in this event
     */
    public function saleProducts(): HasMany
    {
        return $this->hasMany(SaleProduct::class);
    }

    /**
     * Get products in this sale through sale_products pivot
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'sale_products')
            ->withPivot([
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
                'ends_at'
            ])
            ->withTimestamps();
    }

    /**
     * Get bundle deals associated with this sale
     */
    public function bundles(): HasMany
    {
        return $this->hasMany(BundleDeal::class);
    }

    /**
     * Get dynamic coupons for this sale
     */
    public function coupons(): HasMany
    {
        return $this->hasMany(DynamicCoupon::class);
    }

    /**
     * Get challenges for this sale
     */
    public function challenges(): HasMany
    {
        return $this->hasMany(SaleChallenge::class);
    }

    /**
     * Get spin wheel campaigns for this sale
     */
    public function spinWheelCampaigns(): HasMany
    {
        return $this->hasMany(SpinWheelCampaign::class);
    }

    /**
     * Get banners for this sale
     */
    public function banners(): HasMany
    {
        return $this->hasMany(SaleBanner::class);
    }

    /**
     * Get analytics for this sale
     */
    public function analytics(): HasMany
    {
        return $this->hasMany(SaleAnalytic::class);
    }

    /**
     * Get user behaviors for this sale
     */
    public function userBehaviors(): HasMany
    {
        return $this->hasMany(UserSaleBehavior::class);
    }

    /**
     * Get orders made during this sale
     */
    public function saleOrders(): HasMany
    {
        return $this->hasMany(SaleOrder::class);
    }

    /**
     * Get notifications for this sale
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(SaleNotification::class);
    }

    /**
     * Scope: Active sales
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    /**
     * Scope: Upcoming sales
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', 'scheduled')
            ->where('starts_at', '>', now());
    }

    /**
     * Scope: Featured sales
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: Public sales
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope: Sales by type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Check if sale is currently active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && $this->starts_at <= now() 
            && $this->ends_at >= now();
    }

    /**
     * Check if sale is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->status === 'scheduled' && $this->starts_at > now();
    }

    /**
     * Check if sale has ended
     */
    public function hasEnded(): bool
    {
        return $this->ends_at < now() || $this->status === 'ended';
    }

    /**
     * Get time remaining for sale (in seconds)
     */
    public function getTimeRemaining(): int
    {
        if (!$this->isActive()) {
            return 0;
        }

        return max(0, $this->ends_at->diffInSeconds(now()));
    }

    /**
     * Get time until sale starts (in seconds)
     */
    public function getTimeUntilStart(): int
    {
        if ($this->starts_at <= now()) {
            return 0;
        }

        return $this->starts_at->diffInSeconds(now());
    }

    /**
     * Check if early access is available
     */
    public function hasEarlyAccess(): bool
    {
        return $this->early_access_starts_at && 
            $this->early_access_starts_at <= now() && 
            $this->starts_at > now();
    }

    /**
     * Get sale progress percentage (0-100)
     */
    public function getProgressPercentage(): float
    {
        if (!$this->isActive()) {
            return $this->hasEnded() ? 100 : 0;
        }

        $totalDuration = $this->starts_at->diffInSeconds($this->ends_at);
        $elapsed = $this->starts_at->diffInSeconds(now());

        return min(100, max(0, ($elapsed / $totalDuration) * 100));
    }

    /**
     * Get total discount given in this sale
     */
    public function getTotalDiscountGiven(): float
    {
        return $this->saleOrders()->sum('sale_discount_amount');
    }

    /**
     * Get conversion rate for this sale
     */
    public function getConversionRate(): float
    {
        $totalVisitors = $this->analytics()->sum('unique_visitors');
        $totalOrders = $this->analytics()->sum('orders_completed');

        if ($totalVisitors === 0) {
            return 0;
        }

        return ($totalOrders / $totalVisitors) * 100;
    }
}