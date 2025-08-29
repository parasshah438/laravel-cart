@foreach($products as $product)
@php $stock = $product->stocks->first(); @endphp
<div class="col-lg-3 col-md-4 col-sm-6">
    <div class="product-card">
        <div class="product-image">
            <a href="{{ route('product.show', $product->slug) }}">
                <img src="{{ $product->image ?: 'https://via.placeholder.com/300x250/f8f9fa/6c757d?text=No+Image' }}" 
                     alt="{{ $product->name }}">
            </a>
            <button class="wishlist-btn {{ auth()->guest() ? 'guest-wishlist' : 'wishlist-toggle' }}"
                    data-product-id="{{ $product->id }}">
                <span class="wishlist-icon">
                    {{ auth()->check() && $wishlistProductIds->contains($product->id) ? '❤️' : '🤍' }}
                </span>
            </button>
        </div>
        <div class="product-info">
            <div class="product-brand">{{ $product->category->name ?? 'ShopCart' }}</div>
            <h6 class="product-name">
                <a href="{{ route('product.show', $product->slug) }}" class="text-decoration-none text-dark">
                    {{ $product->name }}
                </a>
            </h6>
            <div class="product-price">
                <span class="current-price">₹{{ number_format($product->price) }}</span>
                @if($product->original_price && $product->original_price > $product->price)
                <span class="original-price">₹{{ number_format($product->original_price) }}</span>
                <span class="discount">
                    {{ round((($product->original_price - $product->price) / $product->original_price) * 100) }}% OFF
                </span>
                @endif
            </div>
            
            @if($stock?->isOutOfStock())
            <button class="add-to-cart-btn" disabled>Out of Stock</button>
            @else
            <form class="add-to-cart-form" data-product-id="{{ $product->id }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="add-to-cart-btn">
                    Add to Bag
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
@endforeach