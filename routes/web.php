<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{ProfileController, CartController, WishlistController, FrontendController, ProductController, CheckoutController, AddressController, CategoryController};


Route::get('/', [FrontendController::class, 'index'])->name('front.index');
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
// Product routes
Route::get('/product/{slug}', [ProductController::class, 'showProduct'])->name('product.show');
Route::get('/recently-viewed', [ProductController::class, 'getRecentlyViewedProducts'])->name('product.recentlyViewed');
Route::post('/recently-viewed/clear', [ProductController::class, 'clearRecentlyViewed'])->name('recently-viewed.clear')->middleware('auth');
//trendingProducts
Route::get('/trending-products', [ProductController::class, 'getTrendingProducts'])->name('product.trending');

//wishlist
Route::middleware('auth')->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/wishlist/move-to-cart', [WishlistController::class, 'moveToCart'])->name('wishlist.moveToCart');
    Route::post('/wishlist/move-all-to-cart', [WishlistController::class, 'moveAllToCart'])->name('wishlist.moveAllToCart');
    Route::delete('/wishlist/clear', [WishlistController::class, 'clear'])->name('wishlist.clear');
    Route::post('/cart/move-to-wishlist', [CartController::class, 'moveToWishlist'])->name('cart.moveToWishlist');
});

Route::post('/cart/save-for-later', [CartController::class, 'saveForLater'])->name('cart.saveForLater');
Route::post('/cart/move-to-cart', [CartController::class, 'moveToCartFromSaved'])->name('cart.moveToCartFromSaved');
Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.applyCoupon');
Route::post('/cart/remove-coupon', [CartController::class, 'removeCoupon'])->name('cart.removeCoupon');

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

Route::get('/cart/gift-products', [CartController::class, 'getGiftProducts'])->name('cart.giftProducts');
Route::post('/cart/add-gifts', [CartController::class, 'addGifts'])->name('cart.addGifts');
Route::post('/customization/save-image', [CartController::class, 'saveCustomizationImage'])->name('customization.saveImage');


// Checkout Routes
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/address', [AddressController::class, 'store'])->name('checkout.address.saveed');
    Route::get('/checkout/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/checkout/address/select', [CheckoutController::class, 'selectAddress'])->name('checkout.address.select');
    Route::post('/checkout/address/save', [AddressController::class, 'store'])->name('checkout.address.save');
    Route::get('/checkout/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
});

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
    Route::resource('address', AddressController::class);
    Route::post('address/{address}/default', [AddressController::class, 'setDefault'])->name('address.setDefault');
});
require __DIR__.'/auth.php';
