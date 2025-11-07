@extends('layouts.admin')

@section('title', 'Pending COD Orders')

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Pending COD Orders</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.orders.dashboard') }}">Orders</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pending COD</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.orders.dashboard') }}" class="btn btn-outline-primary">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-list"></i> All Orders
        </a>
        @if($orders->count() > 0)
        <button type="button" class="btn btn-success" onclick="bulkConfirmAll()">
            <i class="fas fa-check-double"></i> Confirm All
        </button>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    
    @if($orders->count() > 0)
    <!-- Alert for Action Required -->
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Action Required:</strong> {{ $orders->total() }} COD orders are pending confirmation. 
        These orders need manual review and confirmation before they can be processed for shipment.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    
    <!-- Bulk Actions -->
    <div class="card shadow mb-4 bulk-actions" style="display: none;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong><span class="selected-count">0</span> orders selected</strong>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success" onclick="bulkConfirmSelected()">
                        <i class="fas fa-check"></i> Confirm Selected
                    </button>
                    <button type="button" class="btn btn-danger" onclick="bulkCancelSelected()">
                        <i class="fas fa-times"></i> Cancel Selected
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- COD Orders List -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-warning">
                <i class="fas fa-money-bill-wave"></i> Pending COD Orders ({{ $orders->total() }})
            </h6>
            <div class="d-flex gap-2">
                <small class="text-muted">Page {{ $orders->currentPage() }} of {{ $orders->lastPage() }}</small>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30px;">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                            </th>
                            <th>Order Details</th>
                            <th>Customer Information</th>
                            <th>Delivery Address</th>
                            <th>Amount Details</th>
                            <th>Order Date</th>
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
                                <div class="fw-bold text-primary">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none">
                                        {{ $order->order_number }}
                                    </a>
                                </div>
                                <div class="small text-muted">
                                    {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                                </div>
                                <div class="small">
                                    <span class="badge bg-warning text-dark">COD - Pending Confirmation</span>
                                </div>
                                @if($order->delivery_date)
                                <div class="small text-info mt-1">
                                    <i class="fas fa-calendar"></i> Delivery: {{ $order->delivery_date->format('M d, Y') }}
                                </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold">{{ $order->user->name }}</div>
                                <div class="small text-muted">{{ $order->user->email }}</div>
                                @if($order->user->phone)
                                <div class="small text-muted">
                                    <i class="fas fa-phone"></i> {{ $order->user->phone }}
                                </div>
                                @endif
                                <div class="small text-muted">
                                    Customer since {{ $order->user->created_at->format('M Y') }}
                                </div>
                            </td>
                            <td>
                                @if($order->address)
                                <div class="fw-bold">{{ $order->address->name }}</div>
                                <div class="small text-muted">
                                    {{ $order->address->address_line_1 }}
                                    @if($order->address->address_line_2), {{ $order->address->address_line_2 }}@endif
                                </div>
                                <div class="small text-muted">
                                    {{ $order->address->city }}, {{ $order->address->state }} {{ $order->address->postal_code }}
                                </div>
                                @if($order->address->phone)
                                <div class="small text-muted">
                                    <i class="fas fa-phone"></i> {{ $order->address->phone }}
                                </div>
                                @endif
                                @else
                                <span class="text-muted">No address available</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-success">₹{{ number_format($order->grand_total, 2) }}</div>
                                <div class="small text-muted">Subtotal: ₹{{ number_format($order->total, 2) }}</div>
                                @if($order->shipping_cost > 0)
                                <div class="small text-muted">Shipping: ₹{{ number_format($order->shipping_cost, 2) }}</div>
                                @endif
                                @if($order->discount > 0)
                                <div class="small text-success">Discount: -₹{{ number_format($order->discount, 2) }}</div>
                                @endif
                                @if($order->coupon_code)
                                <div class="small text-info">
                                    <i class="fas fa-tag"></i> {{ $order->coupon_code }}
                                </div>
                                @endif
                            </td>
                            <td>
                                <div class="small">{{ $order->created_at->format('M d, Y') }}</div>
                                <div class="small text-muted">{{ $order->created_at->format('H:i A') }}</div>
                                <div class="small text-muted">{{ $order->created_at->diffForHumans() }}</div>
                                @if($order->created_at->diffInHours() > 24)
                                <div class="small text-danger">
                                    <i class="fas fa-exclamation-circle"></i> Urgent
                                </div>
                                @endif
                            </td>
                            <td>
                                <div class="d-grid gap-1">
                                    <button class="btn btn-success btn-sm" 
                                            onclick="confirmCodOrder({{ $order->id }})" 
                                            title="Confirm COD Order">
                                        <i class="fas fa-check"></i> Confirm
                                    </button>
                                    <a href="{{ route('admin.orders.show', $order) }}" 
                                       class="btn btn-outline-primary btn-sm" 
                                       title="View Order Details">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <button class="btn btn-outline-danger btn-sm" 
                                            onclick="cancelOrder({{ $order->id }})" 
                                            title="Cancel Order">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
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
                {{ $orders->links() }}
            </div>
        </div>
    </div>
    
    @else
    <!-- No Pending COD Orders -->
    <div class="card shadow">
        <div class="card-body text-center py-5">
            <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
            <h4 class="text-success">All Caught Up!</h4>
            <p class="text-muted">There are no pending COD orders requiring confirmation at the moment.</p>
            <div class="mt-4">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-primary">
                    <i class="fas fa-list"></i> View All Orders
                </a>
                <a href="{{ route('admin.orders.dashboard') }}" class="btn btn-outline-primary">
                    <i class="fas fa-tachometer-alt"></i> Orders Dashboard
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- COD Confirmation Modal -->
<div class="modal fade" id="codConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm COD Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Confirming this COD order will:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Mark the payment as paid (to be collected on delivery)</li>
                        <li>Change order status from pending to confirmed</li>
                        <li>Automatically trigger shipment creation process</li>
                        <li>Send confirmation notification to the customer</li>
                    </ul>
                </div>
                <div class="mt-3">
                    <label class="form-label">Confirmation Notes (Optional)</label>
                    <textarea class="form-control" id="codConfirmNotes" rows="3" 
                              placeholder="Add any notes about this confirmation (visible to other admins)..."></textarea>
                </div>
                <div class="mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notifyCustomer" checked>
                        <label class="form-check-label" for="notifyCustomer">
                            Send confirmation email to customer
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmCodBtn">
                    <i class="fas fa-check"></i> Confirm COD Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Confirmation Modal -->
<div class="modal fade" id="bulkConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Confirm COD Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Are you sure?</strong> This will confirm <span id="bulkConfirmCount">0</span> COD orders.
                </div>
                <p>All selected orders will be:</p>
                <ul>
                    <li>Marked as paid (COD payment pending on delivery)</li>
                    <li>Status changed to confirmed</li>
                    <li>Automatically queued for shipment creation</li>
                    <li>Customer notifications sent</li>
                </ul>
                <div class="mt-3">
                    <label class="form-label">Bulk Confirmation Notes (Optional)</label>
                    <textarea class="form-control" id="bulkConfirmNotes" rows="2" 
                              placeholder="Add notes for all selected orders..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="bulkConfirmBtn">
                    <i class="fas fa-check-double"></i> Confirm All Selected
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedOrderId = null;
let selectedOrderIds = [];

// Single COD Order Confirmation
function confirmCodOrder(orderId) {
    selectedOrderId = orderId;
    const modal = new bootstrap.Modal(document.getElementById('codConfirmModal'));
    modal.show();
}

// Bulk Confirm All Orders
function bulkConfirmAll() {
    const allOrderIds = Array.from(document.querySelectorAll('input[name="selected_items[]"]')).map(cb => cb.value);
    if (allOrderIds.length === 0) return;
    
    selectedOrderIds = allOrderIds;
    document.getElementById('bulkConfirmCount').textContent = allOrderIds.length;
    const modal = new bootstrap.Modal(document.getElementById('bulkConfirmModal'));
    modal.show();
}

// Bulk Confirm Selected Orders
function bulkConfirmSelected() {
    const selected = document.querySelectorAll('input[name="selected_items[]"]:checked');
    if (selected.length === 0) {
        alert('Please select orders first');
        return;
    }
    
    selectedOrderIds = Array.from(selected).map(cb => cb.value);
    document.getElementById('bulkConfirmCount').textContent = selectedOrderIds.length;
    const modal = new bootstrap.Modal(document.getElementById('bulkConfirmModal'));
    modal.show();
}

// Cancel Order
function cancelOrder(orderId) {
    if (!confirm('Are you sure you want to cancel this COD order?')) return;
    
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
            // Show success message
            showAlert('success', data.message);
            // Remove the row from table
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('danger', 'Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Error cancelling order');
    });
}

