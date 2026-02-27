<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{ProfileController, CartController, WishlistController, FrontendController, ProductController, CheckoutController, AddressController, CategoryController, ReviewController, CompareController, SupportController, WishlistShareController, NotificationController, PaymentController};


Route::get('/', [FrontendController::class, 'index'])->name('front.index');

// ================================================================================================
// 🔗 WEBHOOK ROUTES (Before other routes to avoid conflicts)
// ================================================================================================

// ShipRocket webhook for tracking updates
Route::post('/webhooks/shiprocket', [App\Http\Controllers\WebhookController::class, 'shiprocket'])
    ->name('webhooks.shiprocket');

// Razorpay webhook for payment events
Route::post('/webhooks/razorpay', [App\Http\Controllers\WebhookController::class, 'razorpay'])
    ->name('webhooks.razorpay');

Route::get('/category/{slug}', [FrontendController::class, 'categoryProducts'])->name('category.products');
Route::get('/search-suggestions', [FrontendController::class, 'searchSuggestions'])->name('search.suggestions');
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

//Product routes
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
Route::get('/product/{slug}', [ProductController::class, 'showProduct'])->name('product.show');
Route::get('/recently-viewed', [ProductController::class, 'getRecentlyViewedProducts'])->name('product.recentlyViewed');
Route::post('/recently-viewed/clear', [ProductController::class, 'clearRecentlyViewed'])->name('recently-viewed.clear')->middleware('auth');

//Shop routes
Route::get('/shop', [ProductController::class, 'shop'])->name('shop');
Route::get('/shop/products', [ProductController::class, 'getProducts'])->name('shop.products'); // AJAX endpoint
Route::get('/shop/filters', [ProductController::class, 'getFilters'])->name('shop.filters'); // AJAX endpoint
Route::get('/shop/load-more', [ProductController::class, 'loadMore'])->name('shop.load-more'); // Load more products
Route::get('/shop/search-suggestions', [ProductController::class, 'getSearchSuggestions'])->name('shop.search-suggestions'); // Search suggestions

//TrendingProducts
Route::get('/trending-products', [ProductController::class, 'trending'])->name('product.trending');
Route::get('/recommendations', [ProductController::class, 'recommendedProducts'])->name('products.recommended');
Route::get('/ai/recommendations', [App\Http\Controllers\AIController::class, 'personalizedRecommendations'])->name('ai.recommendations');
Route::get('/ai/api/recommendations', [App\Http\Controllers\AIController::class, 'getRecommendationsApi'])->name('ai.api.recommendations');


/*
Route::get('/cart', [CartController::class, 'view'])->name('cart.view');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
*/

