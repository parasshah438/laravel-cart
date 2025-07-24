@if($recentlyViewed->count())
<div class="container mt-4">
    <h5>Recently Viewed Products</h5>
    <div class="row flex-nowrap overflow-auto">
        @foreach($recentlyViewed as $product)
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <div class="card mb-3">
                    <a href="{{ route('product.show', $product->slug) }}">
                        <img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}">
                    </a>
                    <div class="card-body p-2">
                        <h6 class="card-title">{{ Str::limit($product->name, 40) }}</h6>
                        <p class="card-text text-muted mb-0">₹{{ $product->price }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