// Single COD Confirmation Handler
document.getElementById('confirmCodBtn').addEventListener('click', function() {
    if (!selectedOrderId) return;
    
    const notes = document.getElementById('codConfirmNotes').value;
    const notifyCustomer = document.getElementById('notifyCustomer').checked;
    const btn = this;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Confirming...';
    
    fetch(`/admin/orders/cod/${selectedOrderId}/confirm`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            notes: notes,
            notify_customer: notifyCustomer
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('codConfirmModal')).hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error(data.message || 'Failed to confirm order');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Error confirming order: ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});

// Bulk COD Confirmation Handler
document.getElementById('bulkConfirmBtn').addEventListener('click', function() {
    if (selectedOrderIds.length === 0) return;
    
    const notes = document.getElementById('bulkConfirmNotes').value;
    const btn = this;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Confirming...';
    
    fetch('/admin/orders/cod/bulk-confirm', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            order_ids: selectedOrderIds,
            notes: notes
        })
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('bulkConfirmModal')).hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            // If redirected (Laravel redirect response), the page will reload automatically
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Error with bulk confirmation');
        setTimeout(() => location.reload(), 2000);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});

// Show Alert Helper - Enhanced with Toast Notifications
function showAlert(type, message) {
    // Show toast notification for better UX
    if (window.showToast) {
        window.showToast(message, type, 5000);
    }
    
    // Also show alert in page for immediate context
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto hide after 4 seconds (toast will last longer)
    setTimeout(() => {
        if (alertDiv.parentNode) {
            const bsAlert = new bootstrap.Alert(alertDiv);
            bsAlert.close();
        }
    }, 4000);
}

// Update bulk actions visibility
function updateBulkActionsVisibility() {
    const checkboxes = document.querySelectorAll('input[name="selected_items[]"]:checked');
    const bulkActions = document.querySelector('.bulk-actions');
    
    if (bulkActions) {
        if (checkboxes.length > 0) {
            bulkActions.style.display = 'block';
            bulkActions.querySelector('.selected-count').textContent = checkboxes.length;
        } else {
            bulkActions.style.display = 'none';
        }
    }
}

// Select all functionality
function toggleSelectAll(source) {
    const checkboxes = document.getElementsByName('selected_items[]');
    for (let i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = source.checked;
    }
    updateBulkActionsVisibility();
}

// Auto-refresh every 30 seconds if there are pending orders
@if($orders->count() > 0)
setInterval(() => {
    // Only refresh if user hasn't interacted recently
    if (document.hidden === false && Date.now() - lastInteraction > 30000) {
        location.reload();
    }
}, 30000);

let lastInteraction = Date.now();
document.addEventListener('click', () => lastInteraction = Date.now());
document.addEventListener('keypress', () => lastInteraction = Date.now());
@endif
</script>
@endpush