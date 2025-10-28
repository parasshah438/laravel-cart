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
          
      //  $this->command->info('📦 Seeding Indian Postal Codes...');

        $url = 'https://raw.githubusercontent.com/deep5050/indian-pincodes-database/master/data.json';

        // Fetch data from GitHub
        $response = Http::withOptions([
           
            'timeout' => 180,
            'connect_timeout' => 60,
            'verify' => false,
        ])->get($url);

        if (!$response->successful()) {
            $this->command->error("❌ Failed to fetch data. HTTP Status: {$response->status()}");
            return;
        }

        // Clean BOM & Decode JSON
        $body = preg_replace('/^\xEF\xBB\xBF/', '', $response->body());
        $json = json_decode($body, true);

        if (!isset($json['Sheet1'])) {
            $this->command->error('❌ Invalid JSON format.');
            return;
        }

       

        $rows = $json['Sheet1'];
          echo '<pre>'; print_r($rows); echo '</pre>'; exit;
        $this->command->info('✅ Loaded ' . count($rows) . ' postal records.');

        // Preload states & cities for faster lookup
        $countryId = 100; // India ID
        $states = State::where('country_id', $countryId)->pluck('id', 'name')->toArray();
        $cities = City::where('country_id', $countryId)->pluck('id', 'name')->toArray();

        $batchData = [];
        $inserted = 0;

        foreach ($rows as $row) {
            $pincode = trim($row['Pincode'] ?? '');
            $area = trim($row['PostOfficeName'] ?? '');
            $stateName = trim($row['State'] ?? '');
            $cityName = trim($row['City'] ?? '');

            if (!$pincode || !$stateName || !$cityName) continue;

            $stateId = $states[$stateName] ?? null;
            $cityId = $cities[$cityName] ?? null;

            if (!$stateId || !$cityId) continue;

            $batchData[] = [
                'code'        => $pincode,
                'area'        => $area,
                'state_id'    => $stateId,
                'city_id'     => $cityId,
                'country_id'  => $countryId,
                'is_active'   => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            // Insert in chunks to avoid memory issues
            if (count($batchData) >= 1000) {
                PostalCode::upsert($batchData, ['code', 'country_id'], ['area', 'state_id', 'city_id', 'updated_at']);
                $inserted += count($batchData);
                $batchData = [];
                $this->command->info("Inserted {$inserted} records...");
            }
        }

        // Insert remaining data
        if (!empty($batchData)) {
            PostalCode::upsert($batchData, ['code', 'country_id'], ['area', 'state_id', 'city_id', 'updated_at']);
            $inserted += count($batchData);
        }

        $this->command->info("🎉 Completed seeding {$inserted} postal codes for India!");
   





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
