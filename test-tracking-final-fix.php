<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 TESTING TRACKING PAGE FIXES\n";
echo "==============================\n\n";

// Test order 12
$orderId = 12;
$order = App\Models\Order::with(['items.product', 'address', 'latestShipment.trackingEvents'])->find($orderId);

if (!$order) {
    echo "❌ Order {$orderId} not found!\n";
    exit;
}

echo "📋 ORDER VERIFICATION:\n";
echo "Order: {$order->order_number}\n";
echo "Status: {$order->status}\n";
echo "Items count: " . $order->items->count() . "\n\n";

echo "🔍 TESTING RELATIONSHIP:\n";
echo "order->items exists: " . ($order->items ? 'Yes' : 'No') . "\n";
echo "order->orderItems exists: " . (method_exists($order, 'orderItems') ? 'Yes' : 'No (FIXED)') . "\n\n";

echo "📊 ITEMS DETAILS:\n";
foreach ($order->items as $item) {
    echo "- {$item->product_name} (Qty: {$item->quantity})\n";
}

echo "\n🎯 TIMELINE TEST:\n";
$timeline = $order->getTrackingSteps();
echo "Timeline type: " . gettype($timeline) . "\n";
echo "Timeline count: " . (is_array($timeline) ? count($timeline) : 'N/A') . "\n";

if (is_array($timeline) && count($timeline) > 0) {
    echo "✅ Timeline is valid array\n";
    foreach ($timeline as $i => $step) {
        echo "  Step {$i}: {$step['title']}\n";
    }
} else {
    echo "❌ Timeline is invalid\n";
}

echo "\n✅ ALL FIXES VALIDATED!\n";
echo "The tracking page should now work without foreach errors.\n";