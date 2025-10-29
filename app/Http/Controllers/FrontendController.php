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
     * Display products for a specific category
     */
    public function categoryProducts(Request $request, $slug)
    {
        $perPage = 12;
        $searchQuery = $request->get('search');
        
        // Find the category by slug
        $category = Category::where('slug', $slug)->first();
        
        if (!$category) {
            abort(404, 'Category not found');
        }
        
        // Get all descendant category IDs (including the category itself)
        $categoryIds = $this->getAllCategoryIds($category);
        
        // Build products query
        $productsQuery = Product::with(['stocks', 'category'])
            ->whereIn('category_id', $categoryIds);
        
        // Apply search filter if provided
        if ($searchQuery) {
            $productsQuery->where(function($query) use ($searchQuery) {
                $query->where('name', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('description', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('slug', 'LIKE', "%{$searchQuery}%");
            });
        }
        
        $products = $productsQuery->latest()->paginate($perPage);
        
        // Load categories for mega menu
        $categories = Category::with(['children.children'])
            ->whereNull('parent_id')
            ->where('status', true)
            ->orderBy('sort_order')
            ->get();

        $wishlistProductIds = auth()->check()
            ? auth()->user()->wishlist()->pluck('product_id')
            : collect();

        // Get breadcrumb data
        $breadcrumbs = $this->getBreadcrumbs($category);

        // AJAX (Load More)
        if ($request->ajax()) {
            $html = view('partials._product_cards', compact('products', 'wishlistProductIds'))->render();
            return response()->json([
                'html' => $html,
                'nextPage' => $products->currentPage() + 1,
                'hasMorePages' => $products->hasMorePages()
            ]);
        }
        
        return view('category-products', compact('products', 'wishlistProductIds', 'searchQuery', 'categories', 'category', 'breadcrumbs'));
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
