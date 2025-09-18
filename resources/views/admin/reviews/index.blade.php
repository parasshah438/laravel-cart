@extends('layouts.admin')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Review Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Review Management</h1>
                    <p class="text-muted">Amazon-style post-moderation system</p>
                </div>
                <div class="d-flex gap-2">
                    <div class="badge bg-success fs-6">
                        {{ $stats['approved'] }} Approved
                    </div>
                    <div class="badge bg-warning fs-6">
                        {{ $stats['flagged'] }} Flagged
                    </div>
                    <div class="badge bg-danger fs-6">
                        {{ $stats['rejected'] }} Rejected
                    </div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <ul class="nav nav-tabs mb-4" id="reviewTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="flagged-tab" data-bs-toggle="tab" data-bs-target="#flagged" type="button" role="tab">
                        <i class="fas fa-flag text-warning me-2"></i>Flagged Reviews 
                        <span class="badge bg-warning ms-2">{{ $stats['flagged'] }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="recent-tab" data-bs-toggle="tab" data-bs-target="#recent" type="button" role="tab">
                        <i class="fas fa-clock me-2"></i>Recent Reviews
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="high-trust-tab" data-bs-toggle="tab" data-bs-target="#high-trust" type="button" role="tab">
                        <i class="fas fa-star me-2"></i>Top Reviews
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="reviewTabsContent">
                <!-- Flagged Reviews -->
                <div class="tab-pane fade show active" id="flagged" role="tabpanel">
                    @if($flaggedReviews->count() > 0)
                        @foreach($flaggedReviews as $review)
                            <div class="card mb-3 border-warning">
                                <div class="card-header bg-warning bg-opacity-25">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">
                                                <i class="fas fa-flag text-warning me-2"></i>
                                                Review for: <a href="{{ route('products.show', $review->product->slug) }}" target="_blank">{{ $review->product->name }}</a>
                                            </h6>
                                            <small class="text-muted">{{ $review->report_count }} reports • {{ $review->created_at->diffForHumans() }}</small>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-success" onclick="approveReview({{ $review->id }})">
                                                <i class="fas fa-check"></i> Keep Review
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="rejectReview({{ $review->id }})">
                                                <i class="fas fa-times"></i> Remove Review
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="me-3">
                                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <span class="text-white fw-bold">{{ strtoupper(substr($review->user->name, 0, 1)) }}</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">{{ $review->user->name }}</h6>
                                                    <div class="d-flex align-items-center">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }} small"></i>
                                                        @endfor
                                                        <span class="ms-2 small">{{ $review->rating }}/5</span>
                                                        @if($review->verified_purchase)
                                                            <span class="badge bg-success ms-2 small">Verified</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            @if($review->title)
                                                <h6 class="text-primary mb-2">{{ $review->title }}</h6>
                                            @endif
                                            
                                            <p class="mb-2">{{ $review->comment }}</p>
                                            
                                            @if($review->hasPhotos())
                                                <div class="mb-2">
                                                    <small class="text-muted d-block">Review Photos:</small>
                                                    <div class="d-flex gap-2">
                                                        @foreach($review->photos as $photo)
                                                            <img src="{{ asset('storage/' . $photo) }}" alt="Review Photo" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="text-danger">Report Details:</h6>
                                            @if($review->admin_notes)
                                                <div class="small text-muted" style="max-height: 150px; overflow-y: auto;">
                                                    {!! nl2br(e($review->admin_notes)) !!}
                                                </div>
                                            @else
                                                <p class="text-muted small">No specific report details available.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                            <h5>No Flagged Reviews</h5>
                            <p class="text-muted">All reviews are currently clean and approved.</p>
                        </div>
                    @endif
                </div>

                <!-- Recent Reviews -->
                <div class="tab-pane fade" id="recent" role="tabpanel">
                    <div class="row">
                        @foreach($recentReviews as $review)
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">
                                                <a href="{{ route('products.show', $review->product->slug) }}" target="_blank">{{ Str::limit($review->product->name, 30) }}</a>
                                            </h6>
                                            <div class="d-flex align-items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }} small"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        <p class="card-text small">{{ Str::limit($review->comment, 100) }}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">{{ $review->user->name }}</small>
                                            <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- High Trust Reviews -->
                <div class="tab-pane fade" id="high-trust" role="tabpanel">
                    <div class="row">
                        @foreach($topReviews as $review)
                            <div class="col-md-6 mb-3">
                                <div class="card border-warning">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-crown text-warning me-1"></i>
                                                <a href="{{ route('products.show', $review->product->slug) }}" target="_blank">{{ Str::limit($review->product->name, 30) }}</a>
                                            </h6>
                                            <div class="d-flex align-items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }} small"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        <p class="card-text small">{{ Str::limit($review->comment, 100) }}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted">{{ $review->user->name }}</small>
                                                @if($review->verified_purchase)
                                                    <span class="badge bg-success ms-1" style="font-size: 0.7em;">Verified</span>
                                                @endif
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-thumbs-up text-primary me-1"></i>
                                                <small class="text-muted">{{ $review->helpful_count }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
function approveReview(reviewId) {
    if (confirm('Keep this review? It will remain published and visible to customers.')) {
        fetch(`/admin/reviews/${reviewId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function rejectReview(reviewId) {
    if (confirm('Remove this review? This action cannot be undone.')) {
        fetch(`/admin/reviews/${reviewId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}
</script>
@endsection