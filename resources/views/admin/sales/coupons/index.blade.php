@extends('layouts.admin')

@section('title', 'Dynamic Coupons Management')

@section('content')
<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tags text-purple me-2"></i>Dynamic Coupons Management
        </h1>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#bulkGenerateModal">
                <i class="fas fa-magic"></i> Bulk Generate
            </button>
            <a href="{{ route('admin.sales.coupons.create') }}" class="btn btn-purple">
                <i class="fas fa-plus"></i> Create New Coupon
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-purple shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-purple text-uppercase mb-1">
                                Total Coupons
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalCoupons">
                                {{ $dynamicCoupons->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Coupons
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="activeCoupons">
                                Loading...
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Used Coupons
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="usedCoupons">
                                Loading...
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Savings
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalSavings">
                                Loading...
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-piggy-bank fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Row -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-purple">
                <i class="fas fa-filter"></i> Filters & Search
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.sales.coupons.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search Coupons</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Search by code, description, or user...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="type">Coupon Type</label>
                            <select class="form-control" id="type" name="type">
                                <option value="">All Types</option>
                                <option value="personal" {{ request('type') == 'personal' ? 'selected' : '' }}>Personal</option>
                                <option value="category_based" {{ request('type') == 'category_based' ? 'selected' : '' }}>Category Based</option>
                                <option value="behavior_based" {{ request('type') == 'behavior_based' ? 'selected' : '' }}>Behavior Based</option>
                                <option value="loyalty_reward" {{ request('type') == 'loyalty_reward' ? 'selected' : '' }}>Loyalty Reward</option>
                                <option value="referral_bonus" {{ request('type') == 'referral_bonus' ? 'selected' : '' }}>Referral Bonus</option>
                                <option value="cart_abandonment" {{ request('type') == 'cart_abandonment' ? 'selected' : '' }}>Cart Abandonment</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>Used</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-purple">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('admin.sales.coupons.index') }}" class="btn btn-secondary">
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

    <!-- Coupons Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-purple">
                <i class="fas fa-list"></i> Dynamic Coupons ({{ $dynamicCoupons->total() }} total)
            </h6>
        </div>
        <div class="card-body">
            @if($dynamicCoupons->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Coupon Details</th>
                                <th>User & Type</th>
                                <th>Discount</th>
                                <th>Usage & Limits</th>
                                <th>Validity</th>
                                <th>Performance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dynamicCoupons as $coupon)
                                <tr>
                                    <td>
                                        <div>
                                            <h6 class="mb-1">
                                                <code class="bg-purple text-white p-1 rounded">{{ $coupon->coupon_code }}</code>
                                            </h6>
                                            <small class="text-muted">{{ Str::limit($coupon->description, 50) }}</small>
                                            @if($coupon->saleEvent)
                                                <br><small class="text-info">
                                                    <i class="fas fa-fire"></i> {{ $coupon->saleEvent->name }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($coupon->user)
                                            <div class="mb-2">
                                                <strong>{{ $coupon->user->name }}</strong>
                                                <br><small class="text-muted">{{ $coupon->user->email }}</small>
                                            </div>
                                        @else
                                            <div class="mb-2">
                                                <span class="badge bg-secondary">Public</span>
                                            </div>
                                        @endif
                                        <span class="badge bg-info text-white">
                                            {{ ucfirst(str_replace('_', ' ', $coupon->coupon_type)) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="mb-2">
                                            @if($coupon->discount_type === 'percentage')
                                                <h5 class="text-success mb-0">{{ $coupon->discount_value }}%</h5>
                                                <small>Percentage</small>
                                            @else
                                                <h5 class="text-success mb-0">${{ $coupon->discount_value }}</h5>
                                                <small>Fixed Amount</small>
                                            @endif
                                        </div>
                                        @if($coupon->min_order_amount)
                                            <small class="text-muted">
                                                Min: ${{ $coupon->min_order_amount }}
                                            </small>
                                        @endif
                                        @if($coupon->max_discount_amount)
                                            <br><small class="text-muted">
                                                Max: ${{ $coupon->max_discount_amount }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="mb-2">
                                            <div class="text-primary font-weight-bold">{{ $coupon->used_count }}</div>
                                            <small>Used</small>
                                        </div>
                                        @if($coupon->max_uses)
                                            <div class="progress mb-2" style="height: 8px;">
                                                <div class="progress-bar bg-success" 
                                                     role="progressbar" 
                                                     style="width: {{ ($coupon->used_count / $coupon->max_uses) * 100 }}%">
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ $coupon->max_uses - $coupon->used_count }} remaining</small>
                                        @else
                                            <small class="text-success">Unlimited</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            @if($coupon->expires_at > now())
                                                @if($coupon->is_active)
                                                    <span class="badge bg-success mb-2">
                                                        <i class="fas fa-check-circle"></i> Active
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary mb-2">
                                                        <i class="fas fa-pause"></i> Paused
                                                    </span>
                                                @endif
                                            @else
                                                <span class="badge bg-danger mb-2">
                                                    <i class="fas fa-times-circle"></i> Expired
                                                </span>
                                            @endif
                                            <br>
                                            <small class="text-muted">
                                                Expires: {{ $coupon->expires_at->format('M d, Y H:i') }}
                                            </small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="mb-2">
                                            <div class="text-success font-weight-bold">
                                                ${{ number_format($coupon->saleOrders->sum('coupon_discount_amount'), 2) }}
                                            </div>
                                            <small>Total Savings</small>
                                        </div>
                                        <div>
                                            <div class="text-primary font-weight-bold">{{ $coupon->sale_orders_count }}</div>
                                            <small>Orders</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical d-block">
                                            <a href="{{ route('admin.sales.coupons.show', $coupon) }}" 
                                               class="btn btn-sm btn-info mb-1">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="{{ route('admin.sales.coupons.edit', $coupon) }}" 
                                               class="btn btn-sm btn-warning mb-1">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-{{ $coupon->is_active ? 'danger' : 'success' }} mb-1"
                                                    onclick="toggleStatus({{ $coupon->id }}, {{ $coupon->is_active ? 'false' : 'true' }})">
                                                <i class="fas fa-{{ $coupon->is_active ? 'pause' : 'play' }}"></i> 
                                                {{ $coupon->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            @if(!$coupon->is_used && $coupon->used_count == 0)
                                                <form action="{{ route('admin.sales.coupons.destroy', $coupon) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this coupon?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $dynamicCoupons->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Dynamic Coupons Found</h5>
                    <p class="text-muted">Create your first dynamic coupon to get started!</p>
                    <a href="{{ route('admin.sales.coupons.create') }}" class="btn btn-purple">
                        <i class="fas fa-plus"></i> Create Dynamic Coupon
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Bulk Generate Modal -->
    <div class="modal fade" id="bulkGenerateModal" tabindex="-1" aria-labelledby="bulkGenerateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkGenerateModalLabel">
                        <i class="fas fa-magic"></i> Bulk Generate Coupons
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="bulkGenerateForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bulkCouponType">Coupon Type</label>
                                    <select class="form-control" id="bulkCouponType" name="coupon_type" required>
                                        <option value="personal">Personal Coupon</option>
                                        <option value="loyalty_reward">Loyalty Reward</option>
                                        <option value="referral_bonus">Referral Bonus</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bulkDiscountType">Discount Type</label>
                                    <select class="form-control" id="bulkDiscountType" name="discount_type" required>
                                        <option value="percentage">Percentage</option>
                                        <option value="fixed_amount">Fixed Amount</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bulkDiscountValue">Discount Value</label>
                                    <input type="number" class="form-control" id="bulkDiscountValue" name="discount_value" required min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bulkExpiresAt">Expires At</label>
                                    <input type="datetime-local" class="form-control" id="bulkExpiresAt" name="expires_at" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="bulkDescription">Description</label>
                            <textarea class="form-control" id="bulkDescription" name="description" rows="3" required placeholder="Description for the bulk generated coupons..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Select Users</label>
                            <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAllUsers">
                                    <label class="form-check-label" for="selectAllUsers">
                                        <strong>Select All Users</strong>
                                    </label>
                                </div>
                                <hr>
                                <!-- Users will be loaded here via AJAX -->
                                <div id="usersList">Loading users...</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-purple">
                            <i class="fas fa-magic"></i> Generate Coupons
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load analytics data
    loadAnalytics();

    // Load users for bulk generation
    loadUsers();

    // Handle bulk generate form
    document.getElementById('bulkGenerateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        generateBulkCoupons();
    });

    // Handle select all users checkbox
    document.getElementById('selectAllUsers').addEventListener('change', function() {
        const userCheckboxes = document.querySelectorAll('input[name="user_ids[]"]');
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
});

function loadAnalytics() {
    fetch('{{ route("admin.sales.coupons.analytics") }}?range=30')
        .then(response => response.json())
        .then(data => {
            document.getElementById('activeCoupons').textContent = data.active_coupons || '0';
            document.getElementById('usedCoupons').textContent = data.used_coupons || '0';
            document.getElementById('totalSavings').textContent = '$' + (data.total_savings || 0).toFixed(2);
        })
        .catch(error => {
            console.error('Error loading analytics:', error);
            document.getElementById('activeCoupons').textContent = 'Error';
            document.getElementById('usedCoupons').textContent = 'Error';
            document.getElementById('totalSavings').textContent = 'Error';
        });
}

function loadUsers() {
    // For demo purposes, we'll create some mock users
    // In a real application, you'd fetch this from an API
    const usersList = document.getElementById('usersList');
    usersList.innerHTML = '<p class="text-muted">Loading users...</p>';
    
    // Mock users for demonstration
    setTimeout(() => {
        usersList.innerHTML = `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="user_ids[]" value="1" id="user1">
                <label class="form-check-label" for="user1">John Doe (john@example.com)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="user_ids[]" value="2" id="user2">
                <label class="form-check-label" for="user2">Jane Smith (jane@example.com)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="user_ids[]" value="3" id="user3">
                <label class="form-check-label" for="user3">Bob Johnson (bob@example.com)</label>
            </div>
        `;
    }, 1000);
}

function generateBulkCoupons() {
    const formData = new FormData(document.getElementById('bulkGenerateForm'));
    const selectedUsers = Array.from(document.querySelectorAll('input[name="user_ids[]"]:checked')).map(cb => cb.value);
    
    if (selectedUsers.length === 0) {
        alert('Please select at least one user.');
        return;
    }

    formData.delete('user_ids[]');
    selectedUsers.forEach(userId => formData.append('user_ids[]', userId));

    fetch('{{ route("admin.sales.coupons.generate-bulk") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('bulkGenerateModal'));
            modal.hide();
            
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
            }, 2000);
        } else {
            alert('Error generating coupons. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while generating coupons.');
    });
}

function toggleStatus(couponId, newStatus) {
    fetch(`/admin/sales/coupons/${couponId}/toggle-status`, {
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
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                <i class="fas fa-check-circle"></i> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container-fluid').prepend(alert);
            
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
.text-purple {
    color: #6f42c1 !important;
}

.bg-purple {
    background-color: #6f42c1 !important;
}

.btn-purple {
    background-color: #6f42c1;
    border-color: #6f42c1;
    color: white;
}

.btn-purple:hover {
    background-color: #5a359a;
    border-color: #5a359a;
    color: white;
}

.border-left-purple {
    border-left: 0.25rem solid #6f42c1 !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

code.bg-purple {
    font-size: 0.9em;
}
</style>
@endpush

@endsection