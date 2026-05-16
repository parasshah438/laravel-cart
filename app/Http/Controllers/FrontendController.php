<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Slider;
use App\Models\Category;
use App\Services\CartService;
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

        $wishlistProductIds = auth()->check()
            ? auth()->user()->wishlist()->pluck('product_id')
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

        // New Arrivals filter
        if ($request->filled('new_arrivals')) {
            $days = (int) $request->new_arrivals;
            if (in_array($days, [30, 90])) {
                $productsQuery->where('created_at', '>=', now()->subDays($days));
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

        $wishlistProductIds = auth()->check()
            ? auth()->user()->wishlist()->pluck('product_id')
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

            return response()->json([
                'success'         => true,
                'html'            => $html,
                'pagination'      => [
                    'current_page'   => $products->currentPage(),
                    'last_page'      => $products->lastPage(),
                    'total'          => $total,
                    'per_page'       => $products->perPage(),
                    'from'           => $from,
                    'to'             => $to,
                    'has_more_pages' => $products->hasMorePages(),
                    'next_page'      => $products->currentPage() + 1,
                ],
                'results_text'    => $resultsText,
                'filters_applied' => $this->getCategoryAppliedFiltersCount($request),
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
        if ($request->filled('subcategory'))  $count++;
        return $count;
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

        foreach ($products as $product) {
            $suggestions[] = [
                'type'  => 'product',
                'text'  => $product->name,
                'value' => $product->name,
                'icon'  => 'fas fa-box',
            ];
        }

        $subcategories = Category::whereIn('id', $categoryIds)
            ->where('name', 'LIKE', "%{$query}%")
            ->select('name')
            ->limit(3)
            ->get();

        foreach ($subcategories as $subcat) {
            $suggestions[] = [
                'type'  => 'category',
                'text'  => 'in ' . $subcat->name,
                'value' => $subcat->name,
                'icon'  => 'fas fa-tags',
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
