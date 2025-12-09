<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaleEvent;
use App\Models\Product;
use App\Models\Category;
use App\Models\SaleProduct;
use App\Models\SaleAnalytic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SaleEventController extends Controller
{
    /**
     * Display a listing of sale events
     */
    public function index(Request $request)
    {
        $query = SaleEvent::with(['saleProducts', 'analytics'])
            ->withCount(['saleProducts', 'saleOrders']);

        // Search functionality
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('sale_type', $request->type);
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

        $saleEvents = $query->latest()->paginate(15);

        return view('admin.sales.events.index', compact('saleEvents'));
    }

    /**
     * Show the form for creating a new sale event
     */
    public function create()
    {
        $categories = Category::all();
        $saleTypes = [
            'flash_sale' => 'Flash Sale',
            'weekend_sale' => 'Weekend Sale',
            'holiday_sale' => 'Holiday Sale',
            'clearance_sale' => 'Clearance Sale',
            'bulk_discount' => 'Bulk Discount',
            'seasonal_sale' => 'Seasonal Sale'
        ];

        return view('admin.sales.events.create', compact('categories', 'saleTypes'));
    }

    /**
     * Store a newly created sale event
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'sale_type' => 'required|string|in:flash_sale,weekend_sale,holiday_sale,clearance_sale,bulk_discount,seasonal_sale',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'max_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_products_per_user' => 'nullable|integer|min:1',
            'priority' => 'required|integer|min:1|max:10',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $image = $request->file('banner_image');
            $imageName = time() . '_sale_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/sales'), $imageName);
            $data['banner_image'] = 'storage/sales/' . $imageName;
        }

        $saleEvent = SaleEvent::create($data);

        // Create initial analytics record
        SaleAnalytic::create([
            'sale_event_id' => $saleEvent->id,
            'total_views' => 0,
            'total_clicks' => 0,
            'total_orders' => 0,
            'total_revenue' => 0,
            'conversion_rate' => 0
        ]);

        return redirect()->route('admin.sales.events.index')
            ->with('success', 'Sale event created successfully!');
    }

    /**
     * Display the specified sale event
     */
    public function show(SaleEvent $saleEvent)
    {
        $saleEvent->load(['saleProducts.product', 'analytics', 'saleOrders.order']);
        
        $stats = [
            'total_products' => $saleEvent->saleProducts->count(),
            'total_orders' => $saleEvent->saleOrders->count(),
            'total_revenue' => $saleEvent->saleOrders->sum('final_amount'),
            'average_discount' => $saleEvent->saleProducts->avg('discount_percentage'),
            'conversion_rate' => $saleEvent->analytics?->conversion_rate ?? 0
        ];

        return view('admin.sales.events.show', compact('saleEvent', 'stats'));
    }

    /**
     * Show the form for editing the specified sale event
     */
    public function edit(SaleEvent $saleEvent)
    {
        $categories = Category::all();
        $saleTypes = [
            'flash_sale' => 'Flash Sale',
            'weekend_sale' => 'Weekend Sale',
            'holiday_sale' => 'Holiday Sale',
            'clearance_sale' => 'Clearance Sale',
            'bulk_discount' => 'Bulk Discount',
            'seasonal_sale' => 'Seasonal Sale'
        ];

        return view('admin.sales.events.edit', compact('saleEvent', 'categories', 'saleTypes'));
    }

    /**
     * Update the specified sale event
     */
    public function update(Request $request, SaleEvent $saleEvent)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'sale_type' => 'required|string|in:flash_sale,weekend_sale,holiday_sale,clearance_sale,bulk_discount,seasonal_sale',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'max_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_products_per_user' => 'nullable|integer|min:1',
            'priority' => 'required|integer|min:1|max:10',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            // Delete old image if exists
            if ($saleEvent->banner_image && file_exists(public_path($saleEvent->banner_image))) {
                unlink(public_path($saleEvent->banner_image));
            }

            $image = $request->file('banner_image');
            $imageName = time() . '_sale_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/sales'), $imageName);
            $data['banner_image'] = 'storage/sales/' . $imageName;
        }

        $saleEvent->update($data);

        return redirect()->route('admin.sales.events.show', $saleEvent)
            ->with('success', 'Sale event updated successfully!');
    }

    /**
     * Remove the specified sale event
     */
    public function destroy(SaleEvent $saleEvent)
    {
        // Check if sale event has orders
        if ($saleEvent->saleOrders()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete sale event with existing orders.');
        }

        // Delete banner image if exists
        if ($saleEvent->banner_image && file_exists(public_path($saleEvent->banner_image))) {
            unlink(public_path($saleEvent->banner_image));
        }

        $saleEvent->delete();

        return redirect()->route('admin.sales.events.index')
            ->with('success', 'Sale event deleted successfully!');
    }

    /**
     * Add products to sale event
     */
    public function addProducts(Request $request, SaleEvent $saleEvent)
    {
        $validator = Validator::make($request->all(), [
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.sale_price' => 'required|numeric|min:0',
            'products.*.max_quantity_per_user' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        foreach ($request->products as $productData) {
            $product = Product::find($productData['product_id']);
            
            // Check if product already in sale
            $existingProduct = $saleEvent->saleProducts()
                ->where('product_id', $productData['product_id'])
                ->first();

            if ($existingProduct) {
                continue; // Skip if already exists
            }

            $discountPercentage = (($product->price - $productData['sale_price']) / $product->price) * 100;

            SaleProduct::create([
                'sale_event_id' => $saleEvent->id,
                'product_id' => $productData['product_id'],
                'original_price' => $product->price,
                'sale_price' => $productData['sale_price'],
                'discount_percentage' => $discountPercentage,
                'max_quantity_per_user' => $productData['max_quantity_per_user'] ?? null,
                'is_active' => true
            ]);
        }

        return response()->json(['message' => 'Products added to sale successfully!']);
    }

    /**
     * Remove product from sale event
     */
    public function removeProduct(SaleEvent $saleEvent, Product $product)
    {
        $saleProduct = $saleEvent->saleProducts()
            ->where('product_id', $product->id)
            ->first();

        if (!$saleProduct) {
            return redirect()->back()
                ->with('error', 'Product not found in this sale event.');
        }

        $saleProduct->delete();

        return redirect()->back()
            ->with('success', 'Product removed from sale successfully!');
    }

    /**
     * Toggle sale event status
     */
    public function toggleStatus(SaleEvent $saleEvent)
    {
        $saleEvent->update([
            'is_active' => !$saleEvent->is_active
        ]);

        $status = $saleEvent->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'message' => "Sale event {$status} successfully!",
            'status' => $saleEvent->is_active
        ]);
    }

    /**
     * Get products for adding to sale (AJAX)
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