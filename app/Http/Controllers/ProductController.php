<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use App\Models\RecentlyViewedProduct;

class ProductController extends Controller
{
    public function showProduct($slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        $sessionId = Session::getId();

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

        $similarProducts = Product::where('id', '!=', $product->id)
            ->inRandomOrder()
            ->take(10)
            ->get();

        return view('products.show', compact('product', 'similarProducts'));
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
        return view('product.recently-viewed', compact('recentlyViewed'));
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
}