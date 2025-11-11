<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 TESTING TRACKING FUNCTIONALITY\n";
echo "==================================\n\n";

// Test the CheckoutController trackOrder method directly
$orderId = 12;
$order = App\Models\Order::with(['items.product', 'address', 'latestShipment.trackingEvents'])->find($orderId);

if (!$order) {
    echo "❌ Order {$orderId} not found!\n";
    exit;
}

echo "📋 ORDER FOUND:\n";
echo "Order: {$order->order_number}\n";
echo "Status: {$order->status}\n\n";

echo "🔧 TESTING TRACKING STEPS:\n";
$timeline = $order->getTrackingSteps();

if ($timeline === null) {
    echo "❌ Timeline is NULL\n";
} elseif (!is_array($timeline)) {
    echo "❌ Timeline is not an array: " . gettype($timeline) . "\n";
} elseif (empty($timeline)) {
    echo "❌ Timeline is empty array\n";
} else {
    echo "✅ Timeline has " . count($timeline) . " steps:\n\n";
    
    foreach ($timeline as $key => $step) {
        echo "Step {$key}:\n";
        echo "  Title: " . ($step['title'] ?? 'N/A') . "\n";
        echo "  Description: " . ($step['description'] ?? 'N/A') . "\n";
        echo "  Icon: " . ($step['icon'] ?? 'N/A') . "\n";
        echo "  Completed: " . (($step['completed'] ?? false) ? 'Yes' : 'No') . "\n";
        echo "  Date: " . (isset($step['date']) ? $step['date'] : 'N/A') . "\n\n";
    }
}

echo "🎯 SIMULATION COMPLETE!\n";
echo "The foreach error should now be fixed.\n";
echo "Visit: http://127.0.0.1:8000/order/{$orderId}/track\n";