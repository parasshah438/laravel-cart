<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - Reviews & Ratings</title>
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
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
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--secondary-color);
        }
        
        .main-header {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar-nav .nav-link {
            color: var(--secondary-color);
            font-weight: 500;
            padding: 15px 20px;
            transition: all 0.3s ease;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--primary-color);
        }
        
        .product-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        
        .rating-breakdown {
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .review-card {
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .review-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .star-rating {
            color: #ffc107;
        }
        
        .progress-custom {
            height: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Simple Header -->
    <header class="main-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand fw-bold" href="{{ route('front.index') }}">
                    <i class="fas fa-shopping-bag text-primary me-2"></i>FashionStore
                </a>
                
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('front.index') }}" class="text-decoration-none">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('product.show', $product->slug) }}" class="text-decoration-none">{{ $product->name }}</a></li>
                            <li class="breadcrumb-item active">Reviews</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </nav>
    </header>

    <div class="container py-4">
        <!-- ================================================================================================ -->
        <!-- 📝 PRODUCT REVIEWS PAGE - Amazon/Flipkart Style -->
        <!-- ================================================================================================ -->
        
        <!-- Product Info Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="product-info-card p-4">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <img src="{{ $product->image_url ?? '/images/placeholder.jpg' }}" 
                                 alt="{{ $product->name }}" 
                                 class="img-fluid rounded shadow" style="max-height: 120px; object-fit: cover;">
                        </div>
                        <div class="col-md-7">
                            <h1 class="h3 mb-2">{{ $product->name }}</h1>
                            <p class="mb-2 opacity-75">{{ $product->description }}</p>
                            <div class="d-flex align-items-center">
                                <span class="h4 mb-0 me-3">${{ number_format($product->price, 2) }}</span>
                                @if($product->hasReviews())
                                    <div class="d-flex align-items-center">
                                        <div class="star-rating me-2">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= round($product->average_rating) ? '' : 'opacity-25' }}"></i>
                                            @endfor
                                        </div>
                                        <span class="fw-bold">{{ number_format($product->average_rating, 1) }}</span>
                                        <span class="ms-2 opacity-75">({{ $product->review_count }} reviews)</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3 text-end">
                            <a href="{{ route('product.show', $product->slug) }}" class="btn btn-light btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>Back to Product
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Overview & Stats -->
        <div class="row mb-4">
            <!-- Rating Overview -->
            <div class="col-md-4">
                <div class="rating-breakdown p-4 h-100">
                    <h5 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Rating Breakdown</h5>
                    
                    @if($product->hasReviews())
                        <div class="text-center mb-4">
                            <div class="display-4 fw-bold text-warning">{{ number_format($product->average_rating, 1) }}</div>
                            <div class="star-rating fs-5 mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= round($product->average_rating) ? '' : 'opacity-25' }}"></i>
                                @endfor
                            </div>
                            <div class="text-muted">{{ $product->review_count }} total reviews</div>
                        </div>

                        <!-- Rating Distribution -->
                        @for($rating = 5; $rating >= 1; $rating--)
                            @php
                                $count = $reviews->where('rating', $rating)->count();
                                $percentage = $product->review_count > 0 ? ($count / $product->review_count) * 100 : 0;
                            @endphp
                            <div class="d-flex align-items-center mb-2">
                                <span class="me-2 text-nowrap">{{ $rating }} ★</span>
                                <div class="progress progress-custom flex-grow-1 me-2">
                                    <div class="progress-bar bg-warning" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="text-muted small">{{ $count }}</span>
                            </div>
                        @endfor
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-star fa-3x text-muted mb-3"></i>
                            <h6>No Reviews Yet</h6>
                            <p class="text-muted mb-0">Be the first to review this product!</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Review Filters -->
            <div class="col-md-8">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Reviews</h5>
                        
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Sort By</label>
                                <select name="sort" class="form-select" onchange="this.form.submit()">
                                    <option value="newest" {{ $sortBy === 'newest' ? 'selected' : '' }}>Newest First</option>
                                    <option value="helpful" {{ $sortBy === 'helpful' ? 'selected' : '' }}>Most Helpful</option>
                                    <option value="rating_high" {{ $sortBy === 'rating_high' ? 'selected' : '' }}>Highest Rating</option>
                                    <option value="rating_low" {{ $sortBy === 'rating_low' ? 'selected' : '' }}>Lowest Rating</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Rating</label>
                                <select name="rating" class="form-select" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    @for($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" {{ $filterRating == $i ? 'selected' : '' }}>{{ $i }} ★</option>
                                    @endfor
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Filter</label>
                                <div class="d-flex gap-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="verified" {{ $filterVerified ? 'checked' : '' }} onchange="this.form.submit()">
                                        <label class="form-check-label small">Verified</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="photos" {{ $withPhotos ? 'checked' : '' }} onchange="this.form.submit()">
                                        <label class="form-check-label small">With Photos</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Write a Review</label>
                                <div>
                                    @auth
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#writeReviewModal">
                                            <i class="fas fa-star me-2"></i>Write Review
                                        </button>
                                    @else
                                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                                            <i class="fas fa-sign-in-alt me-2"></i>Login to Review
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews List -->
        <div class="row">
            <div class="col-12">
                <h5 class="mb-3">
                    <i class="fas fa-comments me-2"></i>Customer Reviews 
                    <span class="badge bg-secondary">{{ $reviews->total() }}</span>
                </h5>
                
                @if($reviews->count() > 0)
                    @foreach($reviews as $review)
                        <div class="review-card card mb-3">
                            <div class="card-body">
                                <!-- Review Header -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 45px; height: 45px;">
                                                <span class="text-white fw-bold">{{ strtoupper(substr($review->user->name, 0, 1)) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $review->user->name }}</h6>
                                            <div class="star-rating mb-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star {{ $i <= $review->rating ? '' : 'opacity-25' }}"></i>
                                                @endfor
                                                <span class="ms-2 fw-bold">{{ $review->rating }}/5</span>
                                            </div>
                                            <div class="d-flex align-items-center text-muted small">
                                                <span>{{ $review->created_at->format('M d, Y') }}</span>
                                                @if($review->verified_purchase)
                                                    <span class="badge bg-success ms-2">
                                                        <i class="fas fa-check-circle"></i> Verified Purchase
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Review Title -->
                                @if($review->title)
                                    <h6 class="mb-2 text-primary">{{ $review->title }}</h6>
                                @endif

                                <!-- Review Comment -->
                                <p class="mb-3">{{ $review->comment }}</p>

                                <!-- Review Photos -->
                                @if($review->hasPhotos())
                                    <div class="mb-3">
                                        <div class="row g-2">
                                            @foreach($review->photos as $photo)
                                                <div class="col-auto">
                                                    <img src="{{ Storage::url($photo) }}" alt="Review Photo" 
                                                         class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;"
                                                         onclick="showImageModal('{{ Storage::url($photo) }}')">
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Would Recommend -->
                                @if($review->would_recommend !== null)
                                    <div class="mb-3">
                                        @if($review->would_recommend)
                                            <span class="badge bg-success fs-6">
                                                <i class="fas fa-thumbs-up me-1"></i>Recommends this product
                                            </span>
                                        @else
                                            <span class="badge bg-warning text-dark fs-6">
                                                <i class="fas fa-thumbs-down me-1"></i>Doesn't recommend
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                <!-- Review Helpfulness -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        @auth
                                            @if($review->user_id !== auth()->id())
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            onclick="voteHelpful({{ $review->id }}, true)">
                                                        <i class="fas fa-thumbs-up me-1"></i>
                                                        Helpful ({{ $review->helpful_count }})
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="voteHelpful({{ $review->id }}, false)">
                                                        <i class="fas fa-thumbs-down me-1"></i>
                                                        Not Helpful ({{ $review->not_helpful_count }})
                                                    </button>
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-muted">
                                                <i class="fas fa-thumbs-up text-success me-1"></i>
                                                {{ $review->helpful_count }} found this helpful
                                            </div>
                                        @endauth
                                    </div>
                                    
                                    @auth
                                        @if($review->user_id !== auth()->id())
                                            <button class="btn btn-sm btn-outline-secondary" onclick="reportReview({{ $review->id }})">
                                                <i class="fas fa-flag me-1"></i>Report
                                            </button>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $reviews->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-star fa-3x text-muted mb-3"></i>
                            <h5>No Reviews Yet</h5>
                            <p class="text-muted">Be the first to review {{ $product->name }}!</p>
                            @auth
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#writeReviewModal">
                                    <i class="fas fa-star me-2"></i>Write First Review
                                </button>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary">Login to Write Review</a>
                            @endauth
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Write Review Modal -->
    @auth
        @include('reviews.partials.write-modal', ['product' => $product])
    @endauth

    <!-- Image Preview Modal -->
    @include('reviews.partials.image-modal')

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
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
                showToast('success', data.message);
                location.reload();
            } else {
                showToast('error', data.message);
            }
        });
    }

    // Report review
    function reportReview(reviewId) {
        const reason = prompt('Please select reason for reporting:\n1. Spam\n2. Inappropriate content\n3. Fake review\n4. Offensive language\n5. Other\n\nEnter number (1-5):');
        
        if (!reason || reason < 1 || reason > 5) return;
        
        const reasons = ['', 'spam', 'inappropriate', 'fake', 'offensive', 'other'];
        const details = reason === '5' ? prompt('Please provide additional details:') : '';
        
        fetch(`/review/${reviewId}/report`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                reason: reasons[reason],
                details: details 
            })
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.success ? 'success' : 'error', data.message);
        });
    }

    // Show image in modal
    function showImageModal(imageUrl) {
        document.getElementById('imageModalImg').src = imageUrl;
        new bootstrap.Modal(document.getElementById('imageModal')).show();
    }

    // Toast notifications
    function showToast(type, message) {
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        const toastContainer = document.getElementById('toast-container') || createToastContainer();
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        const toast = new bootstrap.Toast(toastContainer.lastElementChild);
        toast.show();
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }
    </script>
</body>
</html>