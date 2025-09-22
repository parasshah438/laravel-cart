<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $share->name }} - Shared Wishlist</title>
    
    <!-- Bootstrap 5.3.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6.4.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            --accent-gradient: linear-gradient(135deg, #667eea20, #764ba220);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--primary-gradient);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 119, 198, 0.2) 0%, transparent 50%);
            animation: gradientShift 8s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes gradientShift {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        /* Glass morphism containers */
        .glass-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: 
                0 8px 32px rgba(31, 38, 135, 0.37),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.1),
                0 8px 25px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin: 2rem 0;
            overflow: hidden;
        }

        /* Header styling */
        .page-header {
            background: var(--accent-gradient);
            padding: 3rem 2rem;
            text-align: center;
            border-radius: 25px 25px 0 0;
            position: relative;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border-radius: 25px 25px 0 0;
        }

        .page-header .content {
            position: relative;
            z-index: 2;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            text-shadow: none;
        }

        .page-subtitle {
            font-size: 1.2rem;
            color: #6c757d;
            margin-bottom: 2rem;
        }

        /* Card styling */
        .product-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .product-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            border-color: rgba(102, 126, 234, 0.3);
        }

        .product-card img {
            transition: transform 0.4s ease;
        }

        .product-card:hover img {
            transform: scale(1.05);
        }

        /* Button styling */
        .btn-gradient {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #333;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            color: #333;
        }

        /* Share actions card */
        .share-actions {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 2rem;
            margin: 2rem 0;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        /* Stats styling */
        .stats-container {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 1.5rem 0;
            flex-wrap: wrap;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1rem 1.5rem;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #666;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .stat-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .stat-item i {
            margin-right: 0.5rem;
            color: #667eea;
        }

        /* Animation for items */
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.6s ease forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Toast notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            border-radius: 15px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 2rem;
            opacity: 0.5;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
            
            .main-container {
                margin: 1rem;
                border-radius: 20px;
            }
            
            .page-header {
                padding: 2rem 1rem;
                border-radius: 20px 20px 0 0;
            }
            
            .stats-container {
                gap: 1rem;
            }
            
            .stat-item {
                padding: 0.75rem 1rem;
                font-size: 0.8rem;
            }
        }

        /* Call to action styling */
        .cta-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 20px;
            padding: 3rem 2rem;
            text-align: center;
            margin: 3rem 0;
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
        }

        .cta-card h4 {
            margin-bottom: 1rem;
        }

        .cta-card .btn {
            margin: 0.5rem;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .cta-card .btn-light:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="main-container">
                    <!-- Page Header -->
                    <div class="page-header">
                        <div class="content">
                            <div class="mb-3">
                                <i class="fas fa-heart text-danger fa-3x"></i>
                            </div>
                            <h1 class="page-title">{{ $share->name }}</h1>
                            @if($share->description)
                                <p class="page-subtitle">{{ $share->description }}</p>
                            @endif
                            
                            <div class="stats-container">
                                <div class="stat-item">
                                    <i class="fas fa-user"></i>
                                    Shared by {{ $share->user->name }}
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-calendar"></i>
                                    {{ $share->created_at->format('M j, Y') }}
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-eye"></i>
                                    {{ $share->view_count }} views
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <!-- Share Actions -->
                        <div class="share-actions">
                            <h5 class="text-center mb-4">
                                <i class="fas fa-share-alt me-2"></i>Share This Wishlist
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <button class="btn btn-gradient w-100" onclick="copyShareUrl()">
                                        <i class="fas fa-copy me-2"></i>Copy Link
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <a href="mailto:?subject=Check out this wishlist&body=I wanted to share this wishlist with you: {{ $share->share_url }}" 
                                       class="btn btn-glass w-100">
                                        <i class="fas fa-envelope me-2"></i>Email
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="https://wa.me/?text=Check out this wishlist: {{ $share->share_url }}" 
                                       target="_blank" 
                                       class="btn btn-glass w-100">
                                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Wishlist Items -->
                        @if($share->items->count() > 0)
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h3 class="fw-bold">
                                    <i class="fas fa-gift me-2 text-primary"></i>
                                    Wishlist Items ({{ $share->items->count() }})
                                </h3>
                                @auth
                                    <button class="btn btn-gradient" onclick="addAllToMyWishlist()">
                                        <i class="fas fa-heart me-2"></i>Add All to My Wishlist
                                    </button>
                                @endauth
                            </div>
                            
                            <div class="row g-4">
                                @foreach($share->items as $index => $item)
                                    <div class="col-lg-4 col-md-6">
                                        <div class="card h-100 product-card fade-in-up" style="animation-delay: {{ $index * 0.1 }}s">
                                            <div class="position-relative">
                                                @if($item->product->media->count() > 0)
                                                    <img src="{{ asset('storage/' . $item->product->media->first()->file_path) }}" 
                                                         class="card-img-top" 
                                                         style="height: 250px; object-fit: cover;"
                                                         alt="{{ $item->product->name }}">
                                                @else
                                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                                         style="height: 250px;">
                                                        <i class="fas fa-image text-muted fa-3x"></i>
                                                    </div>
                                                @endif
                                                
                                                <!-- Product Actions Overlay -->
                                                <div class="position-absolute top-0 end-0 p-2">
                                                    @auth
                                                        <button class="btn btn-sm btn-outline-light rounded-circle me-1" 
                                                                onclick="addToWishlist({{ $item->product->id }})"
                                                                title="Add to My Wishlist">
                                                            <i class="fas fa-heart"></i>
                                                        </button>
                                                    @endauth
                                                </div>
                                            </div>
                                            
                                            <div class="card-body d-flex flex-column">
                                                <h5 class="card-title mb-2">
                                                    <a href="{{ route('product.show', $item->product->slug) }}" 
                                                       class="text-decoration-none text-dark">
                                                        {{ $item->product->name }}
                                                    </a>
                                                </h5>
                                                
                                                @if($item->product->description)
                                                    <p class="card-text text-muted small flex-grow-1">
                                                        {{ Str::limit(strip_tags($item->product->description), 100) }}
                                                    </p>
                                                @endif
                                                
                                                <div class="mt-auto">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h4 class="text-primary fw-bold mb-0">
                                                                ${{ number_format($item->product->price, 2) }}
                                                            </h4>
                                                            @if($item->product->compare_price && $item->product->compare_price > $item->product->price)
                                                                <small class="text-muted text-decoration-line-through">
                                                                    ${{ number_format($item->product->compare_price, 2) }}
                                                                </small>
                                                            @endif
                                                        </div>
                                                        
                                                        @if($item->product->stock > 0)
                                                            <div class="btn-group" role="group">
                                                                @auth
                                                                    <button class="btn btn-sm btn-outline-primary" 
                                                                            onclick="addToCart({{ $item->product->id }})">
                                                                        <i class="fas fa-shopping-cart me-1"></i>Cart
                                                                    </button>
                                                                @endauth
                                                                <a href="{{ route('product.show', $item->product->slug) }}" 
                                                                   class="btn btn-sm btn-primary">
                                                                    View
                                                                </a>
                                                            </div>
                                                        @else
                                                            <span class="badge bg-danger">Out of Stock</span>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <i class="fas fa-plus-circle me-1"></i>
                                                            Added {{ $item->added_at->diffForHumans() }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-heart-broken"></i>
                                <h3>This wishlist is empty</h3>
                                <p>It looks like this wishlist doesn't have any items yet.</p>
                            </div>
                        @endif

                        <!-- Call to Action -->
                        @guest
                            <div class="cta-card">
                                <h4 class="fw-bold mb-3">Create Your Own Wishlist</h4>
                                <p class="mb-3">Join thousands of users who share their favorite products with friends and family.</p>
                                <div class="d-flex gap-2 justify-content-center flex-wrap">
                                    <a href="{{ route('register') }}" class="btn btn-light btn-lg">
                                        <i class="fas fa-user-plus me-2"></i>Sign Up
                                    </a>
                                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login
                                    </a>
                                </div>
                            </div>
                        @endguest

                        <!-- Hidden input for share URL -->
                        <input type="hidden" id="shareUrl" value="{{ $share->share_url }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3.2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function copyShareUrl() {
            const shareUrl = document.getElementById('shareUrl').value;
            navigator.clipboard.writeText(shareUrl).then(function() {
                showToast('Link copied to clipboard!', 'success');
            });
        }

        @auth
        function addToWishlist(productId) {
            fetch('/wishlist/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ product_id: productId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || 'Error adding to wishlist', 'error');
                }
            })
            .catch(error => {
                showToast('Error adding to wishlist', 'error');
            });
        }

        function addToCart(productId) {
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
                    showToast('Added to cart!', 'success');
                    // Update cart count if element exists
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount && data.cart_count) {
                        cartCount.textContent = data.cart_count;
                    }
                } else {
                    showToast(data.message || 'Error adding to cart', 'error');
                }
            })
            .catch(error => {
                showToast('Error adding to cart', 'error');
            });
        }

        function addAllToMyWishlist() {
            const productIds = @json($share->items->pluck('product_id'));
            
            if (productIds.length === 0) return;
            
            Promise.all(productIds.map(productId => 
                fetch('/wishlist/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ product_id: productId })
                })
            ))
            .then(() => {
                showToast(`Added ${productIds.length} items to your wishlist!`, 'success');
            })
            .catch(error => {
                showToast('Error adding items to wishlist', 'error');
            });
        }
        @endauth

        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'} me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            // Create toast container if it doesn't exist
            let container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container position-fixed top-0 end-0 p-3';
                document.body.appendChild(container);
            }
            
            container.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Remove toast after it's hidden
            toast.addEventListener('hidden.bs.toast', () => {
                container.removeChild(toast);
            });
        }

        // Enhanced animations and interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe all product cards
            document.querySelectorAll('.product-card').forEach(card => {
                observer.observe(card);
            });

            // Enhanced product card interactions
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach((card, index) => {
                // Initial animation
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 150);

                // Hover effects
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Parallax effect for background elements
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const backgrounds = document.querySelectorAll('body::before');
                backgrounds.forEach(bg => {
                    bg.style.transform = `translateY(${scrolled * 0.5}px)`;
                });
            });

            // Share button animations
            document.querySelectorAll('.btn-gradient, .btn-glass').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });
        });
    </script>

</body>
</html>