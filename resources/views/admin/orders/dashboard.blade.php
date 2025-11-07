@extends('layouts.admin')

@section('title', 'Order Management Dashboard')

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Order Management Dashboard</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Orders Dashboard</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-primary">
            <i class="fas fa-list"></i> All Orders
        </a>
        <a href="{{ route('admin.orders.cod.pending') }}" class="btn btn-warning">
            <i class="fas fa-money-bill-wave"></i> Pending COD
            @if($pendingCodOrders > 0)
                <span class="badge bg-danger ms-1">{{ $pendingCodOrders }}</span>
            @endif
        </a>
        <a href="{{ route('admin.orders.analytics') }}" class="btn btn-info">
            <i class="fas fa-chart-line"></i> Analytics
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- Overview Cards Row -->
    <div class="row mb-4">
        <!-- Total Orders -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Orders
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalOrders) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pending Orders -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Orders
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($pendingOrders) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹{{ number_format($totalRevenue, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-rupee-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Today's Revenue -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Today's Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₹{{ number_format($todayRevenue, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Status Breakdown Row -->
    <div class="row mb-4">
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Order Status Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6 mb-3">
                            <div class="small text-muted">Confirmed</div>
                            <div class="h5 text-success">{{ $confirmedOrders }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="small text-muted">Shipped</div>
                            <div class="h5 text-primary">{{ $shippedOrders }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="small text-muted">Delivered</div>
                            <div class="h5 text-info">{{ $deliveredOrders }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="small text-muted">Cancelled</div>
                            <div class="h5 text-danger">{{ $cancelledOrders }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Methods</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="small text-muted">COD Orders</div>
                            <div class="h5 text-warning">{{ $codOrders }}</div>
                            <small class="text-muted">Pending: {{ $pendingCodOrders }}</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="small text-muted">Online Payment</div>
                            <div class="h5 text-success">{{ $onlineOrders }}</div>
                            <small class="text-muted">Confirmed: {{ $confirmedCodOrders }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Status</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4 mb-3">
                            <div class="small text-muted">Paid</div>
                            <div class="h5 text-success">{{ $paidOrders }}</div>
                        </div>
                        <div class="col-4 mb-3">
                            <div class="small text-muted">Pending</div>
                            <div class="h5 text-warning">{{ $pendingPayments }}</div>
                        </div>
                        <div class="col-4 mb-3">
                            <div class="small text-muted">Failed</div>
                            <div class="h5 text-danger">{{ $failedPayments }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Required Section -->
    @if($pendingCodList->count() > 0 || $readyForShipment->count() > 0)
    <div class="row mb-4">
        <!-- Pending COD Orders -->
        @if($pendingCodList->count() > 0)
        <div class="col-lg-6 mb-4">
            <div class="card shadow border-left-warning">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-exclamation-triangle"></i> Pending COD Orders (Action Required)
                    </h6>
                    <a href="{{ route('admin.orders.cod.pending') }}" class="btn btn-sm btn-outline-warning">View All</a>
                </div>
                <div class="card-body">
                    @foreach($pendingCodList as $order)
                    <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <strong>{{ $order->order_number }}</strong><br>
                            <small class="text-muted">{{ $order->user->name }} - ₹{{ number_format($order->grand_total, 2) }}</small><br>
                            <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-sm btn-success" 
                                    onclick="confirmCodOrder({{ $order->id }})">
                                <i class="fas fa-check"></i> Confirm
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        
        <!-- Ready for Shipment -->
        @if($readyForShipment->count() > 0)
        <div class="col-lg-6 mb-4">
            <div class="card shadow border-left-info">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-shipping-fast"></i> Ready for Shipment
                    </h6>
                    <a href="{{ route('admin.shipments.ready-orders') }}" class="btn btn-sm btn-outline-info">Manage Shipments</a>
                </div>
                <div class="card-body">
                    @foreach($readyForShipment as $order)
                    <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <strong>{{ $order->order_number }}</strong><br>
                            <small class="text-muted">{{ $order->user->name }} - ₹{{ number_format($order->grand_total, 2) }}</small><br>
                            <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
    
    <!-- Recent Orders and Charts -->
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>{{ $order->user->name ?? 'N/A' }}</td>
                                    <td>₹{{ number_format($order->grand_total, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $order->payment_status_badge_class }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ $order->payment_method_display }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $order->status_badge_class }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $order->created_at->format('M d, Y') }}<br>
                                        <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.orders.show', $order) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($order->payment_method === 'cod' && $order->status === 'pending')
                                            <button class="btn btn-outline-success btn-sm" 
                                                    onclick="confirmCodOrder({{ $order->id }})">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No recent orders found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Daily Trends Chart -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Last 7 Days Trends</h6>
                </div>
                <div class="card-body">
                    <canvas id="dailyTrendsChart" height="300"></canvas>
                </div>
            </div>
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
                <p>Are you sure you want to confirm this COD order? This will:</p>
                <ul>
                    <li>Mark the payment as paid (to be collected on delivery)</li>
                    <li>Change order status to confirmed</li>
                    <li>Automatically create a shipment</li>
                </ul>
                <div class="mt-3">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea class="form-control" id="codConfirmNotes" rows="3" 
                              placeholder="Add any notes about this confirmation..."></textarea>
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let selectedOrderId = null;

// Daily Trends Chart
const ctx = document.getElementById('dailyTrendsChart').getContext('2d');
const dailyTrendsData = @json($dailyTrends);

new Chart(ctx, {
    type: 'line',
    data: {
        labels: dailyTrendsData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }),
        datasets: [{
            label: 'Orders',
            data: dailyTrendsData.map(item => item.count),
            borderColor: 'rgb(78, 115, 223)',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// COD Order Confirmation
function confirmCodOrder(orderId) {
    selectedOrderId = orderId;
    const modal = new bootstrap.Modal(document.getElementById('codConfirmModal'));
    modal.show();
}

document.getElementById('confirmCodBtn').addEventListener('click', function() {
    if (!selectedOrderId) return;
    
    const notes = document.getElementById('codConfirmNotes').value;
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
        body: JSON.stringify({ notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.content-wrapper').insertBefore(alertDiv, document.querySelector('.content-wrapper').firstChild);
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('codConfirmModal')).hide();
            
            // Refresh page after 1 second
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error(data.message || 'Failed to confirm order');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error confirming order: ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});
</script>
@endpush