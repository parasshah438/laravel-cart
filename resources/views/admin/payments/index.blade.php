@extends('layouts.admin')

@section('title', 'Payment Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Payment Management</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.payments.dashboard') }}" class="btn btn-outline-primary">
                <i class="fas fa-chart-line"></i> Analytics Dashboard
            </a>
            <a href="{{ route('admin.payments.export', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-download"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Gateway</label>
                    <select name="gateway" class="form-select">
                        <option value="">All Gateways</option>
                        <option value="razorpay" {{ request('gateway') == 'razorpay' ? 'selected' : '' }}>Razorpay</option>
                        <option value="cod" {{ request('gateway') == 'cod' ? 'selected' : '' }}>Cash on Delivery</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Method</label>
                    <select name="method" class="form-select">
                        <option value="">All Methods</option>
                        <option value="card" {{ request('method') == 'card' ? 'selected' : '' }}>Card</option>
                        <option value="upi" {{ request('method') == 'upi' ? 'selected' : '' }}>UPI</option>
                        <option value="netbanking" {{ request('method') == 'netbanking' ? 'selected' : '' }}>Net Banking</option>
                        <option value="wallet" {{ request('method') == 'wallet' ? 'selected' : '' }}>Wallet</option>
                        <option value="cod" {{ request('method') == 'cod' ? 'selected' : '' }}>COD</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block">Filter</button>
                </div>
            </form>
            <form method="GET" class="mt-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by Payment ID, Order Number, Customer..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                Payment Records ({{ $payments->total() }} total)
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Payment ID</th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Gateway</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>
                                <strong>{{ $payment->payment_id }}</strong>
                                @if($payment->gateway_payment_id)
                                <br><small class="text-muted">{{ Str::limit($payment->gateway_payment_id, 20) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($payment->order)
                                <a href="{{ route('admin.orders.show', $payment->order) }}" class="text-decoration-none">
                                    {{ $payment->order->order_number }}
                                </a>
                                <br><small class="text-muted">₹{{ number_format($payment->order->grand_total, 2) }}</small>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($payment->user)
                                <strong>{{ $payment->user->name }}</strong>
                                <br><small class="text-muted">{{ $payment->user->email }}</small>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $payment->gateway === 'razorpay' ? 'primary' : 'success' }}">
                                    {{ strtoupper($payment->gateway) }}
                                </span>
                            </td>
                            <td>
                                @if($payment->method)
                                <span class="badge bg-secondary">{{ strtoupper($payment->method) }}</span>
                                @if($payment->payment_method && $payment->payment_method !== $payment->method)
                                <br><small class="text-muted">{{ $payment->payment_method }}</small>
                                @endif
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <strong>₹{{ number_format($payment->amount, 2) }}</strong>
                                <br><small class="text-muted">{{ $payment->currency }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ 
                                    $payment->payment_status === 'paid' ? 'success' : 
                                    ($payment->payment_status === 'failed' ? 'danger' : 
                                    ($payment->payment_status === 'refunded' ? 'info' : 'warning')) 
                                }}">
                                    {{ ucfirst($payment->payment_status) }}
                                </span>
                                @if($payment->payment_status === 'failed' && $payment->failure_reason)
                                <br><small class="text-danger">{{ Str::limit($payment->failure_reason, 30) }}</small>
                                @endif
                            </td>
                            <td>
                                <small>
                                    {{ $payment->created_at->format('M d, Y') }}
                                    <br>{{ $payment->created_at->format('H:i:s') }}
                                </small>
                                @if($payment->paid_at)
                                <br><small class="text-success">Paid: {{ $payment->paid_at->format('M d, H:i') }}</small>
                                @endif
                                @if($payment->failed_at)
                                <br><small class="text-danger">Failed: {{ $payment->failed_at->format('M d, H:i') }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.payments.show', $payment) }}" 
                                       class="btn btn-sm btn-outline-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($payment->order)
                                    <a href="{{ route('admin.orders.show', $payment->order) }}" 
                                       class="btn btn-sm btn-outline-primary" title="View Order">
                                        <i class="fas fa-shopping-cart"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-credit-card fa-3x mb-3"></i>
                                    <h5>No payments found</h5>
                                    <p>Try adjusting your search criteria or filters.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payments->hasPages())
        <div class="card-footer">
            {{ $payments->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection