<!DOCTYPE html>
<html lang="en">
<head>
    <title>ShopCart - Best Online Shopping Experience</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Shop the latest fashion, electronics, home decor and more at ShopCart. Best prices, fast delivery, authentic products.">
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
    <style>
        .hero-slider {
            position: relative;
            overflow: hidden;
        }
        
        .hero-slide {
            min-height: 60vh;
            display: flex;
            align-items: center;
        }
        
        .min-vh-60 {
            min-height: 60vh;
        }
        
        .swiper {
            width: 100%;
            height: 100%;
        }
        
        .swiper-slide {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .swiper-button-next,
        .swiper-button-prev {
            color: white;
            background: rgba(0,0,0,0.3);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-top: 0;
        }
        
        .swiper-button-next:after,
        .swiper-button-prev:after {
            font-size: 20px;
        }
        
        .swiper-pagination-bullet {
            background: white;
            opacity: 0.5;
            width: 12px;
            height: 12px;
        }
        
        .swiper-pagination-bullet-active {
            opacity: 1;
            background: white;
        }
        
        .hero-content h1 {
            animation: fadeInUp 1s ease-out;
        }
        
        .hero-content p {
            animation: fadeInUp 1s ease-out 0.2s both;
        }
        
        .hero-actions {
            animation: fadeInUp 1s ease-out 0.4s both;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
        }
        
        .navbar {
            transition: all 0.3s ease;
        }
        
        .navbar.scrolled {
            background-color: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .btn-cart {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-cart:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
            transform: translateY(-2px);
        }
        
        footer {
            background: linear-gradient(135deg, #2c3e50, #3498db);
        }
        
        /* Enhanced Search Suggestions Styling */
        .search-suggestions-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e0e0e0;
            border-top: none;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            max-height: 400px;
            overflow-y: auto;
            z-index: 1050;
            margin-top: 1px;
        }
        
        .search-item {
            padding: 12px 16px;
            border-bottom: 1px solid #f5f5f5;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            min-height: 60px;
        }
        
        .search-item:hover,
        .search-item.selected {
            background-color: #f8f9fa;
            transform: translateX(2px);
        }
        
        .search-item.selected {
            background-color: #e3f2fd;
            border-left: 3px solid #007bff;
        }
        
        .search-item:last-child {
            border-bottom: none;
            border-radius: 0 0 8px 8px;
        }
        
        .search-item img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 12px;
            border: 1px solid #e9ecef;
        }
        
        .search-item-content {
            flex: 1;
            min-width: 0;
        }
        
        .search-item-name {
            font-weight: 500;
            color: #333;
            margin-bottom: 2px;
            font-size: 14px;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .search-item-price {
            color: #007bff;
            font-weight: 600;
            font-size: 13px;
        }
        
        .search-item-category {
            color: #6c757d;
            font-size: 12px;
            margin-left: 8px;
        }
        
        .search-no-results {
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-style: italic;
        }
        
        .search-loading {
            padding: 20px;
            text-align: center;
            color: #007bff;
        }
        
        /* Custom scrollbar for search dropdown */
        .search-suggestions-dropdown::-webkit-scrollbar {
            width: 6px;
        }
        
        .search-suggestions-dropdown::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 0 0 8px 0;
        }
        
        .search-suggestions-dropdown::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        .search-suggestions-dropdown::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Mega Menu Styling */
        .mega-menu {
            position: relative;
        }
        
        .category-nav-item {
            position: relative;
        }
        
        .category-nav-link {
            padding: 12px 20px !important;
            transition: all 0.3s ease;
            font-weight: 500;
            border-bottom: 2px solid transparent;
        }
        
        .category-nav-link:hover {
            background: rgba(0,123,255,0.1);
            color: #007bff !important;
            border-bottom-color: #007bff;
        }
        
        /* Individual Category Dropdown */
        .category-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border: 1px solid #e9ecef;
            border-top: 3px solid #007bff;
            z-index: 1000;
            min-width: 800px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            border-radius: 0 0 8px 8px;
            overflow: hidden;
        }
        
        .category-nav-item:hover .category-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .category-dropdown-content {
            display: flex;
            flex-direction: column;
            min-height: 400px;
        }
        
        .category-sidebar {
            width: 100%;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .category-sidebar-item {
            padding: 8px 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid #e9ecef;
            border-radius: 20px;
            display: flex;
            align-items: center;
            background: white;
            font-size: 13px;
            font-weight: 500;
        }
        
        .category-sidebar-item:hover,
        .category-sidebar-item.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .category-sidebar-item i {
            margin-right: 6px;
            font-size: 12px;
        }
        
        .category-subcategories {
            flex: 1;
            padding: 20px;
            position: relative;
        }
        
        .subcategory-content {
            display: none;
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            overflow-y: auto;
        }
        
        .subcategory-content.active {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-content: start;
        }
        
        .subcategory-column {
            margin-bottom: 20px;
        }
        
        .subcategory-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        
        .subcategory-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .subcategory-list li {
            margin-bottom: 8px;
        }
        
        .subcategory-list a {
            color: #6c757d;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.2s ease;
            display: block;
            padding: 2px 0;
        }
        
        .subcategory-list a:hover {
            color: #007bff;
            text-decoration: underline;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .category-dropdown {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                min-width: auto;
                border-radius: 0;
                transform: translateX(100%);
                opacity: 1;
                visibility: visible;
            }
            
            .category-dropdown.show {
                transform: translateX(0);
            }
            
            .category-dropdown-content {
                height: 100vh;
                overflow-y: auto;
            }
            
            .category-sidebar {
                width: 100%;
                border-bottom: 2px solid #e9ecef;
                padding: 10px;
                flex-direction: column;
                gap: 5px;
            }
            
            .category-sidebar-item {
                justify-content: center;
                border-radius: 6px;
                padding: 10px 15px;
            }
            
            .category-subcategories {
                flex: 1;
                padding: 15px;
            }
            
            .subcategory-content.active {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }
        
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .hero-content p {
                font-size: 1rem;
            }
            
            .hero-actions .btn {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }
            
            .navbar-brand {
                font-size: 1.1rem;
            }
            
            .search-suggestions-dropdown {
                left: -15px;
                right: -15px;
                max-height: 300px;
            }
            
            .search-item {
                padding: 10px 12px;
                min-height: 55px;
            }
            
            .search-item img {
                width: 35px;
                height: 35px;
                margin-right: 10px;
            }
            
            .search-item-name {
                font-size: 13px;
            }
            
            .search-item-price {
                font-size: 12px;
            }
        }
    </style>
    
    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</head>
<body>
    <!-- Header Section -->
    <header class="sticky-top bg-white shadow-sm">
        <!-- Top Bar -->
        <div class="bg-primary text-white py-1">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <small class="d-none d-md-block">
                            <i class="fas fa-truck"></i> Free Delivery on Orders Above ₹500
                        </small>
                    </div>
                    <div class="col-md-6 text-end">
                        <small>
                            <i class="fas fa-phone"></i> Help: +91-1234567890
                            <span class="ms-3">
                                <i class="fas fa-envelope"></i> support@shopcart.com
                            </span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Header -->
        <nav class="navbar navbar-expand-lg navbar-light py-3">
            <div class="container">
                <!-- Logo -->
                <a class="navbar-brand fw-bold fs-3 text-primary" href="{{ route('front.index') }}">
                    <i class="fas fa-shopping-bag me-2"></i>ShopCart
                </a>
                
                <!-- Mobile Menu Toggle -->
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <!-- Main Navigation -->
                <div class="collapse navbar-collapse" id="navbarContent">
                    <!-- Search Bar -->
                    <div class="mx-auto position-relative" style="max-width: 500px; width: 100%;">
                        <div class="input-group">
                            <input type="text" 
                                   id="searchInput" 
                                   class="form-control border-0 bg-light" 
                                   placeholder="Search products, brands, categories..."
                                   autocomplete="off">
                            <button class="btn btn-primary" type="button" id="searchBtn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <!-- Search Suggestions -->
                        <div id="searchSuggestions" class="search-suggestions-dropdown" style="display: none;">
                            <!-- Dynamic suggestions will appear here -->
                        </div>
                    </div>
                    
                    <!-- Right Side Icons -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Wishlist -->
                        <li class="nav-item me-3">
                            <a class="nav-link position-relative" href="{{ route('wishlist.index') }}">
                                <i class="fas fa-heart fa-lg"></i>
                                <span id="wishlistCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ auth()->check() ? auth()->user()->wishlist()->count() : 0 }}
                                </span>
                                <small class="d-block">Wishlist</small>
                            </a>
                        </li>
                        
                        <!-- Cart -->
                        <li class="nav-item me-3">
                            <a class="nav-link position-relative" href="{{ route('cart.view') }}">
                                <i class="fas fa-shopping-cart fa-lg"></i>
                                <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                                  
                                </span>
                                <small class="d-block">Cart</small>
                            </a>
                        </li>
                        
                        <!-- User Account -->
                        <li class="nav-item dropdown">
                            @auth
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user fa-lg"></i>
                                <small class="d-block">{{ Str::limit(auth()->user()->name, 10) }}</small>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>My Profile</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-box me-2"></i>My Orders</a></li>
                                <li><a class="dropdown-item" href="{{ route('wishlist.index') }}"><i class="fas fa-heart me-2"></i>Wishlist</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                            @else
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt fa-lg"></i>
                                <small class="d-block">Login</small>
                            </a>
                            @endauth
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <!-- Category Navigation with Individual Dropdowns -->
        <div class="bg-light border-top mega-menu">
            <div class="container">
                <nav class="navbar navbar-expand-lg navbar-light py-0">
                    <button class="navbar-toggler border-0 py-2" type="button" data-bs-toggle="collapse" data-bs-target="#categoryNav">
                        <i class="fas fa-bars"></i> Categories
                    </button>
                    
                    <div class="collapse navbar-collapse" id="categoryNav">
                        <ul class="navbar-nav w-100">
                            @foreach($categories->take(7) as $category)
                            <li class="nav-item category-nav-item">
                                <a class="nav-link category-nav-link" href="{{ route('category.products', $category->slug) }}">
                                    <i class="{{ $category->icon ?? 'fas fa-tag' }} me-1"></i> 
                                    {{ $category->name }}
                                </a>
                                
                                @if($category->children->count() > 0)
                                <!-- Individual Category Dropdown -->
                                <div class="category-dropdown">
                                    <div class="category-dropdown-content">
                                        <!-- Category Sidebar -->
                                        <div class="category-sidebar">
                                            @foreach($category->children as $index => $subcategory)
                                            <div class="category-sidebar-item {{ $index === 0 ? 'active' : '' }}" data-subcategory="{{ $subcategory->id }}">
                                                <i class="{{ $subcategory->icon ?? 'fas fa-folder' }}"></i>
                                                <span>{{ $subcategory->name }}</span>
                                            </div>
                                            @endforeach
                                        </div>
                                        
                                        <!-- Subcategories Content -->
                                        <div class="category-subcategories">
                                            @foreach($category->children as $index => $subcategory)
                                            <div class="subcategory-content {{ $index === 0 ? 'active' : '' }}" id="subcategory-content-{{ $subcategory->id }}">
                                                @if($subcategory->children->count() > 0)
                                                    @foreach($subcategory->children->chunk(8) as $chunk)
                                                    <div class="subcategory-column">
                                                        <div class="subcategory-title">
                                                            <i class="{{ $subcategory->icon ?? 'fas fa-folder' }} me-2"></i>
                                                            {{ $subcategory->name }}
                                                        </div>
                                                        <ul class="subcategory-list">
                                                            @foreach($chunk as $subSubcategory)
                                                            <li>
                                                                <a href="{{ route('category.products', $subSubcategory->slug) }}">
                                                                    {{ $subSubcategory->name }}
                                                                </a>
                                                            </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    @endforeach
                                                @else
                                                <div class="subcategory-column">
                                                    <div class="subcategory-title">
                                                        <i class="{{ $subcategory->icon ?? 'fas fa-tag' }} me-2"></i>
                                                        All {{ $subcategory->name }}
                                                    </div>
                                                    <ul class="subcategory-list">
                                                        <li>
                                                            <a href="{{ route('category.products', $subcategory->slug) }}">
                                                                View All {{ $subcategory->name }}
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                @endif
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </li>
                            @endforeach
                            
                            @if($categories->count() > 7)
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle category-nav-link" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-h me-1"></i> More
                                </a>
                                <ul class="dropdown-menu">
                                    @foreach($categories->skip(7) as $category)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('category.products', $category->slug) }}">
                                            <i class="{{ $category->icon ?? 'fas fa-tag' }} me-2"></i>
                                            {{ $category->name }}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </li>
                            @endif
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <!-- Main Content -->
    <main>
        <!-- Hero Slider Section -->
        <section class="hero-slider">
            <div class="swiper heroSwiper">
                <div class="swiper-wrapper">
                    @if(isset($sliders) && $sliders->count() > 0)
                        @foreach($sliders as $slider)
                        <div class="swiper-slide">
                            <div class="hero-slide position-relative" style="background: linear-gradient(135deg, {{ $slider->bg_color ?? '#667eea' }}, {{ $slider->bg_color_secondary ?? '#764ba2' }});">
                                <div class="container">
                                    <div class="row align-items-center min-vh-60">
                                        <div class="col-lg-6">
                                            <div class="hero-content text-white">
                                                <h1 class="display-4 fw-bold mb-3">{{ $slider->title }}</h1>
                                                <p class="lead mb-4">{{ $slider->subtitle }}</p>
                                                <div class="hero-actions">
                                                    <a href="{{ $slider->button_link ?? '#' }}" class="btn btn-light btn-lg me-3">
                                                        {{ $slider->button_text ?? 'Shop Now' }}
                                                    </a>
                                                    <a href="#products" class="btn btn-outline-light btn-lg">
                                                        Browse All
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="hero-image text-center">
                                                @if($slider->image)
                                                <img src="{{ $slider->image }}" alt="{{ $slider->title }}" class="img-fluid" style="max-height: 400px;">
                                                @else
                                                <img src="https://via.placeholder.com/600x400/ffffff/007bff?text=Featured+Product" alt="Featured Product" class="img-fluid">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <!-- Default Slides -->
                        <div class="swiper-slide">
                            <div class="hero-slide position-relative" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                <div class="container">
                                    <div class="row align-items-center min-vh-60">
                                        <div class="col-lg-6">
                                            <div class="hero-content text-white">
                                                <h1 class="display-4 fw-bold mb-3">Summer Fashion Sale</h1>
                                                <p class="lead mb-4">Up to 70% off on trending fashion items. Limited time offer!</p>
                                                <div class="hero-actions">
                                                    <a href="#products" class="btn btn-light btn-lg me-3">Shop Now</a>
                                                    <a href="#products" class="btn btn-outline-light btn-lg">Browse All</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="hero-image text-center">
                                                <img src="https://via.placeholder.com/600x400/ffffff/667eea?text=Fashion+Sale" alt="Fashion Sale" class="img-fluid">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="swiper-slide">
                            <div class="hero-slide position-relative" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                                <div class="container">
                                    <div class="row align-items-center min-vh-60">
                                        <div class="col-lg-6">
                                            <div class="hero-content text-white">
                                                <h1 class="display-4 fw-bold mb-3">Electronics Mega Sale</h1>
                                                <p class="lead mb-4">Latest gadgets and electronics at unbeatable prices!</p>
                                                <div class="hero-actions">
                                                    <a href="#products" class="btn btn-light btn-lg me-3">Shop Electronics</a>
                                                    <a href="#products" class="btn btn-outline-light btn-lg">View Deals</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="hero-image text-center">
                                                <img src="https://via.placeholder.com/600x400/ffffff/f5576c?text=Electronics+Sale" alt="Electronics Sale" class="img-fluid">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="swiper-slide">
                            <div class="hero-slide position-relative" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                                <div class="container">
                                    <div class="row align-items-center min-vh-60">
                                        <div class="col-lg-6">
                                            <div class="hero-content text-white">
                                                <h1 class="display-4 fw-bold mb-3">Home & Living</h1>
                                                <p class="lead mb-4">Transform your space with our beautiful home decor collection.</p>
                                                <div class="hero-actions">
                                                    <a href="#products" class="btn btn-light btn-lg me-3">Explore Home</a>
                                                    <a href="#products" class="btn btn-outline-light btn-lg">View Collection</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="hero-image text-center">
                                                <img src="https://via.placeholder.com/600x400/ffffff/00f2fe?text=Home+Decor" alt="Home Decor" class="img-fluid">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Slider Pagination -->
                <div class="swiper-pagination"></div>
                
                <!-- Slider Navigation -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </section>
        
        <!-- Features Section -->
        <section class="features py-5 bg-light">
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-3 col-sm-6">
                        <div class="feature-card text-center p-4 bg-white rounded shadow-sm h-100">
                            <i class="fas fa-shipping-fast fa-3x text-primary mb-3"></i>
                            <h5>Free Shipping</h5>
                            <p class="text-muted mb-0">Free delivery on orders above ₹500</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="feature-card text-center p-4 bg-white rounded shadow-sm h-100">
                            <i class="fas fa-undo-alt fa-3x text-success mb-3"></i>
                            <h5>Easy Returns</h5>
                            <p class="text-muted mb-0">30-day hassle-free returns</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="feature-card text-center p-4 bg-white rounded shadow-sm h-100">
                            <i class="fas fa-shield-alt fa-3x text-warning mb-3"></i>
                            <h5>Secure Payment</h5>
                            <p class="text-muted mb-0">100% secure payment gateway</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="feature-card text-center p-4 bg-white rounded shadow-sm h-100">
                            <i class="fas fa-headset fa-3x text-info mb-3"></i>
                            <h5>24/7 Support</h5>
                            <p class="text-muted mb-0">Round-the-clock customer support</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Products Section -->
        <section id="products" class="py-5">
            <div class="container">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h2 class="mb-3">Our Products</h2>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary active">All</button>
                            <button type="button" class="btn btn-outline-primary">Fashion</button>
                            <button type="button" class="btn btn-outline-primary">Electronics</button>
                            <button type="button" class="btn btn-outline-primary">Home</button>
                        </div>
                    </div>
                </div>
        <!-- Display error messages if available -->
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <!-- Display success message if available -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <!-- Product Cards -->
        <div class="row g-4"  id="productGrid">
           @include('partials._product_cards', ['products' => $products, 'wishlistProductIds' => $wishlistProductIds])
        </div>
        <!-- Load More Button -->
        @if ($products->hasMorePages())
        <div class="text-center mt-4">
            <button id="loadMoreBtn" class="btn btn-outline-primary" data-next-page="{{ $products->currentPage() + 1 }}">Load More</button>
        </div>
        @endif
    </div>


        </section>
    </main>

    <!-- Footer Section -->
    <footer class="text-white py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <!-- Company Info -->
                <div class="col-lg-4 col-md-6">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-shopping-bag me-2"></i>ShopCart
                    </h5>
                    <p class="text-light mb-3">
                        Your one-stop destination for the latest fashion, electronics, home decor, and more. 
                        We bring you quality products at the best prices with fast, reliable delivery.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light fs-5"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-light fs-5"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light fs-5"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light fs-5"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="text-light fs-5"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Contact Us</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Track Order</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Return Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">FAQs</a></li>
                    </ul>
                </div>
                
                <!-- Categories -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3">Categories</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Fashion</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Electronics</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Home & Furniture</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Beauty</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Sports</a></li>
                    </ul>
                </div>
                
                <!-- Customer Service -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3">Customer Service</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Help Center</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Shipping Info</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Returns</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Size Guide</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Care Instructions</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3">Contact Info</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2 text-light">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <small>123 Shopping Street, Mumbai, India</small>
                        </li>
                        <li class="mb-2 text-light">
                            <i class="fas fa-phone me-2"></i>
                            <small>+91-1234567890</small>
                        </li>
                        <li class="mb-2 text-light">
                            <i class="fas fa-envelope me-2"></i>
                            <small>support@shopcart.com</small>
                        </li>
                        <li class="mb-2 text-light">
                            <i class="fas fa-clock me-2"></i>
                            <small>24/7 Customer Support</small>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Newsletter -->
            <div class="row mt-4 pt-4 border-top border-secondary">
                <div class="col-md-8">
                    <h6 class="fw-bold mb-3">Subscribe to Our Newsletter</h6>
                    <div class="input-group mb-3" style="max-width: 400px;">
                        <input type="email" class="form-control" placeholder="Enter your email">
                        <button class="btn btn-light" type="button">
                            <i class="fas fa-paper-plane"></i> Subscribe
                        </button>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <h6 class="fw-bold mb-3">We Accept</h6>
                    <div class="d-flex gap-2 justify-content-md-end">
                        <img src="https://img.icons8.com/color/40/visa.png" alt="Visa">
                        <img src="https://img.icons8.com/color/40/mastercard.png" alt="Mastercard">
                        <img src="https://img.icons8.com/color/40/paypal.png" alt="PayPal">
                        <img src="https://img.icons8.com/color/40/google-pay.png" alt="Google Pay">
                    </div>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="row mt-4 pt-3 border-top border-secondary">
                <div class="col-md-6">
                    <p class="text-light mb-0">
                        &copy; {{ date('Y') }} ShopCart. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-light text-decoration-none me-3">Privacy Policy</a>
                    <a href="#" class="text-light text-decoration-none me-3">Terms of Service</a>
                    <a href="#" class="text-light text-decoration-none">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Gift Products Modal -->
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
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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

