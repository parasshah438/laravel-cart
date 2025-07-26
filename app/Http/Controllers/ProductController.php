<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use App\Models\RecentlyViewedProduct;
use App\Services\RecentlyViewedService;
use Illuminate\Support\Str;
use App\Models\Wishlist;
use App\Models\Cart;
use App\Models\CartItem;

class ProductController extends Controller
{
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
            $existing->touch(); // update `updated_at`
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


        return view('products.show', compact('product', 'similarProducts', 'wishlistProductIds'));
    }

public function getRecentlyViewedProducts()
{

    $query = RecentlyViewedProduct::with('product')
        ->orderByDesc('updated_at')
        ->limit(10);

    if (auth()->check()) {
        $query->where('user_id', auth()->id());
    } else {
        $sessionId = session()->get('cart_session_id');
        if (!$sessionId) {
            // Return early with empty collection if no session ID
            return view('products.recently-viewed', ['recentlyViewed' => collect()]);
        }
        $query->where('session_id', $sessionId);
    }


    $recentlyViewed = $query->get()->pluck('product')->filter();
    
    dd($recentlyViewed);
    return view('products.recently-viewed', compact('recentlyViewed'));
}

    public function clearRecentlyViewed()
    {
        $sessionId = Session::getId();

        $query = RecentlyViewedProduct::where('user_id', auth()->id());

        if (!auth()->check()) {
            $query->where('session_id', $sessionId);
        }

        $deletedCount = $query->delete();

        if ($deletedCount > 0) {
            Session::flash('success', 'Recently viewed products cleared successfully.');
        } else {
            Session::flash('info', 'No recently viewed products to clear.');
        }

        return redirect()->route('product.recentlyViewed');
    }

    public function getTrendingProducts()
    {
        $trendingProducts = Product::withCount([
            'views as recent_views_count' => function ($query) {
                $query->where('created_at', '>=', now()->subDays(7));
            },
            'wishlists as wishlists_count'
        ])
        ->havingRaw('(recent_views_count * 2 + wishlists_count) > 0')
        ->orderByRaw('(recent_views_count * 2 + wishlists_count) DESC')
        ->get();

        return view('products.trending', compact('trendingProducts'));
    }
}