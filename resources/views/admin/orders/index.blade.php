@extends('layouts.admin')

@section('title', 'Order Management')

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Order Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.orders.dashboard') }}">Orders</a></li>
                <li class="breadcrumb-item active" aria-current="page">All Orders</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.orders.dashboard') }}" class="btn btn-outline-primary">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="{{ route('admin.orders.cod.pending') }}" class="btn btn-warning">
            <i class="fas fa-money-bill-wave"></i> Pending COD
        </a>
        <button type="button" class="btn btn-success" onclick="exportOrders()">
            <i class="fas fa-download"></i> Export CSV
        </button>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filters & Search
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Order number, customer name, email..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <option value="">All Methods</option>
                        @foreach($paymentMethods as $value => $label)
                            <option value="{{ $value }}" {{ request('payment_method') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" class="form-select">
                        <option value="">All Status</option>
                        @foreach($paymentStatuses as $value => $label)
                            <option value="{{ $value }}" {{ request('payment_status') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" 
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bulk Actions (Hidden by default) -->
    <div class="card shadow mb-4 bulk-actions" style="display: none;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong><span class="selected-count">0</span> orders selected</strong>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm" onclick="bulkConfirmCod()">
                        <i class="fas fa-check"></i> Confirm COD
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="bulkUpdateStatus()">
                        <i class="fas fa-edit"></i> Update Status
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="bulkCancel()">
                        <i class="fas fa-times"></i> Cancel Orders
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Orders Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                Orders ({{ $orders->total() }} total)
            </h6>
            <div class="d-flex gap-2">
                <div class="btn-group btn-group-sm">
                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_direction' => 'desc']) }}" 
                       class="btn btn-outline-primary {{ request('sort_by') == 'created_at' && request('sort_direction') == 'desc' ? 'active' : '' }}">
                        Newest First
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_direction' => 'asc']) }}" 
                       class="btn btn-outline-primary {{ request('sort_by') == 'created_at' && request('sort_direction') == 'asc' ? 'active' : '' }}">
                        Oldest First
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'grand_total', 'sort_direction' => 'desc']) }}" 
                       class="btn btn-outline-primary {{ request('sort_by') == 'grand_total' && request('sort_direction') == 'desc' ? 'active' : '' }}">
                        Highest Value
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($orders->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30px;">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                            </th>
                            <th>Order Details</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>
                                <input type="checkbox" name="selected_items[]" value="{{ $order->id }}" 
                                       onchange="updateBulkActionsVisibility()">
                            </td>
                            <td>
                                <div class="fw-bold">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none">
                                        {{ $order->order_number }}
                                    </a>
                                </div>
                                <div class="small text-muted">
                                    {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                                </div>
                                @if($order->delivery_date)
                                <div class="small text-info">
                                    <i class="fas fa-calendar"></i> {{ $order->delivery_date->format('M d, Y') }}
                                </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold">{{ $order->user->name ?? 'N/A' }}</div>
                                <div class="small text-muted">{{ $order->user->email ?? 'N/A' }}</div>
                                
                            </td>
                            <td>
                                <div class="fw-bold">₹{{ number_format($order->grand_total, 2) }}</div>
                                @if($order->shipping_cost > 0)
                                <div class="small text-muted">
                                    Shipping: ₹{{ number_format($order->shipping_cost, 2) }}
                                </div>
                                @endif
                                @if($order->discount > 0)
                                <div class="small text-success">
                                    Discount: -₹{{ number_format($order->discount, 2) }}
                                </div>
                                @endif
                            </td>
                            <td>
                                <div class="mb-1">
                                    <span class="badge {{ $order->payment_status_badge_class }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </div>
                                <div class="small text-muted">{{ $order->payment_method_display }}</div>
                                @if($order->razorpay_payment_id)
                                <div class="small text-muted">
                                    ID: {{ Str::limit($order->razorpay_payment_id, 15) }}
                                </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $order->status_badge_class }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                                @if($order->latestShipment)
                                <div class="small text-muted mt-1">
                                    <i class="fas fa-truck"></i> {{ ucfirst($order->latestShipment->status) }}
                                </div>
                                @endif
                            </td>
                            <td>
                                <div class="small">{{ $order->created_at->format('M d, Y') }}</div>
                                <div class="small text-muted">{{ $order->created_at->format('H:i A') }}</div>
                                <div class="small text-muted">{{ $order->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.orders.show', $order) }}" 
                                       class="btn btn-outline-primary btn-sm" title="View Order">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($order->payment_method === 'cod' && $order->status === 'pending')
                                    <button class="btn btn-outline-success btn-sm" 
                                            onclick="confirmCodOrder({{ $order->id }})" title="Confirm COD">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endif
                                    
                                    @if($order->canBeCancelled())
                                    <button class="btn btn-outline-danger btn-sm" 
                                            onclick="cancelOrder({{ $order->id }})" title="Cancel Order">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endif
                                    
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   onclick="updateOrderStatus({{ $order->id }})">
                                                    <i class="fas fa-edit"></i> Update Status
                                                </a>
                                            </li>
                                            @if($order->latestShipment)
                                            <li>
                                                <a class="dropdown-item" 
                                                   href="{{ route('admin.shipments.show', $order->latestShipment) }}">
                                                    <i class="fas fa-truck"></i> View Shipment
                                                </a>
                                            </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-primary" 
                                                   href="mailto:{{ $order->user->email ?? '' }}">
                                                    <i class="fas fa-envelope"></i> Email Customer
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} results
                </div>
                {{ $orders->appends(request()->query())->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No orders found</h5>
                <p class="text-muted">Try adjusting your filters or search criteria.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- COD Confirmation Modal -->
<div class="modal fade" id="codConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm COD Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to confirm this COD order?</p>
                <div class="mt-3">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea class="form-control" id="codConfirmNotes" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmCodBtn">
                    <i class="fas fa-check"></i> Confirm Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="statusUpdateForm">
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <select class="form-select" id="newStatus" required>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="statusNotes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateStatusBtn">
                    <i class="fas fa-save"></i> Update Status
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Export Form -->
<form id="exportForm" method="POST" action="{{ route('admin.orders.export') }}" style="display: none;">
    @csrf
    <input type="hidden" name="status" value="{{ request('status') }}">
    <input type="hidden" name="payment_method" value="{{ request('payment_method') }}">
    <input type="hidden" name="payment_status" value="{{ request('payment_status') }}">
    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
</form>
@endsection

@push('scripts')
<script>
let selectedOrderId = null;

// Export orders
function exportOrders() {
    document.getElementById('exportForm').submit();
}

// COD Order Confirmation
function confirmCodOrder(orderId) {
    selectedOrderId = orderId;
    const modal = new bootstrap.Modal(document.getElementById('codConfirmModal'));
    modal.show();
}

// Update Order Status
function updateOrderStatus(orderId) {
    selectedOrderId = orderId;
    const modal = new bootstrap.Modal(document.getElementById('statusUpdateModal'));
    modal.show();
}

// Cancel Order
function cancelOrder(orderId) {
    if (!confirm('Are you sure you want to cancel this order?')) return;
    
    const reason = prompt('Please provide a reason for cancellation:');
    if (!reason) return;
    
    fetch(`/admin/orders/${orderId}/cancel`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error cancelling order');
    });
}

// Event Listeners
document.getElementById('confirmCodBtn').addEventListener('click', function() {
    if (!selectedOrderId) return;
    
    const notes = document.getElementById('codConfirmNotes').value;
    
    fetch(`/admin/orders/cod/${selectedOrderId}/confirm`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error confirming order');
    });
});

document.getElementById('updateStatusBtn').addEventListener('click', function() {
    if (!selectedOrderId) return;
    
    const status = document.getElementById('newStatus').value;
    const notes = document.getElementById('statusNotes').value;
    
    fetch(`/admin/orders/${selectedOrderId}/update-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: status, notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating status');
    });
});

// Bulk Actions
function bulkConfirmCod() {
    const selected = document.querySelectorAll('input[name="selected_items[]"]:checked');
    if (selected.length === 0) {
        alert('Please select orders first');
        return;
    }
    
    if (!confirm(`Confirm ${selected.length} COD orders?`)) return;
    
    const orderIds = Array.from(selected).map(cb => cb.value);
    
    fetch('/admin/orders/cod/bulk-confirm', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ order_ids: orderIds })
    })
    .then(response => response.json())
    .then(data => {
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error with bulk confirmation');
    });
}
</script>
@endpush