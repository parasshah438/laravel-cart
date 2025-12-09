@extends('layouts.admin')

@section('title', 'Sale Events Management')

@section('content')
<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-fire text-danger me-2"></i>Sale Events Management
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.sales.analytics.index') }}" class="btn btn-info">
                <i class="fas fa-chart-line"></i> Analytics
            </a>
            <a href="{{ route('admin.sales.events.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Sale Event
            </a>
        </div>
    </div>

    <!-- Filters Row -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filters & Search
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.sales.events.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search Events</label>
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
                            <label for="type">Sale Type</label>
                            <select class="form-control" id="type" name="type">
                                <option value="">All Types</option>
                                <option value="flash_sale" {{ request('type') == 'flash_sale' ? 'selected' : '' }}>Flash Sale</option>
                                <option value="weekend_sale" {{ request('type') == 'weekend_sale' ? 'selected' : '' }}>Weekend Sale</option>
                                <option value="holiday_sale" {{ request('type') == 'holiday_sale' ? 'selected' : '' }}>Holiday Sale</option>
                                <option value="clearance_sale" {{ request('type') == 'clearance_sale' ? 'selected' : '' }}>Clearance Sale</option>
                                <option value="seasonal_sale" {{ request('type') == 'seasonal_sale' ? 'selected' : '' }}>Seasonal Sale</option>
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
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('admin.sales.events.index') }}" class="btn btn-secondary">
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

    <!-- Sale Events Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Sale Events ({{ $saleEvents->total() }} total)
            </h6>
        </div>
        <div class="card-body">
            @if($saleEvents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Event Details</th>
                                <th>Type & Status</th>
                                <th>Duration</th>
                                <th>Products</th>
                                <th>Performance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($saleEvents as $event)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($event->banner_image)
                                                <img src="{{ asset($event->banner_image) }}" 
                                                     alt="{{ $event->name }}" 
                                                     class="rounded me-3" 
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center me-3" 
                                                     style="width: 60px; height: 60px;">
                                                    <i class="fas fa-fire fa-lg"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-1">{{ $event->name }}</h6>
                                                <small class="text-muted">{{ Str::limit($event->description, 50) }}</small>
                                                <br>
                                                <small class="text-info">Priority: {{ $event->priority }}/10</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-white mb-1">
                                            {{ ucfirst(str_replace('_', ' ', $event->sale_type)) }}
                                        </span>
                                        <br>
                                        @if($event->isActive())
                                            <span class="badge bg-success">
                                                <i class="fas fa-play"></i> Active
                                            </span>
                                        @elseif($event->start_time > now())
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock"></i> Upcoming
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-stop"></i> Ended
                                            </span>
                                        @endif

                                        @if($event->is_featured)
                                            <br><span class="badge bg-purple mt-1">
                                                <i class="fas fa-star"></i> Featured
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <strong>Start:</strong><br>
                                            <small>{{ $event->start_time->format('M d, Y H:i') }}</small>
                                        </div>
                                        <div class="mt-2">
                                            <strong>End:</strong><br>
                                            <small>{{ $event->end_time->format('M d, Y H:i') }}</small>
                                        </div>
                                        @if($event->isActive())
                                            <div class="mt-2">
                                                <small class="text-danger">
                                                    <i class="fas fa-clock"></i> {{ $event->getTimeRemaining() }}
                                                </small>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="mb-2">
                                            <span class="badge bg-primary font-size-lg">
                                                {{ $event->sale_products_count }}
                                            </span>
                                            <br><small>Products</small>
                                        </div>
                                        @if($event->max_discount_percentage)
                                            <small class="text-success">
                                                Max {{ $event->max_discount_percentage }}% off
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="text-primary font-weight-bold">{{ $event->sale_orders_count }}</div>
                                                <small>Orders</small>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-success font-weight-bold">
                                                    ${{ number_format($event->saleOrders->sum('final_amount'), 2) }}
                                                </div>
                                                <small>Revenue</small>
                                            </div>
                                        </div>
                                        @if($event->analytics)
                                            <div class="mt-2 text-center">
                                                <small class="text-info">
                                                    {{ $event->analytics->conversion_rate }}% conversion
                                                </small>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical d-block">
                                            <a href="{{ route('admin.sales.events.show', $event) }}" 
                                               class="btn btn-sm btn-info mb-1">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="{{ route('admin.sales.events.edit', $event) }}" 
                                               class="btn btn-sm btn-warning mb-1">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-{{ $event->is_active ? 'danger' : 'success' }} mb-1"
                                                    onclick="toggleStatus({{ $event->id }}, {{ $event->is_active ? 'false' : 'true' }})">
                                                <i class="fas fa-{{ $event->is_active ? 'pause' : 'play' }}"></i> 
                                                {{ $event->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            <form action="{{ route('admin.sales.events.destroy', $event) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this sale event?')">
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
                    {{ $saleEvents->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-fire fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Sale Events Found</h5>
                    <p class="text-muted">Create your first sale event to get started!</p>
                    <a href="{{ route('admin.sales.events.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Sale Event
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>

@push('scripts')
<script>
function toggleStatus(eventId, newStatus) {
    fetch(`/admin/sales/events/${eventId}/toggle-status`, {
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

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
</style>
@endpush

@endsection