<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Review Moderation Settings - Amazon Style
    |--------------------------------------------------------------------------
    |
    | Amazon approach: Immediate publication with post-moderation
    |
    */

    // Review approval workflow (Amazon style)
    'approval_mode' => env('REVIEW_APPROVAL_MODE', 'amazon'), // amazon, manual, hybrid

    // Amazon approach settings
    'amazon_style' => [
        'auto_publish' => true,               // Reviews appear immediately
        'post_moderation' => true,            // Can be reported/removed later
        'trust_verified_purchases' => true,   // Higher trust for verified purchases
        'allow_reporting' => true,            // Users can report inappropriate reviews
        'admin_review_reports' => true,       // Admins review reported content
    ],

    // Auto-approval conditions (when approval_mode is 'hybrid')
    'auto_approve_conditions' => [
        'verified_purchase' => true,  // Auto-approve verified purchases
        'trusted_users' => false,     // Auto-approve users with good review history
        'rating_threshold' => null,   // Auto-approve reviews above certain rating (1-5)
    ],

    // Manual approval settings
    'require_approval_for' => [
        'first_time_reviewers' => false,  // Require approval for users' first review
        'low_ratings' => false,           // Require approval for 1-2 star reviews
        'with_photos' => false,           // Require approval for reviews with photos
        'long_reviews' => false,          // Require approval for reviews > 500 chars
    ],

    // Notification settings
    'notifications' => [
        'admin_on_new_review' => false,      // Notify admin of new reviews
        'admin_on_pending_review' => true,   // Notify admin of reviews needing approval
        'user_on_approval' => true,          // Notify user when review is approved
        'user_on_rejection' => true,         // Notify user when review is rejected
    ],

    // Content filtering
    'content_filters' => [
        'profanity_check' => false,          // Check for inappropriate language
        'spam_detection' => false,           // Basic spam detection
        'minimum_length' => 10,              // Minimum review length
        'maximum_length' => 2000,            // Maximum review length
    ],

    // Review display settings
    'display' => [
        'show_pending_to_author' => true,    // Show pending reviews to the author
        'pending_message' => 'Your review is under moderation and will be published shortly.',
        'approved_message' => 'Thank you for your review!',
        'rejected_message' => 'Your review could not be published. Please contact support for more information.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Amazon-Style Specific Settings
    |--------------------------------------------------------------------------
    */
    'amazon_style' => [
        // Number of reports needed before review is flagged for admin review
        'report_threshold' => 3,
        
        // Trust score threshold for trusted reviewer badge
        'trusted_reviewer_threshold' => 0.8,
        
        // Minimum helpful votes for "helpful review" badge
        'helpful_review_threshold' => 5,
        
        // Minimum review length for detailed review consideration
        'detailed_review_min_length' => 100,
        
        // Enable automatic highlighting of high-quality reviews
        'auto_highlight_quality_reviews' => true,
    ],
];