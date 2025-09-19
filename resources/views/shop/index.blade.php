<!DOCTYPE html>
<html lang="en">

<head>
    <title>Shop - All Products</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background-color: #232f3e !important;
            border-bottom: 1px solid #ddd;
        }

        .navbar-brand,
        .navbar-nav .nav-link {
            color: #fff !important;
        }

        .navbar-nav .nav-link:hover {
            color: #ff9900 !important;
        }

        .shop-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }

        .filter-card {
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .price-original {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .price-current {
            color: #28a745;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .rating-stars {
            color: #ffc107;
        }

        .filter-section {
            border-bottom: 1px solid #eee;
            padding: 1rem 0;
        }

        .filter-section:last-child {
            border-bottom: none;
        }

        .btn-filter {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }

        .btn-filter:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .gift-product {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .gift-product:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .gift-product.selected {
            border-color: #007bff;
            background-color: #f8f9ff;
        }

        .gift-qty-container {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 2px;
        }

        .gift-qty-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: none;
            background: #007bff;
            color: white;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gift-qty-btn:hover {
            background: #0056b3;
            transform: scale(1.1);
        }

        .gift-qty {
            border: none;
            background: transparent;
            text-align: center;
            font-weight: bold;
            width: 40px;
        }

        .gift-checkbox {
            transform: scale(1.3);
            accent-color: #007bff;
        }
    </style>
    <style>
        /* Amazon-style Shop Page Styles */
        .filter-section {
            position: relative;
        }

        .filter-title {
            color: #232f3e;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
        }

        .form-check-input:checked {
            background-color: #ff9900;
            border-color: #ff9900;
        }

        .form-check-label {
            font-size: 0.9rem;
            cursor: pointer;
        }

        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .price-range-slider {
            margin-top: 10px;
        }

        .form-range::-webkit-slider-thumb {
            background: #ff9900;
        }

        .form-range::-moz-range-thumb {
            background: #ff9900;
            border: none;
        }

        #filters-card {
            position: sticky;
            top: 20px;
        }

        .filter-options {
            scrollbar-width: thin;
        }

        .filter-options::-webkit-scrollbar {
            width: 6px;
        }

        .filter-options::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .filter-options::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }

        .filter-options::-webkit-scrollbar-thumb:hover {
            background: #999;
        }

        /* Search Suggestions Dropdown Styles */
        .search-autocomplete-container {
            position: relative;
        }

        .search-suggestions-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            max-height: 300px;
            overflow-y: auto;
        }

        .suggestions-list {
            padding: 0;
            margin: 0;
        }

        .suggestion-item {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s ease;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .suggestion-item:hover,
        .suggestion-item.highlighted {
            background-color: #f8f9fa;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-icon {
            margin-right: 10px;
            color: #666;
            width: 16px;
            text-align: center;
        }

        .suggestion-text {
            flex: 1;
            color: #333;
        }

        .suggestion-type {
            font-size: 0.8rem;
            color: #999;
            margin-left: 8px;
        }

        .no-suggestions {
            padding: 12px 16px;
            color: #999;
            font-style: italic;
            text-align: center;
        }

        /* Loading state for suggestions */
        .suggestions-loading {
            padding: 12px 16px;
            text-align: center;
            color: #666;
        }

        .suggestions-loading i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
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
                        <a class="nav-link active" href="/shop">Shop</a>
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

    <div class="shop-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 mb-2">
                        <i class="fas fa-store me-3"></i>Shop All Products
                    </h1>
                    <p class="lead">Discover amazing products with advanced filtering</p>
                </div>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loading-overlay" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div class="container-fluid py-4" id="shop-container">
        <div class="row">
            {{-- Left Sidebar - Filters --}}
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="card shadow-sm" id="filters-card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i>Filters
                        </h5>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-filters">
                            <i class="fas fa-times me-1"></i>Clear All
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <form id="filters-form">
                            {{-- Search Filter --}}
                            <div class="filter-section p-3 border-bottom">
                                <h6 class="filter-title">
                                    <i class="fas fa-search me-2"></i>Search Products
                                </h6>
                                <div class="search-autocomplete-container position-relative">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="search-input" name="q"
                                            placeholder="Search products..." value="{{ request('q') }}"
                                            autocomplete="off">
                                        <button class="btn btn-outline-secondary" type="button" id="search-btn">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    {{-- Search Suggestions Dropdown --}}
                                    <div class="search-suggestions-dropdown" id="search-suggestions" style="display: none;">
                                        <div class="suggestions-list">
                                            <!-- Suggestions will be populated here -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Category Filter --}}
                            <div class="filter-section p-3 border-bottom">
                                <h6 class="filter-title">
                                    <i class="fas fa-th-list me-2"></i>Categories
                                </h6>
                                <div class="filter-options" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($categories as $category)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox"
                                            value="{{ $category->id }}" name="category[]"
                                            id="category-{{ $category->id }}">
                                        <label class="form-check-label" for="category-{{ $category->id }}">
                                            {{ $category->name }}
                                            <span class="text-muted">({{ $category->products_count }})</span>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Price Range Filter --}}
                            <div class="filter-section p-3 border-bottom">
                                <h6 class="filter-title">
                                    <i class="fas fa-dollar-sign me-2"></i>Price Range
                                </h6>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small">Min Price</label>
                                        <input type="number" class="form-control form-control-sm"
                                            name="price_min" id="price-min"
                                            min="{{ $priceRange['min'] }}"
                                            max="{{ $priceRange['max'] }}"
                                            placeholder="{{ $priceRange['min'] }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Max Price</label>
                                        <input type="number" class="form-control form-control-sm"
                                            name="price_max" id="price-max"
                                            min="{{ $priceRange['min'] }}"
                                            max="{{ $priceRange['max'] }}"
                                            placeholder="{{ $priceRange['max'] }}">
                                    </div>
                                </div>
                                <div class="price-range-slider">
                                    <input type="range" class="form-range"
                                        id="price-range"
                                        min="{{ $priceRange['min'] }}"
                                        max="{{ $priceRange['max'] }}"
                                        step="10">
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span>${{ $priceRange['min'] }}</span>
                                        <span>${{ $priceRange['max'] }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Brand Filter --}}
                            @if(count($brands))
                            <div class="filter-section p-3 border-bottom">
                                <h6 class="filter-title">
                                    <i class="fas fa-tags me-2"></i>Brands
                                </h6>
                                <div class="filter-options" style="max-height: 150px; overflow-y: auto;">
                                    @foreach($brands as $brand)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox"
                                            value="{{ $brand }}" name="brand[]"
                                            id="brand-{{ Str::slug($brand) }}">
                                        <label class="form-check-label" for="brand-{{ Str::slug($brand) }}">
                                            {{ $brand }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            {{-- Rating Filter --}}
                            <div class="filter-section p-3 border-bottom">
                                <h6 class="filter-title">
                                    <i class="fas fa-star me-2"></i>Customer Rating
                                </h6>
                                @foreach($ratings as $rating)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio"
                                        value="{{ $rating }}" name="rating"
                                        id="rating-{{ $rating }}">
                                    <label class="form-check-label" for="rating-{{ $rating }}">
                                        <div class="d-flex align-items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $rating ? 'text-warning' : 'text-muted' }}" style="font-size: 0.8rem;"></i>
                                                @endfor
                                                <span class="ms-2">{{ $rating }} & Up</span>
                                        </div>
                                    </label>
                                </div>
                                @endforeach
                            </div>

                            {{-- Availability Filter --}}
                            <div class="filter-section p-3">
                                <h6 class="filter-title">
                                    <i class="fas fa-box me-2"></i>Availability
                                </h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        value="1" name="in_stock" id="in-stock">
                                    <label class="form-check-label" for="in-stock">
                                        In Stock Only
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Right Side - Products Grid --}}
            <div class="col-lg-9 col-md-8">
                {{-- Top Bar with Sort and View Options --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <h5 class="mb-0 me-3">Products</h5>
                                    <span class="badge bg-primary" id="results-count">Loading...</span>
                                    <span class="badge bg-secondary ms-2" id="filters-count" style="display: none;">
                                        <i class="fas fa-filter me-1"></i><span id="filter-count-text">0</span> filters applied
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-md-end">
                                    {{-- Items per page --}}
                                    <label class="form-label mb-0 me-2 small">Show:</label>
                                    <select class="form-select form-select-sm me-3" id="per-page" style="width: auto;">
                                        <option value="12">12</option>
                                        <option value="24">24</option>
                                        <option value="48">48</option>
                                        <option value="96">96</option>
                                    </select>

                                    {{-- Sort Options --}}
                                    <label class="form-label mb-0 me-2 small">Sort by:</label>
                                    <select class="form-select form-select-sm" id="sort-select" style="width: auto;">
                                        <option value="name_asc">Name (A-Z)</option>
                                        <option value="price_asc">Price (Low to High)</option>
                                        <option value="price_desc">Price (High to Low)</option>
                                        <option value="rating_desc">Highest Rated</option>
                                        <option value="newest">Newest</option>
                                        <option value="popularity">Most Popular</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Products Grid Container --}}
                <div id="products-container">
                    {{-- Loading State --}}
                    <div class="text-center py-5" id="loading-state">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading products...</p>
                    </div>
                </div>

                {{-- Load More Button --}}
                <div class="text-center mt-4" id="load-more-container" style="display: none;">
                    <button class="btn btn-outline-primary btn-lg" id="load-more-btn">
                        <i class="fas fa-plus me-2"></i>Load More Products
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loading-overlay" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div class="modal fade" id="giftProductsModal" tabindex="-1" aria-labelledby="giftProductsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title" id="giftProductsModalLabel">
                        🎁 <strong>Add Something Extra Special!</strong>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Gift products will be loaded here via AJAX -->
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2">Loading gift products...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Skip
                    </button>
                    <button type="button" class="btn btn-primary" id="addGiftsToCart">
                        <i class="fas fa-gift"></i> Continue Shopping
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script>
        $(document).ready(function() {
            // Initialize shop page
            initializeShop();

            // Load initial products
            loadProducts();

            // Event listeners
            setupEventListeners();
        });

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

        function showGiftProductsModal(productId) {
            $.ajax({
                url: "{{ route('cart.giftProducts') }}",
                type: 'GET',
                data: {
                    product_id: productId
                },
                success: function(response) {
                    $('#giftProductsModal .modal-body').html(response.html);
                    $('#giftProductsModal').modal('show');
                },
                error: function() {
                    console.log('Error loading gift products');
                }
            });
        }

        // Initialize shop functionality
        function initializeShop() {
            // Get URL parameters and set form values
            const urlParams = new URLSearchParams(window.location.search);

            // Set search input
            if (urlParams.has('q')) {
                $('#search-input').val(urlParams.get('q'));
            }

            // Set category filters
            if (urlParams.has('category')) {
                const categories = urlParams.getAll('category');
                categories.forEach(cat => {
                    $(`input[name="category[]"][value="${cat}"]`).prop('checked', true);
                });
            }

            // Set other filters
            if (urlParams.has('price_min')) $('#price-min').val(urlParams.get('price_min'));
            if (urlParams.has('price_max')) $('#price-max').val(urlParams.get('price_max'));
            if (urlParams.has('rating')) $(`input[name="rating"][value="${urlParams.get('rating')}"]`).prop('checked', true);
            if (urlParams.has('sort')) $('#sort-select').val(urlParams.get('sort'));
            if (urlParams.has('per_page')) $('#per-page').val(urlParams.get('per_page'));
            if (urlParams.has('in_stock')) $('#in-stock').prop('checked', true);
        }

        // Setup all event listeners
        function setupEventListeners() {
            // Filter change events
            $('#filters-form input, #filters-form select').on('change', function() {
                loadProducts();
            });

            // Search functionality
            $('#search-btn').on('click', function() {
                hideSuggestions();
                loadProducts();
            });

            $('#search-input').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    hideSuggestions();
                    loadProducts();
                }
            });

            // Search suggestions functionality
            let suggestionsTimeout;
            let currentSuggestionIndex = -1;

            $('#search-input').on('input', function() {
                const query = $(this).val().trim();

                // Clear previous timeout
                clearTimeout(suggestionsTimeout);

                if (query.length < 2) {
                    hideSuggestions();
                    return;
                }

                // Debounce the search suggestions request
                suggestionsTimeout = setTimeout(() => {
                    fetchSuggestions(query);
                }, 300);
            });

            // Handle keyboard navigation in search suggestions
            $('#search-input').on('keydown', function(e) {
                const $suggestions = $('#search-suggestions .suggestion-item');

                if (!$suggestions.length) return;

                switch (e.which) {
                    case 38: // Up arrow
                        e.preventDefault();
                        currentSuggestionIndex = Math.max(-1, currentSuggestionIndex - 1);
                        highlightSuggestion($suggestions);
                        break;

                    case 40: // Down arrow
                        e.preventDefault();
                        currentSuggestionIndex = Math.min($suggestions.length - 1, currentSuggestionIndex + 1);
                        highlightSuggestion($suggestions);
                        break;

                    case 13: // Enter
                        if (currentSuggestionIndex >= 0) {
                            e.preventDefault();
                            selectSuggestion($suggestions.eq(currentSuggestionIndex));
                        }
                        break;

                    case 27: // Escape
                        hideSuggestions();
                        break;
                }
            });

            // Click outside to hide suggestions
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.search-autocomplete-container').length) {
                    hideSuggestions();
                }
            });

            // Click on suggestion item
            $(document).on('click', '.suggestion-item', function() {
                selectSuggestion($(this));
            });

            // Sort and pagination
            $('#sort-select, #per-page').on('change', function() {
                loadProducts();
            });

            // Clear filters
            $('#clear-filters').on('click', function() {
                clearAllFilters();
            });

            // Load more button
            $(document).on('click', '#load-more-btn', function() {
                loadMoreProducts();
            });

            // Price range slider
            $('#price-range').on('input', function() {
                const value = $(this).val();
                $('#price-max').val(value);
                // Debounce the filter update
                clearTimeout(window.priceTimeout);
                window.priceTimeout = setTimeout(() => {
                    loadProducts();
                }, 500);
            });
        }

        // Load products with current filters
        function loadProducts(page = 1) {
            showLoading();
            resetLoadMoreButton(); // Reset load more state

            const filterData = getFilterData();
            filterData.page = page;

            $.ajax({
                url: '{{ route("shop.products") }}',
                method: 'GET',
                data: filterData,
                success: function(response) {
                    if (response.success) {
                        $('#products-container').html(response.html);
                        updateResultsInfo(response);
                        updateFiltersCount(response.filters_applied);
                        updateURL(filterData);
                        hideLoading();
                    }
                },
                error: function(xhr) {
                    console.error('Error loading products:', xhr);
                    $('#products-container').html(`
                <div class="alert alert-danger text-center">
                    <h5>Error Loading Products</h5>
                    <p>Please try again or refresh the page.</p>
                    <button class="btn btn-primary" onclick="loadProducts()">Retry</button>
                </div>
            `);
                    hideLoading();
                }
            });
        }

        // Get filter data as regular object for GET requests
        function getFilterData() {
            const data = {};

            // Search
            const search = $('#search-input').val();
            if (search) data.q = search;

            // Categories
            const categories = [];
            $('input[name="category[]"]:checked').each(function() {
                categories.push($(this).val());
            });
            if (categories.length > 0) data.category = categories;

            // Price range
            const priceMin = $('#price-min').val();
            const priceMax = $('#price-max').val();
            if (priceMin) data.price_min = priceMin;
            if (priceMax) data.price_max = priceMax;

            // Brands
            const brands = [];
            $('input[name="brand[]"]:checked').each(function() {
                brands.push($(this).val());
            });
            if (brands.length > 0) data.brand = brands;

            // Rating
            const rating = $('input[name="rating"]:checked').val();
            if (rating) data.rating = rating;

            // In stock
            if ($('#in-stock').is(':checked')) {
                data.in_stock = '1';
            }

            // Sort and pagination
            data.sort = $('#sort-select').val() || 'name_asc';
            data.per_page = $('#per-page').val() || '12';

            return data;
        }

        // Update results information
        function updateResultsInfo(response) {
            $('#results-count').text(response.results_text);

            // Show/hide load more button based on pagination info
            if (response.pagination && response.pagination.current_page < response.pagination.last_page) {
                $('#load-more-container').show();
                $('#load-more-btn').data('next-page', response.pagination.current_page + 1);
            } else {
                $('#load-more-container').hide();
            }
        }

        // Update filters count badge
        function updateFiltersCount(count) {
            if (count > 0) {
                $('#filters-count').show();
                $('#filter-count-text').text(count);
            } else {
                $('#filters-count').hide();
            }
        }

        // Update URL without page reload
        function updateURL(filterData) {
            const params = new URLSearchParams();

            for (let [key, value] of Object.entries(filterData)) {
                if (key !== 'page') {
                    if (Array.isArray(value)) {
                        value.forEach(v => params.append(key + '[]', v));
                    } else {
                        params.append(key, value);
                    }
                }
            }

            const newURL = window.location.pathname + '?' + params.toString();
            window.history.replaceState({}, '', newURL);
        }

        // Clear all filters
        function clearAllFilters() {
            $('#filters-form')[0].reset();
            $('#search-input').val('');
            loadProducts();
        }

        // Show loading state
        function showLoading() {
            $('#loading-overlay').show();
        }

        // Hide loading state  
        function hideLoading() {
            $('#loading-overlay').hide();
        }

        // Reset load more button state
        function resetLoadMoreButton() {
            $('#load-more-container').hide();
            $('#load-more-btn').removeData('next-page')
                .prop('disabled', false)
                .html('<i class="fas fa-plus me-2"></i>Load More Products');
        }

        // Load more products (for infinite scroll alternative)
        function loadMoreProducts() {
            const button = $('#load-more-btn');
            const nextPage = button.data('next-page');

            if (!nextPage) {
                console.log('No more pages available');
                return;
            }

            // Get current filter data
            const filterData = getFilterData();
            filterData.page = nextPage;

            $.ajax({
                url: "{{ route('shop.load-more') }}",
                type: 'GET',
                data: filterData,
                beforeSend: function() {
                    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Loading...');
                },
                success: function(response) {
                    // Append new products to the existing grid
                    $('#products-grid').append(response.html);

                    // Update button state
                    if (response.hasMorePages) {
                        button.data('next-page', response.nextPage)
                            .prop('disabled', false)
                            .html('<i class="fas fa-plus me-2"></i>Load More Products');
                    } else {
                        // No more pages, hide the button
                        $('#load-more-container').hide();
                    }

                    // Update results text if needed
                    if (response.total) {
                        const currentCount = $('#products-grid .col-12').length;
                        $('#results-count').text(`Showing 1-${currentCount} of ${response.total} results`);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading more products:', error);
                    button.prop('disabled', false)
                        .html('<i class="fas fa-plus me-2"></i>Load More Products');

                    // Show error message to user
                    if (typeof showToast === 'function') {
                        showToast('Error loading more products. Please try again.', true);
                    } else {
                        alert('Error loading more products. Please try again.');
                    }
                }
            });
        }

        // Search suggestions helper functions
        function fetchSuggestions(query) {
            $('#search-suggestions .suggestions-list').html('<div class="suggestions-loading"><i class="fas fa-spinner fa-spin"></i>Loading suggestions...</div>');
            $('#search-suggestions').show();

            $.ajax({
                url: "{{ route('shop.search-suggestions') }}",
                type: 'GET',
                data: {
                    q: query
                },
                success: function(suggestions) {
                    displaySuggestions(suggestions);
                },
                error: function() {
                    $('#search-suggestions .suggestions-list').html('<div class="no-suggestions">Error loading suggestions</div>');
                }
            });
        }

        function displaySuggestions(suggestions) {
            const $suggestionsList = $('#search-suggestions .suggestions-list');

            if (suggestions.length === 0) {
                $suggestionsList.html('<div class="no-suggestions">No suggestions found</div>');
                return;
            }

            let html = '';
            suggestions.forEach(function(suggestion) {
                html += `
            <div class="suggestion-item" data-value="${suggestion.value}" data-type="${suggestion.type}">
                <i class="${suggestion.icon} suggestion-icon"></i>
                <span class="suggestion-text">${suggestion.text}</span>
                <span class="suggestion-type">${suggestion.type}</span>
            </div>
        `;
            });

            $suggestionsList.html(html);
            currentSuggestionIndex = -1;
        }

        function highlightSuggestion($suggestions) {
            $suggestions.removeClass('highlighted');

            if (currentSuggestionIndex >= 0) {
                $suggestions.eq(currentSuggestionIndex).addClass('highlighted');

                // Update input value with highlighted suggestion
                const suggestionText = $suggestions.eq(currentSuggestionIndex).find('.suggestion-text').text();
                $('#search-input').val(suggestionText);
            }
        }

        function selectSuggestion($suggestion) {
            const value = $suggestion.data('value');
            const type = $suggestion.data('type');

            $('#search-input').val(value);
            hideSuggestions();

            // Trigger search
            loadProducts();
        }

        function hideSuggestions() {
            $('#search-suggestions').hide();
            currentSuggestionIndex = -1;
        }

        $(document).on('submit', '.add-to-cart-form', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');

            $.ajax({
                type: 'POST',
                url: "{{ route('cart.ajaxAdd') }}",
                data: form.serialize(),
                success: function(response) {
                    showToast(response.message);
                    submitBtn.prop('disabled', false).html('Add to Cart');
                    showGiftProductsModal(response.product_id);
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html('Add to Cart');
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        Object.values(errors).forEach(e => showToast(e[0], false));
                    } else {
                        showToast('Something went wrong!', false);
                    }
                }
            });
        });

        // Gift modal quantity button functionality
        $(document).on('click', '.gift-qty-btn', function() {
            const input = $(this).siblings('input');
            const currentVal = parseInt(input.val()) || 0;
            const isIncrement = $(this).hasClass('increment');

            if (isIncrement) {
                input.val(currentVal + 1);
            } else if (currentVal > 0) {
                input.val(currentVal - 1);
            }

            const checkbox = $(this).closest('.gift-product').find('.gift-checkbox');
            checkbox.prop('checked', parseInt(input.val()) > 0);
        });

        // Gift modal checkbox change functionality
        $(document).on('change', '.gift-checkbox', function() {
            const qtyInput = $(this).closest('.gift-product').find('.gift-qty');
            if (!$(this).is(':checked')) {
                qtyInput.val(0);
            } else if (parseInt(qtyInput.val()) === 0) {
                qtyInput.val(1);
            }
        });

        // Add gifts to cart functionality
        $(document).on('click', '#addGiftsToCart', function() {
            const selectedGifts = [];

            $('.gift-product').each(function() {
                const checkbox = $(this).find('.gift-checkbox');
                const qty = parseInt($(this).find('.gift-qty').val()) || 0;

                if (checkbox.is(':checked') && qty > 0) {
                    selectedGifts.push({
                        product_id: $(this).data('product-id'),
                        quantity: qty
                    });
                }
            });

            if (selectedGifts.length > 0) {
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding Gifts...');

                $.ajax({
                    url: "{{ route('cart.addGifts') }}",
                    type: 'POST',
                    data: {
                        gifts: selectedGifts,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        showToast(response.message);
                        $('#giftProductsModal').modal('hide');
                    },
                    error: function() {
                        showToast('Error adding gifts to cart', false);
                    },
                    complete: function() {
                        $('#addGiftsToCart').prop('disabled', false).html('Continue Shopping');
                    }
                });
            } else {
                $('#giftProductsModal').modal('hide');
            }
        });
    </script>
</body>
</html>