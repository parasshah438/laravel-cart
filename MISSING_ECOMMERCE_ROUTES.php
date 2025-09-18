<?php

// ================================================================================================
// 🚨 CRITICAL MISSING E-COMMERCE ROUTES
// Add these routes to web.php for complete e-commerce functionality
// ================================================================================================

use App\Http\Controllers\{
    ReviewController, 
    OrderController, 
    NotificationController, 
    SupportController,
    CompareController,
    WishlistShareController
};

// ================================================================================================
// 📝 REVIEWS & RATINGS SYSTEM (High Priority)
// ================================================================================================
Route::middleware('auth')->group(function() {
    // Write & manage reviews
    Route::post('/product/{product}/review', [ReviewController::class, 'store'])->name('review.store');
    Route::put('/review/{review}', [ReviewController::class, 'update'])->name('review.update');
    Route::delete('/review/{review}', [ReviewController::class, 'destroy'])->name('review.destroy');
    
    // Review interactions
    Route::post('/review/{review}/helpful', [ReviewController::class, 'markHelpful'])->name('review.helpful');
    Route::post('/review/{review}/report', [ReviewController::class, 'report'])->name('review.report');
    Route::post('/review/{review}/photo', [ReviewController::class, 'addPhoto'])->name('review.photo');
});

// Public review routes
Route::get('/product/{product}/reviews', [ReviewController::class, 'index'])->name('product.reviews');
Route::get('/reviews/search', [ReviewController::class, 'search'])->name('reviews.search');

// ================================================================================================
// 📋 COMPLETE ORDER MANAGEMENT (Critical Missing)
// ================================================================================================
Route::middleware('auth')->group(function() {
    // Order listing & details
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/search', [OrderController::class, 'search'])->name('orders.search');
    
    // Order tracking & status
    Route::get('/order/{order}/track', [OrderController::class, 'track'])->name('order.track');
    Route::get('/order/{order}/timeline', [OrderController::class, 'timeline'])->name('order.timeline');
    
    // Order actions
    Route::post('/order/{order}/cancel', [OrderController::class, 'cancel'])->name('order.cancel');
    Route::post('/order/{order}/return', [OrderController::class, 'initateReturn'])->name('order.return');
    Route::get('/order/{order}/return/form', [OrderController::class, 'returnForm'])->name('order.return.form');
    Route::post('/order/{order}/exchange', [OrderController::class, 'exchange'])->name('order.exchange');
    
    // Order documents
    Route::get('/order/{order}/invoice', [OrderController::class, 'invoice'])->name('order.invoice');
    Route::get('/order/{order}/receipt', [OrderController::class, 'receipt'])->name('order.receipt');
    
    // Reorder functionality
    Route::post('/order/{order}/reorder', [OrderController::class, 'reorder'])->name('order.reorder');
    Route::post('/order/{order}/buy-again', [OrderController::class, 'buyAgain'])->name('order.buyAgain');
});

// ================================================================================================
// 🔔 NOTIFICATIONS SYSTEM (User Engagement)
// ================================================================================================
Route::middleware('auth')->group(function() {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/notifications/preferences', [NotificationController::class, 'preferences'])->name('notifications.preferences');
    Route::post('/notifications/preferences', [NotificationController::class, 'updatePreferences'])->name('notifications.updatePreferences');
});

// ================================================================================================
// ❓ CUSTOMER SUPPORT SYSTEM (Professional Support)
// ================================================================================================
Route::middleware('auth')->group(function() {
    // Support tickets
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::get('/support/tickets', [SupportController::class, 'tickets'])->name('support.tickets');
    Route::get('/support/tickets/create', [SupportController::class, 'create'])->name('support.create');
    Route::post('/support/tickets', [SupportController::class, 'store'])->name('support.store');
    Route::get('/support/tickets/{ticket}', [SupportController::class, 'show'])->name('support.show');
    Route::post('/support/tickets/{ticket}/reply', [SupportController::class, 'reply'])->name('support.reply');
    Route::post('/support/tickets/{ticket}/close', [SupportController::class, 'close'])->name('support.close');
    
    // Live chat
    Route::get('/support/chat', [SupportController::class, 'chat'])->name('support.chat');
    Route::post('/support/chat/send', [SupportController::class, 'sendMessage'])->name('support.chat.send');
});

