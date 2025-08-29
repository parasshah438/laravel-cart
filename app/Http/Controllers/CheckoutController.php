<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CartService; // Assuming you have a CartService to handle cart operations
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Country; // Assuming you have a Country model to fetch countries


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

        //dd($user->addresses);

        return view('checkout.index', compact('cartItems', 'savedItems', 'countries','user'));
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

            // Calculate totals
            $total = $cartItems->sum(function($item) { return $item->product->price * $item->quantity; });
            $discount = 0; // Add coupon logic if needed
            $grandTotal = $total - $discount;

            // Create order
            $order = $user->orders()->create([
                'address_id' => $address->id,
                'order_number' => 'ORD' . strtoupper(uniqid()),
                'total' => $total,
                'discount' => $discount,
                'grand_total' => $grandTotal,
                'status' => 'pending',
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'notes' => null,
            ]);

            // Create order items
            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item->product->id,
                    'product_name' => $item->product->name,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'total' => $item->product->price * $item->quantity,
                ]);
            }

            // Clear cart
            $this->cart->clear();

            return redirect()->route('checkout.thankyou');
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
