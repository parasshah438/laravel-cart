@extends('layouts.admin')

@section('title', 'Order Details - ' . $order->order_number)

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Order Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $order->order_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
        @if($order->payment_method === 'cod' && $order->status === 'pending')
        <button class="btn btn-success" onclick="confirmCodOrder({{ $order->id }})">
            <i class="fas fa-check"></i> Confirm COD
        </button>
        @endif
        @if($order->canBeCancelled())
        <button class="btn btn-danger" onclick="cancelOrder({{ $order->id }})">
            <i class="fas fa-times"></i> Cancel Order
        </button>
        @endif
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-cog"></i> Actions
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="updateOrderStatus({{ $order->id }})">
                    <i class="fas fa-edit"></i> Update Status
                </a></li>
                @if($order->latestShipment)
                <li><a class="dropdown-item" href="{{ route('admin.shipments.show', $order->latestShipment) }}">
                    <i class="fas fa-truck"></i> View Shipment
                </a></li>
                @endif
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="mailto:{{ $order->user->email }}">
                    <i class="fas fa-envelope"></i> Email Customer
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="printOrder()">
                    <i class="fas fa-print"></i> Print Order
                </a></li>
            </ul>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- Order Header -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-primary">{{ $order->order_number }}</h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-calendar"></i> 
                                Placed on {{ $order->created_at->format('F d, Y \a\t H:i A') }}
                            </p>
                            <p class="text-muted mb-2">
                                <i class="fas fa-clock"></i> 
                                {{ $order->created_at->diffForHumans() }}
                            </p>
                            @if($order->delivery_date)
                            <p class="text-info mb-2">
                                <i class="fas fa-truck"></i> 
                                Delivery Date: {{ $order->delivery_date->format('F d, Y') }}
                            </p>
                            @endif
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="mb-2">
                                <span class="badge {{ $order->getStatusBadgeClassProfessional() }} fs-6">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                            <div class="mb-2">
                                <span class="badge {{ $order->payment_status_badge_class }} fs-6">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </div>
                            <div class="h4 text-success mb-0">
                                ₹{{ number_format($order->grand_total, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    @if($order->payment_method === 'cod' && $order->status === 'pending')
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Action Required:</strong> This COD order needs confirmation.
                    </div>
                    @endif
                    
                    @if($order->canCreateShipment())
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Ready:</strong> This order is ready for shipment creation.
                    </div>
                    @endif
                    
                    <div class="d-grid gap-2">
                        @if($order->payment_method === 'cod' && $order->status === 'pending')
                        <button class="btn btn-success" onclick="confirmCodOrder({{ $order->id }})">
                            <i class="fas fa-check"></i> Confirm COD Order
                        </button>
                        @endif
                        
                        <button class="btn btn-primary" onclick="updateOrderStatus({{ $order->id }})">
                            <i class="fas fa-edit"></i> Update Status
                        </button>
                        
                        @if($order->latestShipment)
                        <a href="{{ route('admin.shipments.show', $order->latestShipment) }}" class="btn btn-info">
                            <i class="fas fa-truck"></i> View Shipment
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Customer and Address Information -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Customer Information</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="fas fa-user-circle fa-2x text-gray-400"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $order->user->name }}</h6>
                            <p class="text-muted mb-0">{{ $order->user->email }}</p>
                            @if($order->user->phone)
                            <p class="text-muted mb-0">{{ $order->user->phone }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>Customer Since:</strong><br>
                            <span class="text-muted">{{ $order->user->created_at->format('M Y') }}</span>
                        </div>
                        <div class="col-sm-6">
                            <strong>Total Orders:</strong><br>
                            <span class="text-muted">{{ $order->user->orders()->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Delivery Address</h6>
                </div>
                <div class="card-body">
                    @if($order->address)
                    <div class="d-flex align-items-start mb-3">
                        <div class="me-3">
                            <i class="fas fa-map-marker-alt fa-2x text-gray-400"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">{{ $order->address->name }}</h6>
                            <p class="mb-1">{{ $order->address->address_line_1 }}</p>
                            @if($order->address->address_line_2)
                            <p class="mb-1">{{ $order->address->address_line_2 }}</p>
                            @endif
                            <p class="mb-1">{{ $order->address->city }}, {{ $order->address->state }} {{ $order->address->postal_code }}</p>
                            <p class="mb-0 text-muted">{{ $order->address->country }}</p>
                            @if($order->address->phone)
                            <p class="mb-0 text-muted">{{ $order->address->phone }}</p>
                            @endif
                        </div>
                    </div>
                    
                    @if($order->delivery_instructions)
                    <div class="alert alert-info small">
                        <strong>Delivery Instructions:</strong><br>
                        {{ $order->delivery_instructions }}
                    </div>
                    @endif
                    @else
                    <p class="text-muted">No delivery address available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Information -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Payment Method:</strong><br>
                            <span class="badge bg-secondary">{{ $order->payment_method_display }}</span>
                        </div>
                        <div class="col-sm-6">
                            <strong>Payment Status:</strong><br>
                            <span class="badge {{ $order->payment_status_badge_class }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>
                    </div>
                    
                    @if($order->razorpay_order_id)
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <strong>Razorpay Order ID:</strong><br>
                            <code>{{ $order->razorpay_order_id }}</code>
                        </div>
                        @if($order->razorpay_payment_id)
                        <div class="col-sm-6">
                            <strong>Payment ID:</strong><br>
                            <code>{{ $order->razorpay_payment_id }}</code>
                        </div>
                        @endif
                    </div>
                    @endif
                    
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between">
                            <span>Subtotal:</span>
                            <span>₹{{ number_format($order->total, 2) }}</span>
                        </div>
                        @if($order->discount > 0)
                        <div class="d-flex justify-content-between text-success">
                            <span>Discount:</span>
                            <span>-₹{{ number_format($order->discount, 2) }}</span>
                        </div>
                        @endif
                        @if($order->shipping_cost > 0)
                        <div class="d-flex justify-content-between">
                            <span>Shipping:</span>
                            <span>₹{{ number_format($order->shipping_cost, 2) }}</span>
                        </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between h5">
                            <span>Total:</span>
                            <span>₹{{ number_format($order->grand_total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Tracking</h6>
                </div>
                <div class="card-body">
                    <div class="tracking-timeline">
                        @foreach($trackingSteps as $step)
                        <div class="timeline-item {{ $step['completed'] ? 'completed' : '' }} {{ $step['is_current'] ?? false ? 'current' : '' }}">
                            <div class="timeline-marker">
                                <i class="{{ $step['icon'] }} {{ $step['class'] ?? '' }}"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ $step['title'] }}</h6>
                                <p class="timeline-description">{{ $step['description'] }}</p>
                                @if($step['location'] ?? false)
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> {{ $step['location'] }}
                                </small>
                                @endif
                                @if($step['date'] ?? false)
                                <small class="text-muted d-block">
                                    {{ $step['date']->format('M d, Y H:i A') }}
                                </small>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order Items -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Items ({{ $order->items->count() }})</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->product && $item->product->featured_image)
                                            <img src="{{ asset('storage/' . $item->product->featured_image) }}" 
                                                 alt="{{ $item->product_name }}" 
                                                 class="me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                            <div class="bg-light me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-0">{{ $item->product_name }}</h6>
                                                @if($item->product)
                                                <a href="{{ route('admin.products.edit', $item->product) }}" 
                                                   class="small text-primary">Edit Product</a>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code>{{ $item->product->sku ?? 'N/A' }}</code>
                                    </td>
                                    <td>₹{{ number_format($item->price, 2) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>₹{{ number_format($item->price * $item->quantity, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Subtotal:</th>
                                    <th>₹{{ number_format($order->total, 2) }}</th>
                                </tr>
                                @if($order->discount > 0)
                                <tr>
                                    <th colspan="4" class="text-end text-success">Discount:</th>
                                    <th class="text-success">-₹{{ number_format($order->discount, 2) }}</th>
                                </tr>
                                @endif
                                @if($order->shipping_cost > 0)
                                <tr>
                                    <th colspan="4" class="text-end">Shipping:</th>
                                    <th>₹{{ number_format($order->shipping_cost, 2) }}</th>
                                </tr>
                                @endif
                                <tr>
                                    <th colspan="4" class="text-end">Grand Total:</th>
                                    <th>₹{{ number_format($order->grand_total, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order Notes and History -->
    @if($order->notes || $order->payments->count() > 0)
    <div class="row">
        <div class="col-lg-6">
            @if($order->notes)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Notes</h6>
                </div>
                <div class="card-body">
                    <pre class="mb-0">{{ json_encode($order->notes, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif
        </div>
        
        <div class="col-lg-6">
            @if($order->payments->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment History</h6>
                </div>
                <div class="card-body">
                    @foreach($order->payments as $payment)
                    <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <strong>₹{{ number_format($payment->amount, 2) }}</strong><br>
                            <small class="text-muted">{{ $payment->gateway }} - {{ $payment->payment_method }}</small><br>
                            <small class="text-muted">{{ $payment->created_at->format('M d, Y H:i A') }}</small>
                        </div>
                        <div>
                            <span class="badge {{ $payment->payment_status === 'paid' ? 'bg-success' : ($payment->payment_status === 'failed' ? 'bg-danger' : 'bg-warning') }}">
                                {{ ucfirst($payment->payment_status) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
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
                        <label class="form-label">Current Status</label>
                        <input type="text" class="form-control" value="{{ ucfirst($order->status) }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <select class="form-select" id="newStatus" required>
                            <option value="">Select Status</option>
                            @php
                                $currentStatus = $order->status;
                                $availableStatuses = [];
                                
                                // Professional status flow logic (Amazon/Flipkart style)
                                switch($currentStatus) {
                                    case 'pending':
                                        $availableStatuses = [
                                            'confirmed' => 'Confirmed',
                                            'cancelled' => 'Cancelled'
                                        ];
                                        break;
                                    case 'confirmed':
                                    case 'processing':
                                        $availableStatuses = [
                                            'shipped' => 'Shipped',
                                            'cancelled' => 'Cancelled (with conditions)'
                                        ];
                                        break;
                                    case 'shipped':
                                        $availableStatuses = [
                                            'delivered' => 'Delivered'
                                        ];
                                        break;
                                    case 'delivered':
                                        // Final status - no changes allowed
                                        $availableStatuses = [];
                                        break;
                                    case 'cancelled':
                                        // Final status - no changes allowed
                                        $availableStatuses = [];
                                        break;
                                    default:
                                        $availableStatuses = [
                                            'confirmed' => 'Confirmed',
                                            'cancelled' => 'Cancelled'
                                        ];
                                }
                            @endphp
                            
                            @if(empty($availableStatuses))
                                <option value="">This order status cannot be changed</option>
                            @else
                                @foreach($availableStatuses as $status => $label)
                                    <option value="{{ $status }}">{{ $label }}</option>
                                @endforeach
                            @endif
                        </select>
                        
                        
                        <!-- Professional Status Transition Preview -->
                        <div id="statusTransitionPreview" class="mt-2 p-2 border-start border-primary bg-light d-none">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-arrow-right text-primary me-2"></i>
                                <span id="transitionMessage" class="text-dark"></span>
                            </div>
                        </div>
                        
                        @if(!empty($availableStatuses))
                            <div class="form-text">
                                <i class="fas fa-info-circle text-info"></i>
                                Current status: <strong>{{ ucfirst($currentStatus) }}</strong>
                                - Professional Amazon/Flipkart style transitions
                            </div>
                        @else
                            <div class="form-text text-warning">
                                <i class="fas fa-lock"></i>
                                Order status is final and cannot be changed
                            </div>
                        @endif
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
@endsection

@push('styles')
<style>
.tracking-timeline {
    position: relative;
}

.tracking-timeline::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e3e6f0;
    z-index: 1;
}

.timeline-item {
    position: relative;
    padding-left: 50px;
    margin-bottom: 30px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 40px;
    height: 40px;
    background: white;
    border: 3px solid #e3e6f0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
}

.timeline-item.completed .timeline-marker {
    border-color: #1cc88a;
    background: #1cc88a;
    color: white;
}

.timeline-item.current .timeline-marker {
    border-color: #4e73df;
    background: #4e73df;
    color: white;
    box-shadow: 0 0 0 4px rgba(78, 115, 223, 0.2);
}

.timeline-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.timeline-description {
    margin-bottom: 5px;
    color: #6c757d;
}

.print-only {
    display: none;
}

@media print {
    .no-print {
        display: none !important;
    }
    
    .print-only {
        display: block !important;
    }
    
    .card {
        box-shadow: none !important;
        border: 1px solid #000 !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
let selectedOrderId = {{ $order->id }};

// COD Order Confirmation
function confirmCodOrder(orderId) {
    const modal = new bootstrap.Modal(document.getElementById('codConfirmModal'));
    modal.show();
}

// Update Order Status
function updateOrderStatus(orderId) {
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

// Print Order
function printOrder() {
    window.print();
}

// Event Listeners
document.getElementById('confirmCodBtn').addEventListener('click', function() {
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

// Professional Status Transition Preview Handler
document.getElementById('newStatus').addEventListener('change', function() {
    const selectedStatus = this.value;
    const preview = document.getElementById('statusTransitionPreview');
    const transitionMessage = document.getElementById('transitionMessage');
    
    if (selectedStatus) {
        // Status transition messages (like Amazon/Flipkart)
        const messages = {
            'confirmed': '✅ Order will be marked as confirmed and ready for processing',
            'shipped': '📦 Order will be marked as shipped and customer will be notified',
            'delivered': '🎯 Order will be marked as delivered and completed',
            'cancelled': '❌ Order will be cancelled and cannot be processed further'
        };
        
        transitionMessage.textContent = messages[selectedStatus] || 'Status will be updated';
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
});

document.getElementById('updateStatusBtn').addEventListener('click', function() {
    const status = document.getElementById('newStatus').value;
    const notes = document.getElementById('statusNotes').value;
    
    if (!status) {
        alert('Please select a status');
        return;
    }
    
    // Show loading state
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    this.disabled = true;
    
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
            // Show success message with professional feedback
            alert(`✅ ${data.message}\n${data.transition_message || ''}`);
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
</script>
@endpush