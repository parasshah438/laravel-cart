<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Slider;
use App\Models\Category;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Cart;

class FrontendController extends Controller
{
    protected $cart;

    public function __construct(CartService $cart)
    {
        $this->cart = $cart;
    }
    
    public function index(Request $request)
    {
        $perPage = 12;
        $searchQuery = $request->get('search');
        $categorySlug = $request->get('category');
        
        // Build products query
        $productsQuery = Product::with(['stocks', 'category']);
        
        // Apply category filter if provided
        if ($categorySlug) {
            $category = Category::where('slug', $categorySlug)->first();
            if ($category) {
                // Get all descendant category IDs (including the category itself)
                $categoryIds = $this->getAllCategoryIds($category);
                $productsQuery->whereIn('category_id', $categoryIds);
            }
        }
        
        // Apply search filter if provided
        if ($searchQuery) {
            $productsQuery->where(function($query) use ($searchQuery) {
                $query->where('name', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('description', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('slug', 'LIKE', "%{$searchQuery}%");
            });
        }
        
        $products = $productsQuery->latest()->paginate($perPage);

        $sliders = Slider::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Load categories for mega menu (only parent categories with their children)
        $categories = Category::with(['children.children'])
            ->whereNull('parent_id')
            ->where('status', true)
            ->orderBy('sort_order')
            ->get();

        /** @var \App\Models\User|null $authUser */
        $authUser = Auth::user();
        $wishlistProductIds = $authUser
            ? $authUser->wishlist()->pluck('product_id')
            : collect();


        //AJAX (Load More)
        if ($request->ajax()) {
            $html = view('partials._product_cards', compact('products', 'wishlistProductIds'))->render();
            return response()->json([
                'html' => $html,
                'nextPage' => $products->currentPage() + 1,
                'hasMorePages' => $products->hasMorePages()
            ]);
        }

        $cartCount = $this->cart->getCartItems(false)->count();

        return view('welcome', compact('products', 'wishlistProductIds', 'sliders', 'searchQuery', 'categories', 'categorySlug', 'cartCount'));
    }

    /**
     * Get all category IDs including descendants
     */
    private function getAllCategoryIds($category)
    {
        $categoryIds = [$category->id];
        
        // Get direct children
        $children = $category->children;
        foreach ($children as $child) {
            $categoryIds = array_merge($categoryIds, $this->getAllCategoryIds($child));
        }
        
        return $categoryIds;
    }

    /**
     * Display products for a specific category with full AJAX filter/sort/search support
     */
    public function categoryProducts(Request $request, $slug)
    {
        // Find the category by slug
        $category = Category::where('slug', $slug)->first();

        if (!$category) {
            abort(404, 'Category not found');
        }

        // Get all descendant category IDs (including the category itself)
        $categoryIds = $this->getAllCategoryIds($category);

        // Build products query
        $productsQuery = Product::with(['stocks', 'category'])
            ->whereIn('category_id', $categoryIds)
            ->where('status', true);

        // Subcategory filter
        if ($request->filled('subcategory')) {
            $requestedIds = (array) $request->subcategory;
            $validIds = array_intersect($requestedIds, $categoryIds);
            if (!empty($validIds)) {
                $productsQuery->whereIn('category_id', $validIds);
            }
        }

        // Search filter
        if ($request->filled('q')) {
            $searchQuery = $request->q;
            $productsQuery->where(function ($query) use ($searchQuery) {
                $query->where('name', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('description', 'LIKE', "%{$searchQuery}%");
            });
        }

        // Price range filter
        if ($request->filled('price_min')) {
            $productsQuery->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $productsQuery->where('price', '<=', $request->price_max);
        }

        // Rating filter
        if ($request->filled('rating')) {
            $productsQuery->where('average_rating', '>=', $request->rating);
        }

        // Discount % filter (sale_products): discount_pct >= discount_min
        // discount_pct = ((original_price - sale_price) / original_price) * 100
        // Only apply when discount_min is one of [10,25,50]
        $discountMin = $request->input('discount_min');
        $allowedDiscountMins = [10, 25, 50];
        if (in_array((int) $discountMin, $allowedDiscountMins, true)) {
            $min = (int) $discountMin;
            $productsQuery->whereHas('saleProducts', function ($q) use ($min) {
                // Scope to active sale events only
                $q->whereHas('saleEvent', function ($sq) {
                    $sq->where('status', 'active')
                       ->where('starts_at', '<=', now())
                       ->where('ends_at', '>=', now());
                })
                ->whereColumn('sale_products.sale_price', '<', 'sale_products.original_price')
                ->whereRaw(
                    'CASE ' .
                    'WHEN sale_products.original_price <= 0 THEN 0 ' .
                    'ELSE ((sale_products.original_price - sale_products.sale_price) / sale_products.original_price) * 100 ' .
                    'END >= ?',
                    [$min]
                );
            });
        }





        // New Arrivals filter
        if ($request->filled('new_arrivals')) {
            $days = (int) $request->new_arrivals;
            if (in_array($days, [30, 90])) {
                $productsQuery->where('created_at', '>=', now()->subDays($days));
            }
        }

        // Availability filter using product_stocks table
        if ($request->filled('availability')) {
            switch ($request->availability) {
                case 'in_stock':
                    $productsQuery->whereHas('productStocks', function ($query) {
                        $query->where('status', 'active')
                            ->where('qty', '>', 0);
                    });
                    break;

                case 'out_of_stock':
                    $productsQuery->whereDoesntHave('productStocks', function ($query) {
                        $query->where('status', 'active')
                            ->where('qty', '>', 0);
                    });
                    break;
            }
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_asc':
                $productsQuery->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $productsQuery->orderBy('price', 'desc');
                break;
            case 'rating_desc':
                $productsQuery->orderBy('average_rating', 'desc');
                break;
            case 'name_asc':
                $productsQuery->orderBy('name', 'asc');
                break;
            case 'popularity':
                $productsQuery->orderBy('review_count', 'desc');
                break;
            case 'newest':
            default:
                $productsQuery->latest();
                break;
        }

        $perPage = (int) $request->get('per_page', 12);
        $products = $productsQuery->paginate($perPage);

        /** @var \App\Models\User|null $authUser */
        $authUser = Auth::user();
        $wishlistProductIds = $authUser
            ? $authUser->wishlist()->pluck('product_id')
            : collect();

        // AJAX response (filters, load more, search)
        if ($request->ajax()) {
            $html = view('partials._product_cards', compact('products', 'wishlistProductIds'))->render();

            $total = $products->total();
            $from  = $products->firstItem();
            $to    = $products->lastItem();
            $resultsText = $total === 0
                ? 'No products found'
                : "Showing {$from}-{$to} of {$total} results";

            $trendingSearches = $total === 0
                ? $this->getCategoryTrendingSearches($category, $categoryIds, $request->input('q'))
                : [];

            return response()->json([
                'success'           => true,
                'html'              => $html,
                'trending_searches'  => $trendingSearches,
                'pagination'        => [
                    'current_page'   => $products->currentPage(),
                    'last_page'      => $products->lastPage(),
                    'total'          => $total,
                    'per_page'       => $products->perPage(),
                    'from'           => $from,
                    'to'             => $to,
                    'has_more_pages' => $products->hasMorePages(),
                    'next_page'      => $products->currentPage() + 1,
                ],
                'results_text'      => $resultsText,
                'filters_applied'   => $this->getCategoryAppliedFiltersCount($request),
            ]);
        }

        // Data for full page render
        $categories = Category::with(['children.children'])
            ->whereNull('parent_id')
            ->where('status', true)
            ->orderBy('sort_order')
            ->get();

        $priceRange = [
            'min' => Product::whereIn('category_id', $categoryIds)->min('price') ?? 0,
            'max' => Product::whereIn('category_id', $categoryIds)->max('price') ?? 1000,
        ];

        $breadcrumbs = $this->getBreadcrumbs($category);
        $searchQuery = $request->get('q');

        return view('category-products', compact(
            'products', 'wishlistProductIds', 'searchQuery',
            'categories', 'category', 'breadcrumbs', 'priceRange'
        ));
    }

    /**
     * Count how many filters are applied for a category request
     */
    private function getCategoryAppliedFiltersCount(Request $request): int
    {
        $count = 0;
        if ($request->filled('q'))            $count++;
        if ($request->filled('price_min'))    $count++;
        if ($request->filled('price_max'))    $count++;
        if ($request->filled('rating'))       $count++;
        if ($request->filled('new_arrivals')) $count++;
        if ($request->filled('availability')) $count++;
        if (in_array((int) $request->input('discount_min'), [10, 25, 50], true)) $count++;
        if ($request->filled('subcategory'))  $count++;
        return $count;
    }

    /**
     * Build trending search terms for empty category results
     */
    private function getCategoryTrendingSearches($category, array $categoryIds, ?string $query = null, int $limit = 6): array
    {
        $searchTerms = collect();

        $topProducts = Product::where('status', true)
            ->whereIn('category_id', $categoryIds)
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->orderByDesc('review_count')
            ->orderByDesc('created_at')
            ->limit(20)
            ->pluck('name');

        foreach ($topProducts as $productName) {
            $searchTerms->push($productName);
        }

        $relatedCategories = Category::whereIn('id', $categoryIds)
            ->orderBy('sort_order')
            ->pluck('name');

        foreach ($relatedCategories as $categoryName) {
            $searchTerms->push($categoryName);
        }

        $fallbackTerms = [
            'Best Sellers',
            'New Arrivals',
            'Top Rated',
            'Trending Now',
            $category->name,
        ];

        foreach ($fallbackTerms as $term) {
            $searchTerms->push($term);
        }

        return $searchTerms
            ->filter()
            ->map(function ($term) {
                return trim($term);
            })
            ->unique(function ($term) {
                return mb_strtolower($term);
            })
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * Search suggestions scoped to a category
     */
    public function categorySearchSuggestions(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->first();
        if (!$category) {
            return response()->json([]);
        }

        $query = $request->input('q', '');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $categoryIds = $this->getAllCategoryIds($category);
        $suggestions = [];

        $products = Product::where('status', true)
            ->whereIn('category_id', $categoryIds)
            ->where('name', 'LIKE', "%{$query}%")
            ->select('name')
            ->distinct()
            ->limit(8)
            ->get();

        // Result count per product-name suggestion (exact name match count)
        $productCounts = Product::where('status', true)
            ->whereIn('category_id', $categoryIds)
            ->where('name', 'LIKE', "%{$query}%")
            ->selectRaw('name, COUNT(*) as cnt')
            ->groupBy('name')
            ->limit(8)
            ->pluck('cnt', 'name');

        foreach ($products as $product) {
            $suggestions[] = [
                'type'  => 'product',
                'text'  => $product->name,
                'value' => $product->name,
                'icon'  => 'fas fa-box',
                'count' => (int) ($productCounts[$product->name] ?? 0),
            ];
        }

        $subcategories = Category::whereIn('id', $categoryIds)
            ->where('name', 'LIKE', "%{$query}%")
            ->select('id', 'name')
            ->limit(3)
            ->get();

        foreach ($subcategories as $subcat) {
            $subIds   = $this->getAllCategoryIds($subcat);
            $subCount = Product::where('status', true)
                ->whereIn('category_id', $subIds)
                ->count();
            $suggestions[] = [
                'type'  => 'category',
                'text'  => 'in ' . $subcat->name,
                'value' => $subcat->name,
                'icon'  => 'fas fa-tags',
                'count' => $subCount,
            ];
        }

        // "Did you mean?" typo correction — triggered when no LIKE matches found
        if (empty($suggestions) && strlen($query) >= 3) {
            $allNames = Product::where('status', true)
                ->whereIn('category_id', $categoryIds)
                ->select('name')
                ->distinct()
                ->limit(300)
                ->pluck('name');

            $best      = null;
            $bestScore = 0;

            foreach ($allNames as $name) {
                foreach (preg_split('/\s+/', strtolower($name)) as $word) {
                    if (strlen($word) < 3) {
                        continue;
                    }
                    similar_text(strtolower($query), $word, $pct);
                    if ($pct > $bestScore && $pct >= 62) {
                        $bestScore = $pct;
                        $best      = $word;
                    }
                }
            }

            if ($best && strtolower($best) !== strtolower($query)) {
                $suggestions[] = [
                    'type'  => 'did_you_mean',
                    'text'  => $best,
                    'value' => $best,
                    'icon'  => 'fas fa-spell-check',
                ];
            }
        }

        return response()->json($suggestions);
    }

    /**
     * Trending search terms for a category (lightweight, cacheable)
     */
    public function categoryTrending(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->first();
        if (!$category) {
            return response()->json([]);
        }
        $categoryIds = $this->getAllCategoryIds($category);
        $terms = $this->getCategoryTrendingSearches($category, $categoryIds, null, 6);
        return response()->json($terms);
    }

    /**
     * Save a search query to the authenticated user's history
     */
    public function saveSearchHistory(Request $request, $slug)
    {
        $query = trim($request->input('q', ''));
        if (!$query || strlen($query) > 255) {
            return response()->json(['ok' => false]);
        }

        \DB::table('search_histories')->updateOrInsert(
            [
                'user_id'       => auth()->id(),
                'category_slug' => $slug,
                'query_hash'    => md5(mb_strtolower($query)),
            ],
            [
                'query'       => $query,
                'searched_at' => now(),
            ]
        );

        // Keep only the latest 10 entries per user+category
        $ids = \DB::table('search_histories')
            ->where('user_id', auth()->id())
            ->where('category_slug', $slug)
            ->orderByDesc('searched_at')
            ->skip(10)
            ->take(PHP_INT_MAX)
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            \DB::table('search_histories')->whereIn('id', $ids)->delete();
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Get the authenticated user's recent searches for a category
     */
    public function getSearchHistory(Request $request, $slug)
    {
        $rows = \DB::table('search_histories')
            ->where('user_id', auth()->id())
            ->where('category_slug', $slug)
            ->orderByDesc('searched_at')
            ->limit(5)
            ->pluck('query');

        return response()->json($rows);
    }

    /**
     * Remove a single search history item
     */
    public function removeSearchHistoryItem(Request $request, $slug, $query)
    {
        \DB::table('search_histories')
            ->where('user_id', auth()->id())
            ->where('category_slug', $slug)
            ->where('query_hash', md5(mb_strtolower(urldecode($query))))
            ->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Clear all search history for user+category
     */
    public function clearSearchHistory(Request $request, $slug)
    {
        \DB::table('search_histories')
            ->where('user_id', auth()->id())
            ->where('category_slug', $slug)
            ->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Get breadcrumbs for category
     */
    private function getBreadcrumbs($category)
    {
        $breadcrumbs = [];
        $current = $category;
        
        while ($current) {
            array_unshift($breadcrumbs, $current);
            $current = $current->parent;
        }
        
        return $breadcrumbs;
    }

    /**
     * Handle search suggestions via AJAX
     */
    public function searchSuggestions(Request $request)
    {
        $searchQuery = $request->get('query');
        
        if (!$searchQuery || strlen($searchQuery) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $suggestions = Product::where(function($query) use ($searchQuery) {
                $query->where('name', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('description', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('slug', 'LIKE', "%{$searchQuery}%");
            })
            ->limit(8)
            ->get(['id', 'name', 'slug', 'price', 'image'])
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'image' => $product->image,
                    'url' => route('product.show', $product->slug)
                ];
            });
        
        return response()->json(['suggestions' => $suggestions]);
    }
}
