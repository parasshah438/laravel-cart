<?php

namespace App\Services;

use App\Models\Review;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ReviewModerationService
{
    /**
     * Handle review submission with Amazon-style approach
     */
    public function handleNewReview(Review $review): array
    {
        // Amazon approach: Publish immediately
        $review->update([
            'status' => 'approved',
            'approved_at' => now()
        ]);

        // Log for admin monitoring
        Log::info('New review published immediately', [
            'review_id' => $review->id,
            'user_id' => $review->user_id,
            'product_id' => $review->product_id,
            'verified_purchase' => $review->verified_purchase,
            'rating' => $review->rating
        ]);

        return [
            'status' => 'published',
            'message' => $review->verified_purchase 
                ? 'Thank you for your verified review! Your review is now live and helping other customers.'
                : 'Thank you for your review! Your review is now live.'
        ];
    }

    /**
     * Handle review reports (Amazon-style post-moderation)
     */
    public function handleReviewReport(Review $review, array $reportData): void
    {
        // Increment report count
        $review->increment('report_count');

        // If review gets multiple reports, flag for admin review
        if ($review->report_count >= config('reviews.amazon_style.report_threshold', 3)) {
            $review->update(['status' => 'flagged']);
            
            // Notify admin
            Log::warning('Review flagged for admin review due to multiple reports', [
                'review_id' => $review->id,
                'report_count' => $review->report_count,
                'latest_report' => $reportData
            ]);
        }

        // Log the report
        $this->logReport($review, $reportData);
    }

    /**
     * Calculate user trust score
     */
    public function getUserTrustScore(User $user): float
    {
        $userReviews = $user->reviews()->approved()->get();
        
        if ($userReviews->isEmpty()) {
            return 0.5; // Neutral score for new users
        }

        $trustFactors = [
            'verified_purchases' => $userReviews->where('verified_purchase', true)->count() / $userReviews->count(),
            'helpful_reviews' => $userReviews->avg('helpful_count') ?: 0,
            'review_consistency' => $this->calculateReviewConsistency($userReviews),
            'account_age' => min(1.0, $user->created_at->diffInDays(now()) / 365), // Max 1.0 for 1+ year old accounts
        ];

        // Weighted average
        $weights = [
            'verified_purchases' => 0.4,
            'helpful_reviews' => 0.3,
            'review_consistency' => 0.2,
            'account_age' => 0.1
        ];

        $trustScore = 0;
        foreach ($trustFactors as $factor => $value) {
            $trustScore += ($value / 5) * $weights[$factor]; // Normalize helpful_count to 0-1 scale
        }

        return round(min(1.0, max(0.0, $trustScore)), 2);
    }

    /**
     * Check if review should be auto-highlighted (Amazon "Helpful" badge)
     */
    public function shouldHighlightReview(Review $review): bool
    {
        $criteria = [
            $review->verified_purchase, // Verified purchase
            $review->helpful_count >= config('reviews.amazon_style.helpful_review_threshold', 5), // Multiple helpful votes
            strlen($review->comment) >= config('reviews.amazon_style.detailed_review_min_length', 100), // Detailed review
            !empty($review->photos), // Has photos
            $this->getUserTrustScore($review->user) >= config('reviews.amazon_style.trusted_reviewer_threshold', 0.8) // Trusted user
        ];

        // Must meet at least 2 criteria and auto-highlighting must be enabled
        return config('reviews.amazon_style.auto_highlight_quality_reviews', true) && 
               count(array_filter($criteria)) >= 2;
    }

    /**
     * Get review quality indicators
     */
    public function getReviewQualityIndicators(Review $review): array
    {
        $indicators = [];

        if ($review->verified_purchase) {
            $indicators[] = [
                'type' => 'verified',
                'text' => 'Verified Purchase',
                'icon' => 'check-circle',
                'class' => 'success'
            ];
        }

        if ($review->helpful_count >= config('reviews.amazon_style.helpful_review_threshold', 5)) {
            $indicators[] = [
                'type' => 'helpful',
                'text' => 'Helpful Review',
                'icon' => 'thumbs-up',
                'class' => 'primary'
            ];
        }

        if ($this->getUserTrustScore($review->user) >= config('reviews.amazon_style.trusted_reviewer_threshold', 0.8)) {
            $indicators[] = [
                'type' => 'trusted',
                'text' => 'Trusted Reviewer',
                'icon' => 'star',
                'class' => 'warning'
            ];
        }

        if (!empty($review->photos)) {
            $indicators[] = [
                'type' => 'photos',
                'text' => 'Review with Photos',
                'icon' => 'camera',
                'class' => 'info'
            ];
        }

        return $indicators;
    }

    /**
     * Calculate review consistency (how consistent user's ratings are)
     */
    private function calculateReviewConsistency($reviews): float
    {
        if ($reviews->count() < 2) return 1.0;

        $ratings = $reviews->pluck('rating')->toArray();
        $mean = array_sum($ratings) / count($ratings);
        
        $variance = 0;
        foreach ($ratings as $rating) {
            $variance += pow($rating - $mean, 2);
        }
        $variance /= count($ratings);
        
        // Convert variance to consistency score (lower variance = higher consistency)
        return 1.0 - min(1.0, $variance / 4); // Normalize to 0-1 scale
    }

    /**
     * Log review report
     */
    private function logReport(Review $review, array $reportData): void
    {
        $reportText = sprintf(
            "Reported by user %d for: %s",
            auth()->id(),
            $reportData['reason']
        );
        
        if (!empty($reportData['details'])) {
            $reportText .= " - Details: " . $reportData['details'];
        }

        $currentNotes = $review->admin_notes ?: '';
        $review->update([
            'admin_notes' => $currentNotes . "\n[" . now() . "] " . $reportText
        ]);
    }
}