@foreach($products as $product)
@php $stock = $product->stocks->first(); @endphp
<div class="col-12 col-sm-6 col-md-4 col-lg-3">
    <div class="card h-100 shadow-sm position-relative">
        <button class="btn btn-sm position-absolute top-0 end-0 m-2 {{ auth()->guest() ? 'guest-wishlist' : 'wishlist-toggle' }}"
            data-product-id="{{ $product->id }}"
            style="background-color: white; border: none; z-index: 10;"
            title="Toggle Wishlist">
            <span class="wishlist-icon">
                {{ auth()->check() && $wishlistProductIds->contains($product->id) ? '❤️' : '🤍' }}
            </span>
        </button>
        <a href="{{ route('product.show', $product->slug) }}" class="text-decoration-none text-dark">
            <img src="{{ $product->image }}" class="card-img-top" alt="{{ $product->name }}" style="height: 180px; object-fit: cover;">
        </a>
        <div class="card-body d-flex flex-column">
            <h5 class="card-title">
                <a href="{{ route('product.show', $product->slug) }}" class="text-decoration-none text-dark">{{ $product->name }}</a>
            </h5>
            <p class="card-text text-muted mb-2">₹{{ $product->price }}</p>
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
                <button type="submit" class="btn btn-primary w-100">Add to Cart</button>
                @else
                <div class="text-success small">In Stock</div>
                <button type="submit" class="btn btn-primary w-100">Add to Cart</button>
                @endif
            </form>
        </div>
    </div>
</div>
@endforeach
