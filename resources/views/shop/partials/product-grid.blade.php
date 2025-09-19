{{-- Products Grid Partial - Using same style as _product_cards --}}
<div class="row" id="products-grid">
    @if($products->count() > 0)
        @foreach($products as $product)
        @php $stock = $product->stocks->first(); @endphp
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm position-relative product-card">
                {{-- Wishlist Button --}}
                <button class="btn btn-sm position-absolute top-0 end-0 m-2 {{ auth()->guest() ? 'guest-wishlist' : 'wishlist-toggle' }}"
                    data-product-id="{{ $product->id }}"
                    style="background-color: white; border: none; z-index: 10;"
                    title="Toggle Wishlist">
                    <span class="wishlist-icon">
                        {{ auth()->check() && $wishlistProductIds->contains($product->id) ? '❤️' : '🤍' }}
                    </span>
                </button>
                
                {{-- Product Image --}}
                <a href="{{ route('product.show', $product->slug) }}" class="text-decoration-none text-dark">
                    @if($product->image)
                        <img src="{{ $product->image }}" class="card-img-top" alt="{{ $product->name }}" style="height: 180px; object-fit: cover;">
                    @else
                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 180px;">
                            <i class="fas fa-image fa-3x text-muted"></i>
                        </div>
                    @endif
                </a>
                
                {{-- Product Details --}}
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">
                        <a href="{{ route('product.show', $product->slug) }}" class="text-decoration-none text-dark">{{ $product->name }}</a>
                    </h5>
                    
                    {{-- Price Display --}}
                    <div class="mb-2">
                        <span class="h5 text-primary mb-0">₹{{ number_format($product->price, 2) }}</span>
                        @if($product->average_rating)
                            <div class="mt-1">
                                <span class="rating-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= floor($product->average_rating))
                                            <i class="fas fa-star text-warning"></i>
                                        @elseif($i - $product->average_rating < 1)
                                            <i class="fas fa-star-half-alt text-warning"></i>
                                        @else
                                            <i class="far fa-star text-warning"></i>
                                        @endif
                                    @endfor
                                </span>
                                <small class="text-muted">({{ $product->review_count ?? 0 }})</small>
                            </div>
                        @endif
                    </div>
                    
                    {{-- Add to Cart Form --}}
                    <form method="POST" action="{{ route('cart.ajaxAdd') }}" class="mt-auto add-to-cart-form">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        @if($stock && $stock->isInStock())
                        <div class="input-group mb-2">
                            <input type="number" name="quantity" value="1" min="1" class="form-control" style="max-width: 80px;">
                        </div>
                        @endif
                        @if($stock?->isOutOfStock())
                        <button class="btn btn-secondary w-100" disabled>Out of Stock</button>
                        @elseif($stock?->isLowStock())
                        <div class="text-danger small">Only {{ $stock->qty }} left in stock!</div>
                        <button type="submit" class="btn btn-primary w-100 add-to-cart-btn">Add to Cart</button>
                        @else
                        <div class="text-success small">In Stock</div>
                        <button type="submit" class="btn btn-primary w-100 add-to-cart-btn">Add to Cart</button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    @else
        {{-- No Products Found --}}
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No Products Found</h4>
                <p class="text-muted">Try adjusting your filters or search terms.</p>
                <button class="btn btn-outline-primary" onclick="clearAllFilters()">
                    <i class="fas fa-times me-2"></i>Clear All Filters
                </button>
            </div>
        </div>
    @endif
</div>

<script>
// Wishlist toggle functionality
$(document).on('click', '.wishlist-toggle', function(e) {
    e.preventDefault();
    
    const btn = $(this);
    const productId = btn.data('product-id');
    const icon = btn.find('.wishlist-icon');
    
    $.ajax({
        url: '{{ route("wishlist.toggle") }}',
        method: 'POST',
        data: {
            product_id: productId,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                // Update heart icon
                icon.text(response.added ? '❤️' : '🤍');
                
                // Show message
                Toastify({
                    text: response.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: response.added ? "#28a745" : "#6c757d",
                }).showToast();
            }
        },
        error: function(xhr) {
            console.error('Wishlist Error:', xhr);
            Toastify({
                text: "Please login to add items to wishlist.",
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "#dc3545",
            }).showToast();
        }
    });
});

// Guest wishlist message
$(document).on('click', '.guest-wishlist', function(e) {
    e.preventDefault();
    Toastify({
        text: "Please login to add items to your wishlist.",
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "#ffc107",
    }).showToast();
});
</script>