//Cart routes
Route::get('/cart', [CartController::class, 'view'])->name('cart.view');
Route::get('/cart/load-more', [CartController::class, 'loadMore'])->name('cart.loadMore');
Route::get('/cart/total', [CartController::class, 'getTotal'])->name('cart.total');
Route::post('/cart/add', [CartController::class, 'ajaxAdd'])->name('cart.ajaxAdd');
Route::post('/cart/update', [CartController::class, 'ajaxUpdate'])->name('cart.ajaxUpdate');
Route::post('/cart/remove', [CartController::class, 'ajaxRemove'])->name('cart.ajaxRemove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::get('/cart/count', [CartController::class, 'getCartCount'])->name('cart.count');

Route::get('/cart/saved-items-refresh', [CartController::class, 'refreshSavedItemsView'])->name('cart.savedItems.refresh');
Route::get('/cart/items/refresh', [CartController::class, 'refreshCartView'])->name('cart.items.refresh');
Route::get('/cart/summary', [CartController::class, 'getCartSummary'])->name('cart.summary');

Route::post('/cart/save-for-later', [CartController::class, 'saveForLater'])->name('cart.saveForLater');
Route::post('/cart/move-to-cart', [CartController::class, 'moveToCartFromSaved'])->name('cart.moveToCartFromSaved');
Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.applyCoupon');
Route::post('/cart/remove-coupon', [CartController::class, 'removeCoupon'])->name('cart.removeCoupon');
Route::post('/cart/buy-now', [CartController::class, 'buyNow'])->name('cart.buyNow')->middleware('auth');

//Public review routes
Route::get('/reviews', [ReviewController::class, 'allReviews'])->name('reviews.all');
Route::get('/product/{product}/reviews', [ReviewController::class, 'index'])->name('product.reviews');
Route::get('/cart/available-coupons', [CartController::class, 'getAvailableCoupons'])->name('cart.availableCoupons');

Route::get('/cart/gift-products', [CartController::class, 'getGiftProducts'])->name('cart.giftProducts');
Route::post('/cart/add-gifts', [CartController::class, 'addGifts'])->name('cart.addGifts');
Route::post('/customization/save-image', [CartController::class, 'saveCustomizationImage'])->name('customization.saveImage');

Route::prefix('api')->group(function() {
    Route::get('/postal-code/{code}', [AddressController::class, 'getPostalCodeInfo']);
    Route::get('/states/{country}', [AddressController::class, 'getStates']);
    Route::get('/cities/{state}', [AddressController::class, 'getCities']);
    Route::post('/validate-postal-code', [AddressController::class, 'validatePostalCode']);
    Route::get('/address-suggestions', [AddressController::class, 'getAddressSuggestions']);
    Route::get('/default-addresses', [AddressController::class, 'getDefaultAddresses']);
    Route::get('/postal-codes/city/{cityId}', [AddressController::class, 'getPostalCodesForCity']);
    Route::get('/postal-codes/state/{stateId}', [AddressController::class, 'getPostalCodesForState']);
    Route::get('/load-address/{id}', [AddressController::class, 'apiShow']);
    Route::get('/shipping-time-slots/{method}', [CheckoutController::class, 'getTimeSlots']);
});

Route::middleware(['auth'])->group(function () {
    //Order Management System
    Route::get('/orders', [\App\Http\Controllers\CheckoutController::class, 'orderHistory'])->name('orders.index');
    Route::get('/order/{order}', [\App\Http\Controllers\CheckoutController::class, 'orderDetails'])->name('order.details');
    Route::get('/order/{order}/track', [\App\Http\Controllers\CheckoutController::class, 'trackOrder'])->name('order.track');
    Route::post('/order/{order}/cancel', [\App\Http\Controllers\CheckoutController::class, 'cancelOrder'])->name('order.cancel');
    Route::get('/order/{order}/item/{item}', [\App\Http\Controllers\CheckoutController::class, 'orderItemDetail'])->name('order.item.detail');
    Route::post('/order/{order}/item/{item}/cancel', [\App\Http\Controllers\CheckoutController::class, 'cancelOrderItem'])->name('order.item.cancel');
    Route::post('/order/{order}/reorder', [\App\Http\Controllers\CheckoutController::class, 'reorder'])->name('order.reorder');
    
    //Advanced Order Management Features
    Route::post('/order/{order}/return', [\App\Http\Controllers\CheckoutController::class, 'returnOrder'])->name('order.return');
    Route::post('/order/{order}/cancel-return', [\App\Http\Controllers\CheckoutController::class, 'cancelReturn'])->name('order.cancel-return');
    Route::post('/order/{order}/generate-return-label', [\App\Http\Controllers\CheckoutController::class, 'generateReturnLabel'])->name('order.generate-return-label');
    Route::post('/order/{order}/submit-refund-details', [\App\Http\Controllers\CheckoutController::class, 'submitRefundDetails'])->name('order.submit-refund-details');
    Route::post('/order/{order}/exchange', [\App\Http\Controllers\CheckoutController::class, 'exchangeOrder'])->name('order.exchange');
    Route::get('/order/{order}/invoice', [\App\Http\Controllers\CheckoutController::class, 'downloadInvoice'])->name('order.invoice');
    Route::get('/order/{order}/receipt', [\App\Http\Controllers\CheckoutController::class, 'downloadReceipt'])->name('order.receipt');

    //Admin order status updates (temporary for testing)
    Route::post('/admin/order/{order}/update-status', [\App\Http\Controllers\CheckoutController::class, 'updateOrderStatus'])->name('admin.order.updateStatus');

    //Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/wishlist/move-to-cart', [WishlistController::class, 'moveToCart'])->name('wishlist.moveToCart');
    Route::post('/wishlist/move-all-to-cart', [WishlistController::class, 'moveAllToCart'])->name('wishlist.moveAllToCart');
    Route::delete('/wishlist/clear', [WishlistController::class, 'clear'])->name('wishlist.clear');
    Route::post('/cart/move-to-wishlist', [CartController::class, 'moveToWishlist'])->name('cart.moveToWishlist');

    //WISHLIST SHARING (Social Features)
    Route::get('/wishlist/share', [WishlistShareController::class, 'create'])->name('wishlist.share.create');
    Route::post('/wishlist/share', [WishlistShareController::class, 'store'])->name('wishlist.share.store');
    Route::get('/wishlist/shared', [WishlistShareController::class, 'index'])->name('wishlist.shared.index');
    Route::delete('/wishlist/share/{share}', [WishlistShareController::class, 'destroy'])->name('wishlist.share.destroy');
    Route::post('/wishlist/share/{share}/toggle-visibility', [WishlistShareController::class, 'toggleVisibility'])->name('wishlist.share.toggleVisibility');
    Route::post('/wishlist/share/{share}/extend', [WishlistShareController::class, 'extend'])->name('wishlist.share.extend');

    //Write & manage reviews
    Route::post('/product/{product}/review', [ReviewController::class, 'store'])->name('review.store');
    Route::get('/review/{review}/edit', [ReviewController::class, 'edit'])->name('review.edit');
    Route::put('/review/{review}', [ReviewController::class, 'update'])->name('review.update');
    Route::delete('/review/{review}', [ReviewController::class, 'destroy'])->name('review.destroy');
    
    //Review interactions
    Route::post('/review/{review}/helpful', [ReviewController::class, 'markHelpful'])->name('review.helpful');
    Route::post('/review/{review}/report', [ReviewController::class, 'report'])->name('review.report');
    Route::post('/review/{review}/photo', [ReviewController::class, 'addPhoto'])->name('review.photo');

    //Address
    Route::resource('address', AddressController::class);
    Route::post('address/{address}/default', [AddressController::class, 'setDefault'])->name('address.setDefault');

    //checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/address', [AddressController::class, 'store'])->name('checkout.address.saveed');
    Route::get('/checkout/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/checkout/address/select', [CheckoutController::class, 'selectAddress'])->name('checkout.address.select');
    Route::post('/checkout/address/save', [AddressController::class, 'store'])->name('checkout.address.save');
    Route::get('/checkout/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');

        //Place Order
        Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])->name('checkout.placeOrder');
        //Thank You page
        Route::get('/checkout/thank-you/{order?}', [CheckoutController::class, 'thankYou'])->name('checkout.thankyou');
        
        // ================================================================================================
        // 💳 RAZORPAY PAYMENT ROUTES
        // ================================================================================================
        Route::prefix('payment')->name('payment.')->group(function() {
            // Razorpay specific routes
            Route::post('/razorpay/success', [CheckoutController::class, 'razorpaySuccess'])->name('razorpay.success');
            Route::post('/razorpay/failure', [CheckoutController::class, 'razorpayFailure'])->name('razorpay.failure');
            
            // Stripe specific routes
            Route::post('/stripe/success', [CheckoutController::class, 'stripeSuccess'])->name('stripe.success');
            Route::post('/stripe/failure', [CheckoutController::class, 'stripeFailure'])->name('stripe.failure');
            
            // Payment management routes
            Route::get('/config', [PaymentController::class, 'getRazorpayConfig'])->name('config');
            Route::post('/verify', [PaymentController::class, 'verifyPayment'])->name('verify');
            Route::get('/status/{order}', [PaymentController::class, 'getPaymentStatus'])->name('status');
            Route::post('/refund/{order}', [PaymentController::class, 'initiateRefund'])->name('refund');
            Route::get('/methods', [PaymentController::class, 'getPaymentMethods'])->name('methods');
            Route::get('/test-connection', [PaymentController::class, 'testConnection'])->name('test');
        });
        
        // Razorpay webhook (outside auth middleware)
        Route::post('/webhook/razorpay', [CheckoutController::class, 'razorpayWebhook'])->name('webhook.razorpay');
        
        // Stripe webhook (outside auth middleware)
        Route::post('/webhook/stripe', [CheckoutController::class, 'stripeWebhook'])->name('webhook.stripe');
        
        //TEST EMAIL ROUTE (Remove in production)
        Route::get('/test-order-email', function() {
            $user = auth()->user();
            if (!$user) return redirect()->route('login');
            
            $order = $user->orders()->with(['items', 'address.city', 'address.state', 'address.country'])->first();
            if (!$order) return 'No orders found. Place an order first.';
            
            return new \App\Mail\OrderConfirmation($order);
        })->name('test.order.email');
});

