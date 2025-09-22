<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Compare;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompareController extends Controller
{
    /**
     * Display the comparison page with all compared products
     */
    public function index()
    {
        $comparedProducts = collect();
        
        if (Auth::check()) {
            // For authenticated users, get from database
            $comparedProducts = Product::whereIn('id', function($query) {
                $query->select('product_id')
                      ->from('compares')
                      ->where('user_id', Auth::id());
            })->with(['category', 'productMedias', 'productStocks'])->get();
        } else {
            // For guests, get from session
            $compareIds = session()->get('compare_products', []);
            if (!empty($compareIds)) {
                $comparedProducts = Product::whereIn('id', $compareIds)
                                         ->with(['category', 'productMedias', 'productStocks'])
                                         ->get();
            }
        }

        return view('compare.index', compact('comparedProducts'));
    }

    /**
     * Add a product to comparison
     */
    public function add(Product $product)
    {
        try {
            if (Auth::check()) {
                // For authenticated users
                $userId = Auth::id();
                
                // Check if already comparing
                $existingCompare = Compare::where('user_id', $userId)
                                        ->where('product_id', $product->id)
                                        ->first();
                
                if ($existingCompare) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product is already in comparison',
                        'count' => $this->getCompareCount()
                    ]);
                }
                
                // Check limit (max 4 products)
                $currentCount = Compare::where('user_id', $userId)->count();
                if ($currentCount >= 4) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can compare maximum 4 products at a time',
                        'count' => $currentCount
                    ]);
                }
                
                // Add to comparison
                Compare::create([
                    'user_id' => $userId,
                    'product_id' => $product->id,
                ]);
                
            } else {
                // For guests, use session
                $compareProducts = session()->get('compare_products', []);
                
                if (in_array($product->id, $compareProducts)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product is already in comparison',
                        'count' => count($compareProducts)
                    ]);
                }
                
                if (count($compareProducts) >= 4) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can compare maximum 4 products at a time',
                        'count' => count($compareProducts)
                    ]);
                }
                
                $compareProducts[] = $product->id;
                session()->put('compare_products', $compareProducts);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Product added to comparison',
                'count' => $this->getCompareCount(),
                'product_name' => $product->name
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to comparison'
            ], 500);
        }
    }

    /**
     * Remove a product from comparison
     */
    public function remove(Product $product)
    {
        try {
            if (Auth::check()) {
                // For authenticated users
                Compare::where('user_id', Auth::id())
                       ->where('product_id', $product->id)
                       ->delete();
            } else {
                // For guests, remove from session
                $compareProducts = session()->get('compare_products', []);
                $compareProducts = array_values(array_diff($compareProducts, [$product->id]));
                session()->put('compare_products', $compareProducts);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Product removed from comparison',
                'count' => $this->getCompareCount(),
                'product_name' => $product->name
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove product from comparison'
            ], 500);
        }
    }

    /**
     * Clear all products from comparison
     */
    public function clear()
    {
        try {
            if (Auth::check()) {
                // For authenticated users
                Compare::where('user_id', Auth::id())->delete();
            } else {
                // For guests, clear session
                session()->forget('compare_products');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'All products removed from comparison',
                'count' => 0
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear comparison'
            ], 500);
        }
    }

    /**
     * Get the count of products in comparison
     */
    public function count()
    {
        $count = $this->getCompareCount();
        
        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Helper method to get compare count
     */
    private function getCompareCount()
    {
        if (Auth::check()) {
            return Compare::where('user_id', Auth::id())->count();
        } else {
            return count(session()->get('compare_products', []));
        }
    }

    /**
     * Check if a product is in comparison
     */
    public function isInCompare($productId)
    {
        if (Auth::check()) {
            return Compare::where('user_id', Auth::id())
                         ->where('product_id', $productId)
                         ->exists();
        } else {
            $compareProducts = session()->get('compare_products', []);
            return in_array($productId, $compareProducts);
        }
    }
}