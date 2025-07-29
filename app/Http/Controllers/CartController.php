<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CartService;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Wishlist;
use App\Models\CartItem;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    protected $cart;

    public function __construct(CartService $cart)
    {
        $this->cart = $cart;
    }

    public function refreshSavedItems()
    {
        $savedItems = $this->cart->getCartItems(true);
        $cartCount = $this->cart->getCartItems(false)->count();

        return view('partials._saved_items', compact('savedItems', 'cartCount'));
    }

    public function refreshSavedItemsView()
    {
        $savedItems = $this->cart->getCartItems(true); // true = saved items
        return view('partials.cart-saved-refresh', compact('savedItems'));
    }

    public function refreshCartView()
    {
        $items = $this->cart->getCartItems(false);
        $cart = $this->cart->getCurrentCart();
        $subtotal = $items->sum(fn($i) => $i->quantity * $i->price_at_time);
        $discount = $cart->appliedCoupon ? $cart->appliedCoupon->calculateDiscount($subtotal) : 0;
        $total = $subtotal - $discount;

        return view('partials.cart-items-refresh', compact('items', 'subtotal', 'discount', 'total', 'cart'));
    }


    public function refreshCart()
    {
        $items = $this->cart->getCartItems(false);
        $cart = $this->cart->getCurrentCart();
        $subtotal = $items->sum(fn($i) => $i->quantity * $i->price_at_time);
        $discount = $cart->appliedCoupon ? $cart->appliedCoupon->calculateDiscount($subtotal) : 0;
        $total = $subtotal - $discount;

        return response()->json([
            'cart_items_html' => view('partials._cart_cards', compact('items'))->render(),
            'cart_totals_html' => view('partials._cart_totals', compact('subtotal', 'discount', 'total', 'cart'))->render(),
            'cart_count' => $items->count()
        ]);
    }

    public function view(Request $request)
    {   
        $perPage = 5;
        $page = $request->input('page', 1);
        $items = $this->cart->getCartItems(false, $perPage, $page);
        $savedItems = $this->cart->getCartItems(true); // saved_for_later = true

        $cart = $this->cart->getCurrentCart();
        $subtotal = $items->sum(fn($i) => $i->quantity * $i->price_at_time);
        $discount = $cart->appliedCoupon ? $cart->appliedCoupon->calculateDiscount($subtotal) : 0;
        $total = $subtotal - $discount;

        if ($request->ajax()) {
            $html = view('partials._cart_cards', compact('items'))->render();
            return response()->json([
                'html' => $html,
                'nextPage' => $page + 1,
                'hasMorePages' => $items->hasMorePages(),
            ]);
        }
        $cartCount = $this->cart->getCartItems(false)->count();
        return view('cart.index', compact('items','cart','subtotal','discount','total','savedItems','cartCount'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'nullable|integer|min:1',
        ]);
        //Check if the product is already in the cart
        $this->cart->add($request->product_id, $request->quantity ?? 1);
        return back()->with('success', 'Product added!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $this->cart->update($request->product_id, $request->quantity);

        return back()->with('success', 'Cart updated successfully!');
    }

    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $this->cart->remove($request->product_id);

        return back()->with('success', 'Item removed from cart.');
    }

    // ajax
    public function ajaxAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $product = Product::with('stocks')->find($request->product_id);
        $stock = $product->stocks()->first();
        if (!$stock) {
            return response()->json(['status' => false, 'message' => 'Stock not found.'], 404);
        }

        $quantity = $request->input('quantity', 1);
        $result = $this->cart->validateStock($stock->id, $quantity);
        if ($result !== true) {
            return response()->json(['status' => false, 'message' => $result]);
        }

        $this->cart->add($request->product_id, $request->quantity ?? 1);

        return response()->json(['status' => true, 'message' => 'Added to cart']);
    }

    public function ajaxUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $this->cart->update($request->product_id, $request->quantity);

        return response()->json(['status' => true, 'message' => 'Cart updated']);
    }

    public function ajaxRemove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $this->cart->remove($request->product_id);

        return response()->json(['status' => true, 'message' => 'Item removed']);
    }

    public function getTotal()
    {
        $total = $this->cart->calculateTotal();
        
        return response()->json([
            'status' => true,
            'total' => round($total, 2),
            'formatted' => number_format($total, 2)
        ]);
    }

    public function clear()
    {
        $cart = auth()->check()
            ? Cart::where('user_id', auth()->id())->first()
            : Cart::where('session_id', session('cart_session_id'))->first();

        if ($cart) {
            $cart->items()->delete();
            $cart->delete();
        }

        return response()->json(['status' => true, 'message' => 'Cart cleared successfully.']);
    }

    public function moveToWishlist(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $productId = $request->product_id;

        //Save to wishlist
        Wishlist::updateOrCreate([
            'user_id' => auth()->id(),
            'product_id' => $productId
        ]);

        //Remove from cart
        $this->cart->remove($productId);

        return response()->json([
            'status' => true,
            'message' => 'Your product has been moved to wishlist.',
        ]);
    }

    public function moveToCart(Request $request)
    {
        $item = CartItem::where('product_id', $request->product_id)
            ->where('saved_for_later', true)
            ->where(function($q) {
                auth()->check()
                    ? $q->where('user_id', auth()->id())
                    : $q->where('session_id', session()->getId());
            })
            ->first();

        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Saved item not found.']);
        }

        $item->saved_for_later = false;
        $item->save();

        return response()->json(['status' => true, 'message' => 'Moved back to cart.']);
    }

    public function moveToCartFromSaved(Request $request)
    {
        $cart = Cart::query()
            ->when(auth()->check(), fn($q) => $q->where('user_id', auth()->id()))
            ->when(!auth()->check(), fn($q) => $q->where('session_id', session()->getId()))
            ->first();

        if (!$cart) {
            return response()->json(['status' => false, 'message' => 'Cart not found.']);
        }

        $item = $cart->items()
            ->where('product_id', $request->product_id)
            ->where('saved_for_later', true)
            ->first();

        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Saved item not found.']);
        }

        $item->saved_for_later = false;
        $item->save();

        return response()->json(['status' => true, 'message' => 'Item moved to cart.']);
    }

    public function saveForLater(Request $request)
    {
        $cart = Cart::query()
            ->when(auth()->check(), fn($q) => $q->where('user_id', auth()->id()))
            ->when(!auth()->check(), fn($q) => $q->where('session_id', session()->getId()))
            ->first();

        if (!$cart) {
            return response()->json(['status' => false, 'message' => 'Cart not found.']);
        }

        $item = $cart->items()->where('product_id', $request->product_id)->first();

        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Item not found in cart.']);
        }

        $item->saved_for_later = true;
        $item->save();

        return response()->json(['status' => true, 'message' => 'Item saved for later.']);
    }

    public function loadMore(Request $request)
    {
        $perPage = 5;
        $page = $request->input('page', 1);
        
        $items = $this->cart->getCartItems(false, $perPage, $page);
        $html = view('partials._cart_cards', ['items' => $items])->render();

        $newTotal = $items->sum(function ($item) {
            return $item->quantity * $item->price_at_time;
        });

        return response()->json([
            'html' => $html,
            'hasMorePages' => $items->hasMorePages(),
            'nextPage' => $items->currentPage() + 1,
            'newTotal' => number_format($newTotal, 2),
        ]);
    }

    public function getCartCount()
    {
        $count = $this->cart->getCartItems(false)->count();
        return response()->json(['count' => $count]);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $cart = $this->cart->getCurrentCart();
        $items = $this->cart->getCartItems(false);

        //Optional: Rate limiting to prevent abuse
        if (RateLimiter::tooManyAttempts("coupon:{$request->ip()}", 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many attempts. Try again later.'
            ], 429);
        }
        RateLimiter::hit("coupon:{$request->ip()}", 60); // 5 tries per minute

        //Security check: Ensure items belong to current cart
        if ($items->contains(fn($item) => $item->cart_id !== $cart->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized cart access.'
            ], 403);
        }

        if ($cart->applied_coupon_id) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied a coupon. Please remove it before applying another.'
            ]);
        }

        $coupon = Coupon::where('code', $request->code)->active()->first();
        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired coupon.'
            ]);
        }

        $subtotal = $items->sum(fn($i) => $i->quantity * $i->price_at_time);
        if (!$coupon->isValid($subtotal)) {
            return response()->json([
                'success' => false,
                'message' => 'This coupon is not valid for your cart total.'
            ]);
        }

        //Clamp discount to never exceed subtotal
        $discount = min($coupon->calculateDiscount($subtotal), $subtotal);
        $total = $subtotal - $discount;

        //Optional: Wrap in DB transaction
        DB::transaction(function () use ($cart, $coupon) {
            $cart->applied_coupon_id = $coupon->id;
            $cart->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'updatedCartHtml' => view('partials._cart_cards', ['items' => $items])->render(),
            'totalsHtml' => view('partials._cart_totals', [
                'cart' => $cart->fresh(),
                'items' => $items,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
            ])->render()
        ]);
    }

    public function removeCoupon(Request $request)
    {
        $cart = $this->cart->getCurrentCart();

        if (!$cart || !$cart->applied_coupon_id) {
            return response()->json(['success' => false, 'message' => 'No coupon to remove.']);
        }

        $cart->applied_coupon_id = null;
        $cart->save();

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully.',
            'updatedCartHtml' => view('partials._cart_cards', ['items' => $this->cart->getCartItems(false)])->render(),
            'updatedTotalsHtml' => view('partials._cart_totals', [
                'cart' => $cart->fresh(),
                'items' => $this->cart->getCartItems(false),
                'subtotal' => $this->cart->getCartItems(false)->sum(fn($i) => $i->quantity * $i->price_at_time),
                'discount' => 0,
                'total' => $this->cart->getCartItems(false)->sum(fn($i) => $i->quantity * $i->price_at_time)
            ])->render(),
        ]);
    }

    public function getCartSummary()
    {
        $items = $this->cart->getCartItems(false);
        $cart = $this->cart->getCurrentCart();
        $subtotal = $items->sum(fn($i) => $i->quantity * $i->price_at_time);
        $discount = $cart->appliedCoupon ? $cart->appliedCoupon->calculateDiscount($subtotal) : 0;
        $total = $subtotal - $discount;

        return response()->json([
            'status' => true,
            'subtotal' => number_format($subtotal, 2),
            'discount' => number_format($discount, 2),
            'total' => number_format($total, 2),
            'coupon_code' => $cart->appliedCoupon->code ?? null,
        ]);
    }
}
