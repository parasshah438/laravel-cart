<?php
/**
 * 🚢 COD ORDER SHIPPING DATA ANALYSIS
 * Check which tables store data during COD order process
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use Illuminate\Support\Facades\DB;

echo "\n🚢 COD ORDER SHIPPING DATA ANALYSIS\n";
echo "===================================\n\n";

try {
    // Get table counts
    $tables = [
        'orders' => 'orders',
        'order_shipments' => 'order_shipments',
        'shipment_items' => 'shipment_items', 
        'shipping_carriers' => 'shipping_carriers',
        'shipping_methods' => 'shipping_methods',
        'shipping_tracking_events' => 'shipping_tracking_events',
        'shipping_zones' => 'shipping_zones',
        'shipping_zone_locations' => 'shipping_zone_locations'
    ];
    
    echo "📊 TABLE DATA COUNTS\n";
    echo "--------------------\n";
    
    foreach ($tables as $label => $table) {
        try {
            $count = DB::table($table)->count();
            echo sprintf("%-25s: %d records\n", $label, $count);
        } catch (Exception $e) {
            echo sprintf("%-25s: ❌ Error - %s\n", $label, $e->getMessage());
        }
    }
    
    echo "\n📋 COD ORDER ANALYSIS\n";
    echo "---------------------\n";
    
    // Find COD orders
    $codOrders = Order::where('payment_method', 'cod')->get();
    echo "Total COD orders: " . $codOrders->count() . "\n\n";
    
    if ($codOrders->count() > 0) {
        echo "📦 COD ORDER DETAILS:\n";
        foreach ($codOrders->take(3) as $order) {
            echo "Order {$order->order_number} (Status: {$order->status}):\n";
            
            // Check if order has shipment
            try {
                $shipments = DB::table('order_shipments')->where('order_id', $order->id)->get();
                echo "  - Shipments: " . $shipments->count() . "\n";
                
                if ($shipments->count() > 0) {
                    foreach ($shipments as $shipment) {
                        echo "    * Shipment ID: {$shipment->id}, Status: {$shipment->status}\n";
                        
                        // Check shipment items
                        $items = DB::table('shipment_items')->where('shipment_id', $shipment->id)->count();
                        echo "    * Items: {$items}\n";
                        
                        // Check tracking events
                        $events = DB::table('shipping_tracking_events')->where('shipment_id', $shipment->id)->count();
                        echo "    * Tracking events: {$events}\n";
                    }
                }
            } catch (Exception $e) {
                echo "  - Shipments: ❌ Error checking shipments\n";
            }
            echo "\n";
        }
    }
    
    echo "🔍 COD ORDER WORKFLOW ANALYSIS\n";
    echo "------------------------------\n";
    
    $statusCounts = Order::where('payment_method', 'cod')
        ->selectRaw('status, COUNT(*) as count')
        ->groupBy('status')
        ->get();
        
    echo "COD Orders by Status:\n";
    foreach ($statusCounts as $status) {
        echo "  - {$status->status}: {$status->count} orders\n";
    }
    
    echo "\n🎯 SHIPPING TABLE USAGE SUMMARY\n";
    echo "===============================\n";
    
    $orderShipmentCount = DB::table('order_shipments')->count();
    $shipmentItemCount = DB::table('shipment_items')->count();
    $trackingEventCount = DB::table('shipping_tracking_events')->count();
    $carrierCount = DB::table('shipping_carriers')->count();
    $methodCount = DB::table('shipping_methods')->count();
    $zoneCount = DB::table('shipping_zones')->count();
    $zoneLocationCount = DB::table('shipping_zone_locations')->count();
    
    if ($orderShipmentCount > 0) {
        echo "✅ order_shipments: BEING USED ({$orderShipmentCount} records)\n";
    } else {
        echo "❌ order_shipments: NOT BEING USED (0 records)\n";
    }
    
    if ($shipmentItemCount > 0) {
        echo "✅ shipment_items: BEING USED ({$shipmentItemCount} records)\n";
    } else {
        echo "❌ shipment_items: NOT BEING USED (0 records)\n";
    }
    
    if ($trackingEventCount > 0) {
        echo "✅ shipping_tracking_events: BEING USED ({$trackingEventCount} records)\n";
    } else {
        echo "❌ shipping_tracking_events: NOT BEING USED (0 records)\n";
    }
    
    if ($carrierCount > 0) {
        echo "✅ shipping_carriers: HAS DATA ({$carrierCount} records)\n";
    } else {
        echo "❌ shipping_carriers: NO DATA (0 records)\n";
    }
    
    if ($methodCount > 0) {
        echo "✅ shipping_methods: HAS DATA ({$methodCount} records)\n";
    } else {
        echo "❌ shipping_methods: NO DATA (0 records)\n";
    }
    
    if ($zoneCount > 0) {
        echo "✅ shipping_zones: HAS DATA ({$zoneCount} records)\n";
    } else {
        echo "❌ shipping_zones: NO DATA (0 records)\n";
    }
    
    if ($zoneLocationCount > 0) {
        echo "✅ shipping_zone_locations: HAS DATA ({$zoneLocationCount} records)\n";
    } else {
        echo "❌ shipping_zone_locations: NO DATA (0 records)\n";
    }
    
    echo "\n📝 RECOMMENDATIONS\n";
    echo "------------------\n";
    
    if ($orderShipmentCount == 0) {
        echo "🔧 You should create shipment records when orders are confirmed/shipped\n";
        echo "🔧 Add shipping carrier and method data for better tracking\n";
        echo "🔧 Consider implementing tracking events for customer visibility\n";
    }
    
    if ($carrierCount == 0) {
        echo "🔧 Add shipping carriers (e.g., Delhivery, BlueDart, DTDC) to shipping_carriers\n";
    }
    
    if ($methodCount == 0) {
        echo "🔧 Add shipping methods (e.g., Standard, Express, Same Day) to shipping_methods\n";
    }
    
    echo "\n";
    
} catch (Exception $e) {
    echo "❌ Error during analysis: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}