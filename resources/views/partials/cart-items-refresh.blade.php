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

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Apply Coupon</h5>
            <div class="input-group">
                <input type="text" id="couponCode" class="form-control" placeholder="Enter coupon code">
                <button class="btn btn-primary" id="applyCouponBtn">Apply</button>
            </div>
            <div id="couponMessage" class="mt-2 text-success d-none"></div>
            <div id="removeCouponContainer" class="mt-2 d-none">
                <button class="btn btn-sm btn-danger" id="removeCouponBtn">Remove Coupon</button>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <button class="btn btn-danger btn-sm" id="clear-cart-btn">
            <i class="bi bi-trash"></i> Clear Cart
        </button>
    </div>
@endif