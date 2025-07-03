<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CartService;
use App\Models\Cart;

class CartController extends Controller
{
    protected $cart;

    public function __construct(CartService $cart)
    {
        $this->cart = $cart;
    }

    public function view()
    {
        $items = $this->cart->getCartItems();
        return view('cart.index', compact('items'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'nullable|integer|min:1',
        ]);
        // Check if the product is already in the cart
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
        $validator = \Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $this->cart->add($request->product_id, $request->quantity ?? 1);

        return response()->json(['status' => true, 'message' => 'Added to cart']);
    }

    public function ajaxUpdate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
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
        $validator = \Validator::make($request->all(), [
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

}
