<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Fashion Store - Your Style Destination')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #ff3f6c;
            --secondary-color: #282c3f;
            --accent-color: #ff905a;
            --light-gray: #f5f5f6;
            --dark-gray: #535766;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--secondary-color);
            overflow-x: hidden;
        }
        
        /* Header Styles */
        .top-header {
            background: var(--light-gray);
            padding: 8px 0;
            font-size: 12px;
        }
        
        .main-header {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            overflow: visible;
        }
        
        .navbar {
            position: static;
            overflow: visible;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 24px;
            color: var(--primary-color) !important;
        }
        
        .navbar-nav {
            position: static;
        }
        
        .navbar-nav .nav-link {
            font-weight: 500;
            color: var(--secondary-color) !important;
            margin: 0 15px;
            transition: color 0.3s ease;
            position: relative;
            padding: 15px 0;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .search-box {
            position: relative;
            max-width: 400px;
        }
        
        .search-box input {
            border: 1px solid #ddd;
            border-radius: 25px;
            padding: 10px 45px 10px 20px;
            width: 100%;
        }
        
        .search-box .search-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: var(--dark-gray);
        }
        
        .header-icons {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .header-icons a {
            color: var(--secondary-color);
            text-decoration: none;
            position: relative;
            transition: color 0.3s ease;
        }
        
        .header-icons a:hover {
            color: var(--primary-color);
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Mega Menu - Myntra Style */
        .nav-item {
            position: static;
        }
        
        .mega-menu {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            width: 100vw;
            background: white;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            z-index: 9999;
            border-top: 4px solid var(--primary-color);
        }
        
        .nav-item:hover .mega-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .mega-menu-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .mega-menu-content {
            padding: 40px 0;
        }
        
        .mega-menu h6 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 8px;
            display: inline-block;
        }
        
        .mega-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .mega-menu ul li {
            margin-bottom: 12px;
        }
        
        .mega-menu ul li a {
            color: var(--dark-gray);
            text-decoration: none;
            font-size: 14px;
            font-weight: 400;
            transition: all 0.3s ease;
            display: block;
            padding: 5px 0;
            position: relative;
        }
        
        .mega-menu ul li a:hover {
            color: var(--primary-color);
            padding-left: 10px;
            font-weight: 500;
        }
        
        .mega-menu ul li a::before {
            content: '';
            position: absolute;
            left: -5px;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }
        
        .mega-menu ul li a:hover::before {
            width: 5px;
        }
        
        /* Mega Menu Categories with Icons */
        .mega-category {
            position: relative;
            padding: 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .mega-category:hover {
            background: rgba(255, 63, 108, 0.05);
            transform: translateY(-2px);
        }
        
        .category-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            margin-bottom: 15px;
        }
        
        /* Hero Slider */
        .hero-slider {
            height: 500px;
            overflow: hidden;
        }
        
        .hero-slide {
            height: 500px;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            position: relative;
        }
        
        .hero-slide::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.3);
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
        }
        
        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        
        .btn-primary-custom {
            background: var(--primary-color);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            color: white;
        }
        
        .btn-primary-custom:hover {
            background: #e6356a;
            transform: translateY(-2px);
            color: white;
        }
        
        /* Category Cards */
        .category-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .category-card:hover {
            transform: translateY(-5px);
        }
        
        .category-card img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        
        .category-card .card-body {
            padding: 20px;
            text-align: center;
        }
        
        .category-card h5 {
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        /* Product Cards */
        .product-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .product-image {
            position: relative;
            overflow: hidden;
        }
        
        .product-image img {
            height: 250px;
            object-fit: cover;
            width: 100%;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.1);
        }
        
        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .product-card:hover .product-overlay {
            opacity: 1;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .product-actions .btn {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-brand {
            font-size: 12px;
            color: var(--dark-gray);
            text-transform: uppercase;
            font-weight: 500;
        }
        
        .product-name {
            font-size: 14px;
            font-weight: 500;
            margin: 5px 0;
            color: var(--secondary-color);
        }
        
        .product-price {
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .product-price .original-price {
            text-decoration: line-through;
            color: var(--dark-gray);
            font-size: 12px;
            margin-left: 5px;
        }
        
        .product-price .discount {
            color: var(--accent-color);
            font-size: 12px;
            margin-left: 5px;
        }
        
        /* Deals Section */
        .deals-section {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 60px 0;
        }
        
        .deals-timer {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .timer-box {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            min-width: 80px;
        }
        
        .timer-box .number {
            font-size: 24px;
            font-weight: 700;
        }
        
        .timer-box .label {
            font-size: 12px;
            text-transform: uppercase;
        }
        
        /* Newsletter */
        .newsletter {
            background: var(--light-gray);
            padding: 60px 0;
        }
        
        .newsletter-form {
            max-width: 400px;
            margin: 0 auto;
            display: flex;
            gap: 10px;
        }
        
        .newsletter-form input {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 25px;
            padding: 12px 20px;
        }
        
        /* Footer */
        .footer {
            background: var(--secondary-color);
            color: white;
            padding: 50px 0 20px;
        }
        
        .footer h5 {
            color: white;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .footer ul {
            list-style: none;
            padding: 0;
        }
        
        .footer ul li {
            margin-bottom: 10px;
        }
        
        .footer ul li a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer ul li a:hover {
            color: var(--primary-color);
        }
        
        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-icons a {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: transform 0.3s ease;
        }
        
        .social-icons a:hover {
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            border-top: 1px solid #444;
            margin-top: 30px;
            padding-top: 20px;
            text-align: center;
            color: #ccc;
        }
        
        /* Mobile Responsive Styles */
        @media (max-width: 991.98px) {
            .top-header {
                display: none;
            }
            
            .navbar-brand {
                font-size: 20px;
            }
            
            .search-box {
                max-width: 100%;
                margin: 15px 0;
                order: 3;
                flex: 1 1 100%;
            }
            
            .header-icons {
                gap: 15px;
                order: 2;
            }
            
            .navbar-collapse {
                margin-top: 15px;
            }
            
            .mega-menu {
                position: static;
                width: 100%;
                transform: none;
                left: 0;
                opacity: 1;
                visibility: visible;
                box-shadow: none;
                border-top: 1px solid #eee;
                border-bottom: 1px solid #eee;
                margin: 10px 0;
                max-width: 100%;
            }
            
            .mega-menu-wrapper {
                max-width: 100%;
                padding: 0;
            }
            
            .mega-menu-content {
                padding: 20px 15px;
            }
            
            .mega-menu .row > div {
                margin-bottom: 20px;
            }
            
            .navbar-nav {
                width: 100%;
            }
            
            .navbar-nav .nav-link {
                padding: 12px 0;
                border-bottom: 1px solid #f0f0f0;
                margin: 0;
                display: block;
            }
            
            .nav-item:hover .mega-menu {
                opacity: 1;
                visibility: visible;
            }
            
            .mega-category {
                padding: 15px;
            }
            
            .category-icon {
                width: 30px;
                height: 30px;
                font-size: 14px;
            }
        }
        
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .hero-content p {
                font-size: 1rem;
            }
            
            .hero-slider {
                height: 400px;
            }
            
            .hero-slide {
                height: 400px;
            }
            
            .deals-timer {
                gap: 10px;
            }
            
            .timer-box {
                min-width: 60px;
                padding: 10px;
            }
            
            .timer-box .number {
                font-size: 18px;
            }
            
            .newsletter-form {
                flex-direction: column;
                gap: 15px;
            }
            
            .newsletter-form input,
            .newsletter-form button {
                width: 100%;
            }
            
            .product-card .product-overlay {
                opacity: 1;
                background: rgba(0,0,0,0.5);
            }
            
            .category-card img {
                height: 150px;
            }
            
            .product-image img {
                height: 200px;
            }
            
            .navbar-toggler {
                border: none;
                padding: 4px 8px;
            }
            
            .navbar-toggler:focus {
                box-shadow: none;
            }
            
            .navbar-toggler-icon {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%2840, 44, 63, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='m4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
            }
        }
        
        @media (max-width: 576px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .hero-content {
                text-align: center;
            }
            
            .container {
                padding-left: 15px;
                padding-right: 15px;
            }
            
            .footer .row > div {
                margin-bottom: 30px;
            }
            
            .social-icons {
                justify-content: center;
            }
            
            .deals-timer {
                gap: 8px;
            }
            
            .timer-box {
                min-width: 50px;
                padding: 8px;
            }
            
            .timer-box .number {
                font-size: 16px;
            }
            
            .timer-box .label {
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Top Header -->
    <div class="top-header">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <span><i class="fas fa-phone"></i> +1 234 567 8900</span>
                    <span class="ms-3"><i class="fas fa-envelope"></i> info@fashionstore.com</span>
                </div>
                <div class="col-md-6 text-end">
                    <span>Free Shipping on Orders Over $50</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="#"><i class="fas fa-shopping-bag"></i> FashionStore</a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="#" role="button">Men</a>
                            <div class="mega-menu">
                                <div class="mega-menu-wrapper">
                                    <div class="mega-menu-content">
                                        <div class="row">
                                            <div class="col-md-3 col-6">
                                                <div class="mega-category">
                                                    <div class="category-icon">
                                                        <i class="fas fa-tshirt"></i>
                                                    </div>
                                                    <h6>Topwear</h6>
                                                    <ul>
                                                        <li><a href="#">T-Shirts</a></li>
                                                        <li><a href="#">Casual Shirts</a></li>
                                                        <li><a href="#">Formal Shirts</a></li>
                                                        <li><a href="#">Sweatshirts</a></li>
                                                        <li><a href="#">Sweaters</a></li>
                                                        <li><a href="#">Jackets</a></li>
                                                        <li><a href="#">Blazers & Coats</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="mega-category">
                                                    <div class="category-icon">
                                                        <i class="fas fa-user-tie"></i>
                                                    </div>
                                                    <h6>Bottomwear</h6>
                                                    <ul>
                                                        <li><a href="#">Jeans</a></li>
                                                        <li><a href="#">Casual Trousers</a></li>
                                                        <li><a href="#">Formal Trousers</a></li>
                                                        <li><a href="#">Shorts</a></li>
                                                        <li><a href="#">Track Pants</a></li>
                                                        <li><a href="#">Cargos</a></li>
                                                        <li><a href="#">Joggers</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="mega-category">
                                                    <div class="category-icon">
                                                        <i class="fas fa-shoe-prints"></i>
                                                    </div>
                                                    <h6>Footwear</h6>
                                                    <ul>
                                                        <li><a href="#">Casual Shoes</a></li>
                                                        <li><a href="#">Sports Shoes</a></li>
                                                        <li><a href="#">Formal Shoes</a></li>
                                                        <li><a href="#">Sneakers</a></li>
                                                        <li><a href="#">Sandals & Floaters</a></li>
                                                        <li><a href="#">Flip Flops</a></li>
                                                        <li><a href="#">Boots</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="mega-category">
                                                    <div class="category-icon">
                                                        <i class="fas fa-gem"></i>
                                                    </div>
                                                    <h6>Accessories</h6>
                                                    <ul>
                                                        <li><a href="#">Watches</a></li>
                                                        <li><a href="#">Belts</a></li>
                                                        <li><a href="#">Wallets</a></li>
                                                        <li><a href="#">Sunglasses</a></li>
                                                        <li><a href="#">Bags & Backpacks</a></li>
                                                        <li><a href="#">Caps & Hats</a></li>
                                                        <li><a href="#">Jewellery</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" role="button">Women</a>
                            <div class="mega-menu">
                                <div class="mega-menu-wrapper">
                                    <div class="mega-menu-content">
                                        <div class="row">
                                            <div class="col-md-3 col-6">
                                                <div class="mega-category">
                                                    <div class="category-icon">
                                                        <i class="fas fa-female"></i>
                                                    </div>
                                                    <h6>Indian & Fusion Wear</h6>
                                                    <ul>
                                                        <li><a href="#">Kurtas & Suits</a></li>
                                                        <li><a href="#">Kurtis, Tunics & Tops</a></li>
                                                        <li><a href="#">Sarees</a></li>
                                                        <li><a href="#">Ethnic Wear</a></li>
                                                        <li><a href="#">Leggings, Salwars</a></li>
                                                        <li><a href="#">Skirts & Palazzos</a></li>
                                                        <li><a href="#">Dress Materials</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="mega-category">
                                                    <div class="category-icon">
                                                        <i class="fas fa-user-friends"></i>
                                                    </div>
                                                    <h6>Western Wear</h6>
                                                    <ul>
                                                        <li><a href="#">Dresses</a></li>
                                                        <li><a href="#">Tops</a></li>
                                                        <li><a href="#">Tshirts</a></li>
                                                        <li><a href="#">Jeans</a></li>
                                                        <li><a href="#">Trousers & Capris</a></li>
                                                        <li><a href="#">Shorts & Skirts</a></li>
                                                        <li><a href="#">Shrugs</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="mega-category">
                                                    <div class="category-icon">
                                                        <i class="fas fa-high-heel"></i>
                                                    </div>
                                                    <h6>Footwear</h6>
                                                    <ul>
                                                        <li><a href="#">Flats</a></li>
                                                        <li><a href="#">Casual Shoes</a></li>
                                                        <li><a href="#">Heels</a></li>
                                                        <li><a href="#">Boots</a></li>
                                                        <li><a href="#">Sports Shoes</a></li>
                                                        <li><a href="#">Sandals</a></li>
                                                        <li><a href="#">Flip Flops</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="mega-category">
                                                    <div class="category-icon">
                                                        <i class="fas fa-shopping-bag"></i>
                                                    </div>
                                                    <h6>Accessories</h6>
                                                    <ul>
                                                        <li><a href="#">Handbags</a></li>
                                                        <li><a href="#">Jewellery</a></li>
                                                        <li><a href="#">Sunglasses</a></li>
                                                        <li><a href="#">Watches</a></li>
                                                        <li><a href="#">Hair Accessories</a></li>
                                                        <li><a href="#">Belts</a></li>
                                                        <li><a href="#">Scarves & Wraps</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Kids</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Home & Living</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Beauty</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Studio</a>
                        </li>
                    </ul>
                    
                    <div class="search-box me-3">
                        <input type="text" class="form-control" placeholder="Search for products, brands and more">
                        <button class="search-btn"><i class="fas fa-search"></i></button>
                    </div>
                    
                    <div class="header-icons">
                        <a href="#"><i class="far fa-user"></i></a>
                        <a href="#"><i class="far fa-heart"></i></a>
                        <a href="#">
                            <i class="fas fa-shopping-bag"></i>
                            <span class="cart-count">3</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Slider -->
    <section class="hero-slider">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active hero-slide" style="background-image: url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80')">
                    <div class="container">
                        <div class="hero-content">
                            <h1>Summer Collection 2024</h1>
                            <p>Discover the latest trends in fashion with up to 70% off</p>
                            <button class="btn btn-primary-custom">Shop Now</button>
                        </div>
                    </div>
                </div>
                <div class="carousel-item hero-slide" style="background-image: url('https://images.unsplash.com/photo-1445205170230-053b83016050?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2071&q=80')">
                    <div class="container">
                        <div class="hero-content">
                            <h1>New Arrivals</h1>
                            <p>Fresh styles just landed - Be the first to wear them</p>
                            <button class="btn btn-primary-custom">Explore</button>
                        </div>
                    </div>
                </div>
                <div class="carousel-item hero-slide" style="background-image: url('https://images.unsplash.com/photo-1472851294608-062f824d29cc?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80')">
                    <div class="container">
                        <div class="hero-content">
                            <h1>Premium Brands</h1>
                            <p>Luxury fashion at unbeatable prices</p>
                            <button class="btn btn-primary-custom">Shop Premium</button>
                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Shop by Category</h2>
                <p class="text-muted">Discover our wide range of fashion categories</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card category-card">
                        <img src="https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" class="card-img-top" alt="Men's Fashion">
                        <div class="card-body">
                            <h5 class="card-title">Men's Fashion</h5>
                            <p class="card-text text-muted">Trendy & Comfortable</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card category-card">
                        <img src="https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" class="card-img-top" alt="Women's Fashion">
                        <div class="card-body">
                            <h5 class="card-title">Women's Fashion</h5>
                            <p class="card-text text-muted">Elegant & Stylish</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card category-card">
                        <img src="https://images.unsplash.com/photo-1514090458221-65bb69cf63e6?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" class="card-img-top" alt="Kids Fashion">
                        <div class="card-body">
                            <h5 class="card-title">Kids Fashion</h5>
                            <p class="card-text text-muted">Fun & Colorful</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card category-card">
                        <img src="https://images.unsplash.com/photo-1549298916-b41d501d3772?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" class="card-img-top" alt="Accessories">
                        <div class="card-body">
                            <h5 class="card-title">Accessories</h5>
                            <p class="card-text text-muted">Complete Your Look</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Featured Products</h2>
                <p class="text-muted">Handpicked items just for you</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card product-card">
                        <div class="product-image">
                            <img src="https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" class="card-img-top" alt="Product">
                            <div class="product-overlay">
                                <div class="product-actions">
                                    <button class="btn btn-light"><i class="far fa-heart"></i></button>
                                    <button class="btn btn-primary"><i class="fas fa-shopping-cart"></i></button>
                                    <button class="btn btn-light"><i class="far fa-eye"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-brand">Nike</div>
                            <div class="product-name">Men's Running Shoes</div>
                            <div class="product-price">
                                $89.99
                                <span class="original-price">$129.99</span>
                                <span class="discount">(30% OFF)</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card product-card">
                        <div class="product-image">
                            <img src="https://images.unsplash.com/photo-1434389677669-e08b4cac3105?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" class="card-img-top" alt="Product">
                            <div class="product-overlay">
                                <div class="product-actions">
                                    <button class="btn btn-light"><i class="far fa-heart"></i></button>
                                    <button class="btn btn-primary"><i class="fas fa-shopping-cart"></i></button>
                                    <button class="btn btn-light"><i class="far fa-eye"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-brand">Zara</div>
                            <div class="product-name">Women's Summer Dress</div>
                            <div class="product-price">
                                $59.99
                                <span class="original-price">$79.99</span>
                                <span class="discount">(25% OFF)</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card product-card">
                        <div class="product-image">
                            <img src="https://images.unsplash.com/photo-1556905055-8f358a7a47b2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" class="card-img-top" alt="Product">
                            <div class="product-overlay">
                                <div class="product-actions">
                                    <button class="btn btn-light"><i class="far fa-heart"></i></button>
                                    <button class="btn btn-primary"><i class="fas fa-shopping-cart"></i></button>
                                    <button class="btn btn-light"><i class="far fa-eye"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-brand">Adidas</div>
                            <div class="product-name">Casual T-Shirt</div>
                            <div class="product-price">
                                $29.99
                                <span class="original-price">$39.99</span>
                                <span class="discount">(25% OFF)</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card product-card">
                        <div class="product-image">
                            <img src="https://images.unsplash.com/photo-1553062407-98eeb64c6a62?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" class="card-img-top" alt="Product">
                            <div class="product-overlay">
                                <div class="product-actions">
                                    <button class="btn btn-light"><i class="far fa-heart"></i></button>
                                    <button class="btn btn-primary"><i class="fas fa-shopping-cart"></i></button>
                                    <button class="btn btn-light"><i class="far fa-eye"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-brand">H&M</div>
                            <div class="product-name">Denim Jeans</div>
                            <div class="product-price">
                                $49.99
                                <span class="original-price">$69.99</span>
                                <span class="discount">(28% OFF)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <button class="btn btn-primary-custom">View All Products</button>
            </div>
        </div>
    </section>

    <!-- Flash Deals -->
    <section class="deals-section">
        <div class="container text-center">
            <h2 class="fw-bold mb-3">Flash Sale</h2>
            <p class="mb-4">Limited time offers - Don't miss out!</p>
            <div class="deals-timer">
                <div class="timer-box">
                    <div class="number" id="days">12</div>
                    <div class="label">Days</div>
                </div>
                <div class="timer-box">
                    <div class="number" id="hours">05</div>
                    <div class="label">Hours</div>
                </div>
                <div class="timer-box">
                    <div class="number" id="minutes">23</div>
                    <div class="label">Minutes</div>
                </div>
                <div class="timer-box">
                    <div class="number" id="seconds">45</div>
                    <div class="label">Seconds</div>
                </div>
            </div>
            <button class="btn btn-light btn-lg mt-4">Shop Flash Sale</button>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter">
        <div class="container text-center">
            <h3 class="fw-bold mb-3">Stay Updated</h3>
            <p class="text-muted mb-4">Subscribe to our newsletter for exclusive offers and latest fashion trends</p>
            <div class="newsletter-form">
                <input type="email" class="form-control" placeholder="Enter your email address">
                <button class="btn btn-primary-custom">Subscribe</button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>About FashionStore</h5>
                    <p>Your ultimate destination for trendy and affordable fashion. We bring you the latest styles from around the world.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Customer Service</h5>
                    <ul>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Size Guide</a></li>
                        <li><a href="#">Shipping Info</a></li>
                        <li><a href="#">Returns & Exchanges</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Quick Links</h5>
                    <ul>
                        <li><a href="#">New Arrivals</a></li>
                        <li><a href="#">Best Sellers</a></li>
                        <li><a href="#">Sale</a></li>
                        <li><a href="#">Gift Cards</a></li>
                        <li><a href="#">Track Order</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Contact Info</h5>
                    <ul>
                        <li><i class="fas fa-map-marker-alt me-2"></i> 123 Fashion Street, NY 10001</li>
                        <li><i class="fas fa-phone me-2"></i> +1 234 567 8900</li>
                        <li><i class="fas fa-envelope me-2"></i> info@fashionstore.com</li>
                        <li><i class="fas fa-clock me-2"></i> Mon-Fri: 9AM-6PM</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 FashionStore. All rights reserved. | Privacy Policy | Terms of Service</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Countdown Timer
        function updateTimer() {
            const days = document.getElementById('days');
            const hours = document.getElementById('hours');
            const minutes = document.getElementById('minutes');
            const seconds = document.getElementById('seconds');
            
            let d = parseInt(days.textContent);
            let h = parseInt(hours.textContent);
            let m = parseInt(minutes.textContent);
            let s = parseInt(seconds.textContent);
            
            s--;
            if (s < 0) {
                s = 59;
                m--;
                if (m < 0) {
                    m = 59;
                    h--;
                    if (h < 0) {
                        h = 23;
                        d--;
                    }
                }
            }
            
            days.textContent = d.toString().padStart(2, '0');
            hours.textContent = h.toString().padStart(2, '0');
            minutes.textContent = m.toString().padStart(2, '0');
            seconds.textContent = s.toString().padStart(2, '0');
        }
        
        setInterval(updateTimer, 1000);
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Add to cart functionality
        document.querySelectorAll('.product-actions .btn-primary').forEach(button => {
            button.addEventListener('click', function() {
                // Add animation
                this.innerHTML = '<i class="fas fa-check"></i>';
                this.classList.add('btn-success');
                this.classList.remove('btn-primary');
                
                // Update cart count
                const cartCount = document.querySelector('.cart-count');
                let count = parseInt(cartCount.textContent);
                cartCount.textContent = count + 1;
                
                // Reset after 2 seconds
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-shopping-cart"></i>';
                    this.classList.add('btn-primary');
                    this.classList.remove('btn-success');
                }, 2000);
            });
        });
        
        // Search functionality
        document.querySelector('.search-btn').addEventListener('click', function() {
            const searchInput = document.querySelector('.search-box input');
            const searchTerm = searchInput.value.trim();
            if (searchTerm) {
                alert('Searching for: ' + searchTerm);
                // Here you would typically redirect to search results page
            }
        });
        
        // Newsletter subscription
        document.querySelector('.newsletter-form button').addEventListener('click', function() {
            const emailInput = document.querySelector('.newsletter-form input');
            const email = emailInput.value.trim();
            if (email && email.includes('@')) {
                alert('Thank you for subscribing!');
                emailInput.value = '';
            } else {
                alert('Please enter a valid email address.');
            }
        });
    </script>

    @yield('scripts')
</body>
</html>