@extends('layouts.admin')

@section('title', 'Create Sale Event')

@section('content')
<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-plus text-primary me-2"></i>Create New Sale Event
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.sales.events.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Events
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-fire"></i> Sale Event Details
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.sales.events.store') }}" method="POST" enctype="multipart/form-data" id="saleEventForm">
                @csrf

                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="name" class="form-label required">Event Name</label>
                                            <input type="text" 
                                                   class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" 
                                                   name="name" 
                                                   value="{{ old('name') }}" 
                                                   placeholder="e.g., Black Friday Flash Sale"
                                                   required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sale_type" class="form-label required">Sale Type</label>
                                            <select class="form-control @error('sale_type') is-invalid @enderror" 
                                                    id="sale_type" 
                                                    name="sale_type" 
                                                    required>
                                                <option value="">Select Sale Type</option>
                                                @foreach($saleTypes as $key => $label)
                                                    <option value="{{ $key }}" {{ old('sale_type') == $key ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('sale_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="priority" class="form-label required">Priority (1-10)</label>
                                            <input type="number" 
                                                   class="form-control @error('priority') is-invalid @enderror" 
                                                   id="priority" 
                                                   name="priority" 
                                                   value="{{ old('priority', 5) }}" 
                                                   min="1" 
                                                   max="10"
                                                   required>
                                            @error('priority')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="description" class="form-label required">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="4" 
                                              placeholder="Describe your sale event..."
                                              required>{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="banner_image" class="form-label">Banner Image</label>
                                    <input type="file" 
                                           class="form-control @error('banner_image') is-invalid @enderror" 
                                           id="banner_image" 
                                           name="banner_image" 
                                           accept="image/jpeg,image/png,image/jpg,image/gif">
                                    <small class="form-text text-muted">
                                        Upload a banner image for this sale event. Max size: 2MB. Recommended size: 1200x400px.
                                    </small>
                                    @error('banner_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Timing & Limits -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Timing & Limits</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="start_time" class="form-label required">Start Time</label>
                                            <input type="datetime-local" 
                                                   class="form-control @error('start_time') is-invalid @enderror" 
                                                   id="start_time" 
                                                   name="start_time" 
                                                   value="{{ old('start_time') }}"
                                                   required>
                                            @error('start_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="end_time" class="form-label required">End Time</label>
                                            <input type="datetime-local" 
                                                   class="form-control @error('end_time') is-invalid @enderror" 
                                                   id="end_time" 
                                                   name="end_time" 
                                                   value="{{ old('end_time') }}"
                                                   required>
                                            @error('end_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="max_discount_percentage" class="form-label">Max Discount (%)</label>
                                            <input type="number" 
                                                   class="form-control @error('max_discount_percentage') is-invalid @enderror" 
                                                   id="max_discount_percentage" 
                                                   name="max_discount_percentage" 
                                                   value="{{ old('max_discount_percentage') }}" 
                                                   min="0" 
                                                   max="100"
                                                   step="0.01">
                                            @error('max_discount_percentage')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="min_order_amount" class="form-label">Min Order Amount ($)</label>
                                            <input type="number" 
                                                   class="form-control @error('min_order_amount') is-invalid @enderror" 
                                                   id="min_order_amount" 
                                                   name="min_order_amount" 
                                                   value="{{ old('min_order_amount') }}" 
                                                   min="0"
                                                   step="0.01">
                                            @error('min_order_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="max_products_per_user" class="form-label">Max Products Per User</label>
                                            <input type="number" 
                                                   class="form-control @error('max_products_per_user') is-invalid @enderror" 
                                                   id="max_products_per_user" 
                                                   name="max_products_per_user" 
                                                   value="{{ old('max_products_per_user') }}" 
                                                   min="1">
                                            @error('max_products_per_user')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings Sidebar -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1" 
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        <strong>Active</strong>
                                        <br><small class="text-muted">Enable this sale event immediately</small>
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_featured" 
                                           name="is_featured" 
                                           value="1" 
                                           {{ old('is_featured') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_featured">
                                        <strong>Featured</strong>
                                        <br><small class="text-muted">Show this event prominently on website</small>
                                    </label>
                                </div>

                                <hr>

                                <div class="alert alert-info">
                                    <h6><i class="fas fa-lightbulb"></i> Pro Tips:</h6>
                                    <ul class="mb-0">
                                        <li>Higher priority events show first</li>
                                        <li>Featured events get special promotion</li>
                                        <li>Set realistic time limits to create urgency</li>
                                        <li>Use clear, compelling event names</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats Preview -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Preview</h6>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-fire fa-3x text-danger"></i>
                                    </div>
                                    <h6 id="preview_name">Your Sale Event</h6>
                                    <p class="text-muted" id="preview_type">Sale Type</p>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="text-primary font-weight-bold" id="preview_priority">5</div>
                                            <small>Priority</small>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-success font-weight-bold" id="preview_discount">0%</div>
                                            <small>Max Discount</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <button type="submit" class="btn btn-primary btn-lg me-3">
                                    <i class="fas fa-fire"></i> Create Sale Event
                                </button>
                                <a href="{{ route('admin.sales.events.index') }}" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live preview updates
    const nameInput = document.getElementById('name');
    const typeSelect = document.getElementById('sale_type');
    const priorityInput = document.getElementById('priority');
    const discountInput = document.getElementById('max_discount_percentage');

    function updatePreview() {
        document.getElementById('preview_name').textContent = nameInput.value || 'Your Sale Event';
        document.getElementById('preview_type').textContent = typeSelect.options[typeSelect.selectedIndex].text;
        document.getElementById('preview_priority').textContent = priorityInput.value || '5';
        document.getElementById('preview_discount').textContent = (discountInput.value || '0') + '%';
    }

    nameInput.addEventListener('input', updatePreview);
    typeSelect.addEventListener('change', updatePreview);
    priorityInput.addEventListener('input', updatePreview);
    discountInput.addEventListener('input', updatePreview);

    // Form validation
    document.getElementById('saleEventForm').addEventListener('submit', function(e) {
        const startTime = new Date(document.getElementById('start_time').value);
        const endTime = new Date(document.getElementById('end_time').value);
        const now = new Date();

        if (startTime <= now) {
            alert('Start time must be in the future.');
            e.preventDefault();
            return false;
        }

        if (endTime <= startTime) {
            alert('End time must be after start time.');
            e.preventDefault();
            return false;
        }

        return true;
    });

    // Set minimum datetime to current time
    const now = new Date();
    const minDateTime = now.getFullYear() + '-' + 
                       String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                       String(now.getDate()).padStart(2, '0') + 'T' + 
                       String(now.getHours()).padStart(2, '0') + ':' + 
                       String(now.getMinutes()).padStart(2, '0');
    
    document.getElementById('start_time').setAttribute('min', minDateTime);
    document.getElementById('end_time').setAttribute('min', minDateTime);
});
</script>

<style>
.required::after {
    content: ' *';
    color: red;
}

.form-group {
    margin-bottom: 1rem;
}

.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}
</style>
@endpush

@endsection