@php $stock = $product->stocks->first(); @endphp
<div class="card h-100 shadow-sm position-relative product-card-slider">
    <button class="btn btn-sm position-absolute top-0 end-0 m-2 {{ auth()->guest() ? 'guest-wishlist' : 'wishlist-toggle' }}"
        data-product-id="{{ $product->id }}"
        style="background-color: white; border: none; z-index: 10; border-radius: 50%; width: 35px; height: 35px;"
        title="Toggle Wishlist">
        <span class="wishlist-icon">
            {{ auth()->check() && $wishlistProductIds->contains($product->id) ? '❤️' : '🤍' }}
        </span>
    </button>
    
    <a href="{{ route('product.show', $product->slug) }}" class="text-decoration-none text-dark">
        <img src="{{ $product->image }}" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
    </a>
    
    <div class="card-body d-flex flex-column p-3">
        <h6 class="card-title mb-2">
            <a href="{{ route('product.show', $product->slug) }}" class="text-decoration-none text-dark text-truncate d-block">{{ $product->name }}</a>
        </h6>
        @if($product->isOnSale())
            <p class="text-success fw-bold mb-1">₹{{ number_format($product->getSalePrice(), 2) }}</p>
            <p class="text-muted text-decoration-line-through small mb-1">₹{{ number_format($product->price, 2) }}</p>
            <div class="badge bg-danger small mb-2">{{ number_format($product->getDiscountPercentage(), 1) }}% OFF</div>
        @else
            <p class="text-success fw-bold mb-2">₹{{ number_format($product->price, 2) }}</p>
        @endif
        
        @if($stock?->isOutOfStock())
            <button class="btn btn-secondary w-100 btn-sm mt-auto" disabled>Out of Stock</button>
        @else
            @auth
            <form method="POST" action="{{ route('cart.ajaxAdd') }}" class="mt-auto add-to-cart-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                
                @if($stock?->isLowStock())
                    <div class="text-danger small mb-1">Only {{ $stock->qty }} left!</div>
                @endif
                
                <button type="submit" class="btn btn-primary w-100 btn-sm">
                    <i class="fas fa-cart-plus"></i> Add to Cart
                </button>
            </form>
            @else
            <button type="button" class="btn btn-primary w-100 btn-sm mt-auto" onclick="window.location.href='{{ route('login') }}'">
                <i class="fas fa-sign-in-alt"></i> Login to Buy
            </button>
            @endauth
        @endif
    </div>
</div>
