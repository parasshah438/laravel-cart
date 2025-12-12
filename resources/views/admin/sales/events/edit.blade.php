@extends('layouts.admin')

@section('title', 'Edit Sale Event - ' . $saleEvent->name)

@section('content')
<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit text-warning me-2"></i>Edit Sale Event
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.sales.events.show', $saleEvent) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> View Event
            </a>
            <a href="{{ route('admin.sales.events.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Events
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Main Edit Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-fire"></i> Sale Event Information
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sales.events.update', $saleEvent) }}" method="POST" enctype="multipart/form-data" id="saleEventForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-bold">
                                        Event Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $saleEvent->name) }}" 
                                           required
                                           placeholder="e.g., Black Friday Mega Sale">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label fw-bold">
                                        Sale Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="">Select Sale Type</option>
                                        @foreach($saleTypes as $key => $label)
                                            <option value="{{ $key }}" 
                                                    {{ old('type', $saleEvent->type) == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label fw-bold">
                                        Status <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="draft" {{ old('status', $saleEvent->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="scheduled" {{ old('status', $saleEvent->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                        <option value="active" {{ old('status', $saleEvent->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="paused" {{ old('status', $saleEvent->status) == 'paused' ? 'selected' : '' }}>Paused</option>
                                        <option value="ended" {{ old('status', $saleEvent->status) == 'ended' ? 'selected' : '' }}>Ended</option>
                                        <option value="cancelled" {{ old('status', $saleEvent->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">
                                Description <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4" 
                                      required
                                      placeholder="Describe your sale event, highlight key benefits...">{{ old('description', $saleEvent->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="starts_at" class="form-label fw-bold">
                                        Start Date & Time <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local" 
                                           class="form-control @error('starts_at') is-invalid @enderror" 
                                           id="starts_at" 
                                           name="starts_at" 
                                           value="{{ old('starts_at', $saleEvent->starts_at?->format('Y-m-d\TH:i')) }}" 
                                           required>
                                    @error('starts_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ends_at" class="form-label fw-bold">
                                        End Date & Time <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local" 
                                           class="form-control @error('ends_at') is-invalid @enderror" 
                                           id="ends_at" 
                                           name="ends_at" 
                                           value="{{ old('ends_at', $saleEvent->ends_at?->format('Y-m-d\TH:i')) }}" 
                                           required>
                                    @error('ends_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_discount_percentage" class="form-label fw-bold">
                                        Maximum Discount Percentage
                                    </label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control @error('max_discount_percentage') is-invalid @enderror" 
                                               id="max_discount_percentage" 
                                               name="max_discount_percentage" 
                                               value="{{ old('max_discount_percentage', $saleEvent->max_discount_percentage) }}" 
                                               min="0" 
                                               max="100" 
                                               step="0.01"
                                               placeholder="50">
                                        <span class="input-group-text">%</span>
                                        @error('max_discount_percentage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted">Overall maximum discount limit for this sale</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="banner_image" class="form-label fw-bold">
                                        Banner Image
                                    </label>
                                    <input type="file" 
                                           class="form-control @error('banner_image') is-invalid @enderror" 
                                           id="banner_image" 
                                           name="banner_image" 
                                           accept="image/*">
                                    @error('banner_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($saleEvent->banner_image)
                                        <small class="form-text text-muted">
                                            Current: <a href="{{ asset($saleEvent->banner_image) }}" target="_blank">View current banner</a>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_featured" 
                                           name="is_featured" 
                                           value="1" 
                                           {{ old('is_featured', $saleEvent->is_featured) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="is_featured">
                                        <i class="fas fa-star text-warning"></i> Featured Event
                                    </label>
                                    <div class="form-text text-muted">Display prominently on homepage</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_public" 
                                           name="is_public" 
                                           value="1" 
                                           {{ old('is_public', $saleEvent->is_public) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="is_public">
                                        <i class="fas fa-globe text-info"></i> Public Event
                                    </label>
                                    <div class="form-text text-muted">Visible to all website visitors</div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn btn-outline-danger" onclick="deleteEvent()">
                                    <i class="fas fa-trash"></i> Delete Event
                                </button>
                            </div>
                            <div>
                                <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save"></i> Update Sale Event
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Current Banner Preview -->
            @if($saleEvent->banner_image)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-image"></i> Current Banner
                    </h6>
                </div>
                <div class="card-body text-center">
                    <img src="{{ asset($saleEvent->banner_image) }}" 
                         alt="{{ $saleEvent->name }}" 
                         class="img-fluid rounded"
                         style="max-height: 200px;">
                </div>
            </div>
            @endif

            <!-- Event Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line"></i> Event Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <div class="h4 mb-0 text-primary">{{ $saleEvent->saleProducts->count() }}</div>
                                <small class="text-muted">Products</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-0 text-success">{{ $saleEvent->total_orders ?? 0 }}</div>
                            <small class="text-muted">Orders</small>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="text-center">
                        <div class="h5 mb-0 text-info">${{ number_format($saleEvent->total_revenue ?? 0, 2) }}</div>
                        <small class="text-muted">Total Revenue</small>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-{{ $saleEvent->status === 'active' ? 'warning' : 'success' }}" 
                                onclick="toggleStatus()">
                            <i class="fas fa-{{ $saleEvent->status === 'active' ? 'pause' : 'play' }}"></i>
                            {{ $saleEvent->status === 'active' ? 'Pause Event' : 'Activate Event' }}
                        </button>
                        
                        <a href="{{ route('admin.sales.events.show', $saleEvent) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        
                        <a href="{{ route('admin.sales.analytics.event', $saleEvent) }}" class="btn btn-primary">
                            <i class="fas fa-chart-bar"></i> View Analytics
                        </a>
                    </div>
                </div>
            </div>

            <!-- Event Timeline -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clock"></i> Event Timeline
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Created</h6>
                                <p class="timeline-time">{{ $saleEvent->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        
                        @if($saleEvent->updated_at != $saleEvent->created_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Last Updated</h6>
                                <p class="timeline-time">{{ $saleEvent->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        @endif
                        
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ $saleEvent->starts_at->isFuture() ? 'warning' : 'success' }}"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Event Starts</h6>
                                <p class="timeline-time">{{ $saleEvent->starts_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ $saleEvent->ends_at->isFuture() ? 'secondary' : 'danger' }}"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Event Ends</h6>
                                <p class="timeline-time">{{ $saleEvent->ends_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this sale event? This action cannot be undone.</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    All associated products, analytics, and performance data will be permanently removed.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.sales.events.destroy', $saleEvent) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete Event
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 3px #dee2e6;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -29px;
    top: 12px;
    bottom: -20px;
    width: 2px;
    background-color: #dee2e6;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-title {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 2px;
}

.timeline-time {
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 0;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.border-right {
    border-right: 1px solid #e3e6f0 !important;
}

#banner_preview {
    max-width: 100%;
    max-height: 200px;
    margin-top: 10px;
    border-radius: 0.375rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('saleEventForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    });

    // Banner image preview
    const bannerInput = document.getElementById('banner_image');
    bannerInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                let preview = document.getElementById('banner_preview');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.id = 'banner_preview';
                    preview.className = 'img-fluid rounded mt-2';
                    preview.style.maxHeight = '200px';
                    bannerInput.parentNode.appendChild(preview);
                }
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Date validation
    const startDate = document.getElementById('starts_at');
    const endDate = document.getElementById('ends_at');
    
    function validateDates() {
        if (startDate.value && endDate.value) {
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            
            if (end <= start) {
                endDate.setCustomValidity('End date must be after start date');
            } else {
                endDate.setCustomValidity('');
            }
        }
    }
    
    startDate.addEventListener('change', validateDates);
    endDate.addEventListener('change', validateDates);
});

function toggleStatus() {
    if (confirm('Are you sure you want to toggle this event status?')) {
        fetch(`/admin/sales/events/{{ $saleEvent->id }}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error occurred'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the status.');
        });
    }
}

function deleteEvent() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush