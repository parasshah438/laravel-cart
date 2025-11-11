@extends('layouts.admin')

@section('title', 'Return Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Return Management</h1>
            <p class="mb-0 text-muted">Manage customer return requests and process refunds</p>
        </div>
        <div>
            <span class="badge badge-info">{{ $orders->total() }} Total Returns</span>
        </div>
    </div>

    <!-- Status Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="status" class="form-label">Return Status</label>
                    <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                        @foreach($returnStatuses as $status)
                            <option value="{{ $status }}" {{ request('status', 'all') === $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.returns.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-refresh me-1"></i>Reset Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Returns Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Return Requests</h6>
        </div>
        <div class="card-body p-0">
            @if($orders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Return Details</th>
                                <th>Status</th>
                                <th>Requested Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                @php
                                    $returnRequest = $order->notes['return_request'] ?? null;
                                @endphp
                                @if($returnRequest)
                                <tr>
                                    <td>
                                        <div class="fw-bold">#{{ $order->order_number }}</div>
                                        <small class="text-muted">₹{{ number_format($order->grand_total, 2) }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $order->user->name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $order->user->email ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $returnRequest['reason'] ?? 'Unknown')) }}</div>
                                        @if(isset($returnRequest['details']) && !empty($returnRequest['details']))
                                            <small class="text-muted">{{ Str::limit($returnRequest['details'], 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $status = $returnRequest['status'] ?? 'unknown';
                                        @endphp
                                        <span class="badge 
                                            @if($status === 'pending') bg-warning
                                            @elseif($status === 'approved') bg-info  
                                            @elseif($status === 'picked_up') bg-primary
                                            @elseif($status === 'completed') bg-success
                                            @elseif($status === 'rejected') bg-danger
                                            @elseif($status === 'cancelled') bg-secondary
                                            @else bg-secondary @endif">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if(isset($returnRequest['requested_at']))
                                            <div>{{ \Carbon\Carbon::parse($returnRequest['requested_at'])->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($returnRequest['requested_at'])->format('g:i A') }}</small>
                                        @else
                                            <div>N/A</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.returns.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($status === 'pending')
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="updateReturnStatus({{ $order->id }}, 'approved')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="updateReturnStatus({{ $order->id }}, 'rejected')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            @elseif($status === 'approved')
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="updateReturnStatus({{ $order->id }}, 'picked_up')">
                                                    <i class="fas fa-truck"></i> Picked Up
                                                </button>
                                            @elseif($status === 'picked_up')
                                                <button type="button" class="btn btn-sm btn-outline-success" onclick="updateReturnStatus({{ $order->id }}, 'completed')">
                                                    <i class="fas fa-check-circle"></i> Complete
                                                </button>
                                            @elseif($status === 'completed')
                                                @php
                                                    $refundStatus = $order->notes['refund_status']['status'] ?? 'pending';
                                                    $refundMethod = $order->notes['refund_status']['method'] ?? null;
                                                    $upiDetails = $order->notes['refund_status']['upi_details'] ?? null;
                                                    $bankDetails = $order->notes['refund_status']['bank_details'] ?? null;
                                                @endphp
                                                @if($refundStatus === 'pending')
                                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="processRefund({{ $order->id }})">
                                                        <i class="fas fa-money-check-alt"></i> Process Refund
                                                    </button>
                                                @elseif($refundStatus === 'details_submitted')
                                                    <div class="btn-group" role="group">
                                                        @if($refundMethod === 'upi_transfer' && $upiDetails)
                                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#upiRefundModal{{ $order->id }}">
                                                                <i class="fas fa-mobile-alt"></i> UPI: {{ $upiDetails['upi_id'] }}
                                                            </button>
                                                        @elseif($refundMethod === 'bank_transfer' && $bankDetails)
                                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#bankRefundModal{{ $order->id }}">
                                                                <i class="fas fa-university"></i> Bank: {{ $bankDetails['account_number'] }}
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-sm btn-info" onclick="processRefund({{ $order->id }})">
                                                                <i class="fas fa-money-check-alt"></i> {{ ucfirst($refundMethod) }}
                                                            </button>
                                                        @endif
                                                        <button type="button" class="btn btn-sm btn-success" onclick="markRefundProcessing({{ $order->id }})">
                                                            <i class="fas fa-paper-plane"></i> Send Payment
                                                        </button>
                                                    </div>
                                                @elseif($refundStatus === 'processing')
                                                    <div class="btn-group" role="group">
                                                        <span class="badge bg-info">
                                                            <i class="fas fa-spinner fa-spin"></i> Processing
                                                        </span>
                                                        <button type="button" class="btn btn-sm btn-success" onclick="markRefundCompleted({{ $order->id }})">
                                                            <i class="fas fa-check"></i> Mark Completed
                                                        </button>
                                                    </div>
                                                @elseif($refundStatus === 'completed')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> Refunded
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $orders->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Return Requests Found</h5>
                    <p class="text-muted">No return requests match your current filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Status Update Form (Hidden) -->
<form id="statusUpdateForm" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="status" id="newStatus">
    <input type="hidden" name="admin_notes" id="adminNotes">
</form>

<!-- Refund Processing Form (Hidden) -->
<form id="refundForm" method="POST" style="display: none;">
    @csrf
</form>

<!-- Refund Status Update Form (Hidden) -->
<form id="refundStatusForm" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="refund_status" id="refundStatus">
    <input type="hidden" name="transaction_id" id="transactionId">
    <input type="hidden" name="admin_notes" id="refundNotes">
</form>

<!-- UPI Refund Modals -->
@foreach($orders as $order)
    @if(isset($order->notes['return_request']) && isset($order->notes['refund_status']))
        @php
            $refundStatus = $order->notes['refund_status'];
            $upiDetails = $refundStatus['upi_details'] ?? null;
        @endphp
        
        @if($refundStatus['method'] === 'upi_transfer' && $upiDetails)
            <div class="modal fade" id="upiRefundModal{{ $order->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-mobile-alt me-2"></i>UPI Refund Details - Order #{{ $order->order_number }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>Manual UPI Transfer Required</h6>
                                <p class="mb-0">Please send the refund amount to the customer's UPI ID using your business UPI app.</p>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label"><strong>UPI ID</strong></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="{{ $upiDetails['upi_id'] }}" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ $upiDetails['upi_id'] }}')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Account Holder</strong></label>
                                    <input type="text" class="form-control" value="{{ $upiDetails['holder_name'] }}" readonly>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Refund Amount</strong></label>
                                    <input type="text" class="form-control" value="₹{{ number_format($order->grand_total ?? $order->total ?? $order->amount ?? 0, 2) }}" readonly>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Order Number</strong></label>
                                    <input type="text" class="form-control" value="{{ $order->order_number }}" readonly>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <h6><i class="fas fa-list me-2"></i>Steps to Process:</h6>  
                                <ol class="small">
                                    <li>Open your business UPI app (PhonePe/Google Pay Business)</li>
                                    <li>Send ₹{{ number_format($order->total_amount ?? $order->total ?? $order->amount ?? $order->grand_total ?? 0, 2) }} to <strong>{{ $upiDetails['upi_id'] }}</strong></li>
                                    <li>Use reference: "Refund {{ $order->order_number }}"</li>
                                    <li>Copy the transaction ID after successful payment</li>
                                    <li>Click "Mark as Processing" below and enter transaction ID</li>
                                </ol>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-success" onclick="markRefundProcessing({{ $order->id }})">
                                <i class="fas fa-paper-plane me-1"></i>Mark as Processing
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
@endforeach

<script>
function updateReturnStatus(orderId, status) {
    if (confirm(`Are you sure you want to ${status} this return request?`)) {
        const form = document.getElementById('statusUpdateForm');
        form.action = `/admin/returns/${orderId}/update-status`;
        document.getElementById('newStatus').value = status;
        
        // Optional: prompt for admin notes
        if (status === 'rejected') {
            const notes = prompt('Please provide a reason for rejection (optional):');
            document.getElementById('adminNotes').value = notes || '';
        }
        
        form.submit();
    }
}

function processRefund(orderId) {
    if (confirm('Are you sure you want to process the refund for this completed return? This action cannot be undone.')) {
        const form = document.getElementById('refundForm');
        form.action = `/admin/returns/${orderId}/process-refund`;
        form.submit();
    }
}

function markRefundProcessing(orderId) {
    const transactionId = prompt('Enter the UPI transaction ID after sending payment:');
    if (transactionId && transactionId.trim()) {
        const form = document.getElementById('refundStatusForm');
        form.action = `{{ url('admin/returns') }}/${orderId}/update-refund-status`;
        document.getElementById('refundStatus').value = 'processing';
        document.getElementById('transactionId').value = transactionId.trim();
        document.getElementById('refundNotes').value = `UPI payment sent. Transaction ID: ${transactionId.trim()}`;
        form.submit();
    } else if (transactionId !== null) {
        alert('Please enter a valid transaction ID');
    }
}

function markRefundCompleted(orderId) {
    if (confirm('Mark this refund as completed? This confirms the customer has received the money.')) {
        const form = document.getElementById('refundStatusForm');
        form.action = `{{ url('admin/returns') }}/${orderId}/update-refund-status`;
        document.getElementById('refundStatus').value = 'completed';
        document.getElementById('transactionId').value = '';
        document.getElementById('refundNotes').value = 'Refund marked as completed by admin';
        form.submit();
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Create a temporary tooltip or alert
        const button = event.target.closest('button');
        const originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.classList.add('btn-success');
        button.classList.remove('btn-outline-secondary');
        
        setTimeout(() => {
            button.innerHTML = originalHtml;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    }).catch(function(err) {
        alert('Failed to copy: ' + text);
    });
}
</script>
@endsection