<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DynamicCoupon;
use App\Models\User;
use App\Models\SaleEvent;
use App\Models\TieredDiscount;
use App\Models\TierRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DynamicCouponController extends Controller
{
    /**
     * Display a listing of dynamic coupons
     */
    public function index(Request $request)
    {
        $query = DynamicCoupon::with(['user', 'saleEvent', 'tieredDiscounts'])
            ->withCount('saleOrders');

        // Search functionality
        if ($request->filled('search')) {
            $query->where('coupon_code', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function ($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('coupon_type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'used') {
                $query->where('is_used', true);
            } elseif ($request->status === 'expired') {
                $query->where('expires_at', '<', now());
            }
        }

        $dynamicCoupons = $query->latest()->paginate(15);

        return view('admin.sales.coupons.index', compact('dynamicCoupons'));
    }

    /**
     * Show the form for creating a new dynamic coupon
     */
    public function create()
    {
        $users = User::select('id', 'name', 'email')->get();
        $saleEvents = SaleEvent::active()->select('id', 'name')->get();
        $couponTypes = [
            'personal' => 'Personal Coupon',
            'category_based' => 'Category Based',
            'behavior_based' => 'Behavior Based',
            'loyalty_reward' => 'Loyalty Reward',
            'referral_bonus' => 'Referral Bonus',
            'cart_abandonment' => 'Cart Abandonment'
        ];

        return view('admin.sales.coupons.create', compact('users', 'saleEvents', 'couponTypes'));
    }

    /**
     * Store a newly created dynamic coupon
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'sale_event_id' => 'nullable|exists:sale_events,id',
            'coupon_code' => 'required|string|max:50|unique:dynamic_coupons',
            'coupon_type' => 'required|string|in:personal,category_based,behavior_based,loyalty_reward,referral_bonus,cart_abandonment',
            'discount_type' => 'required|string|in:percentage,fixed_amount',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'required|date|after:now',
            'description' => 'required|string',
            'conditions' => 'nullable|array',
            'is_active' => 'boolean',
            'tiers' => 'nullable|array',
            'tiers.*.min_amount' => 'required_with:tiers|numeric|min:0',
            'tiers.*.discount_type' => 'required_with:tiers|in:percentage,fixed_amount',
            'tiers.*.discount_value' => 'required_with:tiers|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Generate unique coupon code if not provided
        if (empty($data['coupon_code'])) {
            do {
                $data['coupon_code'] = 'DC' . strtoupper(Str::random(8));
            } while (DynamicCoupon::where('coupon_code', $data['coupon_code'])->exists());
        }

        // Remove tiers from main data
        $tiers = $data['tiers'] ?? [];
        unset($data['tiers']);

        $dynamicCoupon = DynamicCoupon::create($data);

        // Create tiered discounts if provided
        if (!empty($tiers)) {
            $tieredDiscount = TieredDiscount::create([
                'dynamic_coupon_id' => $dynamicCoupon->id,
                'name' => $dynamicCoupon->coupon_code . ' Tiers',
                'is_active' => true
            ]);

            foreach ($tiers as $index => $tier) {
                TierRule::create([
                    'tiered_discount_id' => $tieredDiscount->id,
                    'tier_level' => $index + 1,
                    'min_amount' => $tier['min_amount'],
                    'max_amount' => $tier['max_amount'] ?? null,
                    'discount_type' => $tier['discount_type'],
                    'discount_value' => $tier['discount_value']
                ]);
            }
        }

        return redirect()->route('admin.sales.coupons.index')
            ->with('success', 'Dynamic coupon created successfully!');
    }

    /**
     * Display the specified dynamic coupon
     */
    public function show(DynamicCoupon $dynamicCoupon)
    {
        $dynamicCoupon->load(['user', 'saleEvent', 'tieredDiscounts.tierRules', 'saleOrders.order']);
        
        $stats = [
            'total_uses' => $dynamicCoupon->used_count,
            'remaining_uses' => $dynamicCoupon->max_uses ? $dynamicCoupon->max_uses - $dynamicCoupon->used_count : 'Unlimited',
            'total_savings' => $dynamicCoupon->saleOrders->sum('coupon_discount_amount'),
            'total_orders' => $dynamicCoupon->saleOrders->count(),
            'conversion_rate' => 0 // Calculate based on views vs usage
        ];

        return view('admin.sales.coupons.show', compact('dynamicCoupon', 'stats'));
    }

    /**
     * Show the form for editing the specified dynamic coupon
     */
    public function edit(DynamicCoupon $dynamicCoupon)
    {
        $dynamicCoupon->load(['tieredDiscounts.tierRules']);
        $users = User::select('id', 'name', 'email')->get();
        $saleEvents = SaleEvent::active()->select('id', 'name')->get();
        $couponTypes = [
            'personal' => 'Personal Coupon',
            'category_based' => 'Category Based',
            'behavior_based' => 'Behavior Based',
            'loyalty_reward' => 'Loyalty Reward',
            'referral_bonus' => 'Referral Bonus',
            'cart_abandonment' => 'Cart Abandonment'
        ];

        return view('admin.sales.coupons.edit', compact('dynamicCoupon', 'users', 'saleEvents', 'couponTypes'));
    }

    /**
     * Update the specified dynamic coupon
     */
    public function update(Request $request, DynamicCoupon $dynamicCoupon)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'sale_event_id' => 'nullable|exists:sale_events,id',
            'coupon_code' => 'required|string|max:50|unique:dynamic_coupons,coupon_code,' . $dynamicCoupon->id,
            'coupon_type' => 'required|string|in:personal,category_based,behavior_based,loyalty_reward,referral_bonus,cart_abandonment',
            'discount_type' => 'required|string|in:percentage,fixed_amount',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'required|date',
            'description' => 'required|string',
            'conditions' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $dynamicCoupon->update($validator->validated());

        return redirect()->route('admin.sales.coupons.show', $dynamicCoupon)
            ->with('success', 'Dynamic coupon updated successfully!');
    }

    /**
     * Remove the specified dynamic coupon
     */
    public function destroy(DynamicCoupon $dynamicCoupon)
    {
        // Check if coupon has been used
        if ($dynamicCoupon->is_used || $dynamicCoupon->used_count > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete coupon that has been used.');
        }

        $dynamicCoupon->delete();

        return redirect()->route('admin.sales.coupons.index')
            ->with('success', 'Dynamic coupon deleted successfully!');
    }

    /**
     * Generate bulk personal coupons
     */
    public function generateBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'coupon_type' => 'required|string',
            'discount_type' => 'required|string|in:percentage,fixed_amount',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'expires_at' => 'required|date|after:now',
            'description' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $createdCoupons = 0;
        foreach ($request->user_ids as $userId) {
            do {
                $couponCode = 'BULK' . strtoupper(Str::random(8));
            } while (DynamicCoupon::where('coupon_code', $couponCode)->exists());

            DynamicCoupon::create([
                'user_id' => $userId,
                'coupon_code' => $couponCode,
                'coupon_type' => $request->coupon_type,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'min_order_amount' => $request->min_order_amount,
                'max_discount_amount' => $request->max_discount_amount,
                'max_uses' => 1,
                'expires_at' => $request->expires_at,
                'description' => $request->description,
                'is_active' => true
            ]);

            $createdCoupons++;
        }

        return response()->json([
            'message' => "{$createdCoupons} coupons generated successfully!",
            'count' => $createdCoupons
        ]);
    }

    /**
     * Toggle coupon status
     */
    public function toggleStatus(DynamicCoupon $dynamicCoupon)
    {
        $dynamicCoupon->update([
            'is_active' => !$dynamicCoupon->is_active
        ]);

        $status = $dynamicCoupon->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'message' => "Coupon {$status} successfully!",
            'status' => $dynamicCoupon->is_active
        ]);
    }

    /**
     * Get usage analytics
     */
    public function analytics(Request $request)
    {
        $dateRange = $request->get('range', '30');
        $startDate = now()->subDays($dateRange);

        $analytics = [
            'total_coupons' => DynamicCoupon::count(),
            'active_coupons' => DynamicCoupon::active()->count(),
            'used_coupons' => DynamicCoupon::where('used_count', '>', 0)->count(),
            'expired_coupons' => DynamicCoupon::where('expires_at', '<', now())->count(),
            'total_savings' => DynamicCoupon::whereHas('saleOrders')->with('saleOrders')->get()
                ->sum(function ($coupon) {
                    return $coupon->saleOrders->sum('coupon_discount_amount');
                }),
            'usage_by_type' => DynamicCoupon::selectRaw('coupon_type, count(*) as count')
                ->where('used_count', '>', 0)
                ->groupBy('coupon_type')
                ->get()
        ];

        return response()->json($analytics);
    }
}