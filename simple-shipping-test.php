<?php
// Simple test for SimpleProcessShipmentJob
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\ShippingCarrier;
use App\Models\ShippingMethod;

echo "🧪 Testing Shipping Infrastructure...\n\n";

// Check carriers
$carriers = ShippingCarrier::count();
echo "Carriers: {$carriers}\n";

// Check methods  
$methods = ShippingMethod::count();
echo "Methods: {$methods}\n";

// Check existing orders
$codOrders = Order::where('payment_method', 'cod')->count();
echo "COD Orders: {$codOrders}\n";

// Check if we have any delivered COD orders to test with
$deliveredOrder = Order::where('payment_method', 'cod')
    ->where('status', 'delivered')
    ->first();

if ($deliveredOrder) {
    echo "\nFound delivered COD order: {$deliveredOrder->order_number}\n";
    echo "Shipments: " . $deliveredOrder->shipments()->count() . "\n";
} else {
    echo "\nNo delivered COD orders found\n";
}

echo "\n✅ Basic infrastructure check complete\n";