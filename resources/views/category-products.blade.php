<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $category->name }} - ShopCart</title>
    <meta name="description" content="Shop {{ $category->name }} products at ShopCart. Best prices, fast delivery, authentic products.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    
    <style>
        /* Custom Category Page Styles */
        .category-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            position: relative;
            overflow: hidden;
        }
        
        .category-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="10" cy="60" r="1" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grain)"/></svg>') repeat;
            opacity: 0.3;
        }
        
        .category-hero-content {
            position: relative;
            z-index: 2;
        }
        
        .category-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }
        
        .breadcrumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 8px 20px;
            margin-bottom: 2rem;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            color: rgba(255, 255, 255, 0.7);
        }
        
        .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: white;
        }
        
        .filter-sidebar {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .filter-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 8px;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover .product-image {
            transform: scale(1.05);
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-title {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: #007bff;
            margin-bottom: 10px;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-add-cart {
            flex: 1;
            background: #007bff;
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .btn-add-cart:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }
        
        .btn-wishlist {
            background: white;
            border: 2px solid #e9ecef;
            color: #6c757d;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .btn-wishlist:hover {
            border-color: #ff4757;
            color: #ff4757;
        }
        
        .no-products {
            text-align: center;
            padding: 60px 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }
        
        .no-products i {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        .sort-filter-bar {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .results-info {
            color: #6c757d;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .category-hero {
                padding: 40px 0;
            }
            
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 15px;
            }
            
            .filter-sidebar {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header (Same as main page) -->
    <header class="sticky-top">
        <!-- Top Bar -->
        <div class="bg-dark text-white py-2">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <small><i class="fas fa-phone me-2"></i>+1 234 567 8900 | <i class="fas fa-envelope me-2"></i>support@shopcart.com</small>
                    </div>
                    <div class="col-md-6 text-end">
                        <small>Free shipping on orders over $50!</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-bold fs-2 text-primary" href="{{ route('front.index') }}">
                    <i class="fas fa-shopping-bag me-2"></i>ShopCart
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <div class="navbar-nav me-auto">
                        <a class="nav-link" href="{{ route('front.index') }}">Home</a>
                    </div>

                    <!-- Search Bar -->
                    <div class="d-flex align-items-center">
                        <form class="d-flex me-3" style="min-width: 300px;">
                            <div class="input-group">
                                <input class="form-control" type="search" placeholder="Search products..." name="search" value="{{ $searchQuery }}">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>

                        <!-- User Actions -->
                        <div class="d-flex align-items-center gap-3">
                            @auth
                                <a href="{{ route('wishlist.index') }}" class="text-decoration-none">
                                    <i class="fas fa-heart"></i>
                                    <span class="badge bg-danger rounded-pill ms-1" id="wishlistCount">0</span>
                                </a>
                                <a href="{{ route('cart.view') }}" class="text-decoration-none">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span class="badge bg-primary rounded-pill ms-1" id="cartCount">0</span>
                                </a>
                                <div class="dropdown">
                                    <a href="#" class="text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fas fa-user"></i> {{ auth()->user()->name }}
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <button type="submit" class="dropdown-item">Logout</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Login</a>
                                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Register</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Category Navigation -->
        <div class="bg-light border-top mega-menu">
            <div class="container">
                <nav class="navbar navbar-expand-lg navbar-light py-0">
                    <button class="navbar-toggler border-0 py-2" type="button" data-bs-toggle="collapse" data-bs-target="#categoryNav">
                        <i class="fas fa-bars"></i> Categories
                    </button>
                    
                    <div class="collapse navbar-collapse" id="categoryNav">
                        <ul class="navbar-nav w-100">
                            @foreach($categories->take(7) as $cat)
                            <li class="nav-item">
                                <a class="nav-link {{ $cat->slug === $category->slug ? 'active text-primary fw-bold' : '' }}" 
                                   href="{{ route('category.products', $cat->slug) }}">
                                    <i class="{{ $cat->icon ?? 'fas fa-tag' }} me-1"></i> 
                                    {{ $cat->name }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <!-- Category Hero Section -->
    <section class="category-hero">
        <div class="container">
            <div class="category-hero-content text-center">
                <!-- Breadcrumbs -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('front.index') }}">Home</a></li>
                        @foreach($breadcrumbs as $index => $breadcrumb)
                            @if($index === count($breadcrumbs) - 1)
                                <li class="breadcrumb-item active">{{ $breadcrumb->name }}</li>
                            @else
                                <li class="breadcrumb-item"><a href="{{ route('category.products', $breadcrumb->slug) }}">{{ $breadcrumb->name }}</a></li>
                            @endif
                        @endforeach
                    </ol>
                </nav>
                
                <!-- Category Info -->
                <div class="category-icon">
                    <i class="{{ $category->icon ?? 'fas fa-tag' }}"></i>
                </div>
                <h1 class="display-4 fw-bold mb-3">{{ $category->name }}</h1>
                <p class="lead mb-0">Discover amazing products in {{ $category->name }}</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <!-- Sort & Filter Bar -->
            <div class="sort-filter-bar">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="results-info">
                            Showing {{ $products->count() }} of {{ $products->total() }} products
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end gap-3">
                            <select class="form-select form-select-sm" style="width: auto;">
                                <option>Sort by: Latest</option>
                                <option>Price: Low to High</option>
                                <option>Price: High to Low</option>
                                <option>Most Popular</option>
                            </select>
                            <select class="form-select form-select-sm" style="width: auto;">
                                <option>12 per page</option>
                                <option>24 per page</option>
                                <option>48 per page</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Sidebar Filters -->
                <div class="col-lg-3">
                    <div class="filter-sidebar">
                        <h5 class="filter-title">
                            <i class="fas fa-filter me-2"></i>Filters
                        </h5>
                        
                        <!-- Subcategories -->
                        @if($category->children->count() > 0)
                        <div class="mb-4">
                            <h6 class="fw-semibold mb-3">Categories</h6>
                            @foreach($category->children as $subcategory)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="cat{{ $subcategory->id }}">
                                <label class="form-check-label" for="cat{{ $subcategory->id }}">
                                    {{ $subcategory->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @endif
                        
                        <!-- Price Range -->
                        <div class="mb-4">
                            <h6 class="fw-semibold mb-3">Price Range</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" placeholder="Min">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm" placeholder="Max">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Clear Filters -->
                        <button class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-times me-1"></i>Clear All Filters
                        </button>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="col-lg-9">
                    @if($products->count() > 0)
                        <div class="product-grid" id="productGrid">
                            @foreach($products as $product)
                            <div class="product-card">
                                <img src="{{ $product->image ?? 'https://via.placeholder.com/280x200/f8f9fa/6c757d?text=No+Image' }}" 
                                     alt="{{ $product->name }}" class="product-image">
                                
                                <div class="product-info">
                                    <h6 class="product-title">
                                        <a href="{{ route('product.show', $product->slug) }}" class="text-decoration-none text-dark">
                                            {{ $product->name }}
                                        </a>
                                    </h6>
                                    <div class="product-price">₹{{ number_format($product->price, 2) }}</div>
                                    
                                    <div class="product-actions">
                                        @auth
                                            <button class="btn btn-add-cart add-to-cart" data-product-id="{{ $product->id }}">
                                                <i class="fas fa-cart-plus me-1"></i>Add to Cart
                                            </button>
                                            <button class="btn btn-wishlist wishlist-toggle" data-product-id="{{ $product->id }}">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-add-cart add-to-cart-login">
                                                <i class="fas fa-cart-plus me-1"></i>Add to Cart
                                            </button>
                                            <button class="btn btn-wishlist" onclick="window.location.href='{{ route('login') }}'">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($products->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $products->links() }}
                        </div>
                        @endif
                    @else
                        <div class="no-products">
                            <i class="fas fa-box-open"></i>
                            <h4>No Products Found</h4>
                            <p class="text-muted">Sorry, we couldn't find any products in {{ $category->name }}.</p>
                            <a href="{{ route('front.index') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Home
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-shopping-bag me-2"></i>ShopCart
                    </h5>
                    <p class="text-light">Your one-stop destination for all your shopping needs. Quality products, great prices, fast delivery.</p>
                </div>
                <div class="col-md-2 mb-4">
                    <h6 class="fw-semibold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('front.index') }}" class="text-light text-decoration-none">Home</a></li>
                        <li><a href="#" class="text-light text-decoration-none">About</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h6 class="fw-semibold mb-3">Categories</h6>
                    <ul class="list-unstyled">
                        @foreach($categories->take(4) as $cat)
                        <li><a href="{{ route('category.products', $cat->slug) }}" class="text-light text-decoration-none">{{ $cat->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h6 class="fw-semibold mb-3">Newsletter</h6>
                    <p class="text-light">Subscribe to get updates on new products and offers.</p>
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Your email">
                        <button class="btn btn-primary" type="button">Subscribe</button>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="mb-0">&copy; 2025 ShopCart. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script>
        $(document).ready(function() {
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

            // Add to cart functionality
            $(document).on('click', '.add-to-cart', function() {
                const btn = $(this);
                const productId = btn.data('product-id');
                const originalText = btn.html();

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');

                $.post("{{ route('cart.ajaxAdd') }}", {
                    _token: "{{ csrf_token() }}",
                    product_id: productId,
                    quantity: 1
                }, function(response) {
                    if (response.status) {
                        showToast(response.message, true);
                        $('#cartCount').text(response.cart_count);
                        btn.html('<i class="fas fa-check"></i> Added');
                        setTimeout(() => {
                            btn.prop('disabled', false).html(originalText);
                        }, 2000);
                    } else {
                        showToast(response.message, false);
                        btn.prop('disabled', false).html(originalText);
                    }
                }).fail(function() {
                    showToast("Failed to add item to cart", false);
                    btn.prop('disabled', false).html(originalText);
                });
            });

            $(document).on('click', '.add-to-cart-login', function() {
                showToast("Please login to add items to cart", false);
                setTimeout(() => {
                    window.location.href = "{{ route('login') }}";
                }, 1500);
            });

            // Wishlist functionality
            $(document).on('click', '.wishlist-toggle', function() {
                const btn = $(this);
                const productId = btn.data('product-id');

                $.post("{{ route('wishlist.toggle') }}", {
                    _token: "{{ csrf_token() }}",
                    product_id: productId
                }, function(response) {
                    if (response.status) {
                        showToast(response.message, true);
                        $('#wishlistCount').text(response.wishlist_count);
                        btn.toggleClass('text-danger');
                    } else {
                        showToast(response.message, false);
                    }
                }).fail(function() {
                    showToast("Failed to update wishlist", false);
                });
            });
        });
    </script>
</body>
</html>
