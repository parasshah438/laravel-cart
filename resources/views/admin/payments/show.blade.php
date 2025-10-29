@extends('layouts.admin')

@section('title', 'Payment Details - ' . $payment->payment_id)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Payment Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Payments</a></li>
                    <li class="breadcrumb-item active">{{ $payment->payment_id }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Payments
            </a>
            @if($payment->order)
            <a href="{{ route('admin.orders.show', $payment->order) }}" class="btn btn-outline-primary">
                <i class="fas fa-shopping-cart"></i> View Order
            </a>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Payment Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Payment ID:</strong></td>
                                    <td>{{ $payment->payment_id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Gateway:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $payment->gateway === 'razorpay' ? 'primary' : 'success' }}">
                                            {{ strtoupper($payment->gateway) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Amount:</strong></td>
                                    <td><strong>₹{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $payment->payment_status === 'paid' ? 'success' : 
                                            ($payment->payment_status === 'failed' ? 'danger' : 
                                            ($payment->payment_status === 'refunded' ? 'info' : 'warning')) 
                                        }}">
                                            {{ ucfirst($payment->payment_status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Method:</strong></td>
                                    <td>
                                        @if($payment->method)
                                        <span class="badge bg-secondary">{{ strtoupper($payment->method) }}</span>
                                        @if($payment->payment_method && $payment->payment_method !== $payment->method)
                                        <br><small class="text-muted">{{ $payment->payment_method }}</small>
                                        @endif
                                        @else
                                        <span class="text-muted">Not specified</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $payment->created_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                @if($payment->gateway_order_id)
                                <tr>
                                    <td><strong>Gateway Order ID:</strong></td>
                                    <td><code>{{ $payment->gateway_order_id }}</code></td>
                                </tr>
                                @endif
                                @if($payment->gateway_payment_id)
                                <tr>
                                    <td><strong>Gateway Payment ID:</strong></td>
                                    <td><code>{{ $payment->gateway_payment_id }}</code></td>
                                </tr>
                                @endif
                                @if($payment->transaction_id)
                                <tr>
                                    <td><strong>Transaction ID:</strong></td>
                                    <td><code>{{ $payment->transaction_id }}</code></td>
                                </tr>
                                @endif
                                @if($payment->paid_at)
                                <tr>
                                    <td><strong>Paid At:</strong></td>
                                    <td class="text-success">{{ $payment->paid_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                                @endif
                                @if($payment->failed_at)
                                <tr>
                                    <td><strong>Failed At:</strong></td>
                                    <td class="text-danger">{{ $payment->failed_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                                @endif
                                @if($payment->failure_reason)
                                <tr>
                                    <td><strong>Failure Reason:</strong></td>
                                    <td class="text-danger">{{ $payment->failure_reason }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Information -->
            @if($payment->order)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Associated Order</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Order Number:</strong></td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $payment->order) }}" class="text-decoration-none">
                                            {{ $payment->order->order_number }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Order Status:</strong></td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($payment->order->status) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Order Total:</strong></td>
                                    <td><strong>₹{{ number_format($payment->order->grand_total, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Order Date:</strong></td>
                                    <td>{{ $payment->order->created_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Subtotal:</strong></td>
                                    <td>₹{{ number_format($payment->order->total, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Discount:</strong></td>
                                    <td>₹{{ number_format($payment->order->discount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Shipping:</strong></td>
                                    <td>₹{{ number_format($payment->order->shipping_cost, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Grand Total:</strong></td>
                                    <td><strong>₹{{ number_format($payment->order->grand_total, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Gateway Response -->
            @if($payment->gateway_response)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Gateway Response</h6>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded"><code>{{ json_encode($payment->gateway_response, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            </div>
            @endif

            <!-- Metadata -->
            @if($payment->metadata)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Metadata</h6>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded"><code>{{ json_encode($payment->metadata, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Customer Information -->
            @if($payment->user)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Customer Information</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="{{ asset('assets/img/default-avatar.png') }}" 
                             class="rounded-circle" width="60" height="60" alt="Avatar">
                    </div>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td>{{ $payment->user->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $payment->user->email }}</td>
                        </tr>
                        @if($payment->user->phone)
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td>{{ $payment->user->phone }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>Joined:</strong></td>
                            <td>{{ $payment->user->created_at->format('M d, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif

            <!-- Billing Details -->
            @if($payment->billing_details)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Billing Details</h6>
                </div>
                <div class="card-body">
                    <address>
                        <strong>{{ $payment->billing_details['name'] ?? 'N/A' }}</strong><br>
                        {{ $payment->billing_details['address_line_1'] ?? '' }}<br>
                        @if(!empty($payment->billing_details['address_line_2']))
                        {{ $payment->billing_details['address_line_2'] }}<br>
                        @endif
                        {{ $payment->billing_details['city'] ?? '' }}, {{ $payment->billing_details['state'] ?? '' }}<br>
                        {{ $payment->billing_details['country'] ?? '' }} - {{ $payment->billing_details['postal_code'] ?? '' }}<br>
                        @if(!empty($payment->billing_details['phone']))
                        <strong>Phone:</strong> {{ $payment->billing_details['phone'] }}<br>
                        @endif
                        @if(!empty($payment->billing_details['email']))
                        <strong>Email:</strong> {{ $payment->billing_details['email'] }}
                        @endif
                    </address>
                </div>
            </div>
            @endif

            <!-- Technical Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Technical Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        @if($payment->ip_address)
                        <tr>
                            <td><strong>IP Address:</strong></td>
                            <td><code>{{ $payment->ip_address }}</code></td>
                        </tr>
                        @endif
                        @if($payment->user_agent)
                        <tr>
                            <td><strong>User Agent:</strong></td>
                            <td><small>{{ Str::limit($payment->user_agent, 50) }}</small></td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $payment->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Updated:</strong></td>
                            <td>{{ $payment->updated_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($payment->order)
                        <a href="{{ route('admin.orders.show', $payment->order) }}" class="btn btn-outline-primary">
                            <i class="fas fa-shopping-cart"></i> View Full Order
                        </a>
                        @endif
                        @if($payment->user)
                        <a href="{{ route('admin.users.show', $payment->user) }}" class="btn btn-outline-info">
                            <i class="fas fa-user"></i> View Customer Profile
                        </a>
                        @endif
                        <a href="{{ route('admin.payments.index', ['search' => $payment->user->email ?? '']) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-search"></i> View Customer Payments
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection