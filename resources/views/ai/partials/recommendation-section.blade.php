<div class="recommendation-section">
    <div class="section-title">
        <i class="{{ $icon }}"></i>
        <div>
            <h4 class="mb-0">{{ $title }}</h4>
            <small class="text-muted">{{ $description }}</small>
        </div>
    </div>

    @if($products && $products->count() > 0)
        <div class="recommendation-stats">
            <div class="row text-center">
                <div class="col-md-4">
                    <strong>{{ $products->count() }}</strong>
                    <small class="d-block text-muted">Products Found</small>
                </div>
                <div class="col-md-4">
                    <strong>${{ number_format($products->avg('price'), 2) }}</strong>
                    <small class="d-block text-muted">Average Price</small>
                </div>
                <div class="col-md-4">
                    <strong>{{ $products->pluck('category.name')->unique()->count() }}</strong>
                    <small class="d-block text-muted">Categories</small>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach($products as $product)
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card product-card h-100">
                        <div class="product-image" 
                             style="background-image: url('{{ $product->productMedias->first() ? asset('storage/' . $product->productMedias->first()->media_path) : asset('images/placeholder.jpg') }}');">
                            <div class="product-badge">AI Pick</div>
                        </div>
                        <div class="product-info">
                            <div class="product-category">
                                {{ $product->category->name ?? 'Uncategorized' }}
                            </div>
                            <h6 class="product-title">
                                {{ Str::limit($product->name, 60) }}
                            </h6>
                            <div class="product-price">
                                ${{ number_format($product->price, 2) }}
                                @if($product->original_price && $product->original_price > $product->price)
                                    <small class="text-muted text-decoration-line-through ms-2">
                                        ${{ number_format($product->original_price, 2) }}
                                    </small>
                                @endif
                            </div>
                            <div class="d-grid gap-2">
                                <button class="btn btn-ai add-to-cart-btn" data-product-id="{{ $product->id }}">
                                    <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                </button>
                                <a href="{{ route('product.show', $product->slug) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-eye me-2"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($products->count() >= 12)
            <div class="text-center mt-4">
                <button class="btn btn-outline-primary" id="loadMoreBtn" data-type="{{ strtolower(str_replace(' ', '_', $title)) }}">
                    <i class="fas fa-plus me-2"></i>Load More Recommendations
                </button>
            </div>
        @endif
    @else
        <div class="empty-state">
            <i class="fas fa-robot"></i>
            <h5>No Recommendations Yet</h5>
            <p class="mb-4">
                @guest
                    Sign in to get personalized recommendations, or browse our trending products!
                @else
                    Start shopping to help our AI learn your preferences and provide better recommendations.
                @endguest
            </p>
            <div class="d-flex justify-content-center gap-3">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-ai">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </a>
                @endguest
                <a href="{{ route('shop') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-shopping-cart me-2"></i>Browse Products
                </a>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load more recommendations functionality
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const type = this.dataset.type;
            const currentCount = document.querySelectorAll(`#${type.replace(/_/g, '-')} .product-card`).length;
            
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
            this.disabled = true;
            
            fetch(`/ai/api/recommendations?type=${type}&limit=8&offset=${currentCount}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.recommendations.length > 0) {
                        // Add new products to the grid
                        const productGrid = this.closest('.recommendation-section').querySelector('.row');
                        data.recommendations.forEach(product => {
                            const productCard = createProductCard(product);
                            productGrid.appendChild(productCard);
                        });
                        
                        // Reset button
                        this.innerHTML = '<i class="fas fa-plus me-2"></i>Load More Recommendations';
                        this.disabled = false;
                        
                        // Hide button if no more products
                        if (data.recommendations.length < 8) {
                            this.style.display = 'none';
                        }
                    } else {
                        this.innerHTML = '<i class="fas fa-check me-2"></i>No More Products';
                        this.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error loading more recommendations:', error);
                    this.innerHTML = '<i class="fas fa-exclamation me-2"></i>Error Loading';
                    setTimeout(() => {
                        this.innerHTML = '<i class="fas fa-plus me-2"></i>Load More Recommendations';
                        this.disabled = false;
                    }, 3000);
                });
        });
    }
    
    function createProductCard(product) {
        const col = document.createElement('div');
        col.className = 'col-lg-3 col-md-4 col-sm-6 mb-4';
        
        const imageUrl = product.product_medias && product.product_medias.length > 0 
            ? `/storage/${product.product_medias[0].media_path}` 
            : '/images/placeholder.jpg';
            
        col.innerHTML = `
            <div class="card product-card h-100">
                <div class="product-image" style="background-image: url('${imageUrl}');">
                    <div class="product-badge">AI Pick</div>
                </div>
                <div class="product-info">
                    <div class="product-category">
                        ${product.category ? product.category.name : 'Uncategorized'}
                    </div>
                    <h6 class="product-title">
                        ${product.name.length > 60 ? product.name.substring(0, 60) + '...' : product.name}
                    </h6>
                    <div class="product-price">
                        $${parseFloat(product.price).toFixed(2)}
                        ${product.original_price && product.original_price > product.price 
                            ? `<small class="text-muted text-decoration-line-through ms-2">$${parseFloat(product.original_price).toFixed(2)}</small>` 
                            : ''}
                    </div>
                    <div class="d-grid gap-2">
                        <button class="btn btn-ai add-to-cart-btn" data-product-id="${product.id}">
                            <i class="fas fa-cart-plus me-2"></i>Add to Cart
                        </button>
                        <a href="/product/${product.slug}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-eye me-2"></i>View Details
                        </a>
                    </div>
                </div>
            </div>
        `;
        
        return col;
    }
});
</script>