//compare
Route::prefix('compare')->name('compare.')->group(function() {
    Route::get('/', [CompareController::class, 'index'])->name('index');
    Route::post('/add/{product}', [CompareController::class, 'add'])->name('add');
    Route::delete('/remove/{product}', [CompareController::class, 'remove'])->name('remove');
    Route::delete('/clear', [CompareController::class, 'clear'])->name('clear');
    Route::get('/count', [CompareController::class, 'count'])->name('count');
});

// ================================================================================================
// 🔧 ADMIN ROUTES (Amazon-Style Review Management)
// ================================================================================================
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // Review Management
    Route::get('/reviews', [\App\Http\Controllers\Admin\AdminReviewController::class, 'index'])->name('reviews.index');
    Route::get('/reviews/{review}', [\App\Http\Controllers\Admin\AdminReviewController::class, 'show'])->name('reviews.show');
    Route::post('/reviews/{review}/approve', [\App\Http\Controllers\Admin\AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::post('/reviews/{review}/reject', [\App\Http\Controllers\Admin\AdminReviewController::class, 'reject'])->name('reviews.reject');
    Route::get('/reviews/analytics/data', [\App\Http\Controllers\Admin\AdminReviewController::class, 'analytics'])->name('reviews.analytics');
    
    // Return Management
    Route::get('/returns', [\App\Http\Controllers\ReturnManagementController::class, 'index'])->name('returns.index');
    Route::get('/returns/{order}', [\App\Http\Controllers\ReturnManagementController::class, 'show'])->name('returns.show');
    Route::post('/returns/{order}/update-status', [\App\Http\Controllers\ReturnManagementController::class, 'updateStatus'])->name('returns.update-status');
    Route::post('/returns/{order}/process-refund', [\App\Http\Controllers\ReturnManagementController::class, 'processRefund'])->name('returns.process-refund');
    Route::post('/returns/{order}/update-refund-status', [\App\Http\Controllers\ReturnManagementController::class, 'updateRefundStatus'])->name('returns.update-refund-status');
});

