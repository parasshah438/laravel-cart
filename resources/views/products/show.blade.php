<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ $product->name }} - Product Details</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

@php
    use Illuminate\Support\Facades\Storage;
@endphp

<style>
    /* Amazon-style Rating Input Styles */
    .rating-input {
        display: flex;
        gap: 5px;
        align-items: center;
    }
    
    .rating-input input[type="radio"] {
        display: none;
    }
    
    .rating-input label.star {
        font-size: 2rem;
        color: #ddd;
        cursor: pointer;
        transition: color 0.2s ease;
    }
    
    .rating-input label.star:hover {
        color: #ffc107;
        transform: scale(1.1);
    }
    
    /* When a radio is checked, color it and all previous stars */
    .rating-input input[value="1"]:checked ~ label,
    .rating-input input[value="1"]:checked + label {
        color: #ffc107;
    }
    
    .rating-input input[value="2"]:checked ~ label,
    .rating-input input[value="2"]:checked + label,
    .rating-input input[value="1"]:checked + label {
        color: #ffc107;
    }
    
    .rating-input input[value="3"]:checked ~ label,
    .rating-input input[value="3"]:checked + label,
    .rating-input input[value="2"]:checked + label,
    .rating-input input[value="1"]:checked + label {
        color: #ffc107;
    }
    
    .rating-input input[value="4"]:checked ~ label,
    .rating-input input[value="4"]:checked + label,
    .rating-input input[value="3"]:checked + label,
    .rating-input input[value="2"]:checked + label,
    .rating-input input[value="1"]:checked + label {
        color: #ffc107;
    }
    
    .rating-input input[value="5"]:checked ~ label,
    .rating-input input[value="5"]:checked + label,
    .rating-input input[value="4"]:checked + label,
    .rating-input input[value="3"]:checked + label,
    .rating-input input[value="2"]:checked + label,
    .rating-input input[value="1"]:checked + label {
        color: #ffc107;
    }
    
    /* Amazon-style review actions */
    .review-actions {
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .border-bottom:hover .review-actions {
        opacity: 1;
    }
</style>

<script>
    // Enhanced rating system JavaScript - Global functions
    function updateStarDisplay(selectedInput) {
        const container = selectedInput.closest('.rating-input');
        const allLabels = container.querySelectorAll('label.star');
        const selectedValue = parseInt(selectedInput.value);
        
        // Reset all stars
        allLabels.forEach(function(label) {
            label.style.color = '#ddd';
        });
        
        // Color the selected stars
        for (let i = 1; i <= selectedValue; i++) {
            const label = container.querySelector(`label[for*="rating-${i}"]`);
            if (label) {
                label.style.color = '#ffc107';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Add event listeners to all rating inputs
        document.querySelectorAll('.rating-input input[type="radio"]').forEach(function(input) {
            input.addEventListener('change', function() {
                updateStarDisplay(this);
            });
        });
    });
</script>
    <!-- Glide.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/css/glide.core.min.css">

    <!-- Glide.js Theme (Optional) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/css/glide.theme.min.css">
    
    <style>
        /* Quantity Controls Styling */
        .btn-qty-decrease,
        .btn-qty-increase,
        .btn-qty-decrease-new,
        .btn-qty-increase-new {
            border: 1px solid #dee2e6;
            background: #f8f9fa;
            color: #495057;
            font-weight: bold;
            transition: all 0.2s ease;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .btn-qty-decrease:hover,
        .btn-qty-increase:hover,
        .btn-qty-decrease-new:hover,
        .btn-qty-increase-new:hover {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .cart-qty-input,
        .new-product-qty {
            border-left: none !important;
            border-right: none !important;
            text-align: center;
            font-weight: bold;
        }

        .qty-icon {
            font-size: 16px;
            line-height: 1;
        }
        
        .wishlist-toggle-btn {
            transition: all 0.3s ease;
        }
        
        .wishlist-toggle-btn:hover {
            transform: translateY(-2px);
        }

        /* Glide Slider Styles */
        .glide {
            position: relative;
        }

        .glide__slide {
            padding: 0 10px;
        }

        .glide__arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 123, 255, 0.9);
            color: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 123, 255, 0.3);
        }

        .glide__arrow:hover {
            background: #007bff;
            transform: translateY(-50%) scale(1.15);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.5);
        }

        .glide__arrow:focus {
            outline: none;
            background: #007bff;
        }

        .glide__arrow--left {
            left: -25px;
        }

        .glide__arrow--right {
            right: -25px;
        }



        .product-card-slider {
            transition: all 0.3s ease;
            border: 1px solid #e3e6f0;
        }

        .product-card-slider:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            border-color: #007bff;
        }

        .product-card-slider .card-body {
            min-height: 120px;
        }

        @media (max-width: 768px) {
            .glide__arrow--left {
                left: -10px;
            }
            
            .glide__arrow--right {
                right: -10px;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- Glide.js JS -->
    <script src="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/glide.min.js"></script>
</head>
<body>
<div class="container py-4">
    {{-- Page title --}}
    <h1 class="mb-4 h3">{{ $product->name }}</h1>

    <div class="row g-4">
        {{-- Left: Product Image --}}
        <div class="col-md-6">
            <div class="border rounded p-3 text-center bg-white shadow-sm">
                <img src="{{ $product->image }}" class="img-fluid" alt="{{ $product->name }}">
            </div>
        </div>

        {{-- Right: Product Info --}}
        <div class="col-md-6">
            <div class="bg-white p-4 rounded shadow-sm">
                {{-- Price --}}
                <h4 class="text-success fw-bold">₹{{ number_format($product->price, 2) }}</h4>

                {{-- Stock Status --}}
                @php 
                    $stock = $product->stocks->first(); 
                    $cartItem = null;
                    if(auth()->check()) {
                        $cart = auth()->user()->cart;
                        if($cart) {
                            $cartItem = $cart->items()->where('product_id', $product->id)->first();
                        }
                    }
                @endphp

                @if($stock?->isOutOfStock())
                    <div class="alert alert-danger">Out of Stock</div>
                @elseif($stock?->isLowStock())
                    <div class="alert alert-warning">Only {{ $stock->qty }} left in stock!</div>
                @else
                    <div class="alert alert-success">In Stock</div>
                @endif

                {{-- Description --}}
                <p class="text-muted mt-3">{{ $product->description }}</p>

                {{-- Add to Cart Section --}}
                <div class="mt-4" id="cartSection">
                    @if($stock?->isOutOfStock())
                        {{-- Out of Stock --}}
                        <button class="btn btn-secondary btn-lg w-100 mb-3" disabled>Out of Stock</button>
                    @elseif($cartItem)
                        {{-- Product already in cart - show quantity controls --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quantity in Cart:</label>
                            <div class="input-group" style="max-width: 200px;">
                                <button class="btn btn-outline-secondary btn-qty-decrease" type="button" data-product-id="{{ $product->id }}">
                                    <span class="qty-icon">{{ $cartItem->quantity <= 1 ? '🗑️' : '−' }}</span>
                                </button>
                                <input type="number"
                                    class="form-control text-center cart-qty-input"
                                    value="{{ $cartItem->quantity }}"
                                    data-product-id="{{ $product->id }}"
                                    data-max="{{ $stock ? $stock->qty : 999 }}"
                                    min="1"
                                    readonly>
                                <button class="btn btn-outline-secondary btn-qty-increase" type="button" data-product-id="{{ $product->id }}">
                                    <span class="qty-icon">+</span>
                                </button>
                            </div>
                            @if($stock && $stock->qty <= 3)
                                <div class="text-danger small mt-1">Only {{ $stock->qty }} left in stock!</div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-success btn-lg w-100 mb-3" onclick="window.location.href='{{ route('cart.view') }}'">
                            <i class="fas fa-shopping-cart"></i> View Cart
                        </button>
                    @else
                        {{-- Product not in cart - show add to cart --}}
                        @auth
                        <form method="POST" action="{{ route('cart.ajaxAdd') }}" class="add-to-cart-form" id="productAddToCartForm">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Quantity:</label>
                                <div class="input-group" style="max-width: 200px;">
                                    <button class="btn btn-outline-secondary btn-qty-decrease-new" type="button">
                                        <span class="qty-icon">−</span>
                                    </button>
                                    <input type="number" name="quantity" value="1" min="1" max="{{ $stock ? $stock->qty : 999 }}" 
                                           class="form-control text-center new-product-qty">
                                    <button class="btn btn-outline-secondary btn-qty-increase-new" type="button">
                                        <span class="qty-icon">+</span>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </form>
                        @else
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary btn-lg w-100 mb-3" onclick="window.location.href='{{ route('login') }}'">
                                <i class="fas fa-sign-in-alt"></i> Login to Add to Cart
                            </button>
                        </div>
                        @endauth
                    @endif
                </div>

                {{-- Other Buttons --}}
                <div class="d-grid gap-2">
                    @if($stock?->isOutOfStock())
                        <button class="btn btn-warning btn-lg w-100" disabled>
                            <i class="fas fa-ban"></i> Buy Now - Out of Stock
                        </button>
                    @else
                        @auth
                        <button type="button" class="btn btn-warning btn-lg w-100 buy-now-btn" data-product-id="{{ $product->id }}">
                            <i class="fas fa-bolt"></i> Buy Now
                        </button>
                        @else
                        <button type="button" class="btn btn-warning btn-lg w-100" onclick="redirectToLogin('buy_now')">
                            <i class="fas fa-bolt"></i> Buy Now
                        </button>
                        @endauth
                    @endif

                    @auth
                    <button type="button" class="btn btn-outline-danger w-100 wishlist-toggle-btn" data-product-id="{{ $product->id }}">
                        <span class="wishlist-icon">{{ $wishlistProductIds->contains($product->id) ? '❤️' : '🤍' }}</span>
                        <span class="wishlist-text">{{ $wishlistProductIds->contains($product->id) ? 'Remove from Wishlist' : 'Add to Wishlist' }}</span>
                    </button>
                    @else
                    <button type="button" class="btn btn-outline-danger w-100" onclick="window.location.href='{{ route('login') }}'">
                        <span class="wishlist-icon">🤍</span>
                        <span class="wishlist-text">Login to Add to Wishlist</span>
                    </button>
                    @endauth

                    <a href="#inquiryModal" class="btn btn-outline-secondary w-100" data-bs-toggle="modal">
                        <i class="fas fa-envelope"></i> Contact for Inquiry
                    </a>
                </div>

                {{-- Rating (based on user reviews) --}}
                @if($reviewStats['total_reviews'] > 0)
                    <div class="mt-4">
                        <strong>Rating:</strong>
                        <div class="d-inline-flex align-items-center">
                            {{-- Star Rating Display --}}
                            <div class="me-2" style="color: #ffc107; font-size: 1.1rem;">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= floor($reviewStats['average_rating']))
                                        <i class="fas fa-star"></i>
                                    @elseif($i == ceil($reviewStats['average_rating']) && $reviewStats['average_rating'] - floor($reviewStats['average_rating']) >= 0.5)
                                        <i class="fas fa-star-half-alt"></i>
                                    @else
                                        <i class="far fa-star"></i>
                                    @endif
                                @endfor
                            </div>
                            {{-- Rating Value and Count --}}
                            <span class="text-muted">
                                <strong>{{ number_format($reviewStats['average_rating'], 1) }}</strong> out of 5 
                                (<a href="#reviews" class="text-decoration-none" onclick="document.getElementById('reviews-tab').click();">{{ $reviewStats['total_reviews'] }} {{ Str::plural('review', $reviewStats['total_reviews']) }}</a>)
                            </span>
                        </div>
                    </div>
                @else
                    <div class="mt-4">
                        <strong>Rating:</strong>
                        <div class="text-muted">
                            <span style="color: #ddd; font-size: 1.1rem;">
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                            </span>
                            <span class="ms-2">No reviews yet - <a href="#reviews" class="text-decoration-none" onclick="document.getElementById('reviews-tab').click();">Be the first to review!</a></span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Product Inquiry Modal --}}
    <div class="modal fade" id="inquiryModal" tabindex="-1" aria-labelledby="inquiryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" action="" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="inquiryModalLabel">Product Inquiry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Your Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Your Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Send Inquiry</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================================================================================================ -->
    <!-- 📝 AMAZON-STYLE REVIEWS & RATINGS SECTION -->
    <!-- ================================================================================================ -->
    <div class="mt-5">
        <div class="card border-0 shadow-sm">
            <!-- Reviews Header with Tabs -->
            <div class="card-header bg-white border-bottom">
                <ul class="nav nav-tabs card-header-tabs" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">
                            <i class="fas fa-info-circle me-2"></i>Description
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">
                            <i class="fas fa-star me-2"></i>Reviews 
                            @if($reviewStats['has_reviews'])
                                <span class="badge bg-primary">{{ $reviewStats['total_reviews'] }}</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button" role="tab">
                            <i class="fas fa-list me-2"></i>Specifications
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="card-body">
                <div class="tab-content" id="productTabContent">
                    <!-- Description Tab -->
                    <div class="tab-pane fade show active" id="description" role="tabpanel">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="fw-bold mb-3">Product Description</h6>
                                <p>{{ $product->description }}</p>
                                
                                <!-- Additional product details can go here -->
                                <div class="mt-4">
                                    <h6 class="fw-bold">Key Features</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success me-2"></i>High Quality Material</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Comfortable Fit</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Durable Design</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Easy Care Instructions</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light p-3 rounded">
                                    <h6 class="fw-bold mb-3">Product Details</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr><td class="fw-bold">Price:</td><td>${{ number_format($product->price, 2) }}</td></tr>
                                        <tr><td class="fw-bold">Category:</td><td>{{ $product->category->name ?? 'N/A' }}</td></tr>
                                        <tr><td class="fw-bold">Status:</td><td>
                                            <span class="badge bg-success">{{ ucfirst($product->status) }}</span>
                                        </td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews Tab -->
                    <div class="tab-pane fade" id="reviews" role="tabpanel">
                        @if($reviewStats['has_reviews'])
                            <!-- Reviews Summary -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="text-center p-4 bg-light rounded">
                                        <div class="display-4 fw-bold text-warning mb-2">{{ number_format($reviewStats['average_rating'], 1) }}</div>
                                        <div class="mb-2">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= round($reviewStats['average_rating']) ? 'text-warning' : 'text-muted' }}"></i>
                                            @endfor
                                        </div>
                                        <div class="text-muted">{{ $reviewStats['total_reviews'] }} reviews</div>
                                        @if($reviewStats['verified_percentage'] > 0)
                                            <div class="mt-2">
                                                <small class="text-success">
                                                    <i class="fas fa-check-circle"></i> {{ $reviewStats['verified_percentage'] }}% verified purchases
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h6 class="mb-3">Rating Breakdown</h6>
                                    @for($rating = 5; $rating >= 1; $rating--)
                                        @php
                                            $data = $reviewStats['rating_breakdown'][$rating] ?? ['count' => 0, 'percentage' => 0];
                                        @endphp
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="me-2 text-nowrap">{{ $rating }} ★</span>
                                            <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                <div class="progress-bar bg-warning" style="width: {{ $data['percentage'] }}%"></div>
                                            </div>
                                            <span class="text-muted small">{{ $data['count'] }}</span>
                                        </div>
                                    @endfor
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="mb-0">Customer Reviews</h6>
                                <div>
                                    @auth
                                        <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#writeReviewModal">
                                            <i class="fas fa-star me-2"></i>Write Review
                                        </button>
                                    @else
                                        <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">
                                            <i class="fas fa-sign-in-alt me-2"></i>Login to Review
                                        </a>
                                    @endauth
                                    <a href="{{ $reviewStats['all_reviews_url'] }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-external-link-alt me-2"></i>See All Reviews
                                    </a>
                                </div>
                            </div>

                            <!-- Recent Reviews -->
                            @if(isset($reviewsWithIndicators) && $reviewsWithIndicators->count() > 0)
                                @foreach($reviewsWithIndicators as $review)
                                    <div class="border-bottom pb-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <span class="text-white fw-bold">{{ strtoupper(substr($review->user->name, 0, 1)) }}</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">{{ $review->user->name }}</h6>
                                                    <div class="d-flex align-items-center mb-1">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }} small"></i>
                                                        @endfor
                                                        <span class="ms-2 small fw-bold">{{ $review->rating }}/5</span>
                                                        
                                                        <!-- Quality Indicators -->
                                                        @if($review->quality_indicators)
                                                            @foreach($review->quality_indicators as $indicator)
                                                                <span class="badge bg-{{ $indicator['class'] }} ms-1 small" 
                                                                      title="{{ $indicator['text'] }}">
                                                                    <i class="fas fa-{{ $indicator['icon'] }}"></i>
                                                                    @if($indicator['type'] === 'verified')
                                                                        Verified
                                                                    @elseif($indicator['type'] === 'helpful')
                                                                        Helpful
                                                                    @elseif($indicator['type'] === 'trusted')
                                                                        Trusted
                                                                    @elseif($indicator['type'] === 'photos')
                                                                        Photos
                                                                    @endif
                                                                </span>
                                                            @endforeach
                                                        @endif
                                                        
                                                        <!-- Highlight indicator for exceptional reviews -->
                                                        @if($review->should_highlight)
                                                            <span class="badge bg-gradient bg-warning text-dark ms-1 small">
                                                                <i class="fas fa-crown"></i> Top Review
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <small class="text-muted">{{ $review->created_at->format('M d, Y') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if($review->title)
                                            <h6 class="mb-2 text-primary">{{ $review->title }}</h6>
                                        @endif
                                        
                                        <p class="mb-2">{{ $review->comment }}</p>
                                        
                                        @if($review->hasPhotos())
                                            <div class="mb-2">
                                                <div class="d-flex gap-2">
                                                    @foreach($review->photos as $photo)
                                                        <img src="{{ asset('storage/' . $photo) }}" alt="Review Photo" 
                                                             class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                                                             onclick="showImageModal('{{ asset('storage/' . $photo) }}')">
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                        
                                        @if($review->would_recommend !== null)
                                            <div class="mb-2">
                                                @if($review->would_recommend)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-thumbs-up me-1"></i>Recommends
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-thumbs-down me-1"></i>Doesn't recommend
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                        
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                @auth
                                                    @if($review->user_id !== auth()->id())
                                                        <button type="button" class="btn btn-sm btn-outline-success me-2" 
                                                                onclick="voteHelpful({{ $review->id }}, true)">
                                                            <i class="fas fa-thumbs-up me-1"></i>
                                                            Helpful ({{ $review->helpful_count }})
                                                        </button>
                                                    @endif
                                                @else
                                                    <span class="text-muted small">
                                                        <i class="fas fa-thumbs-up text-success me-1"></i>
                                                        {{ $review->helpful_count }} found helpful
                                                    </span>
                                                @endauth
                                            </div>
                                            
                                            <!-- Amazon-Style: Owner Actions -->
                                            @auth
                                                @if($review->user_id === auth()->id())
                                                    <div class="d-flex align-items-center">
                                                        @php
                                                            $canEdit = $review->created_at->diffInDays(now()) <= 90; // 90-day Amazon-style limit
                                                            $isLocked = $review->helpful_count >= 20; // Lock highly helpful reviews
                                                        @endphp
                                                        
                                                        @if($canEdit && !$isLocked)
                                                            <button type="button" class="btn btn-sm btn-outline-primary me-2" 
                                                                    onclick="editReview({{ $review->id }})">
                                                                <i class="fas fa-edit me-1"></i>Edit
                                                            </button>
                                                        @elseif($isLocked)
                                                            <span class="text-muted small me-2" title="This review has received many helpful votes and cannot be edited">
                                                                <i class="fas fa-lock me-1"></i>Locked
                                                            </span>
                                                        @elseif(!$canEdit)
                                                            <span class="text-muted small me-2" title="Reviews can only be edited within 90 days">
                                                                <i class="fas fa-clock me-1"></i>Edit period expired
                                                            </span>
                                                        @endif
                                                        
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                @if($canEdit && !$isLocked)
                                                                    <li><a class="dropdown-item" href="#" onclick="editReview({{ $review->id }})">
                                                                        <i class="fas fa-edit me-2"></i>Edit Review
                                                                    </a></li>
                                                                @endif
                                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteReview({{ $review->id }})">
                                                                    <i class="fas fa-trash me-2"></i>Delete Review
                                                                </a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endauth
                                        </div>
                                    </div>
                                @endforeach

                                @if($reviewStats['total_reviews'] > $reviewStats['recent_reviews_count'])
                                    <div class="text-center mt-4">
                                        <a href="{{ $reviewStats['all_reviews_url'] }}" class="btn btn-outline-primary">
                                            <i class="fas fa-plus me-2"></i>View All {{ $reviewStats['total_reviews'] }} Reviews
                                        </a>
                                    </div>
                                @endif
                            @endif
                        @else
                            <!-- No Reviews State -->
                            <div class="text-center py-5">
                                <i class="fas fa-star fa-3x text-muted mb-3"></i>
                                <h5>No Reviews Yet</h5>
                                <p class="text-muted mb-3">Be the first to review {{ $product->name }}!</p>
                                @auth
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#writeReviewModal">
                                        <i class="fas fa-star me-2"></i>Write First Review
                                    </button>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-primary">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login to Write Review
                                    </a>
                                @endauth
                            </div>
                        @endif
                    </div>

                    <!-- Specifications Tab -->
                    <div class="tab-pane fade" id="specifications" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">General</h6>
                                <table class="table table-striped">
                                    <tr><td class="fw-bold">Brand</td><td>{{ $product->brand ?? 'N/A' }}</td></tr>
                                    <tr><td class="fw-bold">Model</td><td>{{ $product->model ?? 'N/A' }}</td></tr>
                                    <tr><td class="fw-bold">Material</td><td>Premium Quality</td></tr>
                                    <tr><td class="fw-bold">Color</td><td>As shown in image</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Additional Info</h6>
                                <table class="table table-striped">
                                    <tr><td class="fw-bold">Warranty</td><td>1 Year</td></tr>
                                    <tr><td class="fw-bold">Care Instructions</td><td>Follow care label</td></tr>
                                    <tr><td class="fw-bold">Country of Origin</td><td>India</td></tr>
                                    <tr><td class="fw-bold">Package Contents</td><td>1 x {{ $product->name }}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Write Review Modal (for authenticated users) -->
    @auth
        @include('reviews.partials.write-modal', ['product' => $product])
    @endauth

    <!-- Edit Review Modal -->
    @auth
        <div class="modal fade" id="editReviewModal" tabindex="-1" aria-labelledby="editReviewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editReviewModalLabel">
                            <i class="fas fa-edit me-2"></i>Edit Your Review
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editReviewForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Amazon-style editing:</strong> You can edit your review within 90 days. Highly helpful reviews may be locked from editing.
                            </div>
                            
                            <!-- Rating -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Overall Rating</label>
                                <div class="rating-input">
                                    <input type="radio" name="rating" value="1" id="edit-rating-1" required>
                                    <label for="edit-rating-1" class="star">★</label>
                                    
                                    <input type="radio" name="rating" value="2" id="edit-rating-2" required>
                                    <label for="edit-rating-2" class="star">★</label>
                                    
                                    <input type="radio" name="rating" value="3" id="edit-rating-3" required>
                                    <label for="edit-rating-3" class="star">★</label>
                                    
                                    <input type="radio" name="rating" value="4" id="edit-rating-4" required>
                                    <label for="edit-rating-4" class="star">★</label>
                                    
                                    <input type="radio" name="rating" value="5" id="edit-rating-5" required>
                                    <label for="edit-rating-5" class="star">★</label>
                                </div>
                            </div>

                            <!-- Title -->
                            <div class="mb-3">
                                <label for="edit-title" class="form-label fw-bold">Review Title (Optional)</label>
                                <input type="text" class="form-control" id="edit-title" name="title" maxlength="200" placeholder="Summarize your review...">
                            </div>

                            <!-- Comment -->
                            <div class="mb-3">
                                <label for="edit-comment" class="form-label fw-bold">Your Review</label>
                                <textarea class="form-control" id="edit-comment" name="comment" rows="4" required minlength="10" maxlength="2000" placeholder="Share your experience with this product..."></textarea>
                                <div class="form-text">Minimum 10 characters, maximum 2000</div>
                            </div>

                            <!-- Would Recommend -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Would you recommend this product?</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="would_recommend" id="edit-recommend-yes" value="1">
                                    <label class="form-check-label" for="edit-recommend-yes">
                                        <i class="fas fa-thumbs-up text-success me-1"></i>Yes, I recommend this product
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="would_recommend" id="edit-recommend-no" value="0">
                                    <label class="form-check-label" for="edit-recommend-no">
                                        <i class="fas fa-thumbs-down text-danger me-1"></i>No, I don't recommend this product
                                    </label>
                                </div>
                            </div>

                            <!-- Product Variant -->
                            <div class="mb-3">
                                <label for="edit-variant" class="form-label fw-bold">Product Variant (Optional)</label>
                                <input type="text" class="form-control" id="edit-variant" name="product_variant" maxlength="100" placeholder="e.g., Size: Large, Color: Blue">
                            </div>

                            <!-- Current Photos -->
                            <div class="mb-3" id="current-photos-section" style="display: none;">
                                <label class="form-label fw-bold">Current Photos</label>
                                <div id="current-photos-display" class="d-flex gap-2 mb-2"></div>
                            </div>

                            <!-- New Photos -->
                            <div class="mb-3">
                                <label for="edit-photos" class="form-label fw-bold">Add New Photos (Optional)</label>
                                <input type="file" class="form-control" id="edit-photos" name="photos[]" multiple accept="image/*">
                                <div class="form-text">You can upload up to 5 photos total. Supported formats: JPG, PNG, GIF. Max size: 2MB each.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endauth

    <!-- Image Preview Modal -->
    @include('reviews.partials.image-modal')

    {{-- Similar Products --}}
    @if($similarProducts->count())
    <div class="mt-5">
        <h5 class="mb-4">Similar Products</h5>
        <div class="glide" id="similarProductsSlider">
            <div class="glide__track" data-glide-el="track">
                <ul class="glide__slides">
                    @foreach($similarProducts as $product)
                        <li class="glide__slide">
                            <div class="h-100">
                                @include('partials._single_product_card', ['product' => $product, 'wishlistProductIds' => $wishlistProductIds])
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="glide__arrows" data-glide-el="controls">
                <button class="glide__arrow glide__arrow--left" data-glide-dir="<">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="glide__arrow glide__arrow--right" data-glide-dir=">">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Initialize similar products slider
    if (document.getElementById('similarProductsSlider')) {
        const similarProductsGlide = new Glide('#similarProductsSlider', {
            type: 'carousel',
            startAt: 0,
            perView: 4,
            gap: 20,
            autoplay: 4000,
            hoverpause: true,
            keyboard: true,
            animationDuration: 600,
            animationTimingFunc: 'ease-in-out',
            breakpoints: {
                1200: { 
                    perView: 3, 
                    gap: 15 
                },
                992: { 
                    perView: 3, 
                    gap: 15 
                },
                768: { 
                    perView: 2, 
                    gap: 10 
                },
                576: { 
                    perView: 1, 
                    gap: 5 
                }
            }
        });

        // Mount the slider with error handling
        try {
            similarProductsGlide.mount();
            console.log('Similar products slider mounted successfully');
            
            // Pause/resume autoplay on hover
            const sliderElement = document.getElementById('similarProductsSlider');
            sliderElement.addEventListener('mouseenter', function() {
                similarProductsGlide.pause();
            });
            
            sliderElement.addEventListener('mouseleave', function() {
                similarProductsGlide.play();
            });

            // Add manual click handlers as backup
            $(document).on('click', '.glide__arrow--left', function(e) {
                e.preventDefault();
                e.stopPropagation();
                similarProductsGlide.go('<');
                console.log('Left arrow clicked');
            });

            $(document).on('click', '.glide__arrow--right', function(e) {
                e.preventDefault();
                e.stopPropagation();
                similarProductsGlide.go('>');
                console.log('Right arrow clicked');
            });
            
        } catch (error) {
            console.error('Error mounting similar products slider:', error);
        }
    }

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

    $(document).ready(function() {
        // Quantity controls for new products (before adding to cart)
        $(document).on('click', '.btn-qty-increase-new', function() {
            const input = $(this).siblings('.new-product-qty');
            const currentVal = parseInt(input.val()) || 1;
            const maxVal = parseInt(input.attr('max')) || 999;
            
            if (currentVal < maxVal) {
                input.val(currentVal + 1);
            } else {
                showToast('Maximum quantity reached!', false);
            }
        });

        $(document).on('click', '.btn-qty-decrease-new', function() {
            const input = $(this).siblings('.new-product-qty');
            const currentVal = parseInt(input.val()) || 1;
            
            if (currentVal > 1) {
                input.val(currentVal - 1);
            }
        });

        // Quantity controls for products already in cart
        $(document).on('click', '.btn-qty-increase', function() {
            const button = $(this);
            const input = button.siblings('.cart-qty-input');
            const productId = button.data('product-id');
            const currentQty = parseInt(input.val()) || 1;
            const maxQty = parseInt(input.data('max')) || 999;
            
            if (currentQty >= maxQty) {
                showToast('Maximum quantity reached!', false);
                return;
            }

            const newQty = currentQty + 1;
            updateCartQuantity(productId, newQty, input, button);
        });

        $(document).on('click', '.btn-qty-decrease', function() {
            const button = $(this);
            const input = button.siblings('.cart-qty-input');
            const productId = button.data('product-id');
            const currentQty = parseInt(input.val()) || 1;
            
            if (currentQty <= 1) {
                // Remove from cart
                if (confirm('Remove this item from cart?')) {
                    removeFromCart(productId);
                }
                return;
            }

            const newQty = currentQty - 1;
            updateCartQuantity(productId, newQty, input, button);
        });

        function updateCartQuantity(productId, quantity, input, button) {
            const originalQty = input.val();
            input.val(quantity);
            
            // Update the decrease button icon
            const decreaseBtn = button.siblings('.btn-qty-decrease');
            const decreaseIcon = decreaseBtn.find('.qty-icon');
            decreaseIcon.text(quantity <= 1 ? '🗑️' : '−');
            
            $.ajax({
                url: "{{ route('cart.ajaxUpdate') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    product_id: productId,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.status) {
                        showToast(response.message, true);
                    } else {
                        input.val(originalQty);
                        showToast(response.message, false);
                    }
                },
                error: function(xhr) {
                    input.val(originalQty);
                    showToast('Failed to update quantity', false);
                }
            });
        }

        function removeFromCart(productId) {
            $.ajax({
                url: "{{ route('cart.ajaxRemove') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    product_id: productId
                },
                success: function(response) {
                    if (response.status) {
                        showToast(response.message, true);
                        // Reload the page to refresh the cart section
                        location.reload();
                    } else {
                        showToast(response.message, false);
                    }
                },
                error: function(xhr) {
                    showToast('Failed to remove item', false);
                }
            });
        }

        // Add to cart form submission
        $(document).on('submit', '.add-to-cart-form', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.html();

            // Show loading state
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');

            $.ajax({
                type: 'POST',
                url: "{{ route('cart.ajaxAdd') }}",
                data: form.serialize(),
                success: function(response) {
                    // Show success toast
                    showToast(response.message);

                    // Reset button
                    submitBtn.prop('disabled', false).html(originalText);

                    // Reload page to update cart section state
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    // Reset button
                    submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        Object.values(errors).forEach(e => showToast(e[0], false));
                    } else {
                        showToast('Something went wrong!', false);
                    }
                }
            });
        });

        // ✅ PROFESSIONAL BUY NOW LOGIC (Amazon/Flipkart Style)
        $('.buy-now-btn').on('click', function(e) {
            e.preventDefault();
            const btn = $(this);
            const productId = btn.data('product-id');
            
            // Get quantity from current form
            let quantity = 1;
            if ($('.cart-qty-input').length) {
                // Product already in cart - use cart quantity
                quantity = parseInt($('.cart-qty-input').val()) || 1;
            } else if ($('.new-product-qty').length) {
                // Product not in cart - use selected quantity
                quantity = parseInt($('.new-product-qty').val()) || 1;
            }

            // Show loading state
            const originalText = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

            // Professional Buy Now Flow
            $.ajax({
                url: "{{ route('cart.buyNow') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    product_id: productId,
                    quantity: quantity,
                    buy_now: true  // Flag for direct checkout
                },
                success: function(response) {
                    if (response.status) {
                        showToast('Redirecting to checkout...', true);
                        
                        // Direct redirect to checkout (Amazon style)
                        setTimeout(() => {
                            window.location.href = response.checkout_url || "{{ route('checkout') }}";
                        }, 500);
                    } else {
                        showToast(response.message, false);
                        btn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Something went wrong!';
                    
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors)[0][0];
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    showToast(errorMessage, false);
                    btn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Login redirect with intent tracking
        function redirectToLogin(intent) {
            const returnUrl = encodeURIComponent(window.location.href);
            const loginUrl = "{{ route('login') }}" + "?return_url=" + returnUrl + "&intent=" + intent;
            
            showToast("Please login to continue shopping", false);
            setTimeout(() => {
                window.location.href = loginUrl;
            }, 1500);
        }

        // Wishlist toggle functionality
        $('.wishlist-toggle-btn').on('click', function() {
            @auth
            const btn = $(this);
            const productId = btn.data('product-id');
            const icon = btn.find('.wishlist-icon');
            const text = btn.find('.wishlist-text');

            $.post("{{ route('wishlist.toggle') }}", {
                _token: "{{ csrf_token() }}",
                product_id: productId
            }, function(response) {
                if (response.status) {
                    showToast(response.message, true);
                    if (icon.text().trim() === '❤️') {
                        icon.text('🤍');
                        text.text('Add to Wishlist');
                    } else {
                        icon.text('❤️');
                        text.text('Remove from Wishlist');
                    }
                } else {
                    showToast(response.message, false);
                }
            }).fail(function() {
                showToast("Failed to update wishlist", false);
            });
            @else
            showToast("Please login to add items to wishlist", false);
            setTimeout(() => {
                window.location.href = "{{ route('login') }}";
            }, 1500);
            @endauth
        });
    });

     $(document).on('click', '.guest-wishlist', function(e) {
        e.preventDefault();
        showToast("Please login to save items for later.", false);
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

    // ================================================================================================
    // 📝 REVIEWS FUNCTIONALITY (Amazon Style)
    // ================================================================================================
    
    // Vote on review helpfulness
    function voteHelpful(reviewId, isHelpful) {
        fetch(`/review/${reviewId}/helpful`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ is_helpful: isHelpful })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, true);
                // Update the button counts
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message, false);
            }
        })
        .catch(error => {
            showToast('An error occurred. Please try again.', false);
        });
    }

    // Show image in modal
    function showImageModal(imageUrl) {
        document.getElementById('imageModalImg').src = imageUrl;
        new bootstrap.Modal(document.getElementById('imageModal')).show();
    }

    // ================================================================================================
    // 📝 AMAZON-STYLE EDIT/DELETE REVIEW FUNCTIONALITY
    // ================================================================================================
    
    // Edit review - Simplified with better error handling
    function editReview(reviewId) {
        console.log('=== EDIT REVIEW DEBUG START ===');
        console.log('Review ID:', reviewId);
        
        // Test if modal exists first
        const modalElement = document.getElementById('editReviewModal');
        if (!modalElement) {
            console.error('Modal element not found!');
            alert('Edit modal not found on page');
            return;
        }
        
        // Test if form exists
        const formElement = document.getElementById('editReviewForm');
        if (!formElement) {
            console.error('Form element not found!');
            alert('Edit form not found on page');
            return;
        }
        
        console.log('Modal and form elements found, making API call...');
        
        // Fetch review data
        fetch(`/review/${reviewId}/edit`)
            .then(response => {
                console.log('API Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('API Response data:', data);
                
                if (!data.success) {
                    console.error('Server returned error:', data.message);
                    alert(data.message || 'Failed to load review data');
                    return;
                }
                
                const review = data.review;
                console.log('Processing review data:', review);
                
                // Clear form first
                try {
                    formElement.reset();
                    console.log('Form reset successful');
                } catch (error) {
                    console.error('Error resetting form:', error);
                }
                
                // Set rating
                try {
                    const ratingInput = document.querySelector(`input[name="rating"][value="${review.rating}"]`);
                    if (ratingInput) {
                        ratingInput.checked = true;
                        console.log('Rating set successfully:', review.rating);
                        
                        // Update star display
                        if (typeof updateStarDisplay === 'function') {
                            updateStarDisplay(ratingInput);
                            console.log('Star display updated');
                        }
                    } else {
                        console.error('Rating input not found for value:', review.rating);
                    }
                } catch (error) {
                    console.error('Error setting rating:', error);
                }
                
                // Set form fields
                try {
                    const titleField = document.getElementById('edit-title');
                    const commentField = document.getElementById('edit-comment');
                    const variantField = document.getElementById('edit-variant');
                    
                    if (titleField) {
                        titleField.value = review.title || '';
                        console.log('Title set:', review.title);
                    }
                    if (commentField) {
                        commentField.value = review.comment || '';
                        console.log('Comment set');
                    }
                    if (variantField) {
                        variantField.value = review.product_variant || '';
                        console.log('Variant set:', review.product_variant);
                    }
                } catch (error) {
                    console.error('Error setting form fields:', error);
                }
                
                // Set recommendation
                try {
                    if (review.would_recommend !== null && review.would_recommend !== undefined) {
                        const recommendInput = document.getElementById(review.would_recommend ? 'edit-recommend-yes' : 'edit-recommend-no');
                        if (recommendInput) {
                            recommendInput.checked = true;
                            console.log('Recommendation set:', review.would_recommend);
                        }
                    }
                } catch (error) {
                    console.error('Error setting recommendation:', error);
                }
                
                // Set form action
                try {
                    formElement.action = `/review/${reviewId}`;
                    console.log('Form action set to:', formElement.action);
                } catch (error) {
                    console.error('Error setting form action:', error);
                }
                
                // Show modal
                try {
                    console.log('About to show modal...');
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                    console.log('Modal shown successfully');
                } catch (error) {
                    console.error('Error showing modal:', error);
                    alert('Error showing edit modal: ' + error.message);
                }
                
                console.log('=== EDIT REVIEW DEBUG END ===');
            })
            .catch(error => {
                console.error('=== FETCH ERROR ===');
                console.error('Error details:', error);
                console.error('Error stack:', error.stack);
                alert('Network error loading review data: ' + error.message);
            });
    }
    
    // Remove photo from edit form
    function removePhoto(index) {
        const photoElements = document.querySelectorAll('#current-photos-display > div');
        if (photoElements[index]) {
            photoElements[index].remove();
        }
        
        // Check if no photos left
        const remainingPhotos = document.querySelectorAll('#current-photos-display > div');
        if (remainingPhotos.length === 0) {
            document.getElementById('current-photos-section').style.display = 'none';
        }
    }
    
    // Delete review
    function deleteReview(reviewId) {
        if (confirm('Are you sure you want to delete this review? This action cannot be undone.')) {
            fetch(`/review/${reviewId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, true);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message, false);
                }
            })
            .catch(error => {
                showToast('Failed to delete review', false);
            });
        }
    }
    
    // Handle edit review form submission
    document.getElementById('editReviewForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
        
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, true);
                bootstrap.Modal.getInstance(document.getElementById('editReviewModal')).hide();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message, false);
            }
        })
        .catch(error => {
            showToast('Failed to update review', false);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });

    // Handle tab switching to reviews (for URL fragments)
    $(document).ready(function() {
        // Check if URL has #reviews fragment
        if (window.location.hash === '#reviews') {
            $('#reviews-tab').tab('show');
        }
        
        // Update URL when switching to reviews tab
        $('#reviews-tab').on('shown.bs.tab', function (e) {
            window.location.hash = 'reviews';
        });
        
        // Remove hash when switching to other tabs
        $('#description-tab, #specifications-tab').on('shown.bs.tab', function (e) {
            history.replaceState(null, null, window.location.pathname);
        });
    });
</script>
</body>
</html>