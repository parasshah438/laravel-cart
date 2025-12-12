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
        $query = SaleEvent::with(['saleProducts', 'analytics']);

        // Search functionality
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $saleEvents = $query->latest()->paginate(15);

        // Calculate statistics
        $stats = [
            'active' => SaleEvent::where('status', 'active')->count(),
            'inactive' => SaleEvent::where('status', 'inactive')->count(), 
            'scheduled' => SaleEvent::where('status', 'scheduled')->count(),
            'expired' => SaleEvent::where('status', 'expired')->count(),
        ];

        return view('admin.sales.events.index', compact('saleEvents', 'stats'));
    }

    /**
     * Show the form for creating a new sale event
     */
    public function create()
    {
        $categories = Category::all();
        $saleTypes = [
            'flash_sale' => 'Flash Sale',
            'mega_sale' => 'Mega Sale',
            'deal_of_day' => 'Deal of the Day',
            'festival_sale' => 'Festival Sale',
            'seasonal_sale' => 'Seasonal Sale',
            'brand_day' => 'Brand Day',
            'category_sale' => 'Category Sale'
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
            'type' => 'required|string|in:flash_sale,mega_sale,deal_of_day,festival_sale,seasonal_sale,brand_day,category_sale',
            'starts_at' => 'required|date|after:now',
            'ends_at' => 'required|date|after:starts_at',
            'max_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|string|in:draft,scheduled,active,paused,ended,cancelled',
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Generate slug from name
        $data['slug'] = \Str::slug($data['name']) . '-' . time();

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
            'analytics_date' => today(),
            'page_views' => 0,
            'unique_visitors' => 0,
            'products_viewed' => 0,
            'add_to_cart_count' => 0,
            'checkout_initiated' => 0,
            'orders_completed' => 0,
            'gross_revenue' => 0,
            'net_revenue' => 0,
            'total_discount_given' => 0,
            'avg_order_value' => 0,
            'view_to_cart_rate' => 0,
            'cart_to_order_rate' => 0,
            'overall_conversion_rate' => 0
        ]);

        return redirect()->route('admin.sales.events.index')
            ->with('success', 'Sale event created successfully!');
    }

    /**
     * Display the specified sale event
     */
    public function show(SaleEvent $saleEvent)
    {
        $saleEvent->load(['saleProducts.product', 'analytics']);
        
        $latestAnalytics = $saleEvent->analytics()->latest()->first();
        
        $stats = [
            'total_products' => $saleEvent->saleProducts->count(),
            'total_orders' => $saleEvent->total_orders ?? 0,
            'total_revenue' => $saleEvent->total_revenue ?? 0,
            'average_discount' => $saleEvent->saleProducts->avg('discount_percentage') ?? 0,
            'conversion_rate' => $latestAnalytics?->overall_conversion_rate ?? 0
        ];

        // Get categories for product search modal
        $categories = Category::orderBy('name')->get();

        return view('admin.sales.events.show', compact('saleEvent', 'stats', 'categories'));
    }

    /**
     * Show the form for editing the specified sale event
     */
    public function edit(SaleEvent $saleEvent)
    {
        $categories = Category::all();
        $saleTypes = [
            'flash_sale' => 'Flash Sale',
            'mega_sale' => 'Mega Sale',
            'deal_of_day' => 'Deal of the Day',
            'festival_sale' => 'Festival Sale',
            'seasonal_sale' => 'Seasonal Sale',
            'brand_day' => 'Brand Day',
            'category_sale' => 'Category Sale'
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
            'type' => 'required|string|in:flash_sale,mega_sale,deal_of_day,festival_sale,seasonal_sale,brand_day,category_sale',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'max_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|string|in:draft,scheduled,active,paused,ended,cancelled',
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
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
        // Check if sale event has orders (using total_orders field or check if relationship exists)
        if (($saleEvent->total_orders ?? 0) > 0) {
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
    public function addProducts(Request $request, $id)
    {
        try {
            // Find the sale event
            $saleEvent = SaleEvent::findOrFail($id);
            
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

            // Calculate discount
            $discountAmount = $product->price - $productData['sale_price'];
            $discountPercentage = ($discountAmount / $product->price) * 100;

            SaleProduct::create([
                'sale_event_id' => $saleEvent->id,
                'product_id' => $productData['product_id'],
                'discount_type' => 'fixed_amount', // Using fixed amount discount type
                'discount_value' => $discountAmount, // The actual discount amount
                'sale_price' => $productData['sale_price'],
                'original_price' => $product->price,
                'max_discount_amount' => null,
                'sale_quantity_limit' => $productData['max_quantity_per_user'] ?? null,
                'sold_quantity' => 0,
                'per_user_limit' => $productData['max_quantity_per_user'] ?? 0,
                'flash_sale_duration_minutes' => 0,
                'sort_order' => 0,
                'is_featured_in_sale' => false,
                'starts_at' => null, // Will use sale event timing
                'ends_at' => null, // Will use sale event timing
            ]);
        }

            return response()->json(['message' => 'Products added to sale successfully!']);
            
        } catch (\Exception $e) {
            \Log::error('Error in addProducts: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to add products to sale',
                'message' => $e->getMessage()
            ], 500);
        }
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
        $newStatus = $saleEvent->status === 'active' ? 'paused' : 'active';
        $saleEvent->update([
            'status' => $newStatus
        ]);

        $status = $newStatus === 'active' ? 'activated' : 'deactivated';

        return response()->json([
            'message' => "Sale event {$status} successfully!",
            'status' => $newStatus
        ]);
    }

    /**
     * Get products for adding to sale (AJAX)
     */
    public function getProducts(Request $request, $id)
    {
        try {
            // Find the sale event
            $saleEvent = SaleEvent::findOrFail($id);
            
            $query = Product::with('category');

        // Filter by status (handle both numeric and string values)
        if ($request->filled('status') && $request->status !== '') {
            $query->where('status', $request->status);
        } else {
            // Default to active products (handle both 1 and 'active')
            $query->where(function($q) {
                $q->where('status', 'active')
                  ->orWhere('status', 1);
            });
        }

        // Search by name, slug, or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('slug', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Filter by category
        if ($request->filled('category_id') && $request->category_id !== '') {
            $query->where('category_id', $request->category_id);
        }

        // Exclude products already added to this sale event
        $query->whereNotIn('id', function($subQuery) use ($saleEvent) {
            $subQuery->select('product_id')
                ->from('sale_products')
                ->where('sale_event_id', $saleEvent->id);
        });

        // Pagination
        $perPage = $request->get('per_page', 10);
        $products = $query->select('id', 'name', 'slug', 'description', 'price', 'image', 'category_id', 'status')
            ->orderBy('name')
            ->paginate($perPage);

        // Format the response
        $formattedProducts = $products->getCollection()->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => $product->price,
                'image' => $product->image ? asset($product->image) : null,
                'status' => $product->status,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name
                ] : null
            ];
        });

            return response()->json([
                'data' => $formattedProducts,
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in getProducts: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load products',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}