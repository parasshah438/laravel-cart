<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $category->name }} - ShopCart</title>
    <meta name="description" content="Shop {{ $category->name }} products at ShopCart. Best prices, fast delivery, authentic products.">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.css">

    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        .category-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; padding: 60px 0; position: relative; overflow: hidden;
        }
        .category-hero::before {
            content: ''; position: absolute; inset: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grain)"/></svg>') repeat;
            opacity: .3;
        }
        .category-hero-content { position: relative; z-index: 2; }
        .breadcrumb { background: rgba(255,255,255,.1); border-radius: 25px; padding: 8px 20px; margin-bottom: 2rem; }
        .breadcrumb-item + .breadcrumb-item::before { content: "›"; color: rgba(255,255,255,.7); }
        .breadcrumb-item a { color: rgba(255,255,255,.8); text-decoration: none; }
        .breadcrumb-item.active { color: white; }

        /* Filter sidebar */
        .filter-card { border: none; box-shadow: 0 2px 4px rgba(0,0,0,.1); border-radius: 12px; }
        .filter-section { border-bottom: 1px solid #eee; padding: 1rem 0; }
        .filter-section:last-child { border-bottom: none; }
        .filter-title { font-weight: 600; color: #333; font-size: .9rem; margin-bottom: .75rem; }

        /* Search suggestions */
        .search-autocomplete-container { position: relative; }
        .search-suggestions-dropdown {
            position: absolute; top: 100%; left: 0; right: 0; z-index: 1000;
            background: white; border: 1px solid #dee2e6; border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,.15); max-height: 300px; overflow-y: auto;
        }
        .suggestion-item {
            display: flex; align-items: center; padding: 10px 15px; cursor: pointer;
            border-bottom: 1px solid #f0f0f0; transition: background .15s;
        }
        .suggestion-item:hover, .suggestion-item.highlighted { background: #f8f9fa; }
        .suggestion-item:last-child { border-bottom: none; }
        .suggestion-icon { width: 20px; color: #6c757d; margin-right: 10px; font-size: .85rem; }
        .suggestion-text { flex: 1; font-size: .9rem; }
        .suggestion-text strong { font-weight: 700; color: #111; }
        .suggestion-type { font-size: .75rem; color: #adb5bd; text-transform: capitalize; }
        .suggestions-loading, .no-suggestions { padding: 12px 15px; color: #6c757d; font-size: .85rem; text-align: center; }
        .suggestion-dym { background: #fffbeb; border-top: 1px dashed #fcd34d !important; }
        .suggestion-dym:hover, .suggestion-dym.highlighted { background: #fef3c7; }
        .suggestion-dym .suggestion-icon { color: #f59e0b; }
        /* Recent searches */
        .recent-searches-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 8px 15px 4px; font-size: .72rem; color: #9ca3af; text-transform: uppercase; letter-spacing: .04em;
        }
        .recent-searches-header button { background: none; border: none; color: #6366f1; font-size: .75rem; cursor: pointer; padding: 0; }
        .recent-searches-header button:hover { text-decoration: underline; }
        .recent-search-item { display: flex; align-items: center; padding: 9px 15px; cursor: pointer; border-bottom: 1px solid #f0f0f0; transition: background .15s; }
        .recent-search-item:hover { background: #f8f9fa; }
        .recent-search-item .rs-icon { color: #9ca3af; margin-right: 10px; font-size: .85rem; width: 16px; text-align: center; }
        .recent-search-item .rs-text { flex: 1; font-size: .88rem; color: #374151; }
        .recent-search-item .rs-remove { background: none; border: none; color: #d1d5db; padding: 0 0 0 8px; font-size: .8rem; cursor: pointer; line-height: 1; }
        .recent-search-item .rs-remove:hover { color: #6b7280; }

        /* Sort bar */
        .sort-bar { background: white; border-radius: 12px; padding: 15px 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,.08); }

        /* Loading overlay */
        .loading-overlay {
            position: fixed; inset: 0; background: rgba(255,255,255,.75);
            display: flex; align-items: center; justify-content: center; z-index: 9999;
        }

        /* Product card (from _product_cards partial) */
        .card { transition: transform .3s ease, box-shadow .3s ease; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 4px 20px rgba(0,0,0,.15); }

        /* noUiSlider – dual-handle price range */
        .noUi-target {
            background: #e5e7eb; border: none; box-shadow: none;
            height: 4px; border-radius: 2px; margin: 8px 4px;
        }
        .noUi-connect { background: linear-gradient(90deg,#667eea,#764ba2); }
        .noUi-handle {
            width: 20px !important; height: 20px !important;
            right: -10px !important; top: -8px !important;
            border-radius: 50%; border: 2.5px solid #667eea;
            background: #fff; box-shadow: 0 2px 8px rgba(102,126,234,.45);
            cursor: grab;
        }
        .noUi-handle:active { cursor: grabbing; }
        .noUi-handle::before, .noUi-handle::after { display: none; }
        .noUi-handle:focus { outline: 2px solid #667eea; outline-offset: 2px; box-shadow: 0 0 0 4px rgba(102,126,234,.2); }
        .price-range-display {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 12px;
        }
        .price-range-display .price-val {
            background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 6px;
            padding: 5px 10px; font-size: .88rem; font-weight: 600; color: #1f2937;
            min-width: 70px; text-align: center;
        }
        .price-range-display .price-sep { color: #9ca3af; font-size: .85rem; }

        /* Zero-results help panel */
        .zero-results {
            background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
            padding: 40px 32px; text-align: center;
        }
        .zero-results .zr-icon {
            width: 72px; height: 72px; border-radius: 50%;
            background: #f3f4f6; display: inline-flex;
            align-items: center; justify-content: center;
            margin-bottom: 20px;
        }
        .zero-results .zr-icon i { font-size: 2rem; color: #9ca3af; }
        .zero-results h5 { font-weight: 700; color: #111827; margin-bottom: 6px; }
        .zero-results .zr-sub { color: #6b7280; font-size: .92rem; margin-bottom: 24px; }
        .zr-suggestions { list-style: none; padding: 0; margin: 0 0 24px; text-align: left; }
        .zr-suggestions li {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-size: .88rem; color: #374151;
        }
        .zr-suggestions li:last-child { border-bottom: none; }
        .zr-suggestions li i { color: #f59e0b; margin-top: 2px; flex-shrink: 0; }
        .zr-action-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 20px; border-radius: 6px; font-size: .88rem;
            font-weight: 600; cursor: pointer; text-decoration: none;
            transition: all .2s;
        }
        .zr-action-btn.primary { background: #667eea; color: #fff; border: none; }
        .zr-action-btn.primary:hover { background: #5a67d8; color: #fff; }
        .zr-action-btn.secondary { background: #fff; color: #374151; border: 1px solid #d1d5db; }
        .zr-action-btn.secondary:hover { background: #f9fafb; }

        /* Active filter chips (Amazon-style) */
        .active-filters-bar {
            display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
            padding: 10px 16px; background: #fff8e7; border: 1px solid #f0c040;
            border-radius: 8px; margin-bottom: 12px;
        }
        .active-filters-bar .filter-label {
            font-size: .8rem; font-weight: 600; color: #555; white-space: nowrap;
        }
        .filter-chip {
            display: inline-flex; align-items: center; gap: 6px;
            background: #fff; border: 1px solid #c8a415; border-radius: 4px;
            padding: 4px 10px; font-size: .82rem; color: #333;
            cursor: default; user-select: none;
        }
        .filter-chip .chip-label { font-weight: 500; }
        .filter-chip .chip-remove {
            display: inline-flex; align-items: center; justify-content: center;
            width: 16px; height: 16px; border-radius: 50%;
            background: #c8a415; color: #fff; font-size: .65rem;
            cursor: pointer; border: none; padding: 0; line-height: 1;
            transition: background .15s;
        }
        .filter-chip .chip-remove:hover { background: #a0830e; }
        .clear-all-chip {
            font-size: .8rem; color: #0066c0; cursor: pointer;
            text-decoration: underline; background: none; border: none; padding: 0;
        }
        .clear-all-chip:hover { color: #c45500; }

        /* Sticky sidebar */
        .filter-sticky-wrap {
            position: sticky;
            top: 80px; /* below fixed navbar (~70px) */
            max-height: calc(100vh - 100px);
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: #d1d5db transparent;
        }
        .filter-sticky-wrap::-webkit-scrollbar { width: 4px; }
        .filter-sticky-wrap::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 2px; }

        /* Infinite scroll sentinel & spinner */
        #scroll-sentinel { height: 1px; }
        #infinite-spinner {
            display: none; text-align: center; padding: 24px 0;
            color: #667eea; font-size: .9rem;
        }
        #infinite-spinner .spin-txt { margin-top: 8px; color: #6b7280; }

        @media (max-width: 768px) { .category-hero { padding: 40px 0; } .filter-sticky-wrap { position: static; max-height: none; } }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="sticky-top">
        <div class="bg-dark text-white py-2">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6"><small><i class="fas fa-phone me-2"></i>+1 234 567 8900 | <i class="fas fa-envelope me-2"></i>support@shopcart.com</small></div>
                    <div class="col-md-6 text-end"><small>Free shipping on orders over ₹500!</small></div>
                </div>
            </div>
        </div>

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
                        <a class="nav-link" href="{{ route('shop') }}">Shop</a>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        @auth
                            <a href="{{ route('wishlist.index') }}" class="text-decoration-none"><i class="fas fa-heart"></i> <span class="badge bg-danger rounded-pill" id="wishlistCount">0</span></a>
                            <a href="{{ route('cart.view') }}" class="text-decoration-none"><i class="fas fa-shopping-cart"></i> <span class="badge bg-primary rounded-pill" id="cartCount">0</span></a>
                            <div class="dropdown">
                                <a href="#" class="text-decoration-none dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-user"></i> {{ auth()->user()->name }}</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="dropdown-item">Logout</button></form></li>
                                </ul>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Login</a>
                            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Register</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <div class="bg-light border-top">
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
                                    <i class="{{ $cat->icon ?? 'fas fa-tag' }} me-1"></i>{{ $cat->name }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero -->
    <section class="category-hero">
        <div class="container">
            <div class="category-hero-content text-center">
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
                <div style="font-size:3rem; margin-bottom:1rem; opacity:.9;"><i class="{{ $category->icon ?? 'fas fa-tag' }}"></i></div>
                <h1 class="display-4 fw-bold mb-3">{{ $category->name }}</h1>
                <p class="lead mb-0">Discover amazing products in {{ $category->name }}</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <div class="row">

                <!-- Filter Sidebar -->
                <div class="col-lg-3 mb-4">
                    <div class="filter-sticky-wrap">
                    <div class="card filter-card">
                        <div class="card-body p-0">

                            <!-- Search -->
                            <div class="filter-section p-3">
                                <h6 class="filter-title"><i class="fas fa-search me-2"></i>Search</h6>
                                <div class="search-autocomplete-container">
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" id="search-input"
                                               placeholder="Search products…" autocomplete="off"
                                               value="{{ $searchQuery ?? '' }}">
                                        <button class="btn btn-outline-secondary" type="button" id="search-btn">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <div class="search-suggestions-dropdown" id="search-suggestions" style="display:none;">
                                        <div class="suggestions-list"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Subcategories -->
                            @if($category->children->count() > 0)
                            <div class="filter-section p-3">
                                <h6 class="filter-title"><i class="fas fa-sitemap me-2"></i>Sub-categories</h6>
                                <div style="max-height:160px; overflow-y:auto;">
                                    @foreach($category->children as $subcategory)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input subcategory-filter" type="checkbox"
                                               value="{{ $subcategory->id }}" id="subcat{{ $subcategory->id }}">
                                        <label class="form-check-label small" for="subcat{{ $subcategory->id }}">
                                            {{ $subcategory->name }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Price Range -->
                            <div class="filter-section p-3">
                                <h6 class="filter-title"><i class="fas fa-rupee-sign me-2"></i>Price Range</h6>

                                <!-- Selected range display -->
                                <div class="price-range-display">
                                    <span class="price-val" id="price-display-min">₹{{ number_format($priceRange['min']) }}</span>
                                    <span class="price-sep">—</span>
                                    <span class="price-val" id="price-display-max">₹{{ number_format($priceRange['max']) }}</span>
                                </div>

                                <!-- Dual-handle slider -->
                                <div id="price-slider" class="mb-3"></div>

                                <!-- Hidden inputs read by getFilterData() -->
                                <input type="hidden" id="price-min" value="">
                                <input type="hidden" id="price-max" value="">
                            </div>

                            <!-- Rating -->
                            <div class="filter-section p-3">
                                <h6 class="filter-title"><i class="fas fa-star me-2"></i>Customer Rating</h6>
                                @foreach([5, 4, 3, 2, 1] as $rating)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="rating"
                                           value="{{ $rating }}" id="rating-{{ $rating }}">
                                    <label class="form-check-label small" for="rating-{{ $rating }}">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $rating ? 'text-warning' : 'text-muted' }}" style="font-size:.8rem;"></i>
                                        @endfor
                                        <span class="ms-1">{{ $rating }} & Up</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>

                            <!-- New Arrivals -->
                            <div class="filter-section p-3">
                                <h6 class="filter-title"><i class="fas fa-bolt me-2"></i>New Arrivals</h6>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="new_arrivals" value="30" id="new-30">
                                    <label class="form-check-label small" for="new-30">Last 30 days</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="new_arrivals" value="90" id="new-90">
                                    <label class="form-check-label small" for="new-90">Last 90 days</label>
                                </div>
                            </div>

                            <!-- Clear -->
                            <div class="p-3">
                                <button class="btn btn-outline-secondary btn-sm w-100" id="clear-filters">
                                    <i class="fas fa-times me-1"></i>Clear All Filters
                                </button>
                            </div>
                        </div>
                    </div>
                    </div><!-- /.filter-sticky-wrap -->
                </div>

                <!-- Products -->
                <div class="col-lg-9">
                    <!-- Sort bar -->
                    <div class="sort-bar d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <div>
                            <span class="fw-semibold me-2">{{ $category->name }}</span>
                            <span class="badge bg-primary" id="results-count">Loading…</span>
                            <span class="badge bg-secondary ms-1" id="filters-badge" style="display:none;">
                                <i class="fas fa-filter me-1"></i><span id="filters-count">0</span> filters
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <select class="form-select form-select-sm" id="per-page" style="width:auto;">
                                <option value="12">12 / page</option>
                                <option value="24">24 / page</option>
                                <option value="48">48 / page</option>
                            </select>
                            <select class="form-select form-select-sm" id="sort-select" style="width:auto;">
                                <option value="newest">Newest</option>
                                <option value="name_asc">Name (A–Z)</option>
                                <option value="price_asc">Price: Low → High</option>
                                <option value="price_desc">Price: High → Low</option>
                                <option value="rating_desc">Highest Rated</option>
                                <option value="popularity">Most Popular</option>
                            </select>
                        </div>
                    </div>

                    <!-- Active filter chips -->
                    <div id="active-filters-bar" style="display:none;" class="active-filters-bar">
                        <span class="filter-label"><i class="fas fa-filter me-1"></i>Filters:</span>
                        <div id="filter-chips" class="d-flex flex-wrap gap-2 align-items-center"></div>
                        <button class="clear-all-chip ms-1" id="clear-all-chip">Clear all</button>
                    </div>

                    <!-- Products container -->
                    <div id="products-container">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-3 text-muted">Loading products…</p>
                        </div>
                    </div>

                    <!-- Infinite scroll spinner + sentinel -->
                    <div id="infinite-spinner">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <div class="spin-txt">Loading more products…</div>
                    </div>
                    <div id="scroll-sentinel"></div>
                </div>
            </div>
        </div>
    </main>

    <!-- Loading overlay -->
    <div class="loading-overlay" id="loading-overlay" style="display:none;">
        <div class="spinner-border text-primary" role="status"></div>
    </div>


    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-shopping-bag me-2"></i>ShopCart</h5>
                    <p class="text-light">Your one-stop destination for all your shopping needs.</p>
                </div>
                <div class="col-md-2 mb-4">
                    <h6 class="fw-semibold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('front.index') }}" class="text-light text-decoration-none">Home</a></li>
                        <li><a href="{{ route('shop') }}" class="text-light text-decoration-none">Shop</a></li>
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
            <div class="text-center"><p class="mb-0">&copy; {{ date('Y') }} ShopCart. All rights reserved.</p></div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.js"></script>

    <script>
    const CATEGORY_URL  = '{{ route("category.products", $category->slug) }}';
    const SUGGEST_URL   = '{{ route("category.search-suggestions", $category->slug) }}';
    const CSRF          = '{{ csrf_token() }}';
    const PRICE_MIN     = {{ $priceRange['min'] }};
    const PRICE_MAX     = {{ $priceRange['max'] }};
    const RECENT_KEY    = 'cat_recent_{{ $category->slug }}';
    const RECENT_MAX    = 5;

    let suggestionsTimeout;
    let currentSuggestionIndex = -1;
    let priceSlider;

    /* ===== Recent searches (localStorage) ===== */
    function getRecentSearches() {
        try { return JSON.parse(localStorage.getItem(RECENT_KEY) || '[]'); } catch(e) { return []; }
    }
    function saveRecentSearch(q) {
        if (!q) return;
        let list = getRecentSearches().filter(function(s) { return s.toLowerCase() !== q.toLowerCase(); });
        list.unshift(q);
        list = list.slice(0, RECENT_MAX);
        try { localStorage.setItem(RECENT_KEY, JSON.stringify(list)); } catch(e) {}
    }
    function removeRecentSearch(q) {
        const list = getRecentSearches().filter(function(s) { return s !== q; });
        try { localStorage.setItem(RECENT_KEY, JSON.stringify(list)); } catch(e) {}
    }
    function clearRecentSearches() {
        try { localStorage.removeItem(RECENT_KEY); } catch(e) {}
    }
    function showRecentSearches() {
        const list = getRecentSearches();
        if (!list.length) { hideSuggestions(); return; }
        let html = '<div class="recent-searches-header">' +
            '<span><i class="fas fa-history me-1"></i>Recent</span>' +
            '<button id="clear-recents-btn">Clear all</button>' +
            '</div>';
        list.forEach(function(s) {
            const safe = escHtml(s);
            html += '<div class="recent-search-item" data-value="' + safe + '">' +
                '<i class="fas fa-clock rs-icon"></i>' +
                '<span class="rs-text">' + safe + '</span>' +
                '<button class="rs-remove rs-remove-btn" data-term="' + safe + '" title="Remove">&#x2715;</button>' +
                '</div>';
        });
        $('#search-suggestions .suggestions-list').html(html);
        $('#search-suggestions').show();
    }

    /* ===== Toast ===== */
    function showToast(msg, ok = true) {
        Toastify({
            text: msg, duration: 3000, gravity: 'top', position: 'right',
            style: { background: ok ? 'linear-gradient(to right,#00b09b,#96c93d)' : 'linear-gradient(to right,#ff5f6d,#ffc371)' }
        }).showToast();
    }

    /* ===== Collect filter data ===== */
    function getFilterData() {
        const data = {};

        const q = $('#search-input').val().trim();
        if (q) data.q = q;

        const subcats = [];
        $('.subcategory-filter:checked').each(function () { subcats.push($(this).val()); });
        if (subcats.length) data['subcategory[]'] = subcats;

        const pMin = $('#price-min').val();
        const pMax = $('#price-max').val();
        if (pMin) data.price_min = pMin;
        if (pMax) data.price_max = pMax;

        const rating = $('input[name="rating"]:checked').val();
        if (rating) data.rating = rating;

        data.sort     = $('#sort-select').val() || 'newest';
        data.per_page = $('#per-page').val()    || '12';

        const arrivals = $('input[name="new_arrivals"]:checked').val();
        if (arrivals) data.new_arrivals = arrivals;

        return data;
    }

    /* ===== Zero results help builder ===== */
    function buildZeroResultsHTML(filters) {
        const q        = filters.q         || '';
        const pMin     = filters.price_min || '';
        const pMax     = filters.price_max || '';
        const rating   = filters.rating    || '';
        const subcats  = filters['subcategory[]'] || [];

        /* Heading: what did they search for? */
        let headingWhat = '';
        if (q) headingWhat = '\u201c' + $('<div>').text(q).html() + '\u201d';

        /* Build actionable tips */
        const tips = [];
        if (q)                        tips.push({ icon: 'fas fa-spell-check', text: 'Check for spelling mistakes in your search term.' });
        if (q)                        tips.push({ icon: 'fas fa-expand-arrows-alt', text: 'Try more general keywords &mdash; e.g. <em>"' + $('<div>').text(q).html().split(' ')[0] + '"</em> instead of the full phrase.' });
        if (pMin || pMax)             tips.push({ icon: 'fas fa-rupee-sign', text: 'Widen the price range &mdash; your budget filter may be too narrow.' });
        if (rating)                   tips.push({ icon: 'fas fa-star', text: 'Lower the minimum rating &mdash; fewer products may have ' + rating + '+ stars.' });
        if (subcats && subcats.length) tips.push({ icon: 'fas fa-sitemap', text: 'Remove the sub-category filter to search all of <strong>{{ $category->name }}</strong>.' });
        if (!tips.length)             tips.push({ icon: 'fas fa-box-open', text: 'No products have been added to this category yet. Check back soon!' });

        let tipsHTML = '<ul class="zr-suggestions">';
        tips.forEach(function (t) {
            tipsHTML += '<li><i class="' + t.icon + '"></i><span>' + t.text + '</span></li>';
        });
        tipsHTML += '</ul>';

        /* Action buttons */
        let btns = '<div class="d-flex justify-content-center gap-3 flex-wrap">';
        const hasFilters = q || pMin || pMax || rating || (subcats && subcats.length);
        if (hasFilters) {
            btns += '<button class="zr-action-btn primary" onclick="clearAllFilters()">' +
                    '<i class="fas fa-times"></i> Clear all filters</button>';
        }
        if (q) {
            btns += '<a href="{{ route('shop') }}?q=' + encodeURIComponent(q) +
                    '" class="zr-action-btn secondary"><i class="fas fa-search"></i> Search all products</a>';
        }
        btns += '<a href="{{ route('category.products', $category->slug) }}" class="zr-action-btn secondary">' +
                '<i class="fas fa-th"></i> Browse all {{ $category->name }}</a>';
        btns += '</div>';

        return '<div class="zero-results">' +
            '<div class="zr-icon"><i class="fas fa-search"></i></div>' +
            '<h5>No results' + (headingWhat ? ' for ' + headingWhat : '') + '</h5>' +
            '<p class="zr-sub">We couldn\'t find any products matching your filters in <strong>{{ $category->name }}</strong>.</p>' +
            tipsHTML +
            btns +
            '</div>';
    }

    let nextPage    = null;   // next page number, null when no more pages
    let isLoading   = false;  // guard against concurrent fetches

    /* ===== Load products (AJAX) ===== */
    function loadProducts(page) {
        page = page || 1;
        if (isLoading) return;
        isLoading = true;
        if (page === 1) {
            $('#loading-overlay').show();
            nextPage = null;
            $('#infinite-spinner').hide();
            $('#products-container').html(
                '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
            );
        } else {
            $('#infinite-spinner').show();
        }

        const data = getFilterData();
        data.page = page;

        $.ajax({
            url: CATEGORY_URL,
            method: 'GET',
            data: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (res) {
                if (res.success) {
                    if (page === 1) {
                        if (res.html.trim() === '') {
                            $('#products-container').html(buildZeroResultsHTML(getFilterData()));
                        } else {
                            $('#products-container').html('<div class="row g-3" id="products-grid">' + res.html + '</div>');
                        }
                    } else {
                        $('#products-grid').append(res.html);
                    }

                    $('#results-count').text(res.results_text);

                    if (res.filters_applied > 0) {
                        $('#filters-count').text(res.filters_applied);
                        $('#filters-badge').show();
                    } else {
                        $('#filters-badge').hide();
                    }

                    if (res.pagination && res.pagination.has_more_pages) {
                        nextPage = res.pagination.next_page;
                    } else {
                        nextPage = null;
                    }
                    $('#infinite-spinner').hide();

                    updateURL(data);
                }
            },
            error: function () {
                $('#products-container').html(
                    '<div class="alert alert-danger text-center"><h5>Error loading products</h5>' +
                    '<button class="btn btn-primary mt-2" onclick="loadProducts(1)">Retry</button></div>'
                );
            },
            complete: function () { $('#loading-overlay').hide(); isLoading = false; }
        });
    }

    /* ===== Update URL ===== */
    const URL_DEFAULTS = { sort: 'newest', per_page: '12' };

    function updateURL(data) {
        const params = new URLSearchParams();
        for (const [key, val] of Object.entries(data)) {
            if (key === 'page') continue;
            // Skip default values — keeps URL clean for sharing & SEO
            if (URL_DEFAULTS[key] !== undefined && String(val) === URL_DEFAULTS[key]) continue;
            if (Array.isArray(val)) val.forEach(v => params.append(key, v));
            else params.append(key, val);
        }
        const q = params.toString();
        window.history.replaceState({}, '', window.location.pathname + (q ? '?' + q : ''));
    }

    /* ===== Search suggestions ===== */
    function escHtml(str) { return $('<div>').text(String(str || '')).html(); }

    function highlightMatch(text, query) {
        if (!query) return escHtml(text);
        const safe = escHtml(text);
        const pattern = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return safe.replace(new RegExp('(' + pattern + ')', 'gi'), '<strong>$1</strong>');
    }

    function fetchSuggestions(query) {
        $('#search-suggestions .suggestions-list').html('<div class="suggestions-loading"><i class="fas fa-spinner fa-spin me-1"></i>Loading…</div>');
        $('#search-suggestions').show();

        $.ajax({
            url: SUGGEST_URL, method: 'GET', data: { q: query },
            success: function (suggestions) {
                if (!suggestions.length) {
                    $('#search-suggestions .suggestions-list').html('<div class="no-suggestions">No suggestions found</div>');
                    return;
                }
                let html = '';
                suggestions.forEach(function (s) {
                    if (s.type === 'did_you_mean') {
                        html += '<div class="suggestion-item suggestion-dym" data-value="' + escHtml(s.value) + '" data-type="product">' +
                            '<i class="fas fa-spell-check suggestion-icon"></i>' +
                            '<span class="suggestion-text">Did you mean: <strong>' + escHtml(s.text) + '</strong>?</span>' +
                            '</div>';
                    } else {
                        html += '<div class="suggestion-item" data-value="' + escHtml(s.value) + '" data-type="' + escHtml(s.type) + '">' +
                            '<i class="' + escHtml(s.icon) + ' suggestion-icon"></i>' +
                            '<span class="suggestion-text">' + highlightMatch(s.text, query) + '</span>' +
                            '<span class="suggestion-type">' + escHtml(s.type) + '</span></div>';
                    }
                });
                $('#search-suggestions .suggestions-list').html(html);
                currentSuggestionIndex = -1;
            },
            error: function () {
                $('#search-suggestions .suggestions-list').html('<div class="no-suggestions">Error loading suggestions</div>');
            }
        });
    }

    function hideSuggestions() { $('#search-suggestions').hide(); currentSuggestionIndex = -1; }

    function highlightSuggestion($items) {
        $items.removeClass('highlighted');
        if (currentSuggestionIndex >= 0) {
            $items.eq(currentSuggestionIndex).addClass('highlighted');
            $('#search-input').val($items.eq(currentSuggestionIndex).find('.suggestion-text').text());
        }
    }

    /* ===== Subcategory label map (built from blade) ===== */
    const SUBCAT_LABELS = {};
    @foreach($category->children as $sub)
    SUBCAT_LABELS[{{ $sub->id }}] = '{{ addslashes($sub->name) }}';
    @endforeach

    /* ===== Render active filter chips (Amazon-style) ===== */
    function renderActiveFilters() {
        const chips = [];

        const q = $('#search-input').val().trim();
        if (q) chips.push({ key: 'q', label: 'Search: "' + q + '"', remove: function() { $('#search-input').val(''); } });

        const pMin = $('#price-min').val();
        const pMax = $('#price-max').val();
        if (pMin && pMax)  chips.push({ key: 'price', label: '₹' + parseInt(pMin).toLocaleString('en-IN') + ' – ₹' + parseInt(pMax).toLocaleString('en-IN'), remove: function() { priceSlider.set([PRICE_MIN, PRICE_MAX]); } });
        else if (pMin)     chips.push({ key: 'price_min', label: 'Min ₹' + parseInt(pMin).toLocaleString('en-IN'), remove: function() { priceSlider.set([PRICE_MIN, parseInt($('#price-max').val()) || PRICE_MAX]); } });
        else if (pMax)     chips.push({ key: 'price_max', label: 'Max ₹' + parseInt(pMax).toLocaleString('en-IN'), remove: function() { priceSlider.set([parseInt($('#price-min').val()) || PRICE_MIN, PRICE_MAX]); } });

        const rating = $('input[name="rating"]:checked').val();
        if (rating) {
            const stars = '★'.repeat(parseInt(rating)) + '☆'.repeat(5 - parseInt(rating));
            chips.push({ key: 'rating', label: stars + ' & Up', remove: function() { $('input[name="rating"]').prop('checked', false); } });
        }

        $('.subcategory-filter:checked').each(function () {
            const id = $(this).val();
            const name = SUBCAT_LABELS[id] || 'Subcategory';
            chips.push({ key: 'subcat_' + id, label: name, remove: (function(inputId) { return function() { $('.subcategory-filter[value="' + inputId + '"]').prop('checked', false); }; })(id) });
        });

        const arrivals = $('input[name="new_arrivals"]:checked').val();
        if (arrivals) {
            chips.push({ key: 'new_arrivals', label: '⚡ Last ' + arrivals + ' days', remove: function() { $('input[name="new_arrivals"]').prop('checked', false); } });
        }

        if (chips.length === 0) { $('#active-filters-bar').hide(); return; }

        let html = '';
        chips.forEach(function (c) {
            html += '<span class="filter-chip" data-key="' + c.key + '">' +
                '<span class="chip-label">' + c.label + '</span>' +
                '<button class="chip-remove" title="Remove filter">&#x2715;</button>' +
                '</span>';
        });

        $('#filter-chips').html(html);
        $('#active-filters-bar').show();

        /* Store remove callbacks */
        window._chipRemovers = {};
        chips.forEach(function (c) { window._chipRemovers[c.key] = c.remove; });
    }

    /* ===== Restore filters from URL ===== */
    function initFromURL() {
        const p = new URLSearchParams(window.location.search);
        if (p.has('q'))        $('#search-input').val(p.get('q'));
        if (p.has('rating'))   $('input[name="rating"][value="' + p.get('rating') + '"]').prop('checked', true);
        if (p.has('sort'))     $('#sort-select').val(p.get('sort'));
        if (p.has('per_page')) $('#per-page').val(p.get('per_page'));
        if (p.has('price_min') || p.has('price_max')) {
            const sMin = p.has('price_min') ? parseInt(p.get('price_min')) : PRICE_MIN;
            const sMax = p.has('price_max') ? parseInt(p.get('price_max')) : PRICE_MAX;
            priceSlider.set([sMin, sMax]);
        }
        if (p.has('new_arrivals')) $('input[name="new_arrivals"][value="' + p.get('new_arrivals') + '"]').prop('checked', true);
        p.getAll('subcategory[]').concat(p.getAll('subcategory')).forEach(function (id) {
            $('.subcategory-filter[value="' + id + '"]').prop('checked', true);
        });
    }

    /* ===== DOM ready ===== */
    $(document).ready(function () {

        /* --- Initialize dual-handle noUiSlider --- */
        priceSlider = noUiSlider.create(document.getElementById('price-slider'), {
            start: [PRICE_MIN, PRICE_MAX],
            connect: true,
            range: { min: PRICE_MIN, max: PRICE_MAX },
            step: Math.max(1, Math.round((PRICE_MAX - PRICE_MIN) / 200)),
            format: {
                to:   function (v) { return Math.round(v); },
                from: function (v) { return Number(v); }
            }
        });

        priceSlider.on('update', function (values) {
            const lo = parseInt(values[0]);
            const hi = parseInt(values[1]);
            $('#price-display-min').text('₹' + lo.toLocaleString('en-IN'));
            $('#price-display-max').text('₹' + hi.toLocaleString('en-IN'));
            $('#price-min').val(lo > PRICE_MIN ? lo : '');
            $('#price-max').val(hi < PRICE_MAX ? hi : '');
        });

        priceSlider.on('change', function () {
            renderActiveFilters();
            loadProducts(1);
        });

        initFromURL();
        renderActiveFilters();
        loadProducts(1);

        /* Filter changes – instant */
        $(document).on('change', '.subcategory-filter, input[name="rating"], input[name="new_arrivals"]', function () { renderActiveFilters(); loadProducts(1); });
        $('#sort-select, #per-page').on('change', function () { loadProducts(1); });

        /* Search button / enter */
        $('#search-btn').on('click', function () {
            const q = $('#search-input').val().trim();
            if (q) saveRecentSearch(q);
            hideSuggestions(); renderActiveFilters(); loadProducts(1);
        });
        $('#search-input').on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                const q = $(this).val().trim();
                if (q) saveRecentSearch(q);
                hideSuggestions(); renderActiveFilters(); loadProducts(1);
            }
        });

        /* Focus: show recent searches when input is empty */
        $('#search-input').on('focus', function () {
            if (!$(this).val().trim()) showRecentSearches();
        });

        /* Autocomplete */
        $('#search-input').on('input', function () {
            const q = $(this).val().trim();
            clearTimeout(suggestionsTimeout);
            if (q.length < 2) {
                if (!q) showRecentSearches();
                else hideSuggestions();
                return;
            }
            suggestionsTimeout = setTimeout(function () { fetchSuggestions(q); }, 300);
        });

        /* Keyboard nav in suggestions */
        $('#search-input').on('keydown', function (e) {
            const $items = $('#search-suggestions .suggestion-item');
            if (!$items.length) return;
            if (e.which === 38) { e.preventDefault(); currentSuggestionIndex = Math.max(-1, currentSuggestionIndex - 1); highlightSuggestion($items); }
            else if (e.which === 40) { e.preventDefault(); currentSuggestionIndex = Math.min($items.length - 1, currentSuggestionIndex + 1); highlightSuggestion($items); }
            else if (e.which === 13 && currentSuggestionIndex >= 0) { e.preventDefault(); loadProducts(1); hideSuggestions(); }
            else if (e.which === 27) hideSuggestions();
        });

        /* Click suggestion */
        $(document).on('click', '.suggestion-item', function () {
            const val = $(this).data('value');
            $('#search-input').val(val);
            saveRecentSearch(val);
            hideSuggestions();
            renderActiveFilters();
            loadProducts(1);
        });

        /* Click recent search item */
        $(document).on('click', '.recent-search-item', function (e) {
            if ($(e.target).hasClass('rs-remove-btn') || $(e.target).closest('.rs-remove-btn').length) return;
            const val = $(this).data('value');
            $('#search-input').val(val);
            hideSuggestions();
            renderActiveFilters();
            loadProducts(1);
        });

        /* Remove one recent search */
        $(document).on('click', '.rs-remove-btn', function (e) {
            e.stopPropagation();
            removeRecentSearch($(this).data('term'));
            showRecentSearches();
        });

        /* Clear all recent searches */
        $(document).on('click', '#clear-recents-btn', function (e) {
            e.stopPropagation();
            clearRecentSearches();
            hideSuggestions();
        });

        /* Click outside */
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.search-autocomplete-container').length) hideSuggestions();
        });

        /* Clear filters — defined on window so zero-results panel can call it */
        window.clearAllFilters = function clearAllFilters() {
            $('#search-input').val('');
            $('#price-min, #price-max').val('');
            $('input[name="rating"]').prop('checked', false);
            $('input[name="new_arrivals"]').prop('checked', false);
            $('.subcategory-filter').prop('checked', false);
            $('#sort-select').val('newest');
            $('#per-page').val('12');
            priceSlider.set([PRICE_MIN, PRICE_MAX]);
            renderActiveFilters();
            loadProducts(1);
        };
        $('#clear-filters, #clear-all-chip').on('click', window.clearAllFilters);

        /* Individual chip remove */
        $(document).on('click', '.chip-remove', function () {
            const key = $(this).closest('.filter-chip').data('key');
            if (window._chipRemovers && window._chipRemovers[key]) {
                window._chipRemovers[key]();
                renderActiveFilters();
                loadProducts(1);
            }
        });

        /* Infinite scroll – IntersectionObserver + scroll/touchend fallback */
        function triggerInfiniteLoad() {
            if (!nextPage || isLoading) return;
            const sentinel = document.getElementById('scroll-sentinel');
            if (!sentinel) return;
            const rect = sentinel.getBoundingClientRect();
            // 400px look-ahead handles iOS address-bar height shift
            if (rect.top <= window.innerHeight + 400) {
                loadProducts(nextPage);
            }
        }

        if ('IntersectionObserver' in window) {
            const sentinelObserver = new IntersectionObserver(function (entries) {
                if (entries[0].isIntersecting && nextPage && !isLoading) {
                    loadProducts(nextPage);
                }
            }, { rootMargin: '400px' }); // larger margin for iOS address-bar drift
            sentinelObserver.observe(document.getElementById('scroll-sentinel'));
        } else {
            // Fallback: older iOS Safari (<12.1) and legacy Android browsers
            // passive:true = never blocks touch-scroll, critical for mobile performance
            window.addEventListener('scroll',   triggerInfiniteLoad, { passive: true });
            window.addEventListener('touchend', triggerInfiniteLoad, { passive: true });
        }

        /* Add to cart (AJAX form from _product_cards partial) */
        $(document).on('submit', '.add-to-cart-form', function (e) {
            e.preventDefault();
            const form = $(this);
            const btn  = form.find('button[type="submit"]');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            $.ajax({
                type: 'POST', url: "{{ route('cart.ajaxAdd') }}", data: form.serialize(),
                success: function (res) {
                    showToast(res.message || 'Added to cart');
                    btn.prop('disabled', false).html('Add to Cart');
                    if (res.cart_count !== undefined) $('#cartCount').text(res.cart_count);
                },
                error: function (xhr) {
                    btn.prop('disabled', false).html('Add to Cart');
                    showToast((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to add to cart', false);
                }
            });
        });

        /* Wishlist toggle */
        $(document).on('click', '.wishlist-toggle', function () {
            const btn = $(this);
            $.post("{{ route('wishlist.toggle') }}", { _token: CSRF, product_id: btn.data('product-id') }, function (res) {
                if (res.status) {
                    showToast(res.message, true);
                    if (res.wishlist_count !== undefined) $('#wishlistCount').text(res.wishlist_count);
                    const icon = btn.find('.wishlist-icon');
                    if (icon.length) icon.text(icon.text() === '❤️' ? '🤍' : '❤️');
                } else {
                    showToast(res.message, false);
                }
            }).fail(function () { showToast('Failed to update wishlist', false); });
        });

        /* Guest clicks */
        $(document).on('click', '.guest-wishlist', function () {
            showToast('Please login to add to wishlist', false);
            setTimeout(function () { window.location.href = '{{ route("login") }}'; }, 1500);
        });
    });
    </script>
</body>
</html>
