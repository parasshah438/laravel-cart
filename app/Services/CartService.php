<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CartService
{
    protected function getOrCreateCart()
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(['user_id' => Auth::id()]);
        }

        $sessionId = session()->get('cart_session_id');
        if (!$sessionId) {
            $sessionId = Str::uuid();
            session()->put('cart_session_id', $sessionId);
        }

        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    public function add($productId, $quantity = 1, $options = [])
    {
        $product = Product::findOrFail($productId);
        $cart = $this->getOrCreateCart();

        $item = $cart->items()->where('product_id', $productId)->first();

        if ($item) {
            $item->quantity += $quantity;
            $item->save();
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'price_at_time' => $product->price,
                'options' => $options,
            ]);
        }
    }

    public function update($productId, $quantity)
    {
        $cart = $this->getOrCreateCart();

        $item = $cart->items()->where('product_id', $productId)->first();

        if ($item) {
            $item->quantity = $quantity;
            $item->save();
        }
    }

    public function remove($productId)
    {
        $cart = $this->getOrCreateCart();

        $cart->items()->where('product_id', $productId)->delete();
    }

    public function getCartItems()
    {
        return $this->getOrCreateCart()->items()->with('product')->get();
    }

    public function mergeSessionCartToUser()
    {
        if (!Auth::check()) return;

        $sessionId = session()->get('cart_session_id');
        if (!$sessionId) return;

        DB::transaction(function () use ($sessionId) {
            $sessionCart = Cart::where('session_id', $sessionId)->first();
            if (!$sessionCart) return;

            $userCart = Cart::firstOrCreate(['user_id' => Auth::id()]);

            foreach ($sessionCart->items as $sessionItem) {
                $existing = $userCart->items()->where('product_id', $sessionItem->product_id)->first();
                if ($existing) {
                    $existing->quantity += $sessionItem->quantity;
                    $existing->save();
                } else {
                    $sessionItem->cart_id = $userCart->id;
                    $sessionItem->save();
                }
            }

            //Cleanup old guest cart
            $sessionCart->delete();
            session()->forget('cart_session_id');
        });
    }

    public function calculateTotal()
    {
        $items = $this->getCartItems();
        $total = 0;

        foreach ($items as $item) {
            $total += $item->quantity * $item->price_at_time;
        }
        return $total;
    }

}
