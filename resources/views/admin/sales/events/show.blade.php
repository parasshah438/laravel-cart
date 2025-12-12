@extends('layouts.admin')

@section('title', 'Sale Event Details - ' . $saleEvent->name)

@section('content')
<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-fire text-danger me-2"></i>{{ $saleEvent->name }}
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.sales.events.edit', $saleEvent) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Event
            </a>
            <a href="{{ route('admin.sales.events.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Events
            </a>
        </div>
    </div>

    <!-- Event Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_products'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_orders']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($stats['total_revenue'], 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Conversion Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['conversion_rate'], 1) }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Event Details -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Event Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Event Name</label>
                                <p class="form-control-static">{{ $saleEvent->name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Type</label>
                                <p class="form-control-static">
                                    <span class="badge bg-info">{{ ucwords(str_replace('_', ' ', $saleEvent->type)) }}</span>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Status</label>
                                <p class="form-control-static">
                                    <span class="badge bg-{{ $saleEvent->status === 'active' ? 'success' : ($saleEvent->status === 'scheduled' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($saleEvent->status) }}
                                    </span>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Featured Event</label>
                                <p class="form-control-static">
                                    <span class="badge bg-{{ $saleEvent->is_featured ? 'success' : 'secondary' }}">
                                        {{ $saleEvent->is_featured ? 'Yes' : 'No' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Start Date</label>
                                <p class="form-control-static">{{ $saleEvent->starts_at->format('M d, Y H:i') }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">End Date</label>
                                <p class="form-control-static">{{ $saleEvent->ends_at->format('M d, Y H:i') }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Max Discount</label>
                                <p class="form-control-static">
                                    @if($saleEvent->max_discount_percentage)
                                        <span class="badge bg-success">{{ $saleEvent->max_discount_percentage }}% OFF</span>
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Public Event</label>
                                <p class="form-control-static">
                                    <span class="badge bg-{{ $saleEvent->is_public ? 'success' : 'warning' }}">
                                        {{ $saleEvent->is_public ? 'Public' : 'Private' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    @if($saleEvent->description)
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Description</label>
                        <div class="card bg-light">
                            <div class="card-body">
                                {!! nl2br(e($saleEvent->description)) !!}
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($saleEvent->banner_image)
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Banner Image</label>
                        <div class="mt-2">
                            <img src="{{ asset($saleEvent->banner_image) }}" 
                                 alt="{{ $saleEvent->name }}" 
                                 class="img-thumbnail" 
                                 style="max-height: 200px;">
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Products in Sale -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-box"></i> Products in Sale ({{ $saleEvent->saleProducts->count() }})
                    </h6>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="fas fa-plus"></i> Add Products
                    </button>
                </div>
                <div class="card-body">
                    @if($saleEvent->saleProducts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Original Price</th>
                                    <th>Sale Price</th>
                                    <th>Discount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($saleEvent->saleProducts as $saleProduct)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($saleProduct->product->image)
                                                <img src="{{ asset($saleProduct->product->image) }}" 
                                                     alt="{{ $saleProduct->product->name }}" 
                                                     class="rounded me-2" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @endif
                                            <div>
                                                <strong>{{ $saleProduct->product->name }}</strong>
                                                <br>
                                                <small class="text-muted">SKU: {{ $saleProduct->product->sku ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>${{ number_format($saleProduct->original_price, 2) }}</td>
                                    <td>
                                        <span class="text-success fw-bold">
                                            ${{ number_format($saleProduct->sale_price, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">
                                            {{ number_format($saleProduct->discount_percentage, 1) }}% OFF
                                        </span>
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.sales.events.remove-product', [$saleEvent, $saleProduct->product]) }}" 
                                              method="POST" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Remove this product from the sale?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i> Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-box fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No products added to this sale yet.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="fas fa-plus"></i> Add First Product
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Performance & Analytics -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line"></i> Performance Metrics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Average Discount</span>
                            <span class="fw-bold">{{ number_format($stats['average_discount'], 1) }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" 
                                 style="width: {{ min($stats['average_discount'], 100) }}%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Conversion Rate</span>
                            <span class="fw-bold">{{ number_format($stats['conversion_rate'], 1) }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" 
                                 style="width: {{ min($stats['conversion_rate'], 100) }}%"></div>
                        </div>
                    </div>

                    @php
                        $totalDays = $saleEvent->starts_at->diffInDays($saleEvent->ends_at) ?: 1;
                        $dailyRevenue = $stats['total_revenue'] / $totalDays;
                        $totalHours = $saleEvent->starts_at->diffInHours($saleEvent->ends_at) ?: 1;
                        $hourlyRevenue = $stats['total_revenue'] / $totalHours;
                    @endphp

                    <hr class="my-3">
                    
                    <div class="text-center">
                        <div class="row">
                            <div class="col-6">
                                <div class="border-right">
                                    <div class="h5 mb-0 text-success">${{ number_format($dailyRevenue, 0) }}</div>
                                    <small class="text-muted">Daily Avg</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="h5 mb-0 text-info">${{ number_format($hourlyRevenue, 0) }}</div>
                                <small class="text-muted">Hourly Avg</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tools"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-{{ $saleEvent->status === 'active' ? 'warning' : 'success' }}" 
                                onclick="toggleStatus({{ $saleEvent->id }})">
                            <i class="fas fa-{{ $saleEvent->status === 'active' ? 'pause' : 'play' }}"></i>
                            {{ $saleEvent->status === 'active' ? 'Pause Event' : 'Activate Event' }}
                        </button>
                        
                        <a href="{{ route('admin.sales.analytics.event', $saleEvent) }}" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> View Analytics
                        </a>
                        
                        <a href="{{ route('admin.sales.events.edit', $saleEvent) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Event
                        </a>
                        
                        <button class="btn btn-outline-danger" 
                                onclick="deleteEvent({{ $saleEvent->id }})">
                            <i class="fas fa-trash"></i> Delete Event
                        </button>
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
                                <h6 class="timeline-title">Event Created</h6>
                                <p class="timeline-time">{{ $saleEvent->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        
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

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Products to Sale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Search Filters -->
                    <div class="col-12">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="product_search" class="form-label">Search Products</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="product_search" 
                                                   placeholder="Search by name, slug, description..."
                                                   onkeyup="searchProducts()"
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="category_filter" class="form-label">Category</label>
                                            <select class="form-select" id="category_filter" onchange="searchProducts()">
                                                <option value="">All Categories</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="status_filter" class="form-label">Status</label>
                                            <select class="form-select" id="status_filter" onchange="searchProducts()">
                                                <option value="1">Active Only</option>
                                                <option value="">All Status</option>
                                                <option value="0">Inactive</option>
                                                <option value="active">Active (String)</option>
                                                <option value="inactive">Inactive (String)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products List -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Available Products</h6>
                                <div>
                                    <span class="badge bg-primary" id="selected_count">0 Selected</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="selectAll()">
                                        Select All
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                                        Clear All
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Loading Spinner -->
                                <div id="loading_spinner" class="text-center py-4" style="display: none;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Searching products...</p>
                                </div>

                                <!-- Products Table -->
                                <div class="table-responsive" id="products_container">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th width="50">
                                                    <input type="checkbox" id="select_all_checkbox" onchange="toggleSelectAll()">
                                                </th>
                                                <th>Product</th>
                                                <th>SKU</th>
                                                <th>Category</th>
                                                <th>Current Price</th>
                                                <th>Sale Price</th>
                                                <th>Max Qty/User</th>
                                            </tr>
                                        </thead>
                                        <tbody id="products_list">
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <i class="fas fa-search fa-2x text-muted mb-2"></i>
                                                    <p class="text-muted">Enter search terms to find products</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div id="pagination_container" class="d-flex justify-content-center mt-3">
                                    <!-- Pagination will be inserted here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="add_selected_btn" onclick="addSelectedProducts()" disabled>
                    <i class="fas fa-plus"></i> Add Selected Products (<span id="selected_count_btn">0</span>)
                </button>
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
                <form id="deleteForm" method="POST" style="display: inline;">
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

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}
</style>
@endpush

@push('scripts')
<script>
let selectedProducts = [];
let allProducts = [];
let currentPage = 1;
let searchTimeout = null;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize when modal opens
    document.getElementById('addProductModal').addEventListener('shown.bs.modal', function () {
        console.log('Modal opened, triggering search...');
        searchProducts();
    });
});

function toggleStatus(id) {
    if (confirm('Are you sure you want to toggle this event status?')) {
        fetch(`/admin/sales/events/${id}/toggle-status`, {
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

function deleteEvent(id) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `/admin/sales/events/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Product Search Function
function searchProducts(page = 1) {
    console.log('searchProducts called with page:', page);
    clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(() => {
        const searchInput = document.getElementById('product_search');
        const categoryInput = document.getElementById('category_filter');
        const statusInput = document.getElementById('status_filter');
        
        if (!searchInput || !categoryInput || !statusInput) {
            console.error('Required input elements not found!');
            return;
        }
        
        const search = searchInput.value;
        const category = categoryInput.value;
        const status = statusInput.value;
        
        console.log('Search params:', { search, category, status });
        
        // Show loading
        document.getElementById('loading_spinner').style.display = 'block';
        document.getElementById('products_container').style.display = 'none';
        
        // Build query parameters
        const params = new URLSearchParams({
            search: search,
            category_id: category,
            status: status,
            page: page,
            per_page: 10
        });
        
        const url = `/admin/sales/events/{{ $saleEvent->id }}/ajax/products?${params}`;
        console.log('Fetching URL:', url);
        
        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            displayProducts(data.data || data);
            if (data.links) {
                displayPagination(data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            displayError('Failed to load products. Please try again. Error: ' + error.message);
        })
        .finally(() => {
            document.getElementById('loading_spinner').style.display = 'none';
            document.getElementById('products_container').style.display = 'block';
        });
    }, 300);
}

// Display Error Message
function displayError(message) {
    const tbody = document.getElementById('products_list');
    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-4">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                <p class="text-danger">${message}</p>
                <button class="btn btn-sm btn-outline-primary" onclick="searchProducts()">
                    <i class="fas fa-refresh"></i> Retry
                </button>
            </td>
        </tr>
    `;
}

// Display Products in Table
function displayProducts(products) {
    const tbody = document.getElementById('products_list');
    allProducts = products;
    
    if (!products || products.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <i class="fas fa-box fa-2x text-muted mb-2"></i>
                    <p class="text-muted">No products found matching your criteria</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = products.map(product => `
        <tr id="product_row_${product.id}">
            <td>
                <input type="checkbox" 
                       class="product-checkbox" 
                       value="${product.id}"
                       onchange="toggleProductSelection(${product.id})"
                       ${selectedProducts.find(p => p.id == product.id) ? 'checked' : ''}>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    ${product.image ? 
                        `<img src="${product.image}" alt="${product.name}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">` 
                        : 
                        `<div class="bg-secondary rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-box text-white"></i></div>`
                    }
                    <div>
                        <strong>${product.name}</strong>
                        ${product.description ? `<br><small class="text-muted">${product.description.substring(0, 50)}...</small>` : ''}
                    </div>
                </div>
            </td>
            <td>
                <code class="text-muted">${product.sku || 'N/A'}</code>
            </td>
            <td>
                <span class="badge bg-info">${product.category?.name || 'Uncategorized'}</span>
            </td>
            <td>
                <strong>$${parseFloat(product.price || 0).toFixed(2)}</strong>
            </td>
            <td>
                <div class="input-group input-group-sm" style="width: 120px;">
                    <span class="input-group-text">$</span>
                    <input type="number" 
                           class="form-control sale-price-input" 
                           id="sale_price_${product.id}"
                           value="${(parseFloat(product.price || 0) * 0.8).toFixed(2)}"
                           min="0"
                           step="0.01"
                           placeholder="0.00">
                </div>
                <small class="text-success">
                    <span id="discount_${product.id}">20%</span> OFF
                </small>
            </td>
            <td>
                <input type="number" 
                       class="form-control form-control-sm max-qty-input" 
                       id="max_qty_${product.id}"
                       value="5"
                       min="1"
                       placeholder="No limit"
                       style="width: 80px;">
            </td>
        </tr>
    `).join('');
    
    // Add event listeners for price calculations
    products.forEach(product => {
        const salePriceInput = document.getElementById(`sale_price_${product.id}`);
        if (salePriceInput) {
            salePriceInput.addEventListener('input', function() {
                calculateDiscount(product.id, product.price);
            });
        }
    });
    
    updateSelectedCount();
}

// Calculate Discount Percentage
function calculateDiscount(productId, originalPrice) {
    const salePrice = parseFloat(document.getElementById(`sale_price_${productId}`).value) || 0;
    const discount = ((originalPrice - salePrice) / originalPrice * 100).toFixed(0);
    document.getElementById(`discount_${productId}`).textContent = `${discount}%`;
}

// Toggle Product Selection
function toggleProductSelection(productId) {
    const checkbox = document.querySelector(`input[value="${productId}"]`);
    const product = allProducts.find(p => p.id == productId);
    
    if (checkbox.checked) {
        if (!selectedProducts.find(p => p.id == productId)) {
            selectedProducts.push(product);
        }
    } else {
        selectedProducts = selectedProducts.filter(p => p.id != productId);
    }
    
    updateSelectedCount();
}

// Select All Products
function selectAll() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
        toggleProductSelection(checkbox.value);
    });
}

// Clear Selection
function clearSelection() {
    selectedProducts = [];
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('select_all_checkbox').checked = false;
    updateSelectedCount();
}

// Toggle Select All
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('select_all_checkbox');
    if (selectAllCheckbox.checked) {
        selectAll();
    } else {
        clearSelection();
    }
}

// Update Selected Count
function updateSelectedCount() {
    const count = selectedProducts.length;
    document.getElementById('selected_count').textContent = `${count} Selected`;
    document.getElementById('selected_count_btn').textContent = count;
    document.getElementById('add_selected_btn').disabled = count === 0;
    
    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.product-checkbox');
    const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
    const selectAllCheckbox = document.getElementById('select_all_checkbox');
    
    if (checkedBoxes.length === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (checkedBoxes.length === allCheckboxes.length) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else {
        selectAllCheckbox.indeterminate = true;
    }
}

// Add Selected Products to Sale
function addSelectedProducts() {
    if (selectedProducts.length === 0) {
        alert('Please select at least one product to add to the sale.');
        return;
    }
    
    const productsData = selectedProducts.map(product => {
        const salePrice = parseFloat(document.getElementById(`sale_price_${product.id}`).value) || 0;
        const maxQty = parseInt(document.getElementById(`max_qty_${product.id}`).value) || null;
        
        return {
            product_id: product.id,
            sale_price: salePrice,
            max_quantity_per_user: maxQty
        };
    });
    
    // Validate sale prices
    const invalidPrices = productsData.filter(p => p.sale_price <= 0);
    if (invalidPrices.length > 0) {
        alert('Please enter valid sale prices for all selected products.');
        return;
    }
    
    // Show loading state
    const addButton = document.getElementById('add_selected_btn');
    const originalText = addButton.innerHTML;
    addButton.disabled = true;
    addButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Products...';
    
    // Send AJAX request
    console.log('Sending products data:', productsData);
    
    fetch(`/admin/sales/events/{{ $saleEvent->id }}/products`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            products: productsData
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.message) {
            alert(data.message);
            // Close modal and reload page
            bootstrap.Modal.getInstance(document.getElementById('addProductModal')).hide();
            location.reload();
        } else if (data.errors) {
            // Handle validation errors
            let errorMsg = 'Validation errors:\n';
            Object.keys(data.errors).forEach(key => {
                errorMsg += `- ${key}: ${data.errors[key].join(', ')}\n`;
            });
            alert(errorMsg);
        } else {
            alert('Error: ' + (data.error || data.message || 'Unknown error occurred'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding products to the sale.');
    })
    .finally(() => {
        addButton.disabled = false;
        addButton.innerHTML = originalText;
    });
}

// Display Error Message
function displayError(message) {
    const tbody = document.getElementById('products_list');
    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-4">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                <p class="text-danger">${message}</p>
            </td>
        </tr>
    `;
}

// Display Pagination (if needed)
function displayPagination(data) {
    // Implementation for pagination if your API supports it
    const container = document.getElementById('pagination_container');
    if (data.last_page > 1) {
        // Add pagination HTML here if needed
    }
}
</script>
@endpush