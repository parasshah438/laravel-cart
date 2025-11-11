<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Payment;
use App\Jobs\ProcessShipmentJob;
use App\Jobs\TestShipmentJob;
use App\Jobs\SimpleProcessShipmentJob;
use App\Jobs\MinimalShipmentJob;
use App\Jobs\ProcessCODTrackingEventJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AdminOrderController extends Controller
{
    /**
     * Display the admin order dashboard with overview metrics
     */
    public function dashboard()
    {
        // Order statistics
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $confirmedOrders = Order::where('status', 'confirmed')->count();
        $shippedOrders = Order::where('status', 'shipped')->count();
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();
        
        // COD specific statistics
        $codOrders = Order::where('payment_method', 'cod')->count();
        $pendingCodOrders = Order::where('payment_method', 'cod')
                                ->where('status', 'pending')->count();
        $confirmedCodOrders = Order::where('payment_method', 'cod')
                                  ->where('status', 'confirmed')->count();
        
        // Payment statistics
        $onlineOrders = Order::where('payment_method', 'razorpay')->count();
        $paidOrders = Order::where('payment_status', 'paid')->count();
        $pendingPayments = Order::where('payment_status', 'pending')->count();
        $failedPayments = Order::where('payment_status', 'failed')->count();
        
        // Revenue statistics
        $totalRevenue = Order::where('payment_status', 'paid')->sum('grand_total');
        $todayRevenue = Order::where('payment_status', 'paid')
                           ->whereDate('created_at', today())->sum('grand_total');
        $monthRevenue = Order::where('payment_status', 'paid')
                           ->whereMonth('created_at', now()->month)
                           ->whereYear('created_at', now()->year)
                           ->sum('grand_total');
        
        // Recent orders
        $recentOrders = Order::with(['user', 'latestPayment'])
                           ->orderBy('created_at', 'desc')
                           ->limit(10)
                           ->get();
        
        // Pending COD orders that need attention
        $pendingCodList = Order::with(['user', 'address'])
                              ->where('payment_method', 'cod')
                              ->where('status', 'pending')
                              ->orderBy('created_at', 'desc')
                              ->limit(5)
                              ->get();
        
        // Orders ready for shipment
        $readyForShipment = Order::with(['user'])
                                ->where('status', 'confirmed')
                                ->where('payment_status', 'paid')
                                ->whereDoesntHave('shipments')
                                ->orderBy('created_at', 'desc')
                                ->limit(5)
                                ->get();
        
        // Daily order trends (last 7 days)
        $dailyTrends = Order::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(grand_total) as revenue')
                          ->where('created_at', '>=', now()->subDays(7))
                          ->groupBy('date')
                          ->orderBy('date')
                          ->get();
        
        return view('admin.orders.dashboard', compact(
            'totalOrders', 'pendingOrders', 'confirmedOrders', 'shippedOrders', 
            'deliveredOrders', 'cancelledOrders', 'codOrders', 'pendingCodOrders',
            'confirmedCodOrders', 'onlineOrders', 'paidOrders', 'pendingPayments',
            'failedPayments', 'totalRevenue', 'todayRevenue', 'monthRevenue',
            'recentOrders', 'pendingCodList', 'readyForShipment', 'dailyTrends'
        ));
    }
    
    /**
     * Display a listing of orders with filters and search
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'latestPayment', 'latestShipment']);
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('grand_total', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        $query->orderBy($sortBy, $sortDirection);
        
        $orders = $query->paginate(20)->appends($request->query());
        
        // Filter options for dropdowns
        $statusOptions = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled'
        ];
        
        $paymentMethods = [
            'cod' => 'Cash on Delivery',
            'razorpay' => 'Online Payment'
        ];
        
        $paymentStatuses = [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'failed' => 'Failed'
        ];
        
        return view('admin.orders.index', compact(
            'orders', 'statusOptions', 'paymentMethods', 'paymentStatuses'
        ));
    }
    
    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $order->load([
            'user', 
            'address', 
            'items.product', 
            'payments', 
            'shipments.trackingEvents',
            'latestShipment'
        ]);
        
        $trackingSteps = $order->getTrackingSteps();
        
        return view('admin.orders.show', compact('order', 'trackingSteps'));
    }
    
    /**
     * Update order status - Professional Amazon/Flipkart Style
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
            'notes' => 'nullable|string|max:500'
        ]);
        
        // 🎯 PROFESSIONAL STATUS VALIDATION
        if (!$order->canChangeStatus()) {
            return response()->json([
                'success' => false,
                'message' => "Cannot change status for orders that are {$order->status}"
            ], 422);
        }
        
        if (!$order->canTransitionTo($request->status)) {
            $availableStatuses = array_keys($order->getAvailableStatusTransitions());
            return response()->json([
                'success' => false,
                'message' => "Invalid status transition. Available transitions: " . implode(', ', $availableStatuses)
            ], 422);
        }
        
        $oldStatus = $order->status;
        
        $order->update([
            'status' => $request->status,
            'notes' => $request->notes ? array_merge($order->notes ?? [], [
                'status_update' => [
                    'from' => $oldStatus,
                    'to' => $request->status,
                    'notes' => $request->notes,
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                    'transition_message' => $order->getStatusTransitionMessage($request->status)
                ]
            ]) : $order->notes
        ]);
        
        // Handle specific status changes (Professional Flow)
        if ($request->status === 'confirmed' && $order->canCreateShipment()) {
            SimpleProcessShipmentJob::dispatch($order);
        }
        
        // Handle shipping status changes with tracking events
        if ($request->status === 'shipped') {
            $this->handleShippedStatus($order);
        } elseif ($request->status === 'delivered') {
            $this->handleDeliveredStatus($order);
        }
        
        // Professional response with transition message
        return response()->json([
            'success' => true,
            'message' => "Order status updated to {$request->status}",
            'transition_message' => $order->getStatusTransitionMessage($request->status),
            'new_status' => $request->status,
            'old_status' => $oldStatus
        ]);
    }
    
    /**
     * Display pending COD orders
     */
    public function pendingCod()
    {
        $orders = Order::with(['user', 'address', 'items'])
                      ->where('payment_method', 'cod')
                      ->where('status', 'pending')
                      ->orderBy('created_at', 'desc')
                      ->paginate(15);
        
        return view('admin.orders.cod.pending', compact('orders'));
    }
    
    /**
     * Confirm a COD order
     */
    public function confirmCod(Request $request, Order $order)
    {
        // Validate the request
        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);
        
        if ($order->payment_method !== 'cod' || $order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid order for COD confirmation'
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Prepare COD confirmation data
            $codConfirmationData = [
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now()
            ];
            
            // Add notes only if provided and not empty
            if (!empty(trim($request->notes))) {
                $codConfirmationData['notes'] = trim($request->notes);
            }
            
            // Update order status
            $order->update([
                'payment_status' => 'paid', // Will be collected on delivery
                'status' => 'confirmed',
                'notes' => array_merge($order->notes ?? [], [
                    'cod_confirmation' => $codConfirmationData
                ])
            ]);
            
            // Update payment record if exists
            $payment = $order->latestPayment();
            if ($payment) {
                $payment->update([
                    'payment_status' => 'paid',
                    'notes' => array_merge($payment->notes ?? [], [
                        'cod_confirmation' => [
                            'confirmed_by' => auth()->id(),
                            'confirmed_at' => now()
                        ]
                    ])
                ]);
            }
            
            // Trigger shipment creation (using minimal job for localhost)
            MinimalShipmentJob::dispatch($order);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "COD order #{$order->order_number} confirmed successfully! Shipment has been initiated.",
                'order_number' => $order->order_number
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm COD order: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk confirm COD orders
     */
    public function bulkConfirmCod(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id'
        ]);
        
        $confirmed = 0;
        $failed = 0;
        $errors = [];
        
        foreach ($request->order_ids as $orderId) {
            $order = Order::find($orderId);
            
            if ($order->payment_method !== 'cod' || $order->status !== 'pending') {
                $failed++;
                $errors[] = "Order {$order->order_number} is not eligible for COD confirmation";
                continue;
            }
            
            try {
                DB::beginTransaction();
                
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                    'notes' => array_merge($order->notes ?? [], [
                        'bulk_cod_confirmation' => [
                            'confirmed_by' => auth()->id(),
                            'confirmed_at' => now()
                        ]
                    ])
                ]);
                
                SimpleProcessShipmentJob::dispatch($order);
                
                DB::commit();
                $confirmed++;
                
            } catch (\Exception $e) {
                DB::rollback();
                $failed++;
                $errors[] = "Failed to confirm order {$order->order_number}: " . $e->getMessage();
            }
        }
        
        $message = "Confirmed {$confirmed} orders.";
        if ($failed > 0) {
            $message .= " {$failed} orders failed.";
        }
        
        return redirect()->back()->with([
            'success' => $message,
            'errors' => $errors
        ]);
    }
    
    /**
     * Cancel an order
     */
    public function cancel(Request $request, Order $order)
    {
        if (!$order->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be cancelled'
            ], 400);
        }
        
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);
        
        try {
            DB::beginTransaction();
            
            $order->update([
                'status' => 'cancelled',
                'notes' => array_merge($order->notes ?? [], [
                    'cancellation' => [
                        'reason' => $request->reason,
                        'cancelled_by' => auth()->id(),
                        'cancelled_at' => now()
                    ]
                ])
            ]);
            
            // Cancel related shipments if any
            $order->shipments()->update(['status' => 'cancelled']);
            
            // Handle payment refund for online payments
            if ($order->isRazorpayPayment() && $order->isPaid()) {
                // Queue refund job (implementation depends on your refund logic)
                // RefundPaymentJob::dispatch($order);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Analytics dashboard for orders
     */
    public function analytics(Request $request)
    {
        $dateRange = $request->get('date_range', '30');
        $startDate = $request->get('start_date') 
            ? Carbon::parse($request->get('start_date'))
            : now()->subDays($dateRange);
        $endDate = $request->get('end_date') 
            ? Carbon::parse($request->get('end_date'))
            : now();
        
        // Order analytics
        $analytics = [
            'total_orders' => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_revenue' => Order::whereBetween('created_at', [$startDate, $endDate])
                                  ->where('payment_status', 'paid')->sum('grand_total'),
            'average_order_value' => Order::whereBetween('created_at', [$startDate, $endDate])
                                       ->where('payment_status', 'paid')->avg('grand_total'),
            'cod_orders' => Order::whereBetween('created_at', [$startDate, $endDate])
                               ->where('payment_method', 'cod')->count(),
            'online_orders' => Order::whereBetween('created_at', [$startDate, $endDate])
                                  ->where('payment_method', 'razorpay')->count(),
            'pending_orders' => Order::whereBetween('created_at', [$startDate, $endDate])
                                   ->where('status', 'pending')->count(),
            'delivered_orders' => Order::whereBetween('created_at', [$startDate, $endDate])
                                     ->where('status', 'delivered')->count()
        ];
        
        // Daily trends
        $dailyTrends = Order::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(grand_total) as revenue')
                          ->whereBetween('created_at', [$startDate, $endDate])
                          ->groupBy('date')
                          ->orderBy('date')
                          ->get();
        
        // Status breakdown
        $statusBreakdown = Order::selectRaw('status, COUNT(*) as count')
                               ->whereBetween('created_at', [$startDate, $endDate])
                               ->groupBy('status')
                               ->pluck('count', 'status');
        
        // Payment method breakdown
        $paymentMethodBreakdown = Order::selectRaw('payment_method, COUNT(*) as count')
                                      ->whereBetween('created_at', [$startDate, $endDate])
                                      ->groupBy('payment_method')
                                      ->pluck('count', 'payment_method');
        
        // Top customers
        $topCustomers = Order::select('user_id', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(grand_total) as total_spent'))
                            ->with('user')
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->where('payment_status', 'paid')
                            ->groupBy('user_id')
                            ->orderBy('total_spent', 'desc')
                            ->limit(10)
                            ->get();
        
        return view('admin.orders.analytics', compact(
            'analytics', 'dailyTrends', 'statusBreakdown', 'paymentMethodBreakdown',
            'topCustomers', 'dateRange', 'startDate', 'endDate'
        ));
    }
    
    /**
     * Bulk update order status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
            'notes' => 'nullable|string|max:500'
        ]);
        
        $updated = 0;
        $failed = 0;
        $errors = [];
        
        foreach ($request->order_ids as $orderId) {
            $order = Order::find($orderId);
            
            try {
                // 🎯 PROFESSIONAL BULK STATUS VALIDATION
                if (!$order->canChangeStatus()) {
                    $errors[] = "Order {$order->order_number} cannot be changed (status: {$order->status})";
                    $failed++;
                    continue;
                }
                
                if (!$order->canTransitionTo($request->status)) {
                    $availableStatuses = array_keys($order->getAvailableStatusTransitions());
                    $errors[] = "Order {$order->order_number} cannot transition to {$request->status}. Available: " . implode(', ', $availableStatuses);
                    $failed++;
                    continue;
                }
                
                $oldStatus = $order->status;
                
                $order->update([
                    'status' => $request->status,
                    'notes' => $request->notes ? array_merge($order->notes ?? [], [
                        'bulk_status_update' => [
                            'from' => $oldStatus,
                            'to' => $request->status,
                            'notes' => $request->notes,
                            'updated_by' => auth()->id(),
                            'updated_at' => now(),
                            'transition_message' => $order->getStatusTransitionMessage($request->status)
                        ]
                    ]) : $order->notes
                ]);
                
                $updated++;
                
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Failed to update order {$order->order_number}: " . $e->getMessage();
            }
        }
        
        $message = "Updated {$updated} orders to {$request->status}.";
        if ($failed > 0) {
            $message .= " {$failed} orders failed.";
        }
        
        return redirect()->back()->with([
            'success' => $message,
            'errors' => $errors
        ]);
    }
    
    /**
     * Bulk cancel orders
     */
    public function bulkCancel(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'reason' => 'required|string|max:500'
        ]);
        
        $cancelled = 0;
        $failed = 0;
        $errors = [];
        
        foreach ($request->order_ids as $orderId) {
            $order = Order::find($orderId);
            
            if (!$order->canBeCancelled()) {
                $failed++;
                $errors[] = "Order {$order->order_number} cannot be cancelled";
                continue;
            }
            
            try {
                DB::beginTransaction();
                
                $order->update([
                    'status' => 'cancelled',
                    'notes' => array_merge($order->notes ?? [], [
                        'bulk_cancellation' => [
                            'reason' => $request->reason,
                            'cancelled_by' => auth()->id(),
                            'cancelled_at' => now()
                        ]
                    ])
                ]);
                
                // Cancel related shipments if any
                $order->shipments()->update(['status' => 'cancelled']);
                
                DB::commit();
                $cancelled++;
                
            } catch (\Exception $e) {
                DB::rollback();
                $failed++;
                $errors[] = "Failed to cancel order {$order->order_number}: " . $e->getMessage();
            }
        }
        
        $message = "Cancelled {$cancelled} orders.";
        if ($failed > 0) {
            $message .= " {$failed} orders failed.";
        }
        
        return redirect()->back()->with([
            'success' => $message,
            'errors' => $errors
        ]);
    }

    /**
     * Export orders to CSV
     */
    public function export(Request $request)
    {
        $query = Order::with(['user', 'latestPayment']);
        
        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'orders_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Order Number', 'Customer Name', 'Customer Email', 'Order Date',
                'Status', 'Payment Method', 'Payment Status', 'Total Amount',
                'Shipping Cost', 'Grand Total', 'Delivery Date'
            ]);
            
            // CSV data
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->user->name ?? 'N/A',
                    $order->user->email ?? 'N/A',
                    $order->created_at->format('Y-m-d H:i:s'),
                    ucfirst($order->status),
                    $order->payment_method_display,
                    ucfirst($order->payment_status),
                    $order->total,
                    $order->shipping_cost,
                    $order->grand_total,
                    $order->delivery_date ? $order->delivery_date->format('Y-m-d') : 'N/A'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Handle shipped status change with tracking events
     */
    protected function handleShippedStatus(Order $order): void
    {
        // Get the order's shipment (should exist if order was confirmed properly)
        $shipment = $order->shipments()->latest()->first();
        
        if ($shipment) {
            // Create tracking event for admin shipping action
            ProcessCODTrackingEventJob::adminShipped($shipment, auth()->id());
        }
    }

    /**
     * Handle delivered status change with tracking events
     */
    protected function handleDeliveredStatus(Order $order): void
    {
        // Get the order's shipment
        $shipment = $order->shipments()->latest()->first();
        
        if ($shipment) {
            // Create tracking event for admin delivery confirmation
            ProcessCODTrackingEventJob::adminDelivered($shipment, auth()->id(), [
                'delivery_location' => 'Customer Address',
                'delivery_notes' => 'Delivered by admin confirmation'
            ]);
        }
    }
}