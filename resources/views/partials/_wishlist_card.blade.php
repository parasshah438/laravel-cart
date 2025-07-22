@foreach($wishlistItems as $item)
@php $product = $item->product; @endphp
<div class="col">
    <div class="card h-100">
        <img src="{{ $product->image }}" class="card-img-top" alt="{{ $product->name }}">
        <div class="card-body">
            <h5 class="card-title">{{ $product->name }}</h5>
            <p class="card-text">₹{{ number_format($product->price, 2) }}</p>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <button type="button" class="btn btn-sm btn-primary btn-move-to-cart" data-product-id="{{ $product->id }}">Move to Cart</button>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-from-wishlist" data-product-id="{{ $product->id }}">Remove</button>
        </div>
    </div>
</div>
@endforeach