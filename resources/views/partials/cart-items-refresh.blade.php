<h5 class="mb-3">Cart Items</h5>
    @if($items->isEmpty())
    <div class="text-center py-4">
        <h5>Your cart is empty.</h5>
        <a href="/shop" class="btn btn-primary mt-3">Continue Shopping</a>
    </div>
    @else
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="cart-items-container">
            @include('partials._cart_cards', ['items' => $items])
        </tbody>
        <tfoot id="cart-totals-container">
            @include('partials._cart_totals', compact('subtotal', 'discount', 'total', 'cart'))
        </tfoot>
    </table>

    @if($items instanceof \Illuminate\Pagination\LengthAwarePaginator && $items->hasMorePages())
        <div class="text-center mt-3">
            <button class="btn btn-outline-primary" id="load-more-cart" data-next-page="{{ $items->currentPage() + 1 }}">
                Load More
            </button>
        </div>
    @endif

    <!-- ✅ SIMPLE COUPON SECTION (Manual Entry Only) -->
    <div class="card mb-3" id="coupon-section">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-tags me-2"></i>Apply Coupon</h6>
            <button class="btn btn-outline-primary btn-sm" id="viewAllCouponsBtn">
                <i class="fas fa-gift me-1"></i>Browse Offers
            </button>
        </div>
        <div class="card-body">
            <!-- Manual Coupon Input -->
            <div class="mb-3">
                <label class="form-label small text-muted">Have a coupon code?</label>
                <div class="input-group">
                    <input type="text" id="couponCode" class="form-control" placeholder="Enter coupon code">
                    <button class="btn btn-primary" id="applyCouponBtn">Apply</button>
                </div>
                <div id="couponMessage" class="mt-2 text-success d-none"></div>
                <div id="removeCouponContainer" class="mt-2 d-none">
                    <button class="btn btn-sm btn-danger" id="removeCouponBtn">Remove Coupon</button>
                </div>
            </div>

            <!-- Applied Coupon Display -->
            @if($cart->appliedCoupon)
            <div class="alert alert-success d-flex justify-content-between align-items-center" id="appliedCouponAlert">
                <div>
                    <strong><i class="fas fa-check-circle me-2"></i>{{ $cart->appliedCoupon->code }}</strong>
                    <div class="small">{{ $cart->appliedCoupon->title }}</div>
                    <div class="small text-muted">You saved ₹{{ number_format($discount, 2) }}</div>
                </div>
                <button class="btn btn-sm btn-outline-danger" id="removeCouponBtn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @else
            <div class="text-center py-2">
                <small class="text-muted">Click "View Available" to see current offers</small>
            </div>
            @endif
        </div>
    </div>

    <!-- Professional Coupons Modal (Amazon/Flipkart Style) -->
    <div class="modal fade" id="couponsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-tags me-2"></i>Available Coupons & Offers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div id="available-coupons-container">
                                <!-- Available coupons will be loaded here -->
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Cart Summary</h6>
                                    <div class="d-flex justify-content-between">
                                        <span>Subtotal:</span>
                                        <span>₹<span id="cart-subtotal-display">{{ number_format($subtotal, 2) }}</span></span>
                                    </div>
                                    <div class="d-flex justify-content-between text-success">
                                        <span>Discount:</span>
                                        <span>-₹<span id="cart-discount-display">{{ number_format($discount, 2) }}</span></span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total:</span>
                                        <span>₹<span id="cart-total-display">{{ number_format($total, 2) }}</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <button class="btn btn-danger btn-sm" id="clear-cart-btn">
            <i class="bi bi-trash"></i> Clear Cart
        </button>
    </div>
@endif