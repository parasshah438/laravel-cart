@extends('layouts.admin')

@section('title', 'Bundle Deals Management')

@section('content')
<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-boxes text-success me-2"></i>Bundle Deals Management
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.sales.analytics.index') }}" class="btn btn-info">
                <i class="fas fa-chart-line"></i> Analytics
            </a>
            <a href="{{ route('admin.sales.bundles.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Create New Bundle Deal
            </a>
        </div>
    </div>

    <!-- Filters Row -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success">
                <i class="fas fa-filter"></i> Filters & Search
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.sales.bundles.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search Bundles</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Search by name or description...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="type">Bundle Type</label>
                            <select class="form-control" id="type" name="type">
                                <option value="">All Types</option>
                                <option value="buy_x_get_y" {{ request('type') == 'buy_x_get_y' ? 'selected' : '' }}>Buy X Get Y</option>
                                <option value="combo_deal" {{ request('type') == 'combo_deal' ? 'selected' : '' }}>Combo Deal</option>
                                <option value="volume_discount" {{ request('type') == 'volume_discount' ? 'selected' : '' }}>Volume Discount</option>
                                <option value="cross_sell" {{ request('type') == 'cross_sell' ? 'selected' : '' }}>Cross-sell Bundle</option>
                                <option value="seasonal_bundle" {{ request('type') == 'seasonal_bundle' ? 'selected' : '' }}>Seasonal Bundle</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                <option value="ended" {{ request('status') == 'ended' ? 'selected' : '' }}>Ended</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('admin.sales.bundles.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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

    <!-- Bundle Deals Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-success">
                <i class="fas fa-list"></i> Bundle Deals ({{ $bundleDeals->total() }} total)
            </h6>
        </div>
        <div class="card-body">
            @if($bundleDeals->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Bundle Details</th>
                                <th>Type & Status</th>
                                <th>Pricing</th>
                                <th>Duration</th>
                                <th>Products</th>
                                <th>Performance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bundleDeals as $bundle)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($bundle->bundle_image)
                                                <img src="{{ asset($bundle->bundle_image) }}" 
                                                     alt="{{ $bundle->name }}" 
                                                     class="rounded me-3" 
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                <div class="bg-success text-white rounded d-flex align-items-center justify-content-center me-3" 
                                                     style="width: 60px; height: 60px;">
                                                    <i class="fas fa-boxes fa-lg"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-1">{{ $bundle->name }}</h6>
                                                <small class="text-muted">{{ Str::limit($bundle->description, 50) }}</small>
                                                <br>
                                                <small class="text-info">Priority: {{ $bundle->priority }}/10</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-white mb-1">
                                            {{ ucfirst(str_replace('_', ' ', $bundle->bundle_type)) }}
                                        </span>
                                        <br>
                                        @if($bundle->isActive())
                                            <span class="badge bg-success">
                                                <i class="fas fa-play"></i> Active
                                            </span>
                                        @elseif($bundle->start_time > now())
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock"></i> Upcoming
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-stop"></i> Ended
                                            </span>
                                        @endif

                                        @if($bundle->is_featured)
                                            <br><span class="badge bg-purple mt-1">
                                                <i class="fas fa-star"></i> Featured
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            <div class="mb-2">
                                                <strong class="text-success">
                                                    ${{ number_format($bundle->bundle_price, 2) }}
                                                </strong>
                                                <br>
                                                <small class="text-muted text-decoration-line-through">
                                                    ${{ number_format($bundle->original_total_price, 2) }}
                                                </small>
                                            </div>
                                            <div class="badge bg-success">
                                                Save ${{ number_format($bundle->getDiscountAmount(), 2) }}
                                            </div>
                                            <br>
                                            <small class="text-success">
                                                {{ round((($bundle->original_total_price - $bundle->bundle_price) / $bundle->original_total_price) * 100, 1) }}% off
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>Start:</strong><br>
                                            <small>{{ $bundle->start_time->format('M d, Y H:i') }}</small>
                                        </div>
                                        <div class="mt-2">
                                            <strong>End:</strong><br>
                                            <small>{{ $bundle->end_time->format('M d, Y H:i') }}</small>
                                        </div>
                                        @if($bundle->isActive())
                                            <div class="mt-2">
                                                <small class="text-danger">
                                                    <i class="fas fa-clock"></i> {{ $bundle->getTimeRemaining() }}
                                                </small>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="mb-2">
                                            <span class="badge bg-success font-size-lg">
                                                {{ $bundle->bundle_products_count }}
                                            </span>
                                            <br><small>Products</small>
                                        </div>
                                        <div>
                                            <small class="text-muted">
                                                Min: {{ $bundle->min_quantity }}
                                                @if($bundle->max_quantity)
                                                    | Max: {{ $bundle->max_quantity }}
                                                @endif
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="text-primary font-weight-bold">{{ $bundle->sale_orders_count }}</div>
                                                <small>Orders</small>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-success font-weight-bold">
                                                    ${{ number_format($bundle->saleOrders->sum('final_amount'), 2) }}
                                                </div>
                                                <small>Revenue</small>
                                            </div>
                                        </div>
                                        @if($bundle->max_uses_per_user)
                                            <div class="mt-2 text-center">
                                                <small class="text-info">
                                                    Limit: {{ $bundle->max_uses_per_user }}/user
                                                </small>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical d-block">
                                            <a href="{{ route('admin.sales.bundles.show', $bundle) }}" 
                                               class="btn btn-sm btn-info mb-1">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="{{ route('admin.sales.bundles.edit', $bundle) }}" 
                                               class="btn btn-sm btn-warning mb-1">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-{{ $bundle->is_active ? 'danger' : 'success' }} mb-1"
                                                    onclick="toggleStatus({{ $bundle->id }}, {{ $bundle->is_active ? 'false' : 'true' }})">
                                                <i class="fas fa-{{ $bundle->is_active ? 'pause' : 'play' }}"></i> 
                                                {{ $bundle->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            <form action="{{ route('admin.sales.bundles.destroy', $bundle) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this bundle deal?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $bundleDeals->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Bundle Deals Found</h5>
                    <p class="text-muted">Create your first bundle deal to get started!</p>
                    <a href="{{ route('admin.sales.bundles.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Create Bundle Deal
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>

@push('scripts')
<script>
function toggleStatus(bundleId, newStatus) {
    fetch(`/admin/sales/bundles/${bundleId}/toggle-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                <i class="fas fa-check-circle"></i> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container-fluid').prepend(alert);
            
            // Reload page after short delay
            setTimeout(() => {
                location.reload();
            }, 1500);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the status.');
    });
}
</script>

<style>
.bg-purple {
    background-color: #6f42c1 !important;
}

.font-size-lg {
    font-size: 1.1em;
}

.text-decoration-line-through {
    text-decoration: line-through;
}
</style>
@endpush

@endsection