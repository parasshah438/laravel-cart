<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 DEBUGGING ORDER TRACKING ISSUE\n";
echo "===================================\n\n";

// Get order ID 12
$orderId = 12;
$order = App\Models\Order::with(['items.product', 'address', 'latestShipment.trackingEvents'])->find($orderId);

if (!$order) {
    echo "❌ Order {$orderId} not found!\n";
    exit;
}

echo "📋 ORDER DETAILS:\n";
echo "Order: {$order->order_number}\n";
echo "Status: {$order->status}\n";
echo "Payment Method: {$order->payment_method}\n\n";

echo "📦 SHIPMENT DETAILS:\n";
$latestShipment = $order->latestShipment;
if ($latestShipment) {
    echo "Shipment: {$latestShipment->shipment_number}\n";
    echo "Status: {$latestShipment->status}\n";
    echo "Tracking Events Count: " . $latestShipment->trackingEvents()->count() . "\n\n";
    
    echo "📍 TRACKING EVENTS:\n";
    foreach ($latestShipment->trackingEvents as $event) {
        echo "- {$event->status}: {$event->description} (Location: {$event->location})\n";
    }
} else {
    echo "No shipment found for this order\n";
}

echo "\n🔧 TESTING getTrackingSteps() METHOD:\n";
try {
    $trackingSteps = $order->getTrackingSteps();
    if ($trackingSteps === null) {
        echo "❌ getTrackingSteps() returned NULL\n";
    } elseif (is_array($trackingSteps)) {
        echo "✅ getTrackingSteps() returned array with " . count($trackingSteps) . " steps\n";
        foreach ($trackingSteps as $i => $step) {
            echo "Step {$i}: {$step['title']} - {$step['description']}\n";
        }
    } else {
        echo "❌ getTrackingSteps() returned unexpected type: " . gettype($trackingSteps) . "\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR in getTrackingSteps(): " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n✅ DEBUG COMPLETE!\n";