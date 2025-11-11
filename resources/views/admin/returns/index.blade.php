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
</script>
@endsection