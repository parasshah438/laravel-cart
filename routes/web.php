<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{ProfileController, CartController, WishlistController, FrontendController, ProductController, CheckoutController, AddressController, CategoryController, ReviewController, CompareController, SupportController, WishlistShareController, NotificationController};


Route::get('/', [FrontendController::class, 'index'])->name('front.index');
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
});

Route::middleware(['auth'])->group(function () {
    //Order Management System
    Route::get('/orders', [\App\Http\Controllers\CheckoutController::class, 'orderHistory'])->name('orders.index');
    Route::get('/order/{order}', [\App\Http\Controllers\CheckoutController::class, 'orderDetails'])->name('order.details');
    Route::get('/order/{order}/track', [\App\Http\Controllers\CheckoutController::class, 'trackOrder'])->name('order.track');
    Route::post('/order/{order}/cancel', [\App\Http\Controllers\CheckoutController::class, 'cancelOrder'])->name('order.cancel');
    Route::post('/order/{order}/reorder', [\App\Http\Controllers\CheckoutController::class, 'reorder'])->name('order.reorder');
    
    //Advanced Order Management Features
    Route::post('/order/{order}/return', [\App\Http\Controllers\CheckoutController::class, 'returnOrder'])->name('order.return');
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
    Route::get('/reviews', [\App\Http\Controllers\Admin\AdminReviewController::class, 'index'])->name('reviews.index');
    Route::get('/reviews/{review}', [\App\Http\Controllers\Admin\AdminReviewController::class, 'show'])->name('reviews.show');
    Route::post('/reviews/{review}/approve', [\App\Http\Controllers\Admin\AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::post('/reviews/{review}/reject', [\App\Http\Controllers\Admin\AdminReviewController::class, 'reject'])->name('reviews.reject');
    Route::get('/reviews/analytics/data', [\App\Http\Controllers\Admin\AdminReviewController::class, 'analytics'])->name('reviews.analytics');
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


require __DIR__.'/admin.php';
require __DIR__.'/auth.php';

// Include test routes for notification system (remove in production)
include __DIR__ . '/test-notifications.php';
