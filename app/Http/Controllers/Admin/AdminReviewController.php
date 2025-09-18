<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\ReviewModerationService;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    protected $moderationService;

    public function __construct(ReviewModerationService $moderationService)
    {
        $this->moderationService = $moderationService;
        $this->middleware('auth'); // Add proper admin middleware as needed
    }

    /**
     * Display review management dashboard
     */
    public function index()
    {
        // Get review statistics
        $stats = [
            'approved' => Review::where('status', 'approved')->count(),
            'flagged' => Review::where('status', 'flagged')->count(),
            'rejected' => Review::where('status', 'rejected')->count(),
        ];

        // Get flagged reviews (reported multiple times)
        $flaggedReviews = Review::where('status', 'flagged')
            ->with(['user', 'product'])
            ->orderBy('report_count', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Get recent reviews (last 20)
        $recentReviews = Review::approved()
            ->with(['user', 'product'])
            ->latest()
            ->take(20)
            ->get();

        // Get top quality reviews (highlighted reviews)
        $topReviews = Review::approved()
            ->with(['user', 'product'])
            ->where('helpful_count', '>=', config('reviews.amazon_style.helpful_review_threshold', 5))
            ->orderBy('helpful_count', 'desc')
            ->take(12)
            ->get()
            ->filter(function ($review) {
                return $this->moderationService->shouldHighlightReview($review);
            });

        return view('admin.reviews.index', compact('stats', 'flaggedReviews', 'recentReviews', 'topReviews'));
    }

    /**
     * Approve a flagged review (Amazon post-moderation)
     */
    public function approve(Review $review)
    {
        if ($review->status !== 'flagged') {
            return response()->json(['success' => false, 'message' => 'Review is not flagged']);
        }

        $review->update(['status' => 'approved']);

        return response()->json([
            'success' => true, 
            'message' => 'Review approved and will remain visible to customers.'
        ]);
    }

    /**
     * Reject a flagged review
     */
    public function reject(Review $review)
    {
        if ($review->status !== 'flagged') {
            return response()->json(['success' => false, 'message' => 'Review is not flagged']);
        }

        $review->update(['status' => 'rejected']);

        return response()->json([
            'success' => true, 
            'message' => 'Review has been removed and is no longer visible to customers.'
        ]);
    }

    /**
     * Show detailed review information
     */
    public function show(Review $review)
    {
        $review->load(['user', 'product', 'helpfulnessVotes.user']);
        
        // Get quality indicators
        $qualityIndicators = $this->moderationService->getReviewQualityIndicators($review);
        $userTrustScore = $this->moderationService->getUserTrustScore($review->user);
        $shouldHighlight = $this->moderationService->shouldHighlightReview($review);

        return view('admin.reviews.show', compact(
            'review', 
            'qualityIndicators', 
            'userTrustScore', 
            'shouldHighlight'
        ));
    }

    /**
     * Get review analytics data
     */
    public function analytics()
    {
        $data = [
            'total_reviews' => Review::count(),
            'reviews_by_status' => Review::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'reviews_by_rating' => Review::approved()
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating'),
            'monthly_reviews' => Review::approved()
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->take(12)
                ->pluck('count', 'month'),
            'top_reviewers' => Review::approved()
                ->selectRaw('user_id, COUNT(*) as review_count')
                ->with('user:id,name')
                ->groupBy('user_id')
                ->orderBy('review_count', 'desc')
                ->take(10)
                ->get(),
        ];

        return response()->json($data);
    }
}