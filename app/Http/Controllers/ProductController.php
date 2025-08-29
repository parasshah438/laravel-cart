<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Traits\SEOTrait;

class ProductController extends Controller
{
    use SEOTrait;

    public function __construct()
    {
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

        // Related products
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->take(4)
            ->get();

        // Set cache headers for product pages
        $this->setCacheHeaders(30); // 30 minutes

        return view('products.show', compact('product', 'relatedProducts'));
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
}