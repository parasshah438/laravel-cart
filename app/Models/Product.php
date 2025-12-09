<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'price',
        'image',
        'slug', // Ensure slug is fillable for mass assignment
        'status', // active, inactive, out_of_stock
        'average_rating', // Review system
        'review_count', // Review system
    ];

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function productStocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function media()
    {
        return $this->hasMany(ProductMedia::class)->orderBy('sort_order');
    }

    public function productMedias()
    {
        return $this->hasMany(ProductMedia::class)->orderBy('sort_order');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function views()
    {
        return $this->hasMany(RecentlyViewedProduct::class);
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // ================================================================================================
    // 📝 REVIEW RELATIONSHIPS
    // ================================================================================================
    
    /**
     * Get all reviews for this product
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get only approved reviews
     */
    public function approvedReviews()
    {
        return $this->reviews()->approved();
    }

    /**
     * Get reviews with photos
     */
    public function reviewsWithPhotos()
    {
        return $this->reviews()->approved()->withPhotos();
    }

    /**
     * Get verified purchase reviews
     */
    public function verifiedReviews()
    {
        return $this->reviews()->approved()->verified();
    }

    // ================================================================================================
    // 📊 REVIEW STATISTICS & METHODS
    // ================================================================================================
    
    /**
     * Get average rating for this product
     */
    public function getAverageRatingAttribute(): ?float
    {
        $avg = $this->approvedReviews()->avg('rating');
        return $avg ? round($avg, 1) : null;
    }

    /**
     * Get total review count
     */
    public function getReviewCountAttribute(): int
    {
        return $this->approvedReviews()->count();
    }

    /**
     * Get rating breakdown (1-5 stars)
     */
    public function getRatingBreakdownAttribute(): array
    {
        $breakdown = [];
        $totalReviews = $this->review_count;
        
        for ($rating = 1; $rating <= 5; $rating++) {
            $count = $this->approvedReviews()->where('rating', $rating)->count();
            $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
            
            $breakdown[$rating] = [
                'count' => $count,
                'percentage' => $percentage
            ];
        }
        
        return $breakdown;
    }

    /**
     * Get percentage of verified reviews
     */
    public function getVerifiedPercentageAttribute(): int
    {
        $total = $this->review_count;
        if ($total === 0) return 0;
        
        $verified = $this->verifiedReviews()->count();
        return round(($verified / $total) * 100);
    }

    /**
     * Get star display string (★★★★☆)
     */
    public function getStarsDisplayAttribute(): string
    {
        $rating = $this->average_rating ?? 0;
        $fullStars = floor($rating);
        $hasHalfStar = ($rating - $fullStars) >= 0.5;
        
        $stars = str_repeat('★', $fullStars);
        if ($hasHalfStar) $stars .= '☆';
        $stars .= str_repeat('☆', 5 - strlen($stars));
        
        return $stars;
    }

    /**
     * Check if product has reviews
     */
    public function hasReviews(): bool
    {
        return $this->review_count > 0;
    }

    /**
     * Get most helpful review
     */
    public function getMostHelpfulReview()
    {
        return $this->approvedReviews()
                    ->orderBy('helpful_count', 'desc')
                    ->first();
    }

    /**
     * Get recent reviews
     */
    public function getRecentReviews(int $limit = 5)
    {
        return $this->approvedReviews()
                    ->with(['user'])
                    ->latest()
                    ->limit($limit)
                    ->get();
    }

    /**
     * Update cached rating values (call this when reviews change)
     */
    public function updateRatingCache(): void
    {
        $this->update([
            'average_rating' => $this->average_rating,
            'review_count' => $this->review_count
        ]);
    }

    // ================================================================================================
    // 🛍️ SALE SYSTEM RELATIONSHIPS
    // ================================================================================================

    /**
     * Get sale products (products in sales)
     */
    public function saleProducts()
    {
        return $this->hasMany(SaleProduct::class);
    }

    /**
     * Get bundle products (product in bundles)
     */
    public function bundleProducts()
    {
        return $this->hasMany(BundleProduct::class);
    }

    /**
     * Get active sale events for this product
     */
    public function activeSaleEvents()
    {
        return $this->belongsToMany(SaleEvent::class, 'sale_products')
            ->where('is_active', true)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now());
    }

    /**
     * Get active bundle deals for this product
     */
    public function activeBundleDeals()
    {
        return $this->belongsToMany(BundleDeal::class, 'bundle_products')
            ->where('is_active', true)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now());
    }

    /**
     * Get wishlist sale alerts for this product
     */
    public function wishlistSaleAlerts()
    {
        return $this->hasMany(WishlistSaleAlert::class);
    }

    /**
     * Get user sale behaviors for this product
     */
    public function saleBehaviors()
    {
        return $this->hasMany(UserSaleBehavior::class);
    }

    // ================================================================================================
    // 🛍️ SALE SYSTEM METHODS
    // ================================================================================================

    /**
     * Check if product is currently on sale
     */
    public function isOnSale(): bool
    {
        return $this->activeSaleEvents()->exists();
    }

    /**
     * Check if product is in any active bundle
     */
    public function isInBundle(): bool
    {
        return $this->activeBundleDeals()->exists();
    }

    /**
     * Get current sale price
     */
    public function getSalePrice(): ?float
    {
        $activeSale = $this->saleProducts()
            ->whereHas('saleEvent', function ($query) {
                $query->where('is_active', true)
                    ->where('start_time', '<=', now())
                    ->where('end_time', '>=', now());
            })
            ->orderBy('sale_price')
            ->first();

        return $activeSale ? $activeSale->sale_price : null;
    }

    /**
     * Get discount percentage from regular price
     */
    public function getDiscountPercentage(): ?float
    {
        $salePrice = $this->getSalePrice();
        if (!$salePrice || $this->price <= 0) {
            return null;
        }

        return round((($this->price - $salePrice) / $this->price) * 100, 1);
    }

    /**
     * Get effective price (sale price if on sale, otherwise regular price)
     */
    public function getEffectivePrice(): float
    {
        return $this->getSalePrice() ?? $this->price;
    }

    /**
     * Get savings amount from regular price
     */
    public function getSavingsAmount(): ?float
    {
        $salePrice = $this->getSalePrice();
        if (!$salePrice) {
            return null;
        }

        return $this->price - $salePrice;
    }

    /**
     * Get current active sale event
     */
    public function getCurrentSaleEvent()
    {
        return $this->activeSaleEvents()->first();
    }

    /**
     * Get all active promotions (sales and bundles)
     */
    public function getActivePromotions(): array
    {
        $promotions = [];

        // Add sale events
        foreach ($this->activeSaleEvents as $sale) {
            $promotions[] = [
                'type' => 'sale',
                'id' => $sale->id,
                'name' => $sale->name,
                'discount_percentage' => $this->getDiscountPercentage(),
                'sale_price' => $this->getSalePrice(),
                'end_time' => $sale->end_time,
            ];
        }

        // Add bundle deals
        foreach ($this->activeBundleDeals as $bundle) {
            $promotions[] = [
                'type' => 'bundle',
                'id' => $bundle->id,
                'name' => $bundle->name,
                'description' => $bundle->description,
                'bundle_price' => $bundle->bundle_price,
                'end_time' => $bundle->end_time,
            ];
        }

        return $promotions;
    }

    /**
     * Check if user should be notified about sales for this product
     */
    public function shouldNotifyUserAboutSale(User $user): bool
    {
        // Check if user has this product in wishlist
        $inWishlist = $user->wishlist()->where('product_id', $this->id)->exists();
        if (!$inWishlist) {
            return false;
        }

        // Check user's sale preferences
        $preferences = $user->getSalePreferences();
        
        // Check if product meets discount preference
        $discount = $this->getDiscountPercentage();
        if (!$discount || !$preferences->meetsDiscountPreference($discount)) {
            return false;
        }

        // Check if within budget
        $salePrice = $this->getSalePrice();
        if ($salePrice && !$preferences->isWithinBudget($salePrice)) {
            return false;
        }

        // Check category preference
        if (!$preferences->isPreferredCategory($this->category->name)) {
            return false;
        }

        return true;
    }

    /**
     * Create wishlist sale alert for users
     */
    public function createWishlistAlerts(): void
    {
        if (!$this->isOnSale()) {
            return;
        }

        $usersToNotify = User::whereHas('wishlist', function ($query) {
            $query->where('product_id', $this->id);
        })->get();

        foreach ($usersToNotify as $user) {
            if ($this->shouldNotifyUserAboutSale($user)) {
                WishlistSaleAlert::create([
                    'user_id' => $user->id,
                    'product_id' => $this->id,
                    'sale_event_id' => $this->getCurrentSaleEvent()?->id,
                    'original_price' => $this->price,
                    'sale_price' => $this->getSalePrice(),
                    'discount_percentage' => $this->getDiscountPercentage(),
                ]);
            }
        }
    }
}
