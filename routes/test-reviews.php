<?php

use App\Models\Review;
use App\Services\ReviewModerationService;

// Quick test of the Amazon-style review system
Route::get('/test-amazon-reviews', function () {
    $moderationService = new ReviewModerationService();
    
    // Get a sample review if it exists
    $review = Review::with('user')->first();
    
    if (!$review) {
        return response()->json(['message' => 'No reviews found. Create a review first.']);
    }
    
    $results = [
        'review_id' => $review->id,
        'user_trust_score' => $moderationService->getUserTrustScore($review->user),
        'quality_indicators' => $moderationService->getReviewQualityIndicators($review),
        'should_highlight' => $moderationService->shouldHighlightReview($review),
        'config_settings' => config('reviews.amazon_style'),
        'approval_mode' => config('reviews.approval_mode')
    ];
    
    return response()->json($results, 200, [], JSON_PRETTY_PRINT);
})->middleware('web');