// Include test routes for Amazon review system (remove in production)
include __DIR__ . '/test-reviews.php';

//WISHLIST SHARING (Public Routes)
Route::get('/shared-wishlist/{token}', [WishlistShareController::class, 'view'])->name('wishlist.shared.view');

//support
Route::middleware('auth')->group(function() {
    // Support dashboard
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    
    //Support tickets
    Route::get('/support/tickets', [SupportController::class, 'tickets'])->name('support.tickets');
    Route::get('/support/tickets/create', [SupportController::class, 'create'])->name('support.create');
    Route::post('/support/tickets', [SupportController::class, 'store'])->name('support.store');
    Route::get('/support/tickets/{ticket}', [SupportController::class, 'show'])->name('support.show');
    Route::post('/support/tickets/{ticket}/reply', [SupportController::class, 'reply'])->name('support.reply');
    Route::post('/support/tickets/{ticket}/close', [SupportController::class, 'close'])->name('support.close');
    
    //Live chat
    Route::get('/support/chat', [SupportController::class, 'chat'])->name('support.chat');
    Route::post('/support/chat/start', [SupportController::class, 'startChat'])->name('support.chat.start');
    Route::post('/support/chat/send', [SupportController::class, 'sendMessage'])->name('support.chat.send');
    Route::post('/support/chat/{chat}/end', [SupportController::class, 'endChat'])->name('support.chat.end');
    
    //Debug route for testing chat messages
    Route::post('/support/chat/test', function(Request $request) {
        return response()->json([
            'received_data' => $request->all(),
            'has_message' => $request->has('message'),
            'has_chat_id' => $request->has('chat_id'),
            'message_value' => $request->get('message'),
            'chat_id_value' => $request->get('chat_id')
        ]);
    })->name('support.chat.test');

    // ================================================================================================
    // 🔔 NOTIFICATIONS SYSTEM
    // ================================================================================================
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/notifications/preferences', [NotificationController::class, 'preferences'])->name('notifications.preferences');
    Route::post('/notifications/preferences', [NotificationController::class, 'updatePreferences'])->name('notifications.updatePreferences');
    
    // API routes for real-time notifications
    Route::get('/api/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unreadCount');
    Route::get('/api/notifications/recent', [NotificationController::class, 'getRecent'])->name('notifications.recent');
});

