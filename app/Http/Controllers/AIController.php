<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AIController extends Controller
{
    /**
     * Get personalized AI recommendations for the user
     * Route: GET /ai/recommendations
     */
    public function personalizedRecommendations(Request $request)
    {
        try {
            $user = Auth::user();
            $recommendations = collect();

            // If user is logged in, get personalized recommendations
            if ($user) {
                $recommendations = $this->getPersonalizedRecommendations($user);
            } else {
                // For guest users, show trending and popular products
                $recommendations = $this->getGuestRecommendations();
            }

            // Return as JSON API or view
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'recommendations' => $recommendations,
                    'user_type' => $user ? 'authenticated' : 'guest'
                ]);
            }

            // Return view for web interface
            return view('ai.recommendations', compact('recommendations', 'user'));

        } catch (\Exception $e) {
            Log::error('AI Recommendations Error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unable to load recommendations'
                ], 500);
            }

            return view('ai.recommendations', [
                'recommendations' => collect(),
                'user' => null,
                'error' => 'Unable to load recommendations at this time.'
            ]);
        }
    }

    /**
     * Get personalized recommendations for authenticated users
     */
    private function getPersonalizedRecommendations(User $user)
    {
        $recommendations = collect();

        // 1. Collaborative Filtering: "Users who bought X also bought Y"
        $collaborativeRecommendations = $this->getCollaborativeRecommendations($user);
        
        // 2. Content-Based: Similar products to what user viewed/bought
        $contentBasedRecommendations = $this->getContentBasedRecommendations($user);
        
        // 3. Recently Viewed Items
        $recentlyViewedRecommendations = $this->getRecentlyViewedRecommendations($user);
        
        // 4. Popular in User's Categories
        $categoryRecommendations = $this->getCategoryBasedRecommendations($user);

        // Combine and structure recommendations
        $recommendations = collect([
            'for_you' => $collaborativeRecommendations,
            'because_you_viewed' => $recentlyViewedRecommendations,
            'similar_to_purchased' => $contentBasedRecommendations,
            'trending_in_your_categories' => $categoryRecommendations,
        ]);

        return $recommendations;
    }

    /**
     * Get recommendations for guest users
     */
    private function getGuestRecommendations()
    {
        $recommendations = collect([
            'trending_now' => $this->getTrendingProducts(),
            'most_popular' => $this->getMostPopularProducts(),
            'recently_added' => $this->getRecentlyAddedProducts(),
            'best_sellers' => $this->getBestSellerProducts(),
        ]);

        return $recommendations;
    }

    /**
     * Collaborative Filtering: Find products bought by similar users
     */
    private function getCollaborativeRecommendations(User $user, $limit = 12)
    {
        // Get products the user has purchased
        $userProductIds = OrderItem::whereHas('order', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('status', 'completed');
        })->pluck('product_id')->unique();

        if ($userProductIds->isEmpty()) {
            return $this->getTrendingProducts($limit);
        }

        // Find users who bought similar products
        $similarUsers = OrderItem::whereIn('product_id', $userProductIds)
            ->whereHas('order', function($query) use ($user) {
                $query->where('user_id', '!=', $user->id)
                      ->where('status', 'completed');
            })
            ->with('order')
            ->get()
            ->groupBy('order.user_id')
            ->filter(function($items) {
                return $items->count() >= 2; // Users who bought at least 2 similar products
            })
            ->keys();

        // Get products bought by similar users that current user hasn't bought
        $recommendedProductIds = OrderItem::whereHas('order', function($query) use ($similarUsers) {
            $query->whereIn('user_id', $similarUsers)
                  ->where('status', 'completed');
        })
        ->whereNotIn('product_id', $userProductIds)
        ->select('product_id', DB::raw('count(*) as purchase_count'))
        ->groupBy('product_id')
        ->orderByDesc('purchase_count')
        ->limit($limit)
        ->pluck('product_id');

        return Product::whereIn('id', $recommendedProductIds)
            ->with(['productMedias', 'category'])
            ->get();
    }

    /**
     * Content-Based Filtering: Similar products based on attributes
     */
    private function getContentBasedRecommendations(User $user, $limit = 10)
    {
        // Get user's purchased products
        $purchasedProducts = Product::whereHas('orderItems.order', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('status', 'completed');
        })->with('category')->get();

        if ($purchasedProducts->isEmpty()) {
            // Fallback: Get recently added products if user has no purchase history
            return Product::where('status', 1) // 1 = active status
                ->with(['productMedias', 'category'])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        }

        // Get categories user has purchased from
        $preferredCategories = $purchasedProducts->pluck('category_id')->unique();
        $purchasedProductIds = $purchasedProducts->pluck('id');

        // Find similar products in same categories
        $similarProducts = Product::whereIn('category_id', $preferredCategories)
            ->whereNotIn('id', $purchasedProductIds)
            ->where('status', 1) // 1 = active status
            ->with(['productMedias', 'category'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        // If still no products found, get random products
        if ($similarProducts->isEmpty()) {
            $similarProducts = Product::where('status', 1) // 1 = active status
                ->with(['productMedias', 'category'])
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        }

        return $similarProducts;
    }

    /**
     * Get recommendations based on recently viewed products
     */
    private function getRecentlyViewedRecommendations(User $user, $limit = 8)
    {
        // Check if RecentlyViewedProduct model exists
        if (!class_exists('\App\Models\RecentlyViewedProduct')) {
            return collect();
        }

        try {
            $recentlyViewedProductIds = \App\Models\RecentlyViewedProduct::where('user_id', $user->id)
                ->orderByDesc('viewed_at')
                ->limit(5)
                ->pluck('product_id');

            if ($recentlyViewedProductIds->isEmpty()) {
                return collect();
            }

            // Get categories of recently viewed products
            $viewedCategories = Product::whereIn('id', $recentlyViewedProductIds)
                ->pluck('category_id')
                ->unique();

            // Recommend products from same categories
            $recommendations = Product::whereIn('category_id', $viewedCategories)
                ->whereNotIn('id', $recentlyViewedProductIds)
                ->where('status', 'active')
                ->with(['productMedias', 'category'])
                ->inRandomOrder()
                ->limit($limit)
                ->get();

            return $recommendations;
        } catch (\Exception $e) {
            Log::warning('Recently viewed recommendations error: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get recommendations based on user's preferred categories
     */
    private function getCategoryBasedRecommendations(User $user, $limit = 10)
    {
        // Get user's most purchased categories
        $topCategories = OrderItem::whereHas('order', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('status', 'completed');
        })
        ->join('products', 'order_items.product_id', '=', 'products.id')
        ->select('products.category_id', DB::raw('count(*) as category_count'))
        ->groupBy('products.category_id')
        ->orderByDesc('category_count')
        ->limit(3)
        ->pluck('products.category_id');

        if ($topCategories->isEmpty()) {
            // Fallback: Get random products from all categories
            return Product::where('status', 1) // 1 = active status
                ->with(['productMedias', 'category'])
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        }

        // Get popular products from user's preferred categories
        $recommendations = Product::whereIn('category_id', $topCategories)
            ->where('status', 1) // 1 = active status
            ->with(['productMedias', 'category'])
            ->withCount('orderItems')
            ->orderByDesc('order_items_count')
            ->limit($limit)
            ->get();

        // If still empty, get any products from those categories
        if ($recommendations->isEmpty()) {
            $recommendations = Product::whereIn('category_id', $topCategories)
                ->where('status', 1) // 1 = active status
                ->with(['productMedias', 'category'])
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        }

        return $recommendations;
    }

    /**
     * Get trending products (most ordered in last 30 days)
     */
    private function getTrendingProducts($limit = 12)
    {
        // Try to get trending products based on recent orders
        $trending = Product::whereHas('orderItems.order', function($query) {
            $query->where('created_at', '>=', Carbon::now()->subDays(30))
                  ->where('status', 'completed');
        })
        ->with(['productMedias', 'category'])
        ->withCount(['orderItems' => function($query) {
            $query->whereHas('order', function($subQuery) {
                $subQuery->where('created_at', '>=', Carbon::now()->subDays(30))
                         ->where('status', 'completed');
            });
        }])
        ->orderByDesc('order_items_count')
        ->limit($limit)
        ->get();

        // If no trending products found (no orders), fallback to recently added products
        if ($trending->isEmpty()) {
            return Product::where('status', 1) // 1 = active status
                ->with(['productMedias', 'category'])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        }

        return $trending;
    }

    /**
     * Get most popular products (all time best sellers)
     */
    private function getMostPopularProducts($limit = 12)
    {
        // Try to get popular products based on order count
        $popular = Product::where('status', 1) // 1 = active status
            ->with(['productMedias', 'category'])
            ->withCount('orderItems')
            ->having('order_items_count', '>', 0)
            ->orderByDesc('order_items_count')
            ->limit($limit)
            ->get();

        // If no popular products found (no orders), fallback to random active products
        if ($popular->isEmpty()) {
            return Product::where('status', 1) // 1 = active status
                ->with(['productMedias', 'category'])
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        }

        return $popular;
    }

    /**
     * Get recently added products
     */
    private function getRecentlyAddedProducts($limit = 12)
    {
        return Product::where('status', 1) // 1 = active status
            ->with(['productMedias', 'category'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get best seller products (highest revenue generators)
     */
    private function getBestSellerProducts($limit = 12)
    {
        // Try to get best sellers based on revenue
        $bestSellers = Product::where('status', 1) // 1 = active status
            ->with(['productMedias', 'category'])
            ->select('products.*', DB::raw('COALESCE(SUM(order_items.quantity * order_items.price), 0) as total_revenue'))
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->where('orders.status', 'completed');
            })
            ->groupBy('products.id')
            ->having('total_revenue', '>', 0)
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();

        // If no best sellers found (no completed orders), fallback to products by price (high to low)
        if ($bestSellers->isEmpty()) {
            return Product::where('status', 1) // 1 = active status
                ->with(['productMedias', 'category'])
                ->orderByDesc('price')
                ->limit($limit)
                ->get();
        }

        return $bestSellers;
    }

    /**
     * Get AI recommendations for API (mobile app, AJAX calls)
     */
    public function getRecommendationsApi(Request $request)
    {
        $type = $request->get('type', 'for_you'); // for_you, trending, similar, etc.
        $limit = $request->get('limit', 10);
        $productId = $request->get('product_id'); // For "similar to this product"

        $user = Auth::user();
        $recommendations = collect();

        switch ($type) {
            case 'for_you':
                if ($user) {
                    $recommendations = $this->getCollaborativeRecommendations($user, $limit);
                } else {
                    $recommendations = $this->getTrendingProducts($limit);
                }
                break;

            case 'trending':
                $recommendations = $this->getTrendingProducts($limit);
                break;

            case 'similar':
                if ($productId) {
                    $recommendations = $this->getSimilarProducts($productId, $limit);
                } else {
                    $recommendations = $this->getMostPopularProducts($limit);
                }
                break;

            case 'recently_viewed':
                if ($user) {
                    $recommendations = $this->getRecentlyViewedRecommendations($user, $limit);
                } else {
                    $recommendations = $this->getRecentlyAddedProducts($limit);
                }
                break;

            default:
                $recommendations = $this->getMostPopularProducts($limit);
        }

        return response()->json([
            'success' => true,
            'type' => $type,
            'recommendations' => $recommendations,
            'count' => $recommendations->count()
        ]);
    }

    /**
     * Get products similar to a specific product
     */
    private function getSimilarProducts($productId, $limit = 10)
    {
        $product = Product::find($productId);
        if (!$product) {
            return $this->getMostPopularProducts($limit);
        }

        // Find products in same category
        return Product::where('category_id', $product->category_id)
            ->where('id', '!=', $productId)
            ->where('status', 1) // 1 = active status
            ->with(['productMedias', 'category'])
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}