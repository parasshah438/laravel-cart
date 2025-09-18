<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews - Fashion Store</title>
    
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
    </style>
</head>
<body>
    <!-- Simple Header for Reviews Page -->
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
                            <li class="breadcrumb-item active">Reviews</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </nav>
    </header>
<div class="container py-4">
    <!-- ================================================================================================ -->
    <!-- 📝 ALL REVIEWS PAGE - Bootstrap 5 Professional Design -->
    <!-- ================================================================================================ -->
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('front.index') }}">Home</a></li>
                    <li class="breadcrumb-item active">All Reviews</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">Customer Reviews</h1>
                    <p class="text-muted mb-0">Read what our customers say about our products</p>
                </div>
                <div class="d-flex align-items-center">
                    <i class="fas fa-star text-warning fa-2x me-2"></i>
                    <div>
                        <div class="h4 mb-0">{{ $reviews->count() > 0 ? number_format($reviews->avg('rating'), 1) : 'N/A' }}</div>
                        <small class="text-muted">Average Rating</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Sorting -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
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
                                <option value="">All Ratings</option>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}" {{ $filterRating == $i ? 'selected' : '' }}>{{ $i }} Stars</option>
                                @endfor
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Search Reviews</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="q" 
                                       value="{{ request('q') }}" placeholder="Search reviews...">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <a href="{{ route('reviews.all') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-refresh me-2"></i>Clear Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="row">
        <div class="col-12">
            @if($reviews->count() > 0)
                @foreach($reviews as $review)
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <!-- Review Header -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <span class="text-white fw-bold fs-5">{{ strtoupper(substr($review->user->name, 0, 1)) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ $review->user->name }}</h6>
                                        <div class="mb-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
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
                                
                                <!-- Product Link -->
                                <div class="text-end">
                                    <a href="{{ route('product.show', $review->product->slug) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt me-1"></i>View Product
                                    </a>
                                </div>
                            </div>

                            <!-- Product Info -->
                            <div class="mb-3 p-3 bg-light rounded">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $review->product->image_url ?? '/images/placeholder.jpg' }}" 
                                         alt="{{ $review->product->name }}" 
                                         class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-1">{{ $review->product->name }}</h6>
                                        @if($review->product_variant)
                                            <small class="text-muted">{{ $review->product_variant }}</small>
                                        @endif
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
                                        <div class="text-muted small">
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
                {{ $reviews->appends(request()->query())->links() }}
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-star fa-3x text-muted mb-3"></i>
                        <h5>No Reviews Found</h5>
                        <p class="text-muted">No reviews match your current filters.</p>
                        <a href="{{ route('reviews.all') }}" class="btn btn-primary">View All Reviews</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
@include('reviews.partials.image-modal')

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ================================================================================================
// 📝 REVIEW INTERACTION SCRIPTS
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
            showToast('success', data.message);
            location.reload(); // Reload to update counts
        } else {
            showToast('error', data.message);
        }
    })
    .catch(error => {
        showToast('error', 'An error occurred. Please try again.');
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
        if (data.success) {
            showToast('success', data.message);
        } else {
            showToast('error', data.message);
        }
    });
}

// Show image in modal
function showImageModal(imageUrl) {
    document.getElementById('imageModalImg').src = imageUrl;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}

// Toast notification helper
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