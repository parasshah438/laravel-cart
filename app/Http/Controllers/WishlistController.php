<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use App\Services\CartService;

class WishlistController extends Controller
{   

    protected $cart;

    public function __construct(CartService $cart)
    {
        $this->cart = $cart;
    }

    public function index(Request $request)
    {
        $perPage = 2;

        $wishlistItems = Wishlist::with('product')
        ->where('user_id', Auth::id())
        ->latest()
        ->paginate($perPage);

        if ($request->ajax()) {
            $html = view('partials._wishlist_card', compact('wishlistItems'))->render();
            return response()->json([
                'html' => view('partials._wishlist_card', compact('wishlistItems'))->render(),
                'hasMorePages' => $wishlistItems->hasMorePages(),
                'nextPage' => $wishlistItems->currentPage() + 1
            ]);
        }

        return view('wishlist.index', compact('wishlistItems'));
    }

    public function moveToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = auth()->user();
        $productId = $request->product_id;
        

        // Check if product exists in wishlist
        $wishlistItem = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if (!$wishlistItem) {
            return response()->json(['status' => false, 'message' => 'Item not found in your wishlist.']);
        }

        $this->cart->add($productId, 1);

        //Remove from wishlist
        $wishlistItem->delete();

        return response()->json([
            'status' => true,
            'message' => 'Item moved to cart successfully!',
        ]);
    }

    public function moveAllToCart()
    {
        $user = auth()->user();
        $items = $user->wishlist()->with('product')->get();

        foreach ($items as $item) {
            $this->cart->add($item->product_id, 1);
            $item->delete();
        }

        return response()->json(['status' => true, 'message' => 'All items moved to cart!']);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = auth()->user();
        $productId = $request->product_id;

        $wishlist = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json(['status' => true, 'message' => 'Product successfully removed from your Wishlist.']);
        } else {
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);
            return response()->json(['status' => true, 'message' => 'Product successfully added to your Wishlist.']);
        }
    }

    public function clear()
    {
        auth()->user()->wishlist()->delete();
        return response()->json(['status' => true, 'message' => 'YTour wishlist has been cleared successfully.']);
    }
}
