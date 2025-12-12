<!DOCTYPE html>
<html lang="en">
<head>
    <title>Product Comparison - Compare Products</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: #232f3e !important;
            border-bottom: 1px solid #ddd;
        }
        
        .navbar-brand, .navbar-nav .nav-link {
            color: #fff !important;
        }
        
        .navbar-nav .nav-link:hover {
            color: #ff9900 !important;
        }

        .compare-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .compare-container {
            min-height: 60vh;
        }

        .product-compare-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .product-compare-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .product-image-container {
            height: 250px;
            overflow: hidden;
            border-radius: 12px 12px 0 0;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 20px;
        }

        .price-current {
            color: #28a745;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .price-original {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 1rem;
        }

        .comparison-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .comparison-row {
            border-bottom: 1px solid #e9ecef;
        }

        .comparison-row:last-child {
            border-bottom: none;
        }

        .comparison-label {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            padding: 1rem;
            border-right: 1px solid #e9ecef;
        }

        .comparison-value {
            padding: 1rem;
            text-align: center;
        }

        .remove-product-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: rgba(220, 53, 69, 0.9);
            border: none;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .remove-product-btn:hover {
            background: #dc3545;
            transform: scale(1.1);
        }

        .empty-comparison {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }

        .empty-comparison i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }

        .feature-highlight {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .rating-stars {
            color: #ffc107;
        }

        .btn-add-to-cart {
            background: linear-gradient(135deg, #ff9900, #ff8800);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-add-to-cart:hover {
            background: linear-gradient(135deg, #e68900, #cc7700);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 153, 0, 0.4);
        }

        .btn-compare-more {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-compare-more:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
        }

        .comparison-stats {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }

        .sticky-actions {
            position: sticky;
            top: 20px;
            z-index: 100;
        }

        @media (max-width: 768px) {
            .comparison-table {
                font-size: 0.9rem;
            }
            
            .comparison-label,
            .comparison-value {
                padding: 0.75rem 0.5rem;
            }
            
            .product-image-container {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <!-- Simple Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-shopping-cart me-2"></i>Laravel Shop
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/shop">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/compare">Compare</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/cart">
                            <i class="fas fa-shopping-cart me-1"></i>Cart
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/wishlist">
                            <i class="fas fa-heart me-1"></i>Wishlist
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Compare Header -->
    <div class="compare-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 mb-2">
                        <i class="fas fa-balance-scale me-3"></i>Product Comparison
                    </h1>
                    <p class="lead mb-0">Compare products side by side to make the best choice</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="comparison-stats">
                        <div class="stat-item">
                            <div class="stat-number" id="compare-count">{{ $comparedProducts->count() }}</div>
                            <div class="text-muted">Products</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container compare-container">
        @if($comparedProducts->count() > 0)
            <!-- Sticky Action Bar -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="sticky-actions">
                        <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-3 shadow-sm">
                            <div>
                                <strong>{{ $comparedProducts->count() }} products</strong> being compared
                            </div>
                            <div>
                                <button class="btn btn-outline-danger me-2" onclick="clearAllComparisons()">
                                    <i class="fas fa-trash me-1"></i>Clear All
                                </button>
                                <a href="/shop" class="btn btn-compare-more">
                                    <i class="fas fa-plus me-1"></i>Add More Products
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Cards Row -->
            <div class="row g-4 mb-4">
                @foreach($comparedProducts as $product)
                <div class="col-lg-{{ $comparedProducts->count() == 1 ? '12' : ($comparedProducts->count() == 2 ? '6' : ($comparedProducts->count() == 3 ? '4' : '3')) }}" data-product-id="{{ $product->id }}">
                    <div class="product-compare-card position-relative">
                        <button class="remove-product-btn" onclick="removeFromCompare({{ $product->id }})">
                            <i class="fas fa-times"></i>
                        </button>
                        
                        <div class="product-image-container">
                            @if($product->productMedias->isNotEmpty())
                                <img src="{{ asset('storage/' . $product->productMedias->first()->image_path) }}" 
                                     alt="{{ $product->name }}" 
                                     class="product-image">
                            @else
                                <img src="https://via.placeholder.com/200x200/f8f9fa/6c757d?text=No+Image" 
                                     alt="{{ $product->name }}" 
                                     class="product-image">
                            @endif
                        </div>
                        
                        <div class="p-4">
                            <h5 class="fw-bold mb-2">{{ $product->name }}</h5>
                            <p class="text-muted small mb-3">{{ Str::limit($product->description, 100) }}</p>
                            
                            <div class="mb-3">
                                @if($product->isOnSale())
                                    <div class="price-current">₹{{ number_format($product->getSalePrice(), 2) }}</div>
                                    <div class="price-original">₹{{ number_format($product->price, 2) }}</div>
                                    <div class="text-success small">
                                        Save {{ number_format($product->getDiscountPercentage(), 1) }}%
                                    </div>
                                @elseif($product->discount_price)
                                    <div class="price-current">₹{{ number_format($product->discount_price, 2) }}</div>
                                    <div class="price-original">₹{{ number_format($product->price, 2) }}</div>
                                    <div class="text-success small">
                                        Save {{ round((($product->price - $product->discount_price) / $product->price) * 100) }}%
                                    </div>
                                @else
                                    <div class="price-current">₹{{ number_format($product->price, 2) }}</div>
                                @endif
                            </div>
                            
                            <div class="mb-3">
                                <div class="rating-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star{{ $i <= 4 ? '' : '-o' }}"></i>
                                    @endfor
                                    <span class="text-muted ms-1">(4.0)</span>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-add-to-cart" onclick="addToCart({{ $product->id }})">
                                    <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                                </button>
                                <a href="{{ route('product.show', $product->slug) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Detailed Comparison Table -->
            <div class="comparison-table">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <thead>
                            <tr class="comparison-row">
                                <th class="comparison-label">Features</th>
                                @foreach($comparedProducts as $product)
                                <th class="comparison-value">
                                    <strong>{{ Str::limit($product->name, 20) }}</strong>
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Price Comparison -->
                            <tr class="comparison-row">
                                <td class="comparison-label">
                                    <i class="fas fa-tag me-2"></i>Price
                                </td>
                                @foreach($comparedProducts as $product)
                                <td class="comparison-value">
                                    @if($product->isOnSale())
                                        <div class="price-current">₹{{ number_format($product->getSalePrice(), 2) }}</div>
                                        <div class="price-original">₹{{ number_format($product->price, 2) }}</div>
                                    @elseif($product->discount_price)
                                        <div class="price-current">₹{{ number_format($product->discount_price, 2) }}</div>
                                        <div class="price-original">₹{{ number_format($product->price, 2) }}</div>
                                    @else
                                        <div class="price-current">₹{{ number_format($product->price, 2) }}</div>
                                    @endif
                                </td>
                                @endforeach
                            </tr>

                            <!-- Category -->
                            <tr class="comparison-row">
                                <td class="comparison-label">
                                    <i class="fas fa-folder me-2"></i>Category
                                </td>
                                @foreach($comparedProducts as $product)
                                <td class="comparison-value">
                                    {{ $product->category->name ?? 'N/A' }}
                                </td>
                                @endforeach
                            </tr>

                            <!-- Brand -->
                            <tr class="comparison-row">
                                <td class="comparison-label">
                                    <i class="fas fa-trademark me-2"></i>Brand
                                </td>
                                @foreach($comparedProducts as $product)
                                <td class="comparison-value">
                                    {{ $product->brand ?? 'N/A' }}
                                </td>
                                @endforeach
                            </tr>

                            <!-- Stock Status -->
                            <tr class="comparison-row">
                                <td class="comparison-label">
                                    <i class="fas fa-warehouse me-2"></i>Stock Status
                                </td>
                                @foreach($comparedProducts as $product)
                                <td class="comparison-value">
                                    @if($product->productStocks && $product->productStocks->sum('quantity') > 0)
                                        <span class="badge bg-success">In Stock</span>
                                        <div class="small text-muted mt-1">{{ $product->productStocks->sum('quantity') }} available</div>
                                    @else
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>

                            <!-- Weight/Size -->
                            <tr class="comparison-row">
                                <td class="comparison-label">
                                    <i class="fas fa-weight-hanging me-2"></i>Weight
                                </td>
                                @foreach($comparedProducts as $product)
                                <td class="comparison-value">
                                    {{ $product->weight ?? 'N/A' }}
                                </td>
                                @endforeach
                            </tr>

                            <!-- Rating -->
                            <tr class="comparison-row">
                                <td class="comparison-label">
                                    <i class="fas fa-star me-2"></i>Rating
                                </td>
                                @foreach($comparedProducts as $product)
                                <td class="comparison-value">
                                    <div class="rating-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star{{ $i <= 4 ? '' : '-o' }}"></i>
                                        @endfor
                                    </div>
                                    <div class="text-muted small mt-1">4.0 out of 5</div>
                                </td>
                                @endforeach
                            </tr>

                            <!-- Actions -->
                            <tr class="comparison-row">
                                <td class="comparison-label">
                                    <i class="fas fa-shopping-cart me-2"></i>Actions
                                </td>
                                @foreach($comparedProducts as $product)
                                <td class="comparison-value">
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-add-to-cart btn-sm" onclick="addToCart({{ $product->id }})">
                                            Add to Cart
                                        </button>
                                        <a href="{{ route('product.show', $product->slug) }}" class="btn btn-outline-primary btn-sm">
                                            View Details
                                        </a>
                                    </div>
                                </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        @else
            <!-- Empty State -->
            <div class="empty-comparison">
                <i class="fas fa-balance-scale"></i>
                <h3>No Products to Compare</h3>
                <p class="mb-4">Start comparing products by adding them from our shop. You can compare up to 4 products at a time.</p>
                <a href="/shop" class="btn btn-compare-more">
                    <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                </a>
            </div>
        @endif
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script>
        // Toast notification function
        function showToast(message, isSuccess = true) {
            Toastify({
                text: message,
                duration: 3000,
                gravity: "top",
                position: "right",
                style: {
                    background: isSuccess ? "linear-gradient(to right, #00b09b, #96c93d)" : "linear-gradient(to right, #ff5f6d, #ffc371)",
                },
            }).showToast();
        }

        // Remove product from comparison
        function removeFromCompare(productId) {
            $.ajax({
                url: `/compare/remove/${productId}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, true);
                        $(`[data-product-id="${productId}"]`).fadeOut(300, function() {
                            $(this).remove();
                            updateCompareCount(response.count);
                            
                            // Reload page if no products left
                            if (response.count === 0) {
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            }
                        });
                    } else {
                        showToast(response.message, false);
                    }
                },
                error: function() {
                    showToast('Error removing product from comparison', false);
                }
            });
        }

        // Clear all comparisons
        function clearAllComparisons() {
            if (confirm('Are you sure you want to clear all product comparisons?')) {
                $.ajax({
                    url: '/compare/clear',
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast(response.message, true);
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            showToast(response.message, false);
                        }
                    },
                    error: function() {
                        showToast('Error clearing comparisons', false);
                    }
                });
            }
        }

        // Add to cart
        function addToCart(productId) {
            $.ajax({
                url: '/cart/add',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    product_id: productId,
                    quantity: 1
                },
                success: function(response) {
                    if (response.status) {
                        showToast(response.message, true);
                    } else {
                        showToast(response.message, false);
                    }
                },
                error: function() {
                    showToast('Error adding to cart', false);
                }
            });
        }

        // Update compare count
        function updateCompareCount(count) {
            $('#compare-count').text(count);
        }

        // Set CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
</body>
</html>