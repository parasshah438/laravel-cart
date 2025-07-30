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
}
