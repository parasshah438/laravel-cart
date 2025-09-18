<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Wishlist;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Review;
use App\Services\ReviewModerationService;
use Illuminate\Http\Request;
use App\Models\RecentlyViewedProduct;
use App\Http\Controllers\Traits\SEOTrait;
use Session;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use SEOTrait;

    protected $moderationService;

    public function __construct(ReviewModerationService $moderationService)
    {
        $this->moderationService = $moderationService;
        $this->initializeSEO();
    }

    /**
     * Display product listing
     */
    public function index(Request $request)
    {
        $products = Product::with(['category', 'reviews'])
            ->where('status', 'active')
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('description', 'LIKE', "%{$search}%");
            })
            ->when($request->category, function ($query, $category) {
                return $query->whereHas('category', function ($q) use ($category) {
                    $q->where('slug', $category);
                });
            })
            ->paginate(12);

        // Set SEO data
        $this->setSEO('products', [
            'search' => $request->search,
            'category' => $request->category
        ]);

        // Set breadcrumbs
        $breadcrumbs = [
            ['name' => 'Home', 'url' => route('front.index')],
            ['name' => 'Products', 'url' => route('products.index')]
        ];

        if ($request->category) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $breadcrumbs[] = [
                    'name' => $category->name,
                    'url' => route('categories.show', $category->slug)
                ];
            }
        }

        $this->setSEO('products', [], $breadcrumbs);

        return view('products.index', compact('products'));
    }

    /**
     * Display single product
     */
    public function show($slug)
    {
        $product = Product::with(['category', 'reviews.user', 'images'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        // Track recently viewed
        $this->trackRecentlyViewed($product);

        // Set SEO data
        $breadcrumbs = $this->generateProductBreadcrumbs($product);
        $this->setSEO('product', $product, $breadcrumbs);

        // Add structured data
        $structuredData = $this->addProductStructuredData($product);
        view()->share('productSchema', json_encode($structuredData, JSON_UNESCAPED_SLASHES));

        // Enhanced reviews with quality indicators
        $reviews = $product->reviews()->approved()->with('user')->latest()->get();
        $reviewsWithIndicators = $reviews->map(function ($review) {
            $indicators = $this->moderationService->getReviewQualityIndicators($review);
            $review->quality_indicators = $indicators;
            $review->should_highlight = $this->moderationService->shouldHighlightReview($review);
            return $review;
        });

        // Related products
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->take(4)
            ->get();

        // Set cache headers for product pages
        $this->setCacheHeaders(30); // 30 minutes

        return view('products.show', compact('product', 'relatedProducts', 'reviewsWithIndicators'));
    }

    /**
     * Search products
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return redirect()->route('products.index');
        }

        $products = Product::with(['category', 'reviews'])
            ->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('sku', 'LIKE', "%{$query}%")
                  ->orWhereHas('category', function ($categoryQuery) use ($query) {
                      $categoryQuery->where('name', 'LIKE', "%{$query}%");
                  });
            })
            ->paginate(12);

        // Set SEO data for search results
        $this->setSEO('search', ['query' => $query]);

        // Set breadcrumbs
        $breadcrumbs = [
            ['name' => 'Home', 'url' => route('front.index')],
            ['name' => 'Search Results', 'url' => route('products.search', ['q' => $query])]
        ];

        $this->setSEO('search', ['query' => $query], $breadcrumbs);

        // Set robots tag for search pages
        $this->setRobotsTag(false, true, ['noarchive']);

        return view('products.search', compact('products', 'query'));
    }

    /**
     * Track recently viewed products
     */
    private function trackRecentlyViewed($product)
    {
        $recentlyViewed = session()->get('recently_viewed', []);
        
        // Remove if already exists
        $recentlyViewed = array_filter($recentlyViewed, function ($id) use ($product) {
            return $id != $product->id;
        });
        
        // Add to beginning
        array_unshift($recentlyViewed, $product->id);
        
        // Keep only last 10
        $recentlyViewed = array_slice($recentlyViewed, 0, 10);
        
        session()->put('recently_viewed', $recentlyViewed);
    }

    public function trending()
    {
        $trendingProducts = Product::withCount(['views' => function ($query) {
            $query->where('created_at', '>=', now()->subDays(7)); // last 7 days
        }])
        ->orderByDesc('views_count')
        ->limit(10)
        ->get();

        return view('products.trending', compact('trendingProducts'));
    }

    public function getRecentlyViewedProducts()
    {
        $query = RecentlyViewedProduct::with('product')
            ->orderByDesc('updated_at')
            ->limit(10);

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('session_id', Session::getId());
        }

        $recentlyViewed = $query->get()->pluck('product');

        return view('products.recently-viewed', compact('recentlyViewed'));
    }

   public function recommendedProducts()
    {
        $sessionId = session()->get('cart_session_id');
        $userId = auth()->id();

        // 1. Get viewed products
        $viewedProductIds = RecentlyViewedProduct::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId, fn($q) => $q->where('session_id', $sessionId))
            ->orderByDesc('updated_at')
            ->pluck('product_id')
            ->toArray();

        // 2. Get cart items
        $cartIds = Cart::query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId, fn($q) => $q->where('session_id', $sessionId))
            ->pluck('id');

        $cartProductIds = CartItem::whereIn('cart_id', $cartIds)
            ->pluck('product_id')
            ->toArray();

        // 3. Wishlist items
        $wishlistProductIds = $userId
            ? Wishlist::where('user_id', $userId)->pluck('product_id')->toArray()
            : [];

        // 4. Merge all and remove duplicates
        $allProductIds = array_unique(array_merge($viewedProductIds, $cartProductIds, $wishlistProductIds));

        // 5. Recommended logic — same category, not already viewed
        $recommendedProducts = Product::whereIn('category_id', function ($query) use ($allProductIds) {
                $query->select('category_id')
                    ->from('products')
                    ->whereIn('id', $allProductIds);
            })
            ->whereNotIn('id', $allProductIds)
            ->inRandomOrder()
            ->take(10)
            ->get();

        return view('products.recommended', compact('recommendedProducts'));
    }

    public function showProduct($slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        // Record the product as recently viewed
        $sessionId = session()->get('cart_session_id');
        if (!$sessionId) {
            $sessionId = Str::uuid();
            session()->put('cart_session_id', $sessionId);
        }

        $query = RecentlyViewedProduct::where('product_id', $product->id);

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('session_id', $sessionId);
        }

        $existing = $query->first();

        if ($existing) {
            $existing->touch();
        } else {
            RecentlyViewedProduct::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'session_id' => auth()->check() ? null : $sessionId,
            ]);
        }

        $wishlistProductIds = auth()->check()
            ? auth()->user()->wishlist()->pluck('product_id')
            : collect();

        $similarProducts = Product::where('id', '!=', $product->id)
            ->inRandomOrder()
            ->take(10)
            ->get();
        
        // Check if product is in cart (for displaying quantity controls)
        $cartItem = null;
        if (auth()->check()) {
            $cart = auth()->user()->cart;
            if ($cart) {
                $cartItem = $cart->items()->where('product_id', $product->id)->first();
            }
        }

        // ================================================================================================
        // 📝 LOAD REVIEWS DATA FOR PRODUCT PAGE (Amazon Style)
        // ================================================================================================
        
        // Get recent reviews (limit for initial display)
        $reviews = $product->reviews()
            ->approved()
            ->with(['user', 'helpfulnessVotes'])
            ->latest()
            ->limit(5)
            ->get();

        // Enhanced reviews with quality indicators
        $reviewsWithIndicators = $reviews->map(function ($review) {
            $indicators = $this->moderationService->getReviewQualityIndicators($review);
            $review->quality_indicators = $indicators;
            $review->should_highlight = $this->moderationService->shouldHighlightReview($review);
            return $review;
        });

        // Calculate review statistics
        $reviewStats = [
            'average_rating' => $product->average_rating,
            'total_reviews' => $product->review_count,
            'rating_breakdown' => $this->getProductRatingBreakdown($product),
            'verified_percentage' => $this->getVerifiedPercentage($product),
            'has_reviews' => $product->hasReviews(),
            'recent_reviews_count' => $reviews->count(),
            'all_reviews_url' => route('product.reviews', $product)
        ];
            
        return view('products.show', compact('product','wishlistProductIds','similarProducts','cartItem', 'reviewsWithIndicators', 'reviewStats'));
    }

    /**
     * Get rating breakdown for product
     */
    private function getProductRatingBreakdown($product): array
    {
        $breakdown = [];
        $totalReviews = $product->review_count;
        
        for ($rating = 1; $rating <= 5; $rating++) {
            $count = $product->reviews()->approved()->where('rating', $rating)->count();
            $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
            
            $breakdown[$rating] = [
                'count' => $count,
                'percentage' => $percentage
            ];
        }
        
        return $breakdown;
    }

    /**
     * Get verified purchase percentage
     */
    private function getVerifiedPercentage($product): int
    {
        $total = $product->review_count;
        if ($total === 0) return 0;
        
        $verified = $product->reviews()->approved()->where('verified_purchase', true)->count();
        return round(($verified / $total) * 100);
    }
    
}