//Public support routes
Route::get('/help', [SupportController::class, 'help'])->name('help');
Route::get('/faq', [SupportController::class, 'faq'])->name('faq');

//Legacy routes for backward compatibility
Route::get('/contact-us', [SupportController::class, 'contact'])->name('contact');
Route::post('/contact-us', [SupportController::class, 'submitContact'])->name('contact.submit');



//location routes

Route::get('/location-integration', function () {
    return view('location-integration');
})->name('location.integration');


Route::get('/postal-code-checker', [App\Http\Controllers\PostalCodeController::class, 'index'])->name('postal.code.checker');
Route::post('/api/validate-postal-code', [App\Http\Controllers\PostalCodeController::class, 'validatePostalCode'])->name('api.validate.postal.code');
Route::get('/api/supported-countries', [App\Http\Controllers\PostalCodeController::class, 'getSupportedCountries'])->name('api.supported.countries');

//Geolocation API routes
Route::get('/api/geo-location', [App\Http\Controllers\GeoLocationController::class, 'getCountryCode'])->name('api.geo.location');
Route::post('/api/location-details', [App\Http\Controllers\GeoLocationController::class, 'getLocationDetails'])->name('api.location.details');
Route::get('/api/location-from-ip', [App\Http\Controllers\GeoLocationController::class, 'getLocationFromIP'])->name('api.location.from.ip');
Route::post('/api/ip-location', [App\Http\Controllers\GeoLocationController::class, 'getLocationFromIP'])->name('api.ip.location');
Route::post('/api/network-location', [App\Http\Controllers\GeoLocationController::class, 'getLocationFromIP'])->name('api.network.location');
Route::get('/api/search-locations', [App\Http\Controllers\GeoLocationController::class, 'searchLocations'])->name('api.search.locations');
Route::get('/api/pincode-details', [App\Http\Controllers\GeoLocationController::class, 'getPincodeDetails'])->name('api.pincode.details');

// Location Demo Page
Route::get('/location-demo', function () {
    return view('location-demo');
})->name('location.demo');

// Location Integration Example
Route::get('/location-integration', function () {
    return view('location-integration-example');
})->name('location.integration');

// Test Location Auto-Fill (Development Only)
Route::get('/test-location-form', function () {
    $countries = \App\Models\Country::all();
    $cartItems = []; // Empty for testing
    return view('partials._address_form', compact('countries', 'cartItems'));
})->name('test.location.form');

// ================================================================================================
// 🧪 IMAGE OPTIMIZATION TESTING (Remove in production)
// ================================================================================================
include __DIR__ . '/test-image-optimization.php';
include __DIR__ . '/test-intervention.php';

