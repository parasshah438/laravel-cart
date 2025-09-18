@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- ================================================================================================ -->
    <!-- 📝 PRODUCT REVIEWS PAGE - Bootstrap 5 Professional Design -->
    <!-- ================================================================================================ -->
    
    <!-- Product Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('front.index') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('product.show', $product->slug) }}">{{ $product->name }}</a></li>
                    <li class="breadcrumb-item active">Reviews</li>
                </ol>
            </nav>
            
            <div class="d-flex align-items-center mb-3">
                <img src="{{ $product->image_url ?? '/images/placeholder.jpg' }}" alt="{{ $product->name }}" 
                     class="img-thumbnail me-3" style="width: 80px; height: 80px; object-fit: cover;">
                <div>
                    <h1 class="h3 mb-1">{{ $product->name }}</h1>
                    <p class="text-muted mb-0">Reviews & Ratings</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 bg-light">
                <div class="card-body text-center">
                    <div class="display-4 fw-bold text-warning mb-2">{{ $stats['average_rating'] ?: 'N/A' }}</div>
                    <div class="mb-2">
                        @if($stats['average_rating'])
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= floor($stats['average_rating']))
                                    <i class="fas fa-star text-warning"></i>
                                @elseif($i <= ceil($stats['average_rating']))
                                    <i class="fas fa-star-half-alt text-warning"></i>
                                @else
                                    <i class="far fa-star text-warning"></i>
                                @endif
                            @endfor
                        @else
                            <span class="text-muted">No ratings yet</span>
                        @endif
                    </div>
                    <div class="text-muted">{{ $stats['total_reviews'] }} Reviews</div>
                    @if($stats['verified_count'] > 0)
                        <small class="text-success">
                            <i class="fas fa-check-circle"></i> {{ $stats['verified_count'] }} Verified Purchases
                        </small>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card border-0">
                <div class="card-body">
                    <h6 class="card-title mb-3">Rating Breakdown</h6>
                    @for($rating = 5; $rating >= 1; $rating--)
                        <div class="d-flex align-items-center mb-2">
                            <span class="me-2" style="width: 60px;">{{ $rating }} star</span>
                            <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                <div class="progress-bar bg-warning" 
                                     style="width: {{ $stats['rating_breakdown'][$rating]['percentage'] ?? 0 }}%"></div>
                            </div>
                            <span class="text-muted" style="width: 40px;">{{ $stats['rating_breakdown'][$rating]['count'] ?? 0 }}</span>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Sorting -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Sort By</label>
                            <select name="sort" class="form-select" onchange="this.form.submit()">
                                <option value="newest" {{ $sortBy === 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="oldest" {{ $sortBy === 'oldest' ? 'selected' : '' }}>Oldest First</option>
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
                        
                        <div class="col-md-3">
                            <label class="form-label">Filters</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="verified" value="1" 
                                       {{ $filterVerified ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label">Verified Purchases Only</label>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="photos" value="1" 
                                       {{ $withPhotos ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label">With Photos</label>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Write Review Button (for authenticated users) -->
    @auth
        @if(!$product->reviews()->where('user_id', auth()->id())->exists())
            <div class="mb-4">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#writeReviewModal">
                    <i class="fas fa-star me-2"></i>Write a Review
                </button>
            </div>
        @else
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                You have already reviewed this product. 
                <a href="#your-review" class="alert-link">View your review</a>
            </div>
        @endif
    @else
        <div class="mb-4">
            <a href="{{ route('login') }}" class="btn btn-outline-primary">
                <i class="fas fa-sign-in-alt me-2"></i>Login to Write a Review
            </a>
        </div>
    @endauth

    <!-- Reviews List -->
    <div class="row">
        <div class="col-12">
            @if($reviews->count() > 0)
                @foreach($reviews as $review)
                    <div class="card mb-4 {{ auth()->check() && $review->user_id === auth()->id() ? 'border-primary' : '' }}" 
                         {{ auth()->check() && $review->user_id === auth()->id() ? 'id=your-review' : '' }}>
                        <div class="card-body">
                            <!-- Review Header -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <span class="text-white fw-bold">{{ strtoupper(substr($review->user->name, 0, 1)) }}</span>
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
                                            @if($review->product_variant)
                                                <span class="ms-2">• {{ $review->product_variant }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Review Actions (for review owner) -->
                                @auth
                                    @if($review->user_id === auth()->id())
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="editReview({{ $review->id }})">
                                                    <i class="fas fa-edit me-2"></i>Edit Review
                                                </a></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteReview({{ $review->id }})">
                                                    <i class="fas fa-trash me-2"></i>Delete Review
                                                </a></li>
                                            </ul>
                                        </div>
                                    @endif
                                @endauth
                            </div>

                            <!-- Review Title -->
                            @if($review->title)
                                <h6 class="mb-2">{{ $review->title }}</h6>
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
                                        <span class="badge bg-success">
                                            <i class="fas fa-thumbs-up me-1"></i>Recommends this product
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
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
                        <h5>No Reviews Yet</h5>
                        <p class="text-muted">Be the first to review this product!</p>
                        @auth
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#writeReviewModal">
                                Write the First Review
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary">Login to Write a Review</a>
                        @endauth
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@auth
<!-- Write Review Modal -->
@include('reviews.partials.write-review-modal')
@endauth

<!-- Image Preview Modal -->
@include('reviews.partials.image-modal')

@endsection

@push('scripts')
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

// Edit review
function editReview(reviewId) {
    // Implementation for edit review modal
    alert('Edit review functionality will be implemented');
}

// Delete review
function deleteReview(reviewId) {
    if (confirm('Are you sure you want to delete this review? This action cannot be undone.')) {
        fetch(`/review/${reviewId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Review deleted successfully');
                location.reload();
            } else {
                showToast('error', 'Failed to delete review');
            }
        });
    }
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
@endpush