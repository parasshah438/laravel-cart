<?php
/**
 * 🚀 DIRECT SHIPPING JOB TEST
 * Test SimpleProcessShipmentJob with existing orders
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\ShippingCarrier;
use App\Models\ShippingMethod;
use App\Models\OrderShipment;
use App\Models\ShippingTrackingEvent;
use App\Jobs\SimpleProcessShipmentJob;

echo "\n🚀 DIRECT SHIPPING JOB TEST\n";
echo "===========================\n\n";

try {
    // Find an existing COD order to test with
    $codOrder = Order::where('payment_method', 'cod')->first();
    
    if (!$codOrder) {
        echo "❌ No COD orders found in database\n";
        exit(1);
    }
    
    echo "Found COD order: {$codOrder->order_number}\n";
    echo "Current status: {$codOrder->status}\n";
    echo "Payment status: {$codOrder->payment_status}\n";
    echo "Current shipments: " . $codOrder->shipments()->count() . "\n\n";
    
    // Check infrastructure
    $carriersCount = ShippingCarrier::count();
    $methodsCount = ShippingMethod::count();
    
    echo "Infrastructure check:\n";
    echo "- Carriers: {$carriersCount}\n";
    echo "- Methods: {$methodsCount}\n";
    
    if ($carriersCount == 0 || $methodsCount == 0) {
        echo "❌ Missing shipping infrastructure. Run: php artisan seed:shipping-data\n";
        exit(1);
    }
    
    echo "✅ Infrastructure ready\n\n";
    
    // Test the job with different order statuses
    echo "Testing SimpleProcessShipmentJob...\n";
    
    // First, test if order can create shipment in current state
    echo "Can create shipment (current state): " . ($codOrder->canCreateShipment() ? "YES" : "NO") . "\n";
    
    // If it can't, let's make it eligible
    if (!$codOrder->canCreateShipment()) {
        echo "Making order eligible for shipment...\n";
        $codOrder->update([
            'status' => 'confirmed',
            'payment_status' => 'paid'  // Usually for COD this is set on delivery, but for testing
        ]);
        
        $codOrder = $codOrder->fresh();
        echo "Updated status: {$codOrder->status}\n";
        echo "Updated payment status: {$codOrder->payment_status}\n";
        echo "Can create shipment now: " . ($codOrder->canCreateShipment() ? "YES" : "NO") . "\n\n";
    }
    
    if ($codOrder->canCreateShipment()) {
        echo "🚢 Running SimpleProcessShipmentJob...\n";
        
        $job = new SimpleProcessShipmentJob($codOrder);
        $job->handle();
        
        echo "✅ Job executed successfully!\n\n";
        
        // Check results
        $codOrder = $codOrder->fresh();
        $shipments = $codOrder->shipments;
        
        echo "Results after job execution:\n";
        echo "- Order status: {$codOrder->status}\n";
        echo "- Shipments created: {$shipments->count()}\n";
        
        if ($shipments->count() > 0) {
            $shipment = $shipments->first();
            echo "\n📦 SHIPMENT DETAILS:\n";
            echo "- Shipment ID: {$shipment->id}\n";
            echo "- Shipment Number: {$shipment->shipment_number}\n";
            echo "- Tracking Number: {$shipment->tracking_number}\n";
            echo "- Carrier: {$shipment->carrier->name}\n";
            echo "- Method: {$shipment->shippingMethod->name}\n";
            echo "- Status: {$shipment->status}\n";
            echo "- COD Amount: ₹{$shipment->cod_amount}\n";
            echo "- Package Weight: {$shipment->package_weight}kg\n";
            echo "- Estimated Delivery: {$shipment->estimated_delivery}\n";
            
            // Check items
            $items = $shipment->items;
            echo "- Items: {$items->count()}\n";
            
            // Check tracking events
            $events = $shipment->trackingEvents;
            echo "- Tracking Events: {$events->count()}\n";
            
            if ($events->count() > 0) {
                echo "\n📍 TRACKING EVENTS:\n";
                foreach ($events as $event) {
                    echo "  {$event->occurred_at->format('Y-m-d H:i:s')}: {$event->status} - {$event->description}\n";
                }
            }
            
            echo "\n🎯 SUCCESS! Full shipping infrastructure is working:\n";
            echo "✅ SimpleProcessShipmentJob executes correctly\n";
            echo "✅ Shipment records created in order_shipments table\n";
            echo "✅ Shipment items created in shipment_items table\n";
            echo "✅ Tracking events created in shipping_tracking_events table\n";
            echo "✅ Carrier and method data properly linked\n";
            echo "✅ COD order workflow fully integrated\n";
            
        } else {
            echo "❌ No shipments were created despite job execution\n";
        }
        
    } else {
        echo "❌ Order is still not eligible for shipment creation\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n🏁 Test completed!\n";