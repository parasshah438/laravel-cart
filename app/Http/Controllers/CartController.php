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
use Illuminate\Support\Facades\Storage;

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


    public function getGiftProducts(Request $request)
    {
        try {
            // Get gift/complementary products (you can customize this logic)
            $giftProducts = Product::where('price', '<', 500) // Small price items as gifts
                ->inRandomOrder()
                ->limit(12)
                ->get();

            // If no specific gift products, get random products under ₹300
            if ($giftProducts->isEmpty()) {
                $giftProducts = Product::where('price', '<', 300)
                    ->where('status', 'active')
                    ->inRandomOrder()
                    ->limit(12)
                    ->get();
            }

            $html = view('partials._gift-products', compact('giftProducts'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading gift products'
            ], 500);
        }
    }

    /**
     * Add selected gifts to cart
     */
    public function addGifts(Request $request)
    {
        try {
            $gifts = $request->input('gifts', []);
            $addedCount = 0;
            
            foreach ($gifts as $gift) {
                $product = Product::find($gift['product_id']);

                
                
                if ($product) {
                    // Cart::add($product->id, $product->name, $gift['quantity'], $product->price, [
                    //     'image' => $product->image,

                    // ]);

                    $this->cart->add($product->id, $gift['quantity'] ?? 1);
                    $addedCount++;

                    
                }
            }

            $message = $addedCount === 1 
                ? "1 gift item added to cart!" 
                : "{$addedCount} gift items added to cart!";

            return response()->json([
                'success' => true,
                'message' => $message,
                'cart_count' => Cart::count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding gifts to cart'
            ], 500);
        }
    }

    public function saveCustomizationImage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customized_image' => 'required|image|max:5120', // 5MB max
                'product_id' => 'required|exists:products,id',
                'customizations' => 'required|json'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()]);
            }

            // Store image
            $image = $request->file('customized_image');
            $filename = 'customized_' . time() . '_' . uniqid() . '.png';
            $path = $image->storeAs('customizations', $filename, 'public');

            // Save to database (optional)
            // $customization = new ProductCustomization();
            // $customization->product_id = $request->product_id;
            // $customization->user_id = auth()->id();
            // $customization->session_id = session()->getId();
            // $customization->image_path = $path;
            // $customization->customizations_data = $request->customizations;
            // $customization->save();

            return response()->json([
                'success' => true,
                'image_url' => Storage::url($path),
                'customization_id' => $customization->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save customized image'
            ], 500);
        }
    }

    /**
     * ✅ PROFESSIONAL BUY NOW METHOD (Amazon/Flipkart Style)
     * Handles express checkout - adds item to cart and redirects to checkout
     */
    public function buyNow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false, 
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $product = Product::with('stocks')->findOrFail($request->product_id);
            $stock = $product->stocks()->first();
            
            if (!$stock) {
                return response()->json([
                    'status' => false,
                    'message' => 'Stock information not available for this product'
                ], 404);
            }

            // Use CartService for professional stock validation
            $quantity = $request->input('quantity', 1);
            $stockValidation = $this->cart->validateStock($stock->id, $quantity);
            
            if ($stockValidation !== true) {
                return response()->json([
                    'status' => false,
                    'message' => $stockValidation
                ], 400);
            }

            // Get current cart
            $cart = $this->cart->getCurrentCart();

            // Check if product already exists in cart
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existingItem) {
                // For Buy Now, replace the quantity (don't add to existing)
                $existingItem->update([
                    'quantity' => $quantity,
                    'price_at_time' => $product->price
                ]);
                $message = "Product quantity updated for express checkout";
            } else {
                // Add new item using CartService
                $this->cart->add($request->product_id, $quantity);
                $message = "Product added for express checkout";
            }

            // Get updated cart count
            $cartCount = $cart->fresh()->items()->sum('quantity');

            // Professional response with checkout URL (Amazon style)
            return response()->json([
                'status' => true,
                'message' => $message,
                'cart_count' => $cartCount,
                'checkout_url' => route('checkout'), // Direct checkout redirect
                'buy_now' => true, // Flag for frontend handling
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'stock_available' => $stock->qty
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to process express checkout. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * ✅ SMART CART-BASED COUPON SYSTEM (Amazon/Flipkart Style)
     * Shows relevant coupons based on cart value and intelligent recommendations
     */
    public function getAvailableCoupons(Request $request)
    {
        try {
            $cart = $this->cart->getCurrentCart();
            $cartTotal = $cart->items()->sum(\DB::raw('quantity * price_at_time'));
            $showMode = $request->get('mode', 'smart'); // 'smart' or 'all'

            // SMART FILTERING LOGIC (like real e-commerce)
            if ($showMode === 'smart') {
                $coupons = $this->getSmartCouponRecommendations($cartTotal);
            } else {
                // Show all active coupons for "View All" modal
                $coupons = Coupon::active()->get();
            }
            
            $availableCoupons = [];
            $nearMissCoupons = []; // Coupons close to being applicable
            $otherCoupons = []; // Other unavailable coupons

            foreach ($coupons as $coupon) {
                $couponData = [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'title' => $coupon->title,
                    'description' => $coupon->description,
                    'terms' => $coupon->terms,
                    'category' => $coupon->category,
                    'banner_color' => $coupon->banner_color,
                    'display_info' => $coupon->getDisplayInfo(),
                    'is_applicable' => false,
                    'reason' => '',
                    'priority' => $this->getCouponPriority($coupon, $cartTotal)
                ];

                // Check if coupon is applicable
                if ($coupon->isValid($cartTotal)) {
                    $couponData['is_applicable'] = true;
                    $couponData['discount_amount'] = $coupon->calculateDiscount($cartTotal);
                    $couponData['savings_text'] = "You'll save ₹" . number_format($couponData['discount_amount'], 2);
                    $availableCoupons[] = $couponData;
                } else {
                    // Categorize unavailable coupons smartly
                    if ($coupon->min_cart_value && $cartTotal < $coupon->min_cart_value) {
                        $gap = $coupon->min_cart_value - $cartTotal;
                        $couponData['reason'] = "Add ₹" . number_format($gap, 2) . " more to unlock this offer";
                        $couponData['gap_amount'] = $gap;
                        
                        // Near miss if gap is less than 25% of current cart
                        if ($gap <= ($cartTotal * 0.25) || $gap <= 200) {
                            $nearMissCoupons[] = $couponData;
                        } else {
                            $otherCoupons[] = $couponData;
                        }
                    } elseif ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
                        $couponData['reason'] = "Offer expired - Usage limit reached";
                        $otherCoupons[] = $couponData;
                    } elseif ($coupon->expires_at && now()->gt($coupon->expires_at)) {
                        $couponData['reason'] = "Offer expired";
                        $otherCoupons[] = $couponData;
                    } else {
                        $couponData['reason'] = "Not applicable for your cart";
                        $otherCoupons[] = $couponData;
                    }
                }
            }

            // Sort by priority (highest discount first)
            usort($availableCoupons, fn($a, $b) => $b['priority'] <=> $a['priority']);
            usort($nearMissCoupons, fn($a, $b) => ($a['gap_amount'] ?? 999999) <=> ($b['gap_amount'] ?? 999999));

            return response()->json([
                'status' => true,
                'cart_total' => $cartTotal,
                'show_mode' => $showMode,
                'available_coupons' => $availableCoupons,
                'near_miss_coupons' => $nearMissCoupons, // Show these prominently
                'other_coupons' => $otherCoupons,
                'recommendations' => $this->getCouponRecommendations($cartTotal, $availableCoupons),
                'total_coupons' => count($coupons)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to load coupons',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get smart coupon recommendations based on cart value
     */
    private function getSmartCouponRecommendations(float $cartTotal): \Illuminate\Support\Collection
    {
        return Coupon::active()
            ->where(function ($query) use ($cartTotal) {
                $query
                    // Always show applicable coupons
                    ->where('min_cart_value', '<=', $cartTotal)
                    ->orWhereNull('min_cart_value')
                    // Show near-miss coupons (within 25% or ₹200 of cart total)
                    ->orWhere(function ($q) use ($cartTotal) {
                        $maxGap = max($cartTotal * 0.25, 200);
                        $q->where('min_cart_value', '>', $cartTotal)
                          ->where('min_cart_value', '<=', $cartTotal + $maxGap);
                    });
            })
            // Prioritize by category relevance
            ->orderByRaw("
                CASE category 
                WHEN 'first_order' THEN 1
                WHEN 'festival' THEN 2  
                WHEN 'general' THEN 3
                WHEN 'loyalty' THEN 4
                ELSE 5 END
            ")
            ->orderBy('discount', 'desc')
            ->limit(8) // Limit to top 8 relevant coupons
            ->get();
    }

    /**
     * Calculate coupon priority for sorting
     */
    private function getCouponPriority(Coupon $coupon, float $cartTotal): float
    {
        if (!$coupon->isValid($cartTotal)) {
            return 0;
        }

        $discount = $coupon->calculateDiscount($cartTotal);
        $priority = $discount;

        // Boost priority for certain categories
        $categoryBoost = [
            'first_order' => 1.5,
            'festival' => 1.3,
            'general' => 1.0,
            'loyalty' => 1.2,
        ];

        return $priority * ($categoryBoost[$coupon->category] ?? 1.0);
    }

    /**
     * Get personalized recommendations
     */
    private function getCouponRecommendations(float $cartTotal, array $availableCoupons): array
    {
        $recommendations = [];

        if (empty($availableCoupons)) {
            $recommendations[] = "Add items worth ₹" . max(500 - $cartTotal, 0) . " to unlock more offers!";
        } elseif (count($availableCoupons) === 1) {
            $recommendations[] = "Great! You have 1 offer available.";
        } else {
            $bestSaving = max(array_column($availableCoupons, 'discount_amount'));
            $recommendations[] = "Best offer: Save up to ₹" . number_format($bestSaving, 2) . "!";
        }

        return $recommendations;
    }
}
