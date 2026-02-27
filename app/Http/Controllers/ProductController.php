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
            ['name' => 'Products', 'url' => route('admin.products.index')]
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

    // ================================================================================================
    // 🛍️ AMAZON-STYLE SHOP & PRODUCT LISTING
    // ================================================================================================

    /**
     * Main shop page with filters and products
     */
    public function shop(Request $request)
    {
        // Get categories for filter sidebar
        $categories = Category::withCount('products')->get();
        
        // Price range for filter
        $priceRange = [
            'min' => Product::min('price') ?? 0,
            'max' => Product::max('price') ?? 1000
        ];
        
        // Brand options (if you have brands)
        $brands = [];
        
        // Rating options
        $ratings = [5, 4, 3, 2, 1];
        
        // Set SEO data
        $this->setSEO('shop', [
            'category' => $request->category,
            'search' => $request->q
        ]);

        return view('shop.index', compact('categories', 'priceRange', 'brands', 'ratings'));
    }

    /**
     * AJAX endpoint for getting filtered products
     */
    public function getProducts(Request $request)
    {
        // Debug: Log the incoming request parameters
        \Log::info('Shop filter request:', $request->all());
        
        $query = Product::with(['category', 'reviews'])
            ->where('status', true);

        // ✅ APPLY FILTERS
        
        // Category filter
        if ($request->has('category') && !empty($request->category)) {
            if (is_array($request->category)) {
                $query->whereIn('category_id', $request->category);
            } else {
                $query->where('category_id', $request->category);
            }
        }

        // Price range filter
        if ($request->has('price_min') && $request->price_min !== null && $request->price_min !== '') {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->has('price_max') && $request->price_max !== null && $request->price_max !== '') {
            $query->where('price', '<=', $request->price_max);
        }

        // Rating filter
        if ($request->has('rating') && $request->rating) {
            $query->where('average_rating', '>=', $request->rating);
        }

        // Search filter
        if ($request->has('q') && $request->q) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Note: Removed brand and stock_quantity filters as these columns don't exist in current schema

        // ✅ APPLY SORTING
        $sortBy = $request->get('sort', 'name_asc');
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'rating_desc':
                $query->orderBy('average_rating', 'desc');
                break;
            case 'newest':
                $query->latest();
                break;
            case 'popularity':
                $query->orderBy('review_count', 'desc');
                break;
            case 'name_asc':
            default:
                $query->orderBy('name', 'asc');
                break;
        }

        // ✅ PAGINATION
        $perPage = $request->get('per_page', 12);
        $products = $query->paginate($perPage);

        // ✅ GET WISHLIST STATUS FOR AUTHENTICATED USERS
        $wishlistProductIds = collect();
        if (auth()->check()) {
            $wishlistProductIds = auth()->user()->wishlist()->pluck('product_id');
        }

        // ✅ RETURN JSON FOR AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('shop.partials.product-grid', compact('products', 'wishlistProductIds'))->render(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ],
                'filters_applied' => $this->getAppliedFiltersCount($request),
                'results_text' => $this->getResultsText($products)
            ]);
        }

        return redirect()->route('shop.index');
    }

    /**
     * AJAX endpoint for getting filter options
     */
    public function getFilters(Request $request)
    {
        $categories = Category::withCount('products')->get();
        
        $priceRange = [
            'min' => Product::min('price') ?? 0,
            'max' => Product::max('price') ?? 1000
        ];
        
        $brands = Product::select('brand')->distinct()->whereNotNull('brand')->pluck('brand');

        return response()->json([
            'success' => true,
            'categories' => $categories,
            'price_range' => $priceRange,
            'brands' => $brands
        ]);
    }

    /**
     * Helper method to count applied filters
     */
    private function getAppliedFiltersCount(Request $request): int
    {
        $count = 0;
        
        if ($request->has('category') && $request->category) $count++;
        if ($request->has('price_min') && $request->price_min) $count++;
        if ($request->has('price_max') && $request->price_max) $count++;
        if ($request->has('brand') && $request->brand) $count++;
        if ($request->has('rating') && $request->rating) $count++;
        if ($request->has('q') && $request->q) $count++;
        if ($request->has('in_stock') && $request->in_stock) $count++;
        
        return $count;
    }

    /**
     * Helper method to get results text
     */
    private function getResultsText($products): string
    {
        $total = $products->total();
        $from = $products->firstItem();
        $to = $products->lastItem();
        
        if ($total == 0) {
            return "No products found";
        }
        
        return "Showing {$from}-{$to} of {$total} results";
    }

    /**
     * Load more products for AJAX pagination
     */
    public function loadMore(Request $request)
    {
        $perPage = 12; // Match the default products per page
        $page = $request->input('page', 1);
        
        // Apply the same filters as in getProducts method
        $query = Product::where('status', true);
        
        // Apply filters
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        if ($request->filled('rating')) {
            $query->where('average_rating', '>=', $request->rating);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        
        // Get products with pagination
        $products = $query->with(['stocks', 'reviews'])->paginate($perPage, ['*'], 'page', $page);
        
        // Get wishlist product IDs if user is authenticated
        $wishlistProductIds = collect();
        if (auth()->check()) {
            $wishlistProductIds = auth()->user()->wishlist()->pluck('product_id');
        }
        
        // Render the HTML using the existing partial
        $html = view('shop.partials.product-grid', compact('products', 'wishlistProductIds'))->render();
        
        return response()->json([
            'html' => $html,
            'hasMorePages' => $products->hasMorePages(),
            'nextPage' => $products->currentPage() + 1,
            'total' => $products->total(),
            'currentCount' => $products->count(),
            'currentPage' => $products->currentPage()
        ]);
    }

    /**
     * Get search suggestions for autocomplete
     */
    public function getSearchSuggestions(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = [];
        
        // Get product name suggestions
        $products = Product::where('status', true)
            ->where('name', 'LIKE', "%{$query}%")
            ->select('name')
            ->distinct()
            ->limit(8)
            ->get();

        foreach ($products as $product) {
            $suggestions[] = [
                'type' => 'product',
                'text' => $product->name,
                'value' => $product->name,
                'icon' => 'fas fa-box'
            ];
        }

        // Get category suggestions
        $categories = \App\Models\Category::where('name', 'LIKE', "%{$query}%")
            ->select('name')
            ->limit(3)
            ->get();

        foreach ($categories as $category) {
            $suggestions[] = [
                'type' => 'category',
                'text' => "in " . $category->name,
                'value' => $category->name,
                'icon' => 'fas fa-tags'
            ];
        }

        // Get brand suggestions if you have brands
        // You can uncomment this if you add a brands table/field
        /*
        $brands = Product::where('status', true)
            ->where('brand', 'LIKE', "%{$query}%")
            ->select('brand')
            ->distinct()
            ->limit(3)
            ->get();

        foreach ($brands as $brand) {
            $suggestions[] = [
                'type' => 'brand',
                'text' => "Brand: " . $brand->brand,
                'value' => $brand->brand,
                'icon' => 'fas fa-star'
            ];
        }
        */

        return response()->json($suggestions);
    }

    // ================================================================================================
    // 🔧 ADMIN PRODUCT MANAGEMENT WITH IMAGE OPTIMIZATION
    // ================================================================================================

    /**
     * Store a new product with optimized images (Admin)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'gallery_images' => 'nullable|array|max:10',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $validated = $request->validated();

        // Generate slug
        $validated['slug'] = \Str::slug($validated['name']);

        // Handle main image upload and optimization
        if ($request->hasFile('image')) {
            $optimizedImages = \App\Helpers\ImageOptimizer::optimizeUploadedImage(
                $request->file('image'), 
                'products',
                [
                    'quality' => 85,
                    'maxWidth' => 1200,
                    'maxHeight' => 1200,
                    'generateWebP' => true,
                    'generateThumbnails' => true,
                    'thumbnailSizes' => [150, 300, 600]
                ]
            );
            $validated['image'] = $optimizedImages['optimized'];
        }

        $product = Product::create($validated);

        // Handle gallery images
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $index => $galleryImage) {
                $optimizedImages = \App\Helpers\ImageOptimizer::optimizeUploadedImage(
                    $galleryImage, 
                    'products/gallery',
                    [
                        'quality' => 85,
                        'maxWidth' => 1200,
                        'maxHeight' => 1200,
                        'generateWebP' => true,
                        'generateThumbnails' => true,
                        'thumbnailSizes' => [150, 300, 600]
                    ]
                );

                \App\Models\ProductMedia::create([
                    'product_id' => $product->id,
                    'media_type' => 'image',
                    'file_path' => $optimizedImages['optimized'],
                    'sort_order' => $index + 1
                ]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully with optimized images!');
    }

    /**
     * Update product with optimized images (Admin)
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'gallery_images' => 'nullable|array|max:10',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'remove_gallery_images' => 'nullable|array',
        ]);

        $validated = $request->validated();
        $validated['slug'] = \Str::slug($validated['name']);

        // Handle main image replacement
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $optimizedImages = \App\Helpers\ImageOptimizer::optimizeUploadedImage(
                $request->file('image'), 
                'products',
                [
                    'quality' => 85,
                    'maxWidth' => 1200,
                    'maxHeight' => 1200,
                    'generateWebP' => true,
                    'generateThumbnails' => true,
                    'thumbnailSizes' => [150, 300, 600]
                ]
            );
            $validated['image'] = $optimizedImages['optimized'];
        }

        $product->update($validated);

        // Handle gallery image removal
        if ($request->has('remove_gallery_images')) {
            $mediaToRemove = \App\Models\ProductMedia::where('product_id', $product->id)
                ->whereIn('id', $request->remove_gallery_images)
                ->get();

            foreach ($mediaToRemove as $media) {
                if (Storage::disk('public')->exists($media->file_path)) {
                    Storage::disk('public')->delete($media->file_path);
                }
                $media->delete();
            }
        }

        // Handle new gallery images
        if ($request->hasFile('gallery_images')) {
            $maxSortOrder = $product->media()->max('sort_order') ?? 0;
            
            foreach ($request->file('gallery_images') as $index => $galleryImage) {
                $optimizedImages = \App\Helpers\ImageOptimizer::optimizeUploadedImage(
                    $galleryImage, 
                    'products/gallery',
                    [
                        'quality' => 85,
                        'maxWidth' => 1200,
                        'maxHeight' => 1200,
                        'generateWebP' => true,
                        'generateThumbnails' => true,
                        'thumbnailSizes' => [150, 300, 600]
                    ]
                );

                \App\Models\ProductMedia::create([
                    'product_id' => $product->id,
                    'media_type' => 'image',
                    'file_path' => $optimizedImages['optimized'],
                    'sort_order' => $maxSortOrder + $index + 1
                ]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully with optimized images!');
    }

    /**
     * AJAX upload for product image preview (Admin)
     */
    public function uploadImagePreview(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        try {
            $optimizedImages = \App\Helpers\ImageOptimizer::optimizeUploadedImage(
                $request->file('image'), 
                'temp/products',
                [
                    'quality' => 85,
                    'maxWidth' => 1200,
                    'maxHeight' => 1200,
                    'generateWebP' => true,
                    'generateThumbnails' => true,
                    'thumbnailSizes' => [150, 300]
                ]
            );

            return response()->json([
                'success' => true,
                'image_url' => Storage::url($optimizedImages['optimized']),
                'thumbnail_url' => Storage::url($optimizedImages['thumbnails'][300] ?? $optimizedImages['optimized']),
                'file_path' => $optimizedImages['optimized']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload and optimize image: ' . $e->getMessage()
            ], 500);
        }
    }
    
}