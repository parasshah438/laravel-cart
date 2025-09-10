<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CartService; // Assuming you have a CartService to handle cart operations
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Country; // Assuming you have a Country model to fetch countries
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Mail;


class CheckoutController extends Controller
{
    protected $cart;

    public function __construct(CartService $cart)
    {
        $this->cart = $cart;
    }

    public function index()
    {
        // Logic to display the checkout page
        $user = auth()->user();
        $cartItems = $this->cart->getCartItems(); // Get all cart items
        $savedItems = $this->cart->getCartItems(true); // true = saved items
        $countries = Country::all(); // Assuming you have a Country model to fetch countries
        
        // ✅ GET CART TOTALS AND APPLIED COUPON FOR CHECKOUT
        $cart = $this->cart->getCurrentCart();
        $subtotal = $cartItems->sum(fn($item) => $item->quantity * $item->price_at_time);
        $appliedCoupon = $cart->appliedCoupon;
        $discount = $appliedCoupon ? $appliedCoupon->calculateDiscount($subtotal) : 0;
        $total = $subtotal - $discount;
        
        // Check if cart is empty
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.view')->with('error', 'Your cart is empty. Add items to proceed to checkout.');
        }

        return view('checkout.index', compact(
            'cartItems', 
            'savedItems', 
            'countries', 
            'user',
            'cart',
            'subtotal',
            'discount',
            'total',
            'appliedCoupon'
        ));
    }

    public function processPayment(Request $request)
    {
        // Logic to process payment
        // Validate request, handle payment gateway integration, etc.
        
        return redirect()->route('front.index')->with('success', 'Payment processed successfully!');
    }

    public function orderSummary()
    {
        // Logic to display order summary
        return view('checkout.summary');
    }

        /**
         * Place order (COD only)
         */
        public function placeOrder(Request $request)
        {
            $user = auth()->user();
            $cartItems = $this->cart->getCartItems();
            if ($cartItems->isEmpty()) {
                return redirect()->route('cart.view')->with('error', 'Your cart is empty.');
            }

            // Get selected address (default or selected)
            $address = $user->addresses()->where('is_default', true)->first();
            if (!$address) {
                return redirect()->route('checkout')->with('error', 'Please select a delivery address.');
            }

            // ✅ GET CART WITH APPLIED COUPON
            $cart = $this->cart->getCurrentCart();
            $appliedCoupon = $cart->appliedCoupon;
            
            // Calculate totals with proper coupon logic
            $subtotal = $cartItems->sum(function($item) { 
                return $item->price_at_time * $item->quantity; 
            });
            
            $discount = 0;
            $couponCode = null;
            $couponTitle = null;
            
            if ($appliedCoupon) {
                $discount = $appliedCoupon->calculateDiscount($subtotal);
                $couponCode = $appliedCoupon->code;
                $couponTitle = $appliedCoupon->title;
                
                // Update coupon usage count
                $appliedCoupon->increment('used_count');
            }
            
            $grandTotal = $subtotal - $discount;

            // ✅ CREATE ORDER WITH COUPON DATA
            $order = $user->orders()->create([
                'address_id' => $address->id,
                'order_number' => 'ORD' . strtoupper(uniqid()),
                'total' => $subtotal,
                'discount' => $discount,
                'grand_total' => $grandTotal,
                'coupon_code' => $couponCode,
                'coupon_title' => $couponTitle,
                'coupon_discount' => $discount,
                'status' => 'pending',
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'notes' => null,
            ]);

            // Create order items with cart prices (preserving cart-time pricing)
            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item->product->id,
                    'product_name' => $item->product->name,
                    'price' => $item->price_at_time, // Use cart-time price
                    'quantity' => $item->quantity,
                    'total' => $item->price_at_time * $item->quantity,
                ]);
            }

            // ✅ SEND ORDER CONFIRMATION EMAIL (Professional Amazon/Flipkart Style)
            try {
                Mail::to($user->email)->queue(new OrderConfirmation($order));
            } catch (\Exception $e) {
                // Log email error but don't fail the order
                \Log::error('Order confirmation email failed: ' . $e->getMessage());
            }

            // Clear cart and remove applied coupon
            $this->cart->clear();

            return redirect()->route('checkout.thankyou')->with('success', 'Order placed successfully! Check your email for order confirmation.');
        }

        /**
     * Show the details for a specific order (only for the order owner)
     */
    public function orderDetails($orderId)
    {
        $order = auth()->user()->orders()->with('items.product')->findOrFail($orderId);
        return view('checkout.order-details', compact('order'));
    }    
}
