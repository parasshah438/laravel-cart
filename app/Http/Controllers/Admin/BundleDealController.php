<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BundleDeal;
use App\Models\Product;
use App\Models\Category;
use App\Models\BundleProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BundleDealController extends Controller
{
    /**
     * Display a listing of bundle deals
     */
    public function index(Request $request)
    {
        $query = BundleDeal::with(['bundleProducts.product'])
            ->withCount(['bundleProducts', 'saleOrders']);

        // Search functionality
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('bundle_type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'upcoming') {
                $query->where('start_time', '>', now());
            } elseif ($request->status === 'ended') {
                $query->where('end_time', '<', now());
            }
        }

        $bundleDeals = $query->latest()->paginate(15);

        return view('admin.sales.bundles.index', compact('bundleDeals'));
    }

    /**
     * Show the form for creating a new bundle deal
     */
    public function create()
    {
        $categories = Category::all();
        $bundleTypes = [
            'buy_x_get_y' => 'Buy X Get Y',
            'combo_deal' => 'Combo Deal',
            'volume_discount' => 'Volume Discount',
            'cross_sell' => 'Cross-sell Bundle',
            'seasonal_bundle' => 'Seasonal Bundle'
        ];

        return view('admin.sales.bundles.create', compact('categories', 'bundleTypes'));
    }

    /**
     * Store a newly created bundle deal
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'bundle_type' => 'required|string|in:buy_x_get_y,combo_deal,volume_discount,cross_sell,seasonal_bundle',
            'bundle_price' => 'required|numeric|min:0',
            'min_quantity' => 'required|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'priority' => 'required|integer|min:1|max:10',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'bundle_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'products' => 'required|array|min:2',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.is_free' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Handle bundle image upload
        if ($request->hasFile('bundle_image')) {
            $image = $request->file('bundle_image');
            $imageName = time() . '_bundle_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/bundles'), $imageName);
            $data['bundle_image'] = 'storage/bundles/' . $imageName;
        }

        // Calculate original total price
        $originalTotal = 0;
        foreach ($request->products as $productData) {
            $product = Product::find($productData['product_id']);
            $originalTotal += $product->price * $productData['quantity'];
        }
        
        $data['original_total_price'] = $originalTotal;
        $data['discount_amount'] = max(0, $originalTotal - $data['bundle_price']);

        // Remove products from main data array
        unset($data['products']);

        $bundleDeal = BundleDeal::create($data);

        // Add products to bundle
        foreach ($request->products as $productData) {
            BundleProduct::create([
                'bundle_deal_id' => $bundleDeal->id,
                'product_id' => $productData['product_id'],
                'quantity' => $productData['quantity'],
                'is_free' => $productData['is_free'] ?? false
            ]);
        }

        return redirect()->route('admin.sales.bundles.index')
            ->with('success', 'Bundle deal created successfully!');
    }

    /**
     * Display the specified bundle deal
     */
    public function show(BundleDeal $bundleDeal)
    {
        $bundleDeal->load(['bundleProducts.product', 'saleOrders.order']);
        
        $stats = [
            'total_products' => $bundleDeal->bundleProducts->count(),
            'total_orders' => $bundleDeal->saleOrders->count(),
            'total_revenue' => $bundleDeal->saleOrders->sum('final_amount'),
            'average_savings' => $bundleDeal->getDiscountAmount(),
            'conversion_rate' => 0 // You can calculate this based on views vs orders
        ];

        return view('admin.sales.bundles.show', compact('bundleDeal', 'stats'));
    }

    /**
     * Show the form for editing the specified bundle deal
     */
    public function edit(BundleDeal $bundleDeal)
    {
        $bundleDeal->load('bundleProducts.product');
        $categories = Category::all();
        $bundleTypes = [
            'buy_x_get_y' => 'Buy X Get Y',
            'combo_deal' => 'Combo Deal',
            'volume_discount' => 'Volume Discount',
            'cross_sell' => 'Cross-sell Bundle',
            'seasonal_bundle' => 'Seasonal Bundle'
        ];

        return view('admin.sales.bundles.edit', compact('bundleDeal', 'categories', 'bundleTypes'));
    }

    /**
     * Update the specified bundle deal
     */
    public function update(Request $request, BundleDeal $bundleDeal)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'bundle_type' => 'required|string|in:buy_x_get_y,combo_deal,volume_discount,cross_sell,seasonal_bundle',
            'bundle_price' => 'required|numeric|min:0',
            'min_quantity' => 'required|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'priority' => 'required|integer|min:1|max:10',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'bundle_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'products' => 'required|array|min:2',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.is_free' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Handle bundle image upload
        if ($request->hasFile('bundle_image')) {
            // Delete old image if exists
            if ($bundleDeal->bundle_image && file_exists(public_path($bundleDeal->bundle_image))) {
                unlink(public_path($bundleDeal->bundle_image));
            }

            $image = $request->file('bundle_image');
            $imageName = time() . '_bundle_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/bundles'), $imageName);
            $data['bundle_image'] = 'storage/bundles/' . $imageName;
        }

        // Calculate original total price
        $originalTotal = 0;
        foreach ($request->products as $productData) {
            $product = Product::find($productData['product_id']);
            $originalTotal += $product->price * $productData['quantity'];
        }
        
        $data['original_total_price'] = $originalTotal;
        $data['discount_amount'] = max(0, $originalTotal - $data['bundle_price']);

        // Remove products from main data array
        unset($data['products']);

        $bundleDeal->update($data);

        // Update bundle products
        $bundleDeal->bundleProducts()->delete();
        foreach ($request->products as $productData) {
            BundleProduct::create([
                'bundle_deal_id' => $bundleDeal->id,
                'product_id' => $productData['product_id'],
                'quantity' => $productData['quantity'],
                'is_free' => $productData['is_free'] ?? false
            ]);
        }

        return redirect()->route('admin.sales.bundles.show', $bundleDeal)
            ->with('success', 'Bundle deal updated successfully!');
    }

    /**
     * Remove the specified bundle deal
     */
    public function destroy(BundleDeal $bundleDeal)
    {
        // Check if bundle has orders
        if ($bundleDeal->saleOrders()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete bundle deal with existing orders.');
        }

        // Delete bundle image if exists
        if ($bundleDeal->bundle_image && file_exists(public_path($bundleDeal->bundle_image))) {
            unlink(public_path($bundleDeal->bundle_image));
        }

        $bundleDeal->delete();

        return redirect()->route('admin.sales.bundles.index')
            ->with('success', 'Bundle deal deleted successfully!');
    }

    /**
     * Toggle bundle deal status
     */
    public function toggleStatus(BundleDeal $bundleDeal)
    {
        $bundleDeal->update([
            'is_active' => !$bundleDeal->is_active
        ]);

        $status = $bundleDeal->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'message' => "Bundle deal {$status} successfully!",
            'status' => $bundleDeal->is_active
        ]);
    }

    /**
     * Get products for adding to bundle (AJAX)
     */
    public function getProducts(Request $request)
    {
        $query = Product::where('status', 'active');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->select('id', 'name', 'price', 'image')
            ->limit(50)
            ->get();

        return response()->json($products);
    }
}