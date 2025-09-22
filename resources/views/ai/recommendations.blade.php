<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>🤖 AI Recommendations - Personalized Shopping</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .ai-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            margin-bottom: 30px;
            padding: 30px;
            color: white;
            text-align: center;
        }

        .ai-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            font-weight: 600;
            color: #2c3e50;
        }

        .section-title i {
            font-size: 1.5rem;
            margin-right: 12px;
            color: #667eea;
        }

        .product-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .product-info {
            padding: 20px;
        }

        .product-title {
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
            font-size: 1rem;
            line-height: 1.4;
        }

        .product-category {
            color: #7f8c8d;
            font-size: 0.85rem;
            margin-bottom: 12px;
        }

        .product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 15px;
        }

        .btn-ai {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            color: white;
            padding: 8px 20px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-ai:hover {
            background: linear-gradient(45deg, #5a6fd8, #6c42a0);
            transform: translateY(-2px);
            color: white;
        }

        .ai-badge {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-right: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #bdc3c7;
        }

        .error-state {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.2);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            color: #e74c3c;
        }

        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 10px;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .nav-pills .nav-link.active {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 25px;
        }

        .recommendation-stats {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- AI Header -->
        <div class="ai-header">
            <h1 class="mb-3">
                <i class="fas fa-robot me-3"></i>
                AI-Powered Recommendations
            </h1>
            <p class="mb-0 lead">
                Discover products tailored just for you using advanced artificial intelligence
            </p>
            @if($user)
                <div class="mt-3">
                    <span class="ai-badge">
                        <i class="fas fa-user me-1"></i> Personalized for {{ $user->name }}
                    </span>
                </div>
            @else
                <div class="mt-3">
                    <span class="ai-badge">
                        <i class="fas fa-globe me-1"></i> Trending & Popular
                    </span>
                </div>
            @endif
        </div>

        <!-- Navigation Tabs -->
        <div class="ai-section">
            <ul class="nav nav-pills justify-content-center mb-4" id="recommendationTabs" role="tablist">
                @if($user)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="for-you-tab" data-bs-toggle="pill" data-bs-target="#for-you" type="button" role="tab">
                            <i class="fas fa-heart me-2"></i>For You
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="because-viewed-tab" data-bs-toggle="pill" data-bs-target="#because-viewed" type="button" role="tab">
                            <i class="fas fa-eye me-2"></i>Because You Viewed
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="similar-purchased-tab" data-bs-toggle="pill" data-bs-target="#similar-purchased" type="button" role="tab">
                            <i class="fas fa-shopping-bag me-2"></i>Similar to Purchased
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="trending-categories-tab" data-bs-toggle="pill" data-bs-target="#trending-categories" type="button" role="tab">
                            <i class="fas fa-fire me-2"></i>Trending in Your Categories
                        </button>
                    </li>
                @else
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="trending-tab" data-bs-toggle="pill" data-bs-target="#trending" type="button" role="tab">
                            <i class="fas fa-fire me-2"></i>Trending Now
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="popular-tab" data-bs-toggle="pill" data-bs-target="#popular" type="button" role="tab">
                            <i class="fas fa-star me-2"></i>Most Popular
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="recent-tab" data-bs-toggle="pill" data-bs-target="#recent" type="button" role="tab">
                            <i class="fas fa-clock me-2"></i>Recently Added
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bestsellers-tab" data-bs-toggle="pill" data-bs-target="#bestsellers" type="button" role="tab">
                            <i class="fas fa-trophy me-2"></i>Best Sellers
                        </button>
                    </li>
                @endif
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="recommendationTabsContent">
                @if($user)
                    <!-- For You Tab -->
                    <div class="tab-pane fade show active" id="for-you" role="tabpanel">
                        @include('ai.partials.recommendation-section', [
                            'title' => 'Recommended For You',
                            'icon' => 'fas fa-heart',
                            'products' => $recommendations->get('for_you', collect()),
                            'description' => 'Based on your purchase history and preferences'
                        ])
                    </div>

                    <!-- Because You Viewed Tab -->
                    <div class="tab-pane fade" id="because-viewed" role="tabpanel">
                        @include('ai.partials.recommendation-section', [
                            'title' => 'Because You Viewed',
                            'icon' => 'fas fa-eye',
                            'products' => $recommendations->get('because_you_viewed', collect()),
                            'description' => 'More products like the ones you\'ve been browsing'
                        ])
                    </div>

                    <!-- Similar to Purchased Tab -->
                    <div class="tab-pane fade" id="similar-purchased" role="tabpanel">
                        @include('ai.partials.recommendation-section', [
                            'title' => 'Similar to Your Purchases',
                            'icon' => 'fas fa-shopping-bag',
                            'products' => $recommendations->get('similar_to_purchased', collect()),
                            'description' => 'Products similar to what you\'ve bought before'
                        ])
                    </div>

                    <!-- Trending in Categories Tab -->
                    <div class="tab-pane fade" id="trending-categories" role="tabpanel">
                        @include('ai.partials.recommendation-section', [
                            'title' => 'Trending in Your Categories',
                            'icon' => 'fas fa-fire',
                            'products' => $recommendations->get('trending_in_your_categories', collect()),
                            'description' => 'Popular products in categories you love'
                        ])
                    </div>
                @else
                    <!-- Guest User Tabs -->
                    <div class="tab-pane fade show active" id="trending" role="tabpanel">
                        @include('ai.partials.recommendation-section', [
                            'title' => 'Trending Now',
                            'icon' => 'fas fa-fire',
                            'products' => $recommendations->get('trending_now', collect()),
                            'description' => 'Most popular products right now'
                        ])
                    </div>

                    <div class="tab-pane fade" id="popular" role="tabpanel">
                        @include('ai.partials.recommendation-section', [
                            'title' => 'Most Popular',
                            'icon' => 'fas fa-star',
                            'products' => $recommendations->get('most_popular', collect()),
                            'description' => 'All-time customer favorites'
                        ])
                    </div>

                    <div class="tab-pane fade" id="recent" role="tabpanel">
                        @include('ai.partials.recommendation-section', [
                            'title' => 'Recently Added',
                            'icon' => 'fas fa-clock',
                            'products' => $recommendations->get('recently_added', collect()),
                            'description' => 'Fresh arrivals you might love'
                        ])
                    </div>

                    <div class="tab-pane fade" id="bestsellers" role="tabpanel">
                        @include('ai.partials.recommendation-section', [
                            'title' => 'Best Sellers',
                            'icon' => 'fas fa-trophy',
                            'products' => $recommendations->get('best_sellers', collect()),
                            'description' => 'Top revenue generating products'
                        ])
                    </div>
                @endif
            </div>
        </div>

        @if(isset($error))
            <div class="ai-section">
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle mb-3"></i>
                    <h5>Oops! Something went wrong</h5>
                    <p>{{ $error }}</p>
                    <button class="btn btn-ai" onclick="location.reload()">
                        <i class="fas fa-refresh me-2"></i>Try Again
                    </button>
                </div>
            </div>
        @endif

        <!-- CTA Section -->
        <div class="ai-section text-center">
            <h3 class="mb-3">Want Better Recommendations?</h3>
            @guest
                <p class="mb-4">Sign up for a free account to get personalized AI recommendations based on your shopping behavior!</p>
                <a href="{{ route('register') }}" class="btn btn-ai me-3">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </a>
            @else
                <p class="mb-4">Keep shopping to improve your AI recommendations. The more you browse and buy, the smarter our suggestions become!</p>
                <a href="{{ route('shop') }}" class="btn btn-ai me-3">
                    <i class="fas fa-shopping-cart me-2"></i>Continue Shopping
                </a>
                <a href="{{ route('front.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-home me-2"></i>Back to Home
                </a>
            @endguest
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add loading states and interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Add to cart functionality
            document.querySelectorAll('.add-to-cart-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.dataset.productId;
                    const originalText = this.innerHTML;
                    
                    // Show loading state
                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';
                    this.disabled = true;
                    
                    // Add to cart (you can customize this based on your cart implementation)
                    fetch('/cart/add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            quantity: 1
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.innerHTML = '<i class="fas fa-check me-2"></i>Added!';
                            setTimeout(() => {
                                this.innerHTML = originalText;
                                this.disabled = false;
                            }, 2000);
                        } else {
                            throw new Error(data.message || 'Failed to add to cart');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.innerHTML = '<i class="fas fa-exclamation me-2"></i>Error';
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            this.disabled = false;
                        }, 2000);
                    });
                });
            });
        });
    </script>
</body>
</html>