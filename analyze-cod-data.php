<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 COD ORDER DATA ANALYSIS AFTER DELIVERY\n";
echo "==========================================\n\n";

// Check Orders Table
echo "📋 1. ORDERS TABLE (COD Delivered Orders):\n";
echo "-------------------------------------------\n";
$orders = App\Models\Order::where('payment_method', 'cod')
    ->where('status', 'delivered')
    ->get(['id', 'order_number', 'status', 'payment_status', 'grand_total', 'created_at']);

foreach ($orders as $order) {
    echo "Order: {$order->order_number}\n";
    echo "  Status: {$order->status}\n";  
    echo "  Payment Status: {$order->payment_status}\n";
    echo "  Amount: ₹{$order->grand_total}\n";
    echo "  Date: {$order->created_at}\n\n";
}

// Check Order Shipments Table
echo "📦 2. ORDER_SHIPMENTS TABLE:\n";
echo "-----------------------------\n";
$shipments = App\Models\OrderShipment::with(['order', 'carrier', 'shippingMethod'])
    ->whereHas('order', function($q) {
        $q->where('payment_method', 'cod')->where('status', 'delivered');
    })
    ->get(['id', 'order_id', 'shipment_number', 'carrier_id', 'shipping_method_id', 
           'tracking_number', 'status', 'shipped_at', 'delivered_at', 'cod_amount']);

foreach ($shipments as $shipment) {
    echo "Shipment: {$shipment->shipment_number}\n";
    echo "  Order: {$shipment->order->order_number}\n";
    echo "  Carrier: {$shipment->carrier->name}\n";
    echo "  Method: {$shipment->shippingMethod->name}\n";
    echo "  Tracking: {$shipment->tracking_number}\n";
    echo "  Status: {$shipment->status}\n";
    echo "  COD Amount: ₹{$shipment->cod_amount}\n";
    echo "  Shipped: {$shipment->shipped_at}\n";
    echo "  Delivered: {$shipment->delivered_at}\n\n";
}

// Check Shipment Items Table
echo "📝 3. SHIPMENT_ITEMS TABLE:\n";
echo "----------------------------\n";
$shipmentItems = App\Models\ShipmentItem::with(['shipment.order', 'orderItem.product'])
    ->whereHas('shipment.order', function($q) {
        $q->where('payment_method', 'cod')->where('status', 'delivered');
    })
    ->get(['id', 'shipment_id', 'order_item_id', 'quantity', 'product_name', 'declared_value']);

foreach ($shipmentItems as $item) {
    echo "Item: {$item->product_name}\n";
    echo "  Quantity: {$item->quantity}\n";
    echo "  Value: ₹{$item->declared_value}\n";
    echo "  Shipment: {$item->shipment->shipment_number}\n";
    echo "  Order: {$item->shipment->order->order_number}\n\n";
}

// Check Tracking Events Table
echo "📍 4. SHIPPING_TRACKING_EVENTS TABLE:\n";
echo "--------------------------------------\n";
$trackingEvents = App\Models\ShippingTrackingEvent::with('shipment.order')
    ->whereHas('shipment.order', function($q) {
        $q->where('payment_method', 'cod')->where('status', 'delivered');
    })
    ->orderBy('event_time', 'desc')
    ->get(['id', 'shipment_id', 'status', 'description', 'location', 'event_time']);

foreach ($trackingEvents as $event) {
    echo "Event: {$event->status}\n";
    echo "  Description: {$event->description}\n";
    echo "  Location: {$event->location}\n";
    echo "  Time: {$event->event_time}\n";
    echo "  Shipment: {$event->shipment->shipment_number}\n";
    echo "  Order: {$event->shipment->order->order_number}\n\n";
}

// Summary Statistics
echo "📊 5. SUMMARY STATISTICS:\n";
echo "-------------------------\n";
echo "Total COD Delivered Orders: " . $orders->count() . "\n";
echo "Total Shipments Created: " . $shipments->count() . "\n";
echo "Total Shipment Items: " . $shipmentItems->count() . "\n";
echo "Total Tracking Events: " . $trackingEvents->count() . "\n\n";

// Payment Status Analysis
echo "💰 6. PAYMENT STATUS ANALYSIS:\n";
echo "-------------------------------\n";
$paidOrders = $orders->where('payment_status', 'paid')->count();
$unpaidOrders = $orders->where('payment_status', 'unpaid')->count();
echo "Paid COD Orders: {$paidOrders}\n";
echo "Unpaid COD Orders: {$unpaidOrders}\n\n";

echo "✅ ANALYSIS COMPLETE!\n";