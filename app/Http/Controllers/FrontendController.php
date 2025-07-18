<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class FrontendController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 12;
        $products = Product::with('stocks')
            ->latest()
            ->paginate($perPage);

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
        return view('welcome', compact('products', 'wishlistProductIds'));
    }
}
