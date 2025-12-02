<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 TESTING STRIPE INTEGRATION\n";
echo "==============================\n\n";

// Test 1: Check if StripeService can be instantiated
echo "1. Testing StripeService instantiation...\n";
try {
    $stripeService = app(\App\Services\StripeService::class);
    echo "   ✅ StripeService instantiated successfully\n";
} catch (Exception $e) {
    echo "   ❌ Failed to instantiate StripeService: " . $e->getMessage() . "\n";
}

// Test 2: Check configuration
echo "\n2. Checking Stripe configuration...\n";
$stripeConfig = config('services.stripe');
if ($stripeConfig) {
    echo "   ✅ Stripe configuration found\n";
    echo "   - Publishable Key: " . (isset($stripeConfig['publishable_key']) ? 'Set' : 'Not set') . "\n";
    echo "   - Secret Key: " . (isset($stripeConfig['secret_key']) ? 'Set' : 'Not set') . "\n";
    echo "   - Webhook Secret: " . (isset($stripeConfig['webhook_secret']) ? 'Set' : 'Not set') . "\n";
} else {
    echo "   ❌ Stripe configuration not found\n";
}

// Test 3: Check if Stripe library is available
echo "\n3. Checking Stripe PHP library...\n";
if (class_exists('\Stripe\Stripe')) {
    echo "   ✅ Stripe PHP library loaded\n";
    try {
        $version = \Stripe\Stripe::VERSION;
        echo "   - Version: $version\n";
    } catch (Exception $e) {
        echo "   - Version: Unable to detect\n";
    }
} else {
    echo "   ❌ Stripe PHP library not found\n";
}

// Test 4: Check routes
echo "\n4. Checking Stripe payment routes...\n";
$routes = [
    'payment.stripe.success',
    'payment.stripe.failure',
    'webhook.stripe'
];

foreach ($routes as $routeName) {
    try {
        $url = route($routeName);
        echo "   ✅ Route '$routeName' exists: $url\n";
    } catch (Exception $e) {
        echo "   ❌ Route '$routeName' not found\n";
    }
}

// Test 5: Check CheckoutController methods
echo "\n5. Checking CheckoutController Stripe methods...\n";
$controller = new \App\Http\Controllers\CheckoutController(
    app(\App\Services\CartService::class),
    app(\App\Services\RazorpayService::class),
    app(\App\Services\StripeService::class),
    app(\App\Services\PaymentService::class)
);

$methods = ['stripeSuccess', 'stripeFailure', 'stripeWebhook'];
foreach ($methods as $method) {
    if (method_exists($controller, $method)) {
        echo "   ✅ Method '$method' exists in CheckoutController\n";
    } else {
        echo "   ❌ Method '$method' not found in CheckoutController\n";
    }
}

// Test 6: Check views
echo "\n6. Checking Stripe views...\n";
$views = [
    'checkout.stripe-payment'
];

foreach ($views as $viewName) {
    if (view()->exists($viewName)) {
        echo "   ✅ View '$viewName' exists\n";
    } else {
        echo "   ❌ View '$viewName' not found\n";
    }
}

// Test 7: Check database schema
echo "\n7. Checking database schema...\n";
try {
    $orderColumns = DB::getSchemaBuilder()->getColumnListing('orders');
    if (in_array('stripe_payment_intent_id', $orderColumns)) {
        echo "   ✅ Column 'stripe_payment_intent_id' exists in orders table\n";
    } else {
        echo "   ❌ Column 'stripe_payment_intent_id' not found in orders table\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error checking database schema: " . $e->getMessage() . "\n";
}

// Test 8: Test Stripe service configuration method
echo "\n8. Testing StripeService configuration...\n";
try {
    if (isset($stripeService)) {
        $config = $stripeService->getConfig();
        echo "   ✅ StripeService getConfig() works\n";
        echo "   - Publishable Key: " . (isset($config['publishable_key']) ? 'Present' : 'Missing') . "\n";
        echo "   - Currency: " . ($config['currency'] ?? 'Not set') . "\n";
        echo "   - Country: " . ($config['country'] ?? 'Not set') . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error testing StripeService configuration: " . $e->getMessage() . "\n";
}

echo "\n================================\n";
echo "📋 STRIPE INTEGRATION TEST COMPLETE!\n";
echo "================================\n\n";

echo "📝 SETUP INSTRUCTIONS:\n";
echo "1. Add your Stripe keys to .env file:\n";
echo "   STRIPE_PUBLISHABLE_KEY=pk_test_...\n";
echo "   STRIPE_SECRET_KEY=sk_test_...\n";
echo "   STRIPE_WEBHOOK_SECRET=whsec_...\n\n";

echo "2. Configure Stripe webhook endpoint:\n";
echo "   URL: " . url('/webhook/stripe') . "\n";
echo "   Events: payment_intent.succeeded, payment_intent.payment_failed\n\n";

echo "3. Test the integration:\n";
echo "   - Visit: " . url('/checkout') . "\n";
echo "   - Select 'Online Payment (Stripe)' option\n";
echo "   - Complete the payment flow\n\n";

echo "✅ Integration setup complete!\n";