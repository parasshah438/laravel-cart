<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaleAnalytic;
use App\Models\SaleEvent;
use App\Models\SaleOrder;
use App\Models\UserSaleBehavior;
use App\Models\BannerInteraction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SaleAnalyticsController extends Controller
{
    /**
     * Sales analytics dashboard
     */
    public function index(Request $request)
    {
        $dateRange = $request->get('range', '30');
        $startDate = now()->subDays($dateRange);

        // Key metrics
        $metrics = [
            'total_sales' => SaleOrder::where('created_at', '>=', $startDate)->count(),
            'total_revenue' => SaleOrder::where('created_at', '>=', $startDate)->sum('final_amount'),
            'total_savings' => SaleOrder::where('created_at', '>=', $startDate)->sum('sale_discount_amount'),
            'active_events' => SaleEvent::active()->count(),
            'conversion_rate' => $this->calculateConversionRate($startDate)
        ];

        // Sales by event
        $salesByEvent = SaleEvent::withCount(['saleOrders' => function ($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }])->with(['saleOrders' => function ($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }])->get()->map(function ($event) {
            return [
                'name' => $event->name,
                'orders' => $event->sale_orders_count,
                'revenue' => $event->saleOrders->sum('final_amount'),
                'savings' => $event->saleOrders->sum('sale_discount_amount')
            ];
        });

        // Daily sales trend
        $dailySales = SaleOrder::selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(final_amount) as revenue')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top performing products
        $topProducts = \DB::table('sale_orders')
            ->join('orders', 'sale_orders.order_id', '=', 'orders.id')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', 'products.id')
            ->selectRaw('COUNT(*) as order_count, SUM(order_items.quantity) as total_sold')
            ->where('sale_orders.created_at', '>=', $startDate)
            ->groupBy('products.id', 'products.name')
            ->orderBy('order_count', 'desc')
            ->limit(10)
            ->get();

        return view('admin.sales.analytics.index', compact(
            'metrics', 'salesByEvent', 'dailySales', 'topProducts', 'dateRange'
        ));
    }

    /**
     * Sale event specific analytics
     */
    public function eventAnalytics(SaleEvent $saleEvent)
    {
        $analytics = $saleEvent->analytics;
        if (!$analytics) {
            $analytics = SaleAnalytic::create([
                'sale_event_id' => $saleEvent->id,
                'total_views' => 0,
                'total_clicks' => 0,
                'total_orders' => 0,
                'total_revenue' => 0,
                'conversion_rate' => 0
            ]);
        }

        // Hourly breakdown for the last 24 hours
        $hourlyData = UserSaleBehavior::where('sale_event_id', $saleEvent->id)
            ->where('created_at', '>=', now()->subHours(24))
            ->selectRaw('HOUR(created_at) as hour, action_type, COUNT(*) as count')
            ->groupBy('hour', 'action_type')
            ->get()
            ->groupBy('hour');

        // User behavior patterns
        $behaviorStats = [
            'total_sessions' => UserSaleBehavior::where('sale_event_id', $saleEvent->id)
                ->distinct('session_id')->count(),
            'avg_session_duration' => $this->calculateAverageSessionDuration($saleEvent->id),
            'bounce_rate' => $this->calculateBounceRate($saleEvent->id),
            'top_actions' => UserSaleBehavior::where('sale_event_id', $saleEvent->id)
                ->selectRaw('action_type, COUNT(*) as count')
                ->groupBy('action_type')
                ->orderBy('count', 'desc')
                ->get()
        ];

        // Device and traffic analytics
        $deviceStats = UserSaleBehavior::where('sale_event_id', $saleEvent->id)
            ->selectRaw('device_type, COUNT(DISTINCT session_id) as sessions')
            ->groupBy('device_type')
            ->get();

        return view('admin.sales.analytics.event', compact(
            'saleEvent', 'analytics', 'hourlyData', 'behaviorStats', 'deviceStats'
        ));
    }

    /**
     * Banner performance analytics
     */
    public function bannerAnalytics(Request $request)
    {
        $dateRange = $request->get('range', '7');
        $startDate = now()->subDays($dateRange);

        $banners = \DB::table('sale_banners')
            ->leftJoin('banner_interactions', 'sale_banners.id', '=', 'banner_interactions.sale_banner_id')
            ->select('sale_banners.*')
            ->selectRaw('
                COUNT(CASE WHEN banner_interactions.interaction_type = "view" THEN 1 END) as total_views,
                COUNT(CASE WHEN banner_interactions.interaction_type = "click" THEN 1 END) as total_clicks,
                ROUND(
                    COUNT(CASE WHEN banner_interactions.interaction_type = "click" THEN 1 END) * 100.0 / 
                    NULLIF(COUNT(CASE WHEN banner_interactions.interaction_type = "view" THEN 1 END), 0), 2
                ) as ctr
            ')
            ->where('banner_interactions.created_at', '>=', $startDate)
            ->groupBy('sale_banners.id')
            ->get();

        return view('admin.sales.analytics.banners', compact('banners', 'dateRange'));
    }

    /**
     * Export analytics data
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'sales');
        $dateRange = $request->get('range', '30');
        $startDate = now()->subDays($dateRange);

        switch ($type) {
            case 'sales':
                return $this->exportSalesData($startDate);
            case 'behavior':
                return $this->exportBehaviorData($startDate);
            case 'events':
                return $this->exportEventData($startDate);
            default:
                return redirect()->back()->with('error', 'Invalid export type');
        }
    }

    /**
     * Real-time analytics API
     */
    public function realTimeApi(Request $request)
    {
        $eventId = $request->get('event_id');
        
        if ($eventId) {
            $data = [
                'current_viewers' => $this->getCurrentViewers($eventId),
                'orders_last_hour' => SaleOrder::where('sale_event_id', $eventId)
                    ->where('created_at', '>=', now()->subHour())
                    ->count(),
                'revenue_last_hour' => SaleOrder::where('sale_event_id', $eventId)
                    ->where('created_at', '>=', now()->subHour())
                    ->sum('final_amount'),
                'top_products' => $this->getTopProductsRealTime($eventId)
            ];
        } else {
            $data = [
                'total_active_users' => $this->getTotalActiveUsers(),
                'orders_last_hour' => SaleOrder::where('created_at', '>=', now()->subHour())->count(),
                'revenue_last_hour' => SaleOrder::where('created_at', '>=', now()->subHour())->sum('final_amount'),
                'active_events' => SaleEvent::active()->count()
            ];
        }

        return response()->json($data);
    }

    /**
     * Calculate conversion rate
     */
    private function calculateConversionRate($startDate)
    {
        $totalViews = UserSaleBehavior::where('created_at', '>=', $startDate)
            ->where('action_type', 'view_product')
            ->count();

        $totalOrders = SaleOrder::where('created_at', '>=', $startDate)->count();

        return $totalViews > 0 ? round(($totalOrders / $totalViews) * 100, 2) : 0;
    }

    /**
     * Calculate average session duration
     */
    private function calculateAverageSessionDuration($eventId)
    {
        $sessions = UserSaleBehavior::where('sale_event_id', $eventId)
            ->selectRaw('session_id, MIN(created_at) as start_time, MAX(created_at) as end_time')
            ->groupBy('session_id')
            ->having(\DB::raw('COUNT(*)'), '>', 1)
            ->get();

        if ($sessions->isEmpty()) {
            return 0;
        }

        $totalDuration = $sessions->sum(function ($session) {
            return Carbon::parse($session->end_time)->diffInMinutes(Carbon::parse($session->start_time));
        });

        return round($totalDuration / $sessions->count(), 1);
    }

    /**
     * Calculate bounce rate
     */
    private function calculateBounceRate($eventId)
    {
        $totalSessions = UserSaleBehavior::where('sale_event_id', $eventId)
            ->distinct('session_id')->count();

        $bounceSessions = UserSaleBehavior::where('sale_event_id', $eventId)
            ->selectRaw('session_id, COUNT(*) as action_count')
            ->groupBy('session_id')
            ->having('action_count', '=', 1)
            ->count();

        return $totalSessions > 0 ? round(($bounceSessions / $totalSessions) * 100, 2) : 0;
    }

    /**
     * Get current viewers for an event
     */
    private function getCurrentViewers($eventId)
    {
        return UserSaleBehavior::where('sale_event_id', $eventId)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->distinct('session_id')
            ->count();
    }

    /**
     * Get total active users across all sales
     */
    private function getTotalActiveUsers()
    {
        return UserSaleBehavior::where('created_at', '>=', now()->subMinutes(5))
            ->distinct('session_id')
            ->count();
    }

    /**
     * Get top products for real-time display
     */
    private function getTopProductsRealTime($eventId)
    {
        return \DB::table('sale_orders')
            ->join('orders', 'sale_orders.order_id', '=', 'orders.id')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name')
            ->selectRaw('COUNT(*) as sales_count')
            ->where('sale_orders.sale_event_id', $eventId)
            ->where('sale_orders.created_at', '>=', now()->subHour())
            ->groupBy('products.id', 'products.name')
            ->orderBy('sales_count', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Export sales data
     */
    private function exportSalesData($startDate)
    {
        $sales = SaleOrder::with(['order.user', 'saleEvent'])
            ->where('created_at', '>=', $startDate)
            ->get();

        $filename = 'sales_data_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($sales) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order ID', 'User', 'Sale Event', 'Original Amount', 'Final Amount', 'Discount', 'Date']);

            foreach ($sales as $sale) {
                fputcsv($file, [
                    $sale->order->order_number,
                    $sale->order->user->name,
                    $sale->saleEvent->name,
                    $sale->original_amount,
                    $sale->final_amount,
                    $sale->getTotalDiscountAmount(),
                    $sale->created_at->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export behavior data
     */
    private function exportBehaviorData($startDate)
    {
        $behaviors = UserSaleBehavior::with(['user', 'saleEvent', 'product'])
            ->where('created_at', '>=', $startDate)
            ->get();

        $filename = 'behavior_data_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($behaviors) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['User', 'Sale Event', 'Action', 'Product', 'Device', 'Timestamp']);

            foreach ($behaviors as $behavior) {
                fputcsv($file, [
                    $behavior->user?->name ?? 'Guest',
                    $behavior->saleEvent?->name ?? 'N/A',
                    $behavior->action_type,
                    $behavior->product?->name ?? 'N/A',
                    $behavior->device_type,
                    $behavior->created_at->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export event data
     */
    private function exportEventData($startDate)
    {
        $events = SaleEvent::withCount(['saleProducts', 'saleOrders'])
            ->with(['saleOrders' => function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }])->get();

        $filename = 'events_data_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($events) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Event Name', 'Type', 'Products', 'Orders', 'Revenue', 'Status', 'Start Date', 'End Date']);

            foreach ($events as $event) {
                fputcsv($file, [
                    $event->name,
                    $event->sale_type,
                    $event->sale_products_count,
                    $event->sale_orders_count,
                    $event->saleOrders->sum('final_amount'),
                    $event->isActive() ? 'Active' : 'Inactive',
                    $event->start_time->format('Y-m-d H:i:s'),
                    $event->end_time->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}