</body>
</html>

<script>
    // Individual Category Dropdown Functionality (Like Myntra)
    $(document).ready(function() {
        // Category sidebar item hover in individual dropdowns
        $('.category-sidebar-item').hover(function() {
            const subcategoryId = $(this).data('subcategory');
            const dropdown = $(this).closest('.category-dropdown');
            
            // Remove active class from all sidebar items in this dropdown
            dropdown.find('.category-sidebar-item').removeClass('active');
            dropdown.find('.subcategory-content').removeClass('active');
            
            // Add active class to current
            $(this).addClass('active');
            dropdown.find(`#subcategory-content-${subcategoryId}`).addClass('active');
        });

        // Prevent dropdowns from closing when hovering over them
        $('.category-dropdown').hover(
            function() {
                // Keep dropdown open
            },
            function() {
                // Optional: Add slight delay before closing
            }
        );

        // Close dropdowns when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.category-nav-item').length) {
                $('.category-dropdown').removeClass('show');
            }
        });

        // Mobile category navigation
        $('.category-nav-link').on('click', function(e) {
            if (window.innerWidth <= 768) {
                const dropdown = $(this).siblings('.category-dropdown');
                if (dropdown.length > 0) {
                    e.preventDefault();
                    dropdown.toggleClass('show');
                }
            }
        });
    });

    // Initialize Swiper for Hero Slider
    const swiper = new Swiper('.heroSwiper', {
        loop: true,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        speed: 1000,
    });
    
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Enhanced Search functionality with AJAX
    const searchInput = document.getElementById('searchInput');
    const searchSuggestions = document.getElementById('searchSuggestions');
    let searchTimeout;
    let currentSuggestionIndex = -1;
    
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        currentSuggestionIndex = -1; // Reset selection
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        if (query.length > 2) {
            // Show loading state
            searchSuggestions.innerHTML = '<div class="search-loading"><i class="fas fa-spinner fa-spin me-2"></i>Searching...</div>';
            searchSuggestions.style.display = 'block';
            
            // Debounce search requests
            searchTimeout = setTimeout(() => {
                $.ajax({
                    url: "{{ route('search.suggestions') }}",
                    type: 'GET',
                    data: { 
                        query: query
                    },
                    success: function(response) {
                        if (response.suggestions && response.suggestions.length > 0) {
                            const suggestionsHtml = response.suggestions.map(item => 
                                `<div class="search-item" data-url="${item.url}" data-value="${item.name}" data-id="${item.id}">
                                    <img src="${item.image || 'https://via.placeholder.com/40x40/f8f9fa/6c757d?text=No+Image'}" alt="${item.name}" onerror="this.src='https://via.placeholder.com/40x40/f8f9fa/6c757d?text=No+Image'">
                                    <div class="search-item-content">
                                        <div class="search-item-name">${item.name}</div>
                                        <div class="search-item-price">₹${parseFloat(item.price).toLocaleString('en-IN')}</div>
                                    </div>
                                </div>`
                            ).join('');
                            
                            searchSuggestions.innerHTML = suggestionsHtml;
                            searchSuggestions.style.display = 'block';
                        } else {
                            showDefaultSuggestions(query);
                        }
                    },
                    error: function() {
                        showDefaultSuggestions(query);
                    }
                });
            }, 300);
        } else {
            searchSuggestions.style.display = 'none';
        }
    });
    
    // Keyboard navigation for search suggestions
    searchInput.addEventListener('keydown', function(e) {
        const suggestions = document.querySelectorAll('.search-item');
        
        if (suggestions.length === 0) return;
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                currentSuggestionIndex = Math.min(currentSuggestionIndex + 1, suggestions.length - 1);
                updateSuggestionSelection(suggestions);
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                currentSuggestionIndex = Math.max(currentSuggestionIndex - 1, -1);
                updateSuggestionSelection(suggestions);
                break;
                
            case 'Enter':
                e.preventDefault();
                if (currentSuggestionIndex >= 0 && suggestions[currentSuggestionIndex]) {
                    suggestions[currentSuggestionIndex].click();
                } else {
                    const query = this.value.trim();
                    if (query) {
                        performSearch(query);
                    }
                }
                break;
                
            case 'Escape':
                searchSuggestions.style.display = 'none';
                currentSuggestionIndex = -1;
                break;
        }
    });
    
    function updateSuggestionSelection(suggestions) {
        // Remove previous selection
        suggestions.forEach(item => item.classList.remove('selected'));
        
        // Add selection to current item
        if (currentSuggestionIndex >= 0 && suggestions[currentSuggestionIndex]) {
            suggestions[currentSuggestionIndex].classList.add('selected');
            suggestions[currentSuggestionIndex].scrollIntoView({ block: 'nearest' });
        }
    }
    
    function showDefaultSuggestions(query) {
        const defaultSuggestions = [
            { name: 'T-Shirts', category: 'Fashion', icon: 'fa-tshirt' },
            { name: 'Jeans & Denim', category: 'Fashion', icon: 'fa-tshirt' },
            { name: 'Sneakers & Shoes', category: 'Fashion', icon: 'fa-shoe-prints' },
            { name: 'Smartphones', category: 'Electronics', icon: 'fa-mobile-alt' },
            { name: 'Laptops & Computers', category: 'Electronics', icon: 'fa-laptop' },
            { name: 'Home Decor', category: 'Home & Living', icon: 'fa-home' },
            { name: 'Books & Education', category: 'Books', icon: 'fa-book' },
            { name: 'Sports Equipment', category: 'Sports & Fitness', icon: 'fa-dumbbell' }
        ].filter(item => 
            item.name.toLowerCase().includes(query.toLowerCase()) ||
            item.category.toLowerCase().includes(query.toLowerCase())
        );
        
        if (defaultSuggestions.length > 0) {
            const suggestionsHtml = defaultSuggestions.map(item => 
                `<div class="search-item" data-value="${item.name}">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                        <i class="fas ${item.icon} text-white"></i>
                    </div>
                    <div class="search-item-content">
                        <div class="search-item-name">${item.name}</div>
                        <div class="search-item-category">in ${item.category}</div>
                    </div>
                </div>`
            ).join('');
            
            searchSuggestions.innerHTML = suggestionsHtml;
            searchSuggestions.style.display = 'block';
        } else {
            searchSuggestions.innerHTML = '<div class="search-no-results">No suggestions found for "' + query + '"</div>';
            searchSuggestions.style.display = 'block';
        }
    }
    
    // Handle search suggestion clicks
    $(document).on('click', '.search-item', function() {
        const url = $(this).data('url');
        const value = $(this).data('value');
        
        if (url) {
            // If it's a product suggestion, navigate to product details
            window.location.href = url;
        } else {
            // If it's a category suggestion, perform search
            $('#searchInput').val(value);
            $('#searchSuggestions').hide();
            performSearch(value);
        }
    });
    
    // Handle search button click
    $('#searchBtn').on('click', function() {
        const query = $('#searchInput').val().trim();
        if (query) {
            performSearch(query);
        }
    });
    
    function performSearch(query) {
        window.location.href = `{{ route('front.index') }}?search=${encodeURIComponent(query)}`;
    }
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
            searchSuggestions.style.display = 'none';
        }
    });

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

    $(document).on('click', '.wishlist-toggle', function() {
        const btn = $(this);
        const icon = btn.find('.wishlist-icon');
        const productId = btn.data('product-id');

        $.post("{{ route('wishlist.toggle') }}", {
            _token: "{{ csrf_token() }}",
            product_id: productId
        }, function(response) {
            if (response.status) {
                showToast(response.message, true);
                $('#wishlistCount').text(response.wishlist_count);
                if (icon.text().trim() === '❤️') {
                    icon.text('🤍');
                } else {
                    icon.text('❤️');
                }
            } else {
                showToast(response.message, false);
            }
        }).fail(function() {
            showToast("Failed to update wishlist", false);
        });
    });

    // Load more products
    $(document).ready(function() {
        $('#loadMoreBtn').on('click', function() {
            const button = $(this);
            const nextPage = button.data('next-page');
            button.prop('disabled', true).text('Loading...');
            
            $.ajax({
                url: `?page=${nextPage}`,
                method: 'GET',
                success: function(response) {
                    $('#productGrid').append(response.html);
                    if (response.hasMorePages) {
                        button.data('next-page', response.nextPage).prop('disabled', false).text('Load More');
                    } else {
                        button.remove();
                        showToast('No more products to display.', false);
                    }
                },
                error: function() {
                    showToast('Something went wrong. Please try again.', false);
                    button.prop('disabled', false).text('Load More');
                }
            });
        });
    });

    // Gift products functionality
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

    function showGiftProductsModal(productId) {
        $.ajax({
            url: "{{ route('cart.giftProducts') }}",
            type: 'GET',
            data: { product_id: productId },
            success: function(response) {
                $('#giftProductsModal .modal-body').html(response.html);
                $('#giftProductsModal').modal('show');
            },
            error: function() {
                console.log('Error loading gift products');
            }
        });
    }

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

    $(document).on('change', '.gift-checkbox', function() {
        const qtyInput = $(this).closest('.gift-product').find('.gift-qty');
        if (!$(this).is(':checked')) {
            qtyInput.val(0);
        } else if (parseInt(qtyInput.val()) === 0) {
            qtyInput.val(1);
        }
    });

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