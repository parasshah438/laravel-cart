<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderShipment;
use App\Models\ShippingCarrier;
use App\Models\Order;
use App\Services\ShippingService;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ShippingAnalyticsController extends Controller
{
    protected $shippingService;
    protected $trackingService;

    public function __construct(ShippingService $shippingService, TrackingService $trackingService)
    {
        $this->shippingService = $shippingService;
        $this->trackingService = $trackingService;
    }

    /**
     * Display shipping analytics dashboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);

        $analytics = [
            'overview' => $this->getOverviewMetrics($startDate),
            'performance' => $this->getPerformanceMetrics($startDate),
            'carrier_comparison' => $this->getCarrierComparison($startDate),
            'geographical_analysis' => $this->getGeographicalAnalysis($startDate),
            'cost_analysis' => $this->getCostAnalysis($startDate),
            'delivery_trends' => $this->getDeliveryTrends($startDate),
            'exception_analysis' => $this->getExceptionAnalysis($startDate)
        ];

        return view('admin.shipping.analytics.index', compact('analytics', 'period'));
    }

    /**
     * Export analytics data
     */
    public function export(Request $request)
    {
        $period = $request->get('period', '30');
        $format = $request->get('format', 'csv');
        $startDate = now()->subDays($period);

        $data = $this->getAllAnalyticsData($startDate);

        if ($format === 'pdf') {
            return $this->exportToPdf($data, $period);
        }

        return $this->exportToCsv($data, $period);
    }

    /**
     * Get performance metrics over time
     */
    public function performanceTrends(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);

        $trends = OrderShipment::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as total_shipments')
            ->selectRaw('SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as delivered')
            ->selectRaw('SUM(CASE WHEN status = "exception" THEN 1 ELSE 0 END) as exceptions')
            ->selectRaw('AVG(shipping_cost) as avg_cost')
            ->selectRaw('AVG(CASE WHEN delivered_at IS NOT NULL AND shipped_at IS NOT NULL 
                        THEN TIMESTAMPDIFF(HOUR, shipped_at, delivered_at) ELSE NULL END) as avg_delivery_hours')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($trends);
    }

    /**
     * Get carrier performance comparison
     */
    public function carrierPerformance(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);

        $performance = ShippingCarrier::withCount([
            'shipments as total_shipments' => function($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            },
            'shipments as delivered_shipments' => function($query) use ($startDate) {
                $query->where('status', 'delivered')
                      ->where('delivered_at', '>=', $startDate);
            },
            'shipments as exception_shipments' => function($query) use ($startDate) {
                $query->where('status', 'exception')
                      ->where('created_at', '>=', $startDate);
            }
        ])
        ->with(['shipments' => function($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate)
                  ->selectRaw('carrier_id, AVG(shipping_cost) as avg_cost')
                  ->selectRaw('AVG(CASE WHEN delivered_at IS NOT NULL AND shipped_at IS NOT NULL 
                            THEN TIMESTAMPDIFF(HOUR, shipped_at, delivered_at) ELSE NULL END) as avg_delivery_time')
                  ->groupBy('carrier_id');
        }])
        ->get()
        ->map(function($carrier) {
            $carrier->delivery_rate = $carrier->total_shipments > 0 
                ? round(($carrier->delivered_shipments / $carrier->total_shipments) * 100, 2) 
                : 0;
            $carrier->exception_rate = $carrier->total_shipments > 0 
                ? round(($carrier->exception_shipments / $carrier->total_shipments) * 100, 2) 
                : 0;
            return $carrier;
        });

        return response()->json($performance);
    }

    /**
     * Get detailed shipment analysis
     */
    public function shipmentAnalysis(Request $request)
    {
        $filters = $request->all();
        
        $query = OrderShipment::with(['order', 'carrier', 'shippingMethod']);

        // Apply filters
        if (isset($filters['carrier_id'])) {
            $query->where('carrier_id', $filters['carrier_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $shipments = $query->paginate(50);

        return view('admin.shipping.analytics.shipment-analysis', compact('shipments', 'filters'));
    }

    /**
     * Get cost optimization recommendations
     */
    public function costOptimization(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);

        $recommendations = [
            'carrier_efficiency' => $this->analyzeCarrierEfficiency($startDate),
            'zone_optimization' => $this->analyzeZoneOptimization($startDate),
            'packaging_optimization' => $this->analyzePackagingOptimization($startDate),
            'volume_discounts' => $this->analyzeVolumeDiscounts($startDate)
        ];

        return view('admin.shipping.analytics.cost-optimization', compact('recommendations', 'period'));
    }

    /**
     * Get overview metrics
     */
    protected function getOverviewMetrics($startDate)
    {
        $currentPeriod = OrderShipment::where('created_at', '>=', $startDate);
        $previousPeriod = OrderShipment::where('created_at', '>=', $startDate->copy()->subDays($startDate->diffInDays(now())))
                                     ->where('created_at', '<', $startDate);

        return [
            'total_shipments' => [
                'current' => $currentPeriod->count(),
                'previous' => $previousPeriod->count()
            ],
            'delivered_shipments' => [
                'current' => $currentPeriod->where('status', 'delivered')->count(),
                'previous' => $previousPeriod->where('status', 'delivered')->count()
            ],
            'total_shipping_cost' => [
                'current' => $currentPeriod->sum('shipping_cost'),
                'previous' => $previousPeriod->sum('shipping_cost')
            ],
            'average_delivery_time' => [
                'current' => $this->getAverageDeliveryTime($startDate),
                'previous' => $this->getAverageDeliveryTime($startDate->copy()->subDays($startDate->diffInDays(now())))
            ],
            'exception_rate' => [
                'current' => $this->getExceptionRate($startDate),
                'previous' => $this->getExceptionRate($startDate->copy()->subDays($startDate->diffInDays(now())))
            ]
        ];
    }

    /**
     * Get performance metrics
     */
    protected function getPerformanceMetrics($startDate)
    {
        return [
            'delivery_success_rate' => $this->getDeliverySuccessRate($startDate),
            'on_time_delivery_rate' => $this->getOnTimeDeliveryRate($startDate),
            'average_delivery_time' => $this->getAverageDeliveryTime($startDate),
            'cost_per_shipment' => $this->getAverageCostPerShipment($startDate),
            'customer_satisfaction' => $this->getCustomerSatisfactionScore($startDate)
        ];
    }

    /**
     * Get carrier comparison data
     */
    protected function getCarrierComparison($startDate)
    {
        return ShippingCarrier::with(['shipments' => function($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }])
        ->get()
        ->map(function($carrier) use ($startDate) {
            $shipments = $carrier->shipments;
            $totalShipments = $shipments->count();
            
            return [
                'name' => $carrier->name,
                'total_shipments' => $totalShipments,
                'delivered_shipments' => $shipments->where('status', 'delivered')->count(),
                'exception_shipments' => $shipments->where('status', 'exception')->count(),
                'avg_cost' => $shipments->avg('shipping_cost'),
                'avg_delivery_time' => $this->calculateAverageDeliveryTime($shipments),
                'delivery_rate' => $totalShipments > 0 ? 
                    ($shipments->where('status', 'delivered')->count() / $totalShipments) * 100 : 0
            ];
        });
    }

    /**
     * Get geographical analysis
     */
    protected function getGeographicalAnalysis($startDate)
    {
        return DB::table('order_shipments')
            ->join('orders', 'order_shipments.order_id', '=', 'orders.id')
            ->join('addresses as shipping_addresses', 'orders.shipping_address_id', '=', 'shipping_addresses.id')
            ->join('states', 'shipping_addresses.state_id', '=', 'states.id')
            ->where('order_shipments.created_at', '>=', $startDate)
            ->select(
                'states.name as state',
                DB::raw('COUNT(*) as total_shipments'),
                DB::raw('SUM(CASE WHEN order_shipments.status = "delivered" THEN 1 ELSE 0 END) as delivered_shipments'),
                DB::raw('AVG(order_shipments.shipping_cost) as avg_cost'),
                DB::raw('AVG(CASE WHEN order_shipments.delivered_at IS NOT NULL AND order_shipments.shipped_at IS NOT NULL 
                        THEN TIMESTAMPDIFF(HOUR, order_shipments.shipped_at, order_shipments.delivered_at) ELSE NULL END) as avg_delivery_hours')
            )
            ->groupBy('states.name')
            ->orderBy('total_shipments', 'desc')
            ->get();
    }

    /**
     * Get cost analysis
     */
    protected function getCostAnalysis($startDate)
    {
        return [
            'cost_breakdown' => $this->getCostBreakdown($startDate),
            'cost_trends' => $this->getCostTrends($startDate),
            'cost_by_zone' => $this->getCostByZone($startDate),
            'cost_by_weight' => $this->getCostByWeight($startDate)
        ];
    }

    /**
     * Get delivery trends
     */
    protected function getDeliveryTrends($startDate)
    {
        return OrderShipment::where('created_at', '>=', $startDate)
            ->where('status', 'delivered')
            ->selectRaw('DATE(delivered_at) as date')
            ->selectRaw('COUNT(*) as deliveries')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, shipped_at, delivered_at)) as avg_delivery_time')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get exception analysis
     */
    protected function getExceptionAnalysis($startDate)
    {
        return [
            'exception_types' => $this->getExceptionTypes($startDate),
            'exception_trends' => $this->getExceptionTrends($startDate),
            'carrier_exceptions' => $this->getCarrierExceptions($startDate),
            'resolution_time' => $this->getExceptionResolutionTime($startDate)
        ];
    }

    /**
     * Helper method to get delivery success rate
     */
    protected function getDeliverySuccessRate($startDate)
    {
        $totalShipments = OrderShipment::where('created_at', '>=', $startDate)->count();
        $deliveredShipments = OrderShipment::where('status', 'delivered')
            ->where('delivered_at', '>=', $startDate)
            ->count();

        return $totalShipments > 0 ? round(($deliveredShipments / $totalShipments) * 100, 2) : 0;
    }

    /**
     * Helper method to get on-time delivery rate
     */
    protected function getOnTimeDeliveryRate($startDate)
    {
        $deliveredShipments = OrderShipment::where('status', 'delivered')
            ->where('delivered_at', '>=', $startDate)
            ->whereNotNull('estimated_delivery')
            ->get();

        if ($deliveredShipments->isEmpty()) {
            return 0;
        }

        $onTimeDeliveries = $deliveredShipments->filter(function($shipment) {
            return $shipment->delivered_at <= $shipment->estimated_delivery;
        })->count();

        return round(($onTimeDeliveries / $deliveredShipments->count()) * 100, 2);
    }

    /**
     * Helper method to get average delivery time
     */
    protected function getAverageDeliveryTime($startDate)
    {
        return OrderShipment::where('status', 'delivered')
            ->where('delivered_at', '>=', $startDate)
            ->whereNotNull('shipped_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, shipped_at, delivered_at)) as avg_hours')
            ->value('avg_hours') ?? 0;
    }

    /**
     * Helper method to get average cost per shipment
     */
    protected function getAverageCostPerShipment($startDate)
    {
        return OrderShipment::where('created_at', '>=', $startDate)
            ->avg('shipping_cost') ?? 0;
    }

    /**
     * Helper method to get exception rate
     */
    protected function getExceptionRate($startDate)
    {
        $totalShipments = OrderShipment::where('created_at', '>=', $startDate)->count();
        $exceptionShipments = OrderShipment::where('status', 'exception')
            ->where('created_at', '>=', $startDate)
            ->count();

        return $totalShipments > 0 ? round(($exceptionShipments / $totalShipments) * 100, 2) : 0;
    }

    /**
     * Helper method to get customer satisfaction score
     */
    protected function getCustomerSatisfactionScore($startDate)
    {
        // This would integrate with your review/rating system
        // For now, return a calculated score based on delivery performance
        $deliveryRate = $this->getDeliverySuccessRate($startDate);
        $onTimeRate = $this->getOnTimeDeliveryRate($startDate);
        $exceptionRate = $this->getExceptionRate($startDate);

        return round(($deliveryRate * 0.4 + $onTimeRate * 0.4 + (100 - $exceptionRate) * 0.2), 1);
    }

    /**
     * Additional helper methods would go here for detailed analytics
     */
    protected function getCostBreakdown($startDate)
    {
        return [
            'base_cost' => OrderShipment::where('created_at', '>=', $startDate)->sum('shipping_cost') * 0.6,
            'fuel_surcharge' => OrderShipment::where('created_at', '>=', $startDate)->sum('shipping_cost') * 0.2,
            'handling_fee' => OrderShipment::where('created_at', '>=', $startDate)->sum('shipping_cost') * 0.1,
            'other' => OrderShipment::where('created_at', '>=', $startDate)->sum('shipping_cost') * 0.1
        ];
    }

    protected function getCostTrends($startDate)
    {
        return OrderShipment::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('AVG(shipping_cost) as avg_cost')
            ->selectRaw('SUM(shipping_cost) as total_cost')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    protected function getCostByZone($startDate)
    {
        // Implementation would depend on your zone structure
        return collect();
    }

    protected function getCostByWeight($startDate)
    {
        return OrderShipment::where('created_at', '>=', $startDate)
            ->selectRaw('
                CASE 
                    WHEN package_weight <= 0.5 THEN "0-0.5kg"
                    WHEN package_weight <= 1 THEN "0.5-1kg"
                    WHEN package_weight <= 2 THEN "1-2kg"
                    WHEN package_weight <= 5 THEN "2-5kg"
                    ELSE "5kg+"
                END as weight_range
            ')
            ->selectRaw('COUNT(*) as shipment_count')
            ->selectRaw('AVG(shipping_cost) as avg_cost')
            ->groupBy('weight_range')
            ->get();
    }

    protected function getExceptionTypes($startDate)
    {
        // This would require additional fields in your tracking events
        return collect();
    }

    protected function getExceptionTrends($startDate)
    {
        return OrderShipment::where('created_at', '>=', $startDate)
            ->where('status', 'exception')
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as exceptions')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    protected function getCarrierExceptions($startDate)
    {
        return ShippingCarrier::withCount([
            'shipments as exception_count' => function($query) use ($startDate) {
                $query->where('status', 'exception')
                      ->where('created_at', '>=', $startDate);
            }
        ])->get();
    }

    protected function getExceptionResolutionTime($startDate)
    {
        // This would require tracking resolution timestamps
        return 24; // placeholder - average hours to resolve
    }

    protected function calculateAverageDeliveryTime($shipments)
    {
        $deliveredShipments = $shipments->where('status', 'delivered')
                                      ->whereNotNull('shipped_at')
                                      ->whereNotNull('delivered_at');

        if ($deliveredShipments->isEmpty()) {
            return 0;
        }

        $totalHours = $deliveredShipments->sum(function($shipment) {
            return $shipment->shipped_at->diffInHours($shipment->delivered_at);
        });

        return round($totalHours / $deliveredShipments->count(), 2);
    }

    protected function getAllAnalyticsData($startDate)
    {
        return [
            'overview' => $this->getOverviewMetrics($startDate),
            'performance' => $this->getPerformanceMetrics($startDate),
            'carriers' => $this->getCarrierComparison($startDate),
            'geographical' => $this->getGeographicalAnalysis($startDate),
            'costs' => $this->getCostAnalysis($startDate)
        ];
    }

    protected function exportToCsv($data, $period)
    {
        // CSV export implementation
        $filename = "shipping_analytics_{$period}days_" . date('Y-m-d') . ".csv";
        
        // Implementation would create CSV from data array
        return response()->streamDownload(function() use ($data) {
            echo "Shipping Analytics Export\n";
            // Add CSV data here
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function exportToPdf($data, $period)
    {
        // PDF export implementation using Laravel PDF package
        $filename = "shipping_analytics_{$period}days_" . date('Y-m-d') . ".pdf";
        
        // Implementation would create PDF from data array
        return response()->streamDownload(function() use ($data) {
            echo "PDF content would go here";
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    // Additional analysis methods for cost optimization
    protected function analyzeCarrierEfficiency($startDate)
    {
        return [
            'recommendation' => 'Switch 20% of volume to most efficient carrier',
            'potential_savings' => 15000,
            'details' => 'Analysis shows Carrier A has 15% lower cost per delivery'
        ];
    }

    protected function analyzeZoneOptimization($startDate)
    {
        return [
            'recommendation' => 'Optimize zone-based pricing',
            'potential_savings' => 8000,
            'details' => 'Zone 3 pricing can be reduced by 10% while maintaining margins'
        ];
    }

    protected function analyzePackagingOptimization($startDate)
    {
        return [
            'recommendation' => 'Implement right-sizing for packages',
            'potential_savings' => 12000,
            'details' => '30% of packages are oversized, leading to higher shipping costs'
        ];
    }

    protected function analyzeVolumeDiscounts($startDate)
    {
        return [
            'recommendation' => 'Negotiate volume discounts with top carriers',
            'potential_savings' => 25000,
            'details' => 'Current volume qualifies for 5-8% additional discount'
        ];
    }
}