// Public support routes
Route::get('/help', [SupportController::class, 'help'])->name('help');
Route::get('/faq', [SupportController::class, 'faq'])->name('faq');
Route::get('/contact', [SupportController::class, 'contact'])->name('contact');
Route::post('/contact', [SupportController::class, 'submitContact'])->name('contact.submit');

// ================================================================================================
// ⚖️ PRODUCT COMPARISON (Amazon Style)
// ================================================================================================
Route::prefix('compare')->group(function() {
    Route::get('/', [CompareController::class, 'index'])->name('compare.index');
    Route::post('/add/{product}', [CompareController::class, 'add'])->name('compare.add');
    Route::delete('/remove/{product}', [CompareController::class, 'remove'])->name('compare.remove');
    Route::delete('/clear', [CompareController::class, 'clear'])->name('compare.clear');
    Route::get('/count', [CompareController::class, 'count'])->name('compare.count');
});

// ================================================================================================
// 🔗 WISHLIST SHARING (Social Features)
// ================================================================================================
Route::middleware('auth')->group(function() {
    Route::get('/wishlist/share', [WishlistShareController::class, 'create'])->name('wishlist.share.create');
    Route::post('/wishlist/share', [WishlistShareController::class, 'store'])->name('wishlist.share.store');
    Route::get('/wishlist/shared', [WishlistShareController::class, 'index'])->name('wishlist.shared.index');
});
Route::get('/shared-wishlist/{token}', [WishlistShareController::class, 'view'])->name('wishlist.shared.view');

// ================================================================================================
// 🔍 ADVANCED SEARCH & FILTERS
// ================================================================================================
Route::prefix('search')->group(function() {
    Route::get('/advanced', [\App\Http\Controllers\SearchController::class, 'advanced'])->name('search.advanced');
    Route::post('/save-filter', [\App\Http\Controllers\SearchController::class, 'saveFilter'])->name('search.saveFilter');
    Route::get('/autocomplete', [\App\Http\Controllers\SearchController::class, 'autocomplete'])->name('search.autocomplete');
    Route::get('/trending', [\App\Http\Controllers\SearchController::class, 'trending'])->name('search.trending');
});

// ================================================================================================
// 📊 USER ANALYTICS & INSIGHTS
// ================================================================================================
Route::middleware('auth')->prefix('account')->group(function() {
    Route::get('/insights', [\App\Http\Controllers\UserController::class, 'insights'])->name('account.insights');
    Route::get('/spending-analysis', [\App\Http\Controllers\UserController::class, 'spendingAnalysis'])->name('account.spending');
    Route::get('/order-history-export', [\App\Http\Controllers\UserController::class, 'exportOrderHistory'])->name('account.export');
    Route::get('/data-download', [\App\Http\Controllers\UserController::class, 'downloadData'])->name('account.data.download');
});

// ================================================================================================
// 🎁 GIFT CARDS & VOUCHERS
// ================================================================================================
Route::prefix('gift-cards')->group(function() {
    Route::get('/', [\App\Http\Controllers\GiftCardController::class, 'index'])->name('giftcards.index');
    Route::get('/purchase', [\App\Http\Controllers\GiftCardController::class, 'purchase'])->name('giftcards.purchase');
    Route::post('/buy', [\App\Http\Controllers\GiftCardController::class, 'buy'])->name('giftcards.buy');
    Route::post('/redeem', [\App\Http\Controllers\GiftCardController::class, 'redeem'])->name('giftcards.redeem');
});

Route::middleware('auth')->group(function() {
    Route::get('/gift-cards/my-cards', [\App\Http\Controllers\GiftCardController::class, 'myCards'])->name('giftcards.my');
    Route::get('/gift-cards/{card}', [\App\Http\Controllers\GiftCardController::class, 'show'])->name('giftcards.show');
});

// ================================================================================================
// 📱 MOBILE APP API ROUTES (If planning mobile app)
// ================================================================================================
Route::prefix('api/v1')->group(function() {
    // Product APIs
    Route::get('/products/featured', [\App\Http\Controllers\Api\ProductController::class, 'featured']);
    Route::get('/products/trending', [\App\Http\Controllers\Api\ProductController::class, 'trending']);
    Route::get('/products/deals', [\App\Http\Controllers\Api\ProductController::class, 'deals']);
    
    // User APIs
    Route::middleware('auth:sanctum')->group(function() {
        Route::get('/user/dashboard', [\App\Http\Controllers\Api\UserController::class, 'dashboard']);
        Route::get('/user/quick-reorder', [\App\Http\Controllers\Api\UserController::class, 'quickReorder']);
    });
});