<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product;
use App\Models\ReviewHelpfulness;
use App\Services\ReviewModerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    protected $moderationService;

    public function __construct(ReviewModerationService $moderationService)
    {
        $this->moderationService = $moderationService;
    }
    // ================================================================================================
    // 📝 PUBLIC REVIEW ROUTES
    // ================================================================================================

    /**
     * Display all reviews across the platform
     */
    public function allReviews(Request $request)
    {
        $sortBy = $request->get('sort', 'newest'); // newest, helpful, rating_high, rating_low
        $filterRating = $request->get('rating'); // 1-5 stars filter
        $searchQuery = $request->get('q'); // search in review text

        $query = Review::approved()
            ->with(['user', 'product', 'helpfulnessVotes']);

        // ✅ APPLY FILTERS
        if ($filterRating) {
            $query->rating($filterRating);
        }

        if ($searchQuery) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('title', 'like', "%{$searchQuery}%")
                  ->orWhere('comment', 'like', "%{$searchQuery}%");
            });
        }

        // ✅ APPLY SORTING
        switch ($sortBy) {
            case 'helpful':
                $query->mostHelpful();
                break;
            case 'rating_high':
                $query->orderBy('rating', 'desc');
                break;
            case 'rating_low':
                $query->orderBy('rating', 'asc');
                break;
            default: // newest
                $query->latest();
                break;
        }

        $reviews = $query->paginate(10);

        return view('reviews.all', compact('reviews', 'sortBy', 'filterRating'));
    }

    /**
     * Display reviews for a specific product
     */
    public function index(Product $product, Request $request)
    {
        $sortBy = $request->get('sort', 'newest'); // newest, oldest, helpful, rating_high, rating_low
        $filterRating = $request->get('rating'); // 1-5 stars filter
        $filterVerified = $request->get('verified', false); // verified purchases only
        $withPhotos = $request->get('photos', false); // reviews with photos only

        $query = $product->reviews()
            ->approved()
            ->with(['user', 'helpfulnessVotes']);

        // ✅ APPLY FILTERS
        if ($filterRating) {
            $query->rating($filterRating);
        }

        if ($filterVerified) {
            $query->verified();
        }

        if ($withPhotos) {
            $query->withPhotos();
        }

        // ✅ APPLY SORTING
        switch ($sortBy) {
            case 'oldest':
                $query->oldest();
                break;
            case 'helpful':
                $query->mostHelpful();
                break;
            case 'rating_high':
                $query->orderBy('rating', 'desc');
                break;
            case 'rating_low':
                $query->orderBy('rating', 'asc');
                break;
            default:
                $query->newest();
        }

        $reviews = $query->paginate(10);

        // ✅ CALCULATE REVIEW STATISTICS
        $stats = $this->getProductReviewStats($product);

        return view('reviews.product', compact('product', 'reviews', 'stats', 'sortBy', 'filterRating', 'filterVerified', 'withPhotos'));
    }

    /**
     * Search reviews across all products
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $rating = $request->get('rating');
        $sortBy = $request->get('sort', 'newest');

        $reviews = Review::approved()
            ->with(['user', 'product'])
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subQ) use ($query) {
                    $subQ->where('title', 'like', "%{$query}%")
                         ->orWhere('comment', 'like', "%{$query}%");
                });
            })
            ->when($rating, function ($q) use ($rating) {
                $q->rating($rating);
            })
            ->when($sortBy === 'helpful', function ($q) {
                $q->mostHelpful();
            })
            ->when($sortBy === 'newest', function ($q) {
                $q->newest();
            })
            ->paginate(15);

        return view('reviews.search', compact('reviews', 'query', 'rating', 'sortBy'));
    }

    /**
     * Show all reviews (public browse page)
     */
    // ================================================================================================
    // 📝 AUTHENTICATED USER REVIEW ROUTES
    // ================================================================================================

    /**
     * Store a new review
     */
    public function store(Request $request, Product $product)
    {
        // ✅ VALIDATION
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:200',
            'comment' => 'required|string|min:10|max:2000',
            'would_recommend' => 'nullable|boolean',
            'product_variant' => 'nullable|string|max:100',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = Auth::user();

        // ✅ CHECK IF USER ALREADY REVIEWED THIS PRODUCT
        $existingReview = Review::where('user_id', $user->id)
                               ->where('product_id', $product->id)
                               ->first();

        if ($existingReview) {
            return back()->with('error', 'You have already reviewed this product. You can edit your existing review instead.');
        }

        // ✅ CHECK IF USER PURCHASED THIS PRODUCT
        $verifiedPurchase = $user->orders()
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->where('status', 'completed') // Or whatever status indicates completed order
            ->exists();

        // ✅ HANDLE PHOTO UPLOADS
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('reviews/' . $product->id, 'public');
                $photoPaths[] = $path;
            }
        }

        // ✅ CREATE REVIEW
        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => $validated['rating'],
            'title' => $validated['title'],
            'comment' => $validated['comment'],
            'would_recommend' => $validated['would_recommend'] ?? null,
            'product_variant' => $validated['product_variant'],
            'photos' => $photoPaths ?: null,
            'verified_purchase' => $verifiedPurchase,
            'status' => 'pending', // Will be set by moderation service
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'last_updated_by_user' => now()
        ]);

        // ✅ HANDLE WITH AMAZON-STYLE MODERATION
        $moderationResult = $this->moderationService->handleNewReview($review);

        // ✅ UPDATE PRODUCT RATING CACHE
        $this->updateProductRatingCache($product);

        return back()->with('success', $moderationResult['message']);
    }

    /**
     * Get review data for editing (Amazon-style AJAX endpoint)
     */
    public function edit(Review $review)
    {
        // ✅ AUTHORIZATION
        if ($review->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        // ✅ AMAZON-STYLE EDIT RESTRICTIONS
        $canEdit = $review->created_at->diffInDays(now()) <= 90; // 90-day limit
        $isLocked = $review->helpful_count >= 20; // Lock highly helpful reviews

        if (!$canEdit) {
            return response()->json([
                'success' => false, 
                'message' => 'Reviews can only be edited within 90 days of posting.'
            ]);
        }

        if ($isLocked) {
            return response()->json([
                'success' => false, 
                'message' => 'This review has received many helpful votes and cannot be edited.'
            ]);
        }

        return response()->json([
            'success' => true,
            'review' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'title' => $review->title,
                'comment' => $review->comment,
                'would_recommend' => $review->would_recommend,
                'product_variant' => $review->product_variant,
                'photos' => $review->photos ?? []
            ]
        ]);
    }

    /**
     * Update an existing review
     */
    public function update(Request $request, Review $review)
    {
        // ✅ AUTHORIZATION
        if ($review->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        // ✅ AMAZON-STYLE EDIT RESTRICTIONS
        $canEdit = $review->created_at->diffInDays(now()) <= 90;
        $isLocked = $review->helpful_count >= 20;

        if (!$canEdit) {
            return response()->json([
                'success' => false, 
                'message' => 'Reviews can only be edited within 90 days of posting.'
            ]);
        }

        if ($isLocked) {
            return response()->json([
                'success' => false, 
                'message' => 'This review has received many helpful votes and cannot be edited.'
            ]);
        }

        // ✅ VALIDATION
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:200',
            'comment' => 'required|string|min:10|max:2000',
            'would_recommend' => 'nullable|boolean',
            'product_variant' => 'nullable|string|max:100',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'existing_photos' => 'nullable|array', // Keep these photos
        ]);

        // ✅ HANDLE PHOTO MANAGEMENT
        $existingPhotos = $request->input('existing_photos', []);
        $newPhotos = [];
        
        // Handle new photo uploads
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                if (count($existingPhotos) + count($newPhotos) >= 5) {
                    break; // Maximum 5 photos total
                }
                $path = $photo->store('reviews/' . $review->product_id, 'public');
                $newPhotos[] = $path;
            }
        }

        $allPhotos = array_merge($existingPhotos, $newPhotos);
        
        // Remove specified photos
        if ($request->has('remove_photos')) {
            foreach ($request->remove_photos as $index) {
                if (isset($currentPhotos[$index])) {
                    Storage::disk('public')->delete($currentPhotos[$index]);
                    unset($currentPhotos[$index]);
                }
            }
            $currentPhotos = array_values($currentPhotos); // Reindex array
        }

        // Add new photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                if (count($currentPhotos) < 5) { // Max 5 photos
                    $path = $photo->store('reviews/' . $review->product_id, 'public');
                    $currentPhotos[] = $path;
                }
            }
        }

        // ✅ UPDATE REVIEW
        $review->update([
            'rating' => $validated['rating'],
            'title' => $validated['title'],
            'comment' => $validated['comment'],
            'would_recommend' => $validated['would_recommend'] ?? null,
            'product_variant' => $validated['product_variant'],
            'photos' => $allPhotos ?: null,
            'status' => 'approved', // Re-approve after edit (Amazon style)
            'last_updated_by_user' => now()
        ]);

        // ✅ UPDATE PRODUCT RATING CACHE
        $this->updateProductRatingCache($review->product);

        return response()->json([
            'success' => true, 
            'message' => 'Your review has been updated successfully.'
        ]);
    }

    /**
     * Delete a review (Amazon-style)
     */
    public function destroy(Review $review)
    {
        // ✅ AUTHORIZATION
        if ($review->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        // ✅ DELETE PHOTOS FROM STORAGE
        if ($review->photos) {
            foreach ($review->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        $product = $review->product;
        $review->delete();

        // ✅ UPDATE PRODUCT RATING CACHE
        $this->updateProductRatingCache($product);

        return response()->json([
            'success' => true, 
            'message' => 'Your review has been deleted successfully.'
        ]);
    }

    // ================================================================================================
    // 📝 REVIEW INTERACTIONS
    // ================================================================================================

    /**
     * Mark review as helpful or not helpful
     */
    public function markHelpful(Request $request, Review $review)
    {
        $validated = $request->validate([
            'is_helpful' => 'required|boolean'
        ]);

        $user = Auth::user();

        // ✅ CHECK IF USER ALREADY VOTED
        $existingVote = ReviewHelpfulness::where('review_id', $review->id)
                                        ->where('user_id', $user->id)
                                        ->first();

        if ($existingVote) {
            // Update existing vote if different
            if ($existingVote->is_helpful !== $validated['is_helpful']) {
                $existingVote->update(['is_helpful' => $validated['is_helpful']]);
                $this->updateHelpfulnessCount($review);
                return response()->json(['success' => true, 'message' => 'Vote updated']);
            } else {
                return response()->json(['success' => false, 'message' => 'You have already voted']);
            }
        }

        // ✅ CREATE NEW VOTE
        ReviewHelpfulness::create([
            'review_id' => $review->id,
            'user_id' => $user->id,
            'is_helpful' => $validated['is_helpful'],
            'ip_address' => $request->ip()
        ]);

        $this->updateHelpfulnessCount($review);

        return response()->json(['success' => true, 'message' => 'Thank you for your feedback']);
    }

    /**
     * Report a review
     */
    public function report(Request $request, Review $review)
    {
        $validated = $request->validate([
            'reason' => 'required|string|in:spam,inappropriate,fake,offensive,other',
            'details' => 'nullable|string|max:500'
        ]);

        // ✅ HANDLE REPORT WITH AMAZON-STYLE POST-MODERATION
        $this->moderationService->handleReviewReport($review, $validated);

        return response()->json(['success' => true, 'message' => 'Review reported. Thank you for helping us maintain quality.']);
    }

    /**
     * Add photo to existing review
     */
    public function addPhoto(Request $request, Review $review)
    {
        // ✅ AUTHORIZATION
        if ($review->user_id !== Auth::id()) {
            abort(403, 'You can only add photos to your own reviews.');
        }

        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $currentPhotos = $review->photos ?: [];

        if (count($currentPhotos) >= 5) {
            return response()->json(['success' => false, 'message' => 'Maximum 5 photos allowed per review']);
        }

        $path = $request->file('photo')->store('reviews/' . $review->product_id, 'public');
        $currentPhotos[] = $path;

        $review->update(['photos' => $currentPhotos]);

        return response()->json([
            'success' => true, 
            'message' => 'Photo added successfully',
            'photo_url' => Storage::url($path)
        ]);
    }

    // ================================================================================================
    // 🛠️ HELPER METHODS
    // ================================================================================================

    /**
     * Get review statistics for a product
     */
    private function getProductReviewStats(Product $product): array
    {
        $reviews = $product->reviews()->approved();
        
        $stats = [
            'total_reviews' => $reviews->count(),
            'average_rating' => round($reviews->avg('rating'), 1),
            'verified_count' => $reviews->where('verified_purchase', true)->count(),
            'with_photos_count' => $reviews->whereNotNull('photos')->count(),
            'rating_breakdown' => []
        ];

        // Rating breakdown (1-5 stars)
        for ($i = 1; $i <= 5; $i++) {
            $count = $reviews->where('rating', $i)->count();
            $percentage = $stats['total_reviews'] > 0 ? round(($count / $stats['total_reviews']) * 100) : 0;
            
            $stats['rating_breakdown'][$i] = [
                'count' => $count,
                'percentage' => $percentage
            ];
        }

        return $stats;
    }

    /**
     * Update helpfulness count for a review
     */
    private function updateHelpfulnessCount(Review $review): void
    {
        $helpfulCount = $review->helpfulnessVotes()->where('is_helpful', true)->count();
        $notHelpfulCount = $review->helpfulnessVotes()->where('is_helpful', false)->count();

        $review->update([
            'helpful_count' => $helpfulCount,
            'not_helpful_count' => $notHelpfulCount
        ]);
    }

    /**
     * Update product rating cache
     */
    private function updateProductRatingCache(Product $product): void
    {
        $avgRating = $product->reviews()->approved()->avg('rating');
        $reviewCount = $product->reviews()->approved()->count();

        // Assuming you have these fields in products table
        $product->update([
            'average_rating' => $avgRating ? round($avgRating, 1) : null,
            'review_count' => $reviewCount
        ]);
    }
}
