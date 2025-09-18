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

    public function media()
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
}