// Add test upload route
Route::post('/test-image-upload', function(\Illuminate\Http\Request $request) {
    try {
        // Check PHP upload limits first
        $uploadMaxFilesize = ini_get('upload_max_filesize');
        $postMaxSize = ini_get('post_max_size');
        
        \Log::info('Upload attempt started', [
            'upload_max_filesize' => $uploadMaxFilesize,
            'post_max_size' => $postMaxSize,
            'files_received' => count($_FILES),
            'file_error_code' => $_FILES['test_image']['error'] ?? 'no file'
        ]);
        
        // Check for upload errors before validation
        if (isset($_FILES['test_image']) && $_FILES['test_image']['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE => "File exceeds upload_max_filesize ({$uploadMaxFilesize})",
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE in form',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
            ];
            
            $errorMsg = $errors[$_FILES['test_image']['error']] ?? 'Unknown upload error';
            
            return response()->json([
                'success' => false,
                'message' => "Upload error: {$errorMsg}",
                'error_code' => $_FILES['test_image']['error']
            ], 400);
        }
        
        // Validate the request
        $request->validate([
            'test_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max for testing
        ]);

        $file = $request->file('test_image');
        
        if (!$file || !$file->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file upload'
            ], 400);
        }
        
        $originalSize = $file->getSize();
        
        // Log the upload attempt
        \Log::info('Test image upload started', [
            'original_name' => $file->getClientOriginalName(),
            'size' => $originalSize,
            'mime_type' => $file->getMimeType()
        ]);
        
        // Test the ImageOptimizer
        $result = \App\Helpers\ImageOptimizer::optimizeUploadedImage(
            $file,
            'test-uploads',
            [
                'quality' => 85,
                'maxWidth' => 1200,
                'maxHeight' => 1200,
                'generateWebP' => true,
                'generateThumbnails' => true,
                'thumbnailSizes' => [150, 300, 600]
            ]
        );
        
        if (!$result || !isset($result['optimized'])) {
            return response()->json([
                'success' => false,
                'message' => 'Image optimization failed - no result returned'
            ], 500);
        }
        
        $optimizedPath = storage_path('app/public/' . $result['optimized']);
        $optimizedSize = file_exists($optimizedPath) ? filesize($optimizedPath) : 0;
        
        $compressionRatio = $originalSize > 0 ? round((($originalSize - $optimizedSize) / $originalSize) * 100, 2) : 0;
        
        // Log success
        \Log::info('Test image upload completed', [
            'original_size' => $originalSize,
            'optimized_size' => $optimizedSize,
            'compression_ratio' => $compressionRatio
        ]);
        
        return response()->json([
            'success' => true,
            'original_size' => number_format($originalSize / 1024, 2) . ' KB',
            'optimized_size' => number_format($optimizedSize / 1024, 2) . ' KB',
            'compression_ratio' => $compressionRatio,
            'files' => array_values($result)
        ]);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
        ], 422);
    } catch (\Exception $e) {
        // Log the error
        \Log::error('Test image upload failed', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Optimization failed: ' . $e->getMessage()
        ], 500);
    }
})->name('test.image.upload');

// ================================================================================================
// 🚚 SHIPROCKET WEBHOOK ROUTES (No authentication required)
// ================================================================================================
Route::prefix('webhook')->name('webhook.')->group(function () {
    Route::post('/shiprocket', [\App\Http\Controllers\ShipRocketWebhookController::class, 'handle'])->name('shiprocket');
    Route::post('/shiprocket/pickup', [\App\Http\Controllers\ShipRocketWebhookController::class, 'handlePickup'])->name('shiprocket.pickup');
    Route::post('/shiprocket/delivery', [\App\Http\Controllers\ShipRocketWebhookController::class, 'handleDelivery'])->name('shiprocket.delivery');
    Route::post('/shiprocket/return', [\App\Http\Controllers\ShipRocketWebhookController::class, 'handleReturn'])->name('shiprocket.return');
    Route::post('/shiprocket/exception', [\App\Http\Controllers\ShipRocketWebhookController::class, 'handleException'])->name('shiprocket.exception');
});

// ================================================================================================
// 🤖 CHATBOT AI ROUTES
// ================================================================================================
Route::prefix('chatbot-ai')->name('chatbot.')->group(function() {
    Route::post('/intelligent-chat', [App\Http\Controllers\ChatbotController::class, 'intelligentChat'])->name('intelligent-chat');
    Route::post('/product-consultation', [App\Http\Controllers\ChatbotController::class, 'productConsultation'])->name('product-consultation');
    Route::get('/shopping-assistant', [App\Http\Controllers\ChatbotController::class, 'shoppingAssistant'])->name('shopping-assistant');
    
    // Additional authenticated routes
    Route::middleware('auth')->group(function() {
        Route::get('/history/{sessionId}', [App\Http\Controllers\ChatbotController::class, 'getChatHistory'])->name('history');
        Route::post('/end-session/{sessionId}', [App\Http\Controllers\ChatbotController::class, 'endSession'])->name('end-session');
    });
});

require __DIR__.'/admin.php';
require __DIR__.'/auth.php';
