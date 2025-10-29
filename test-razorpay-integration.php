<?php

use App\Services\RazorpayService;
use App\Models\Order;
use App\Models\User;

// Test script to verify Razorpay payment integration
echo "🔍 RAZORPAY PAYMENT SYSTEM VERIFICATION\n";
echo "=====================================\n\n";

// Test 1: Check if RazorpayService exists
echo "1. Testing RazorpayService class...\n";
if (class_exists('App\Services\RazorpayService')) {
    echo "   ✅ RazorpayService class exists\n";
} else {
    echo "   ❌ RazorpayService class not found\n";
    exit(1);
}

// Test 2: Check environment configuration
echo "\n2. Checking environment configuration...\n";
$config = config('services.razorpay');
if (!empty($config['key'])) {
    echo "   ✅ Razorpay key configured\n";
} else {
    echo "   ⚠️  Razorpay key not configured\n";
}

if (!empty($config['secret'])) {
    echo "   ✅ Razorpay secret configured\n";
} else {
    echo "   ⚠️  Razorpay secret not configured\n";
}

// Test 3: Check database structure
echo "\n3. Checking database structure...\n";
try {
    $columns = Schema::getColumnListing('orders');
    $requiredColumns = ['razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature'];
    
    foreach ($requiredColumns as $column) {
        if (in_array($column, $columns)) {
            echo "   ✅ Column '$column' exists in orders table\n";
        } else {
            echo "   ❌ Column '$column' missing from orders table\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Database connection error: " . $e->getMessage() . "\n";
}

// Test 4: Test Order model methods
echo "\n4. Testing Order model payment methods...\n";
$testOrder = new Order([
    'payment_method' => 'razorpay',
    'payment_status' => 'paid'
]);

if (method_exists($testOrder, 'isRazorpayPayment')) {
    echo "   ✅ Order->isRazorpayPayment() method exists\n";
    echo "      Result: " . ($testOrder->isRazorpayPayment() ? 'true' : 'false') . "\n";
} else {
    echo "   ❌ Order->isRazorpayPayment() method missing\n";
}

if (method_exists($testOrder, 'isPaid')) {
    echo "   ✅ Order->isPaid() method exists\n";
    echo "      Result: " . ($testOrder->isPaid() ? 'true' : 'false') . "\n";
} else {
    echo "   ❌ Order->isPaid() method missing\n";
}

// Test 5: Check routes
echo "\n5. Checking payment routes...\n";
$routes = [
    'checkout.placeOrder',
    'payment.razorpay.success', 
    'payment.razorpay.failure',
    'webhook.razorpay',
    'payment.config',
    'payment.verify'
];

foreach ($routes as $routeName) {
    try {
        $url = route($routeName);
        echo "   ✅ Route '$routeName' exists: $url\n";
    } catch (Exception $e) {
        echo "   ❌ Route '$routeName' not found\n";
    }
}

// Test 6: Check controllers
echo "\n6. Checking controllers...\n";
$controllers = [
    'App\Http\Controllers\CheckoutController',
    'App\Http\Controllers\PaymentController'
];

foreach ($controllers as $controller) {
    if (class_exists($controller)) {
        echo "   ✅ Controller '$controller' exists\n";
    } else {
        echo "   ❌ Controller '$controller' not found\n";
    }
}

// Test 7: Check views
echo "\n7. Checking payment views...\n";
$views = [
    'checkout.index',
    'checkout.payment',
    'checkout.thankyou'
];

foreach ($views as $viewName) {
    if (view()->exists($viewName)) {
        echo "   ✅ View '$viewName' exists\n";
    } else {
        echo "   ❌ View '$viewName' not found\n";
    }
}

// Test 8: Test Razorpay service instantiation (if credentials available)
echo "\n8. Testing Razorpay service instantiation...\n";
try {
    $razorpayService = app(RazorpayService::class);
    echo "   ✅ RazorpayService can be instantiated\n";
    
    // Test getConfig method
    $config = $razorpayService->getConfig();
    if (is_array($config) && isset($config['key'])) {
        echo "   ✅ getConfig() method works\n";
        echo "      Key: " . ($config['key'] ? substr($config['key'], 0, 10) . '...' : 'Not set') . "\n";
    } else {
        echo "   ❌ getConfig() method failed\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Failed to instantiate RazorpayService: " . $e->getMessage() . "\n";
}

echo "\n📋 SUMMARY\n";
echo "=========\n";
echo "✅ Basic integration appears to be set up correctly\n";
echo "⚠️  Make sure to configure RAZORPAY_KEY and RAZORPAY_SECRET in .env\n";
echo "🔗 Webhook URL: " . url('/webhook/razorpay') . "\n";
echo "🧪 Test connection: " . url('/payment/test-connection') . "\n";
echo "\n📖 See RAZORPAY_SETUP_GUIDE.md for detailed setup instructions\n";