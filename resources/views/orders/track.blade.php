<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order #{{ $order->order_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
        :root {
            --primary-color: #2563eb;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            --info-color: #0891b2;
            --light-bg: #f8fafc;
            --border-color: #e2e8f0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            box-shadow: var(--shadow);
        }

        .tracking-card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .tracking-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            color: white;
            padding: 2rem;
        }

        .timeline {
            position: relative;
            padding: 2rem 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 30px;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #e2e8f0;
            border-radius: 2px;
        }

        .timeline-item {
            position: relative;
            padding-left: 80px;
            margin-bottom: 2rem;
            opacity: 0;
            animation: slideIn 0.6s ease forwards;
        }

        .timeline-item:nth-child(1) { animation-delay: 0.1s; }
        .timeline-item:nth-child(2) { animation-delay: 0.2s; }
        .timeline-item:nth-child(3) { animation-delay: 0.3s; }
        .timeline-item:nth-child(4) { animation-delay: 0.4s; }
        .timeline-item:nth-child(5) { animation-delay: 0.5s; }

        .timeline-icon {
            position: absolute;
            left: -30px;
            top: 0;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            z-index: 2;
            border: 4px solid white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .timeline-item.completed .timeline-icon {
            background: linear-gradient(135deg, var(--success-color) 0%, #047857 100%);
            color: white;
        }

        .timeline-item.current .timeline-icon {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            color: white;
            animation: pulse 2s infinite;
        }

        .timeline-item.pending .timeline-icon {
            background: #f1f5f9;
            color: #64748b;
        }

        .timeline-item.cancelled .timeline-icon {
            background: linear-gradient(135deg, var(--danger-color) 0%, #b91c1c 100%);
            color: white;
        }

        .timeline-content {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #e2e8f0;
        }

        .timeline-item.completed .timeline-content {
            border-left-color: var(--success-color);
        }

        .timeline-item.current .timeline-content {
            border-left-color: var(--primary-color);
        }

        .timeline-item.cancelled .timeline-content {
            border-left-color: var(--danger-color);
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-confirmed { background-color: #dbeafe; color: #1e40af; }
        .status-shipped { background-color: #e0e7ff; color: #5b21b6; }
        .status-delivered { background-color: #d1fae5; color: #065f46; }
        .status-cancelled { background-color: #fee2e2; color: #991b1b; }

        .order-summary-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }

        .delivery-address {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
        }

        .btn-custom {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            border: none;
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .animate-fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .tracking-header {
                padding: 1.5rem;
            }
            
            .timeline-item {
                padding-left: 60px;
            }
            
            .timeline::before {
                left: 20px;
            }
            
            .timeline-icon {
                left: -20px;
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('front.index') }}">
                <i class="fas fa-shopping-bag me-2"></i>ShopCart
            </a>
            <div class="navbar-nav ms-auto">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('front.index') }}">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('orders.index') }}">
                            <i class="fas fa-receipt me-1"></i>My Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">
                            <i class="fas fa-map-marker-alt me-1"></i>Track Order
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Back Button -->
        <div class="mb-3 animate-fade-in">
            <a href="{{ route('orders.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>Back to Orders
            </a>
        </div>

        <!-- Tracking Header -->
        <div class="tracking-card animate-fade-in">
            <div class="tracking-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="h3 mb-2">
                            <i class="fas fa-truck me-2"></i>
                            Order #{{ $order->order_number }}
                        </h1>
                        <p class="mb-0 opacity-90">
                            <i class="fas fa-calendar me-2"></i>
                            Placed on {{ $order->created_at->format('F d, Y \a\t g:i A') }}
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <span class="status-badge status-{{ $order->status }}">
                            {{ ucfirst($order->status) }}
                        </span>
                        <div class="mt-2">
                            <h4 class="mb-0">₹{{ number_format($order->grand_total, 2) }}</h4>
                            @if($order->discount > 0)
                                <small class="opacity-90">
                                    <i class="fas fa-tag me-1"></i>Saved ₹{{ number_format($order->discount, 2) }}
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Timeline -->
            <div class="p-4">
                <h5 class="mb-4">
                    <i class="fas fa-route me-2 text-primary"></i>Order Progress
                </h5>
                
                <div class="timeline">
                    @foreach($timeline as $key => $step)
                        @php
                            $status = 'pending';
                            if ($step['completed']) {
                                $status = $order->status === 'cancelled' && $key === 'cancelled' ? 'cancelled' : 'completed';
                            } elseif ($key === strtolower($order->status)) {
                                $status = 'current';
                            }
                        @endphp
                        
                        <div class="timeline-item {{ $status }}">
                            <div class="timeline-icon">
                                <i class="{{ $step['icon'] }}"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1 fw-bold">{{ $step['title'] }}</h6>
                                        <p class="mb-0 text-muted">{{ $step['description'] }}</p>
                                    </div>
                                    @if($step['date'])
                                        <small class="text-muted">
                                            {{ $step['date']->format('M d, Y g:i A') }}
                                        </small>
                                    @endif
                                </div>
                                
                                @if($status === 'current')
                                    <div class="mt-2 p-2 bg-light rounded">
                                        <small class="text-primary fw-bold">
                                            <i class="fas fa-info-circle me-1"></i>
                                            @if($order->status === 'pending')
                                                Your order is being prepared for shipment.
                                            @elseif($order->status === 'confirmed')
                                                Your order has been confirmed and will be shipped soon.
                                            @elseif($order->status === 'shipped')
                                                Your order is on the way! Expected delivery in 2-3 days.
                                            @endif
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Order Items -->
            <div class="col-lg-8">
                <div class="order-summary-card animate-fade-in">
                    <h5 class="mb-3">
                        <i class="fas fa-box me-2 text-primary"></i>
                        Items Ordered ({{ $order->items->count() }})
                    </h5>
                    
                    @foreach($order->items as $item)
                        <div class="d-flex align-items-center p-3 border-bottom">
                            @if($item->product && $item->product->image)
                                <img src="{{ asset('storage/' . $item->product->image) }}" 
                                     alt="{{ $item->product_name }}" 
                                     class="rounded"
                                     style="width: 80px; height: 80px; object-fit: cover; margin-right: 1rem;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center me-3"
                                     style="width: 80px; height: 80px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            @endif
                            
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $item->product_name }}</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        Qty: {{ $item->quantity }} × ₹{{ number_format($item->price, 2) }}
                                    </span>
                                    <span class="fw-bold">₹{{ number_format($item->total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Order Actions -->
                <div class="d-flex gap-2 mt-3 animate-fade-in">
                    <a href="{{ route('order.details', $order) }}" class="btn btn-outline-primary btn-custom">
                        <i class="fas fa-eye me-1"></i>View Full Details
                    </a>
                    
                    @if(in_array($order->status, ['pending', 'confirmed']))
                        <form method="POST" action="{{ route('order.cancel', $order) }}" 
                              class="d-inline"
                              onsubmit="return confirm('Are you sure you want to cancel this order?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-custom">
                                <i class="fas fa-times me-1"></i>Cancel Order
                            </button>
                        </form>
                    @endif
                    
                    @if($order->status === 'delivered')
                        <form method="POST" action="{{ route('order.reorder', $order) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-custom">
                                <i class="fas fa-redo me-1"></i>Reorder
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Admin Status Update (for testing) -->
                @if(auth()->check())
                    <div class="mt-4 p-3 bg-light rounded-3 animate-fade-in">
                        <h6 class="mb-2">
                            <i class="fas fa-tools me-1"></i>Admin Controls (Testing)
                        </h6>
                        <form method="POST" action="{{ route('admin.order.updateStatus', $order) }}" class="d-flex gap-2">
                            @csrf
                            <select name="status" class="form-select form-select-sm">
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit me-1"></i>Update Status
                            </button>
                        </form>
                        <small class="text-muted">This admin panel is for testing purposes</small>
                    </div>
                @endif
            </div>

            <!-- Delivery Information -->
            <div class="col-lg-4">
                <!-- Delivery Address -->
                <div class="order-summary-card animate-fade-in">
                    <h6 class="mb-3">
                        <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                        Delivery Address
                    </h6>
                    
                    @if($order->address)
                        <div class="delivery-address">
                            <strong class="d-block mb-2">{{ $order->address->full_name }}</strong>
                            <div class="mb-1">{{ $order->address->address_line_1 }}</div>
                            @if($order->address->address_line_2)
                                <div class="mb-1">{{ $order->address->address_line_2 }}</div>
                            @endif
                            <div class="mb-1">
                                {{ $order->address->city->name ?? 'N/A' }}, 
                                {{ $order->address->state->name ?? 'N/A' }}
                            </div>
                            <div class="mb-2">{{ $order->address->postal_code }}</div>
                            @if($order->address->phone)
                                <div class="text-muted">
                                    <i class="fas fa-phone me-1"></i>{{ $order->address->phone }}
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-muted">No address information available</p>
                    @endif
                </div>

                <!-- Payment Information -->
                <div class="order-summary-card animate-fade-in">
                    <h6 class="mb-3">
                        <i class="fas fa-credit-card me-2 text-primary"></i>
                        Payment Details
                    </h6>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Payment Method:</span>
                        <span class="fw-bold">{{ strtoupper($order->payment_method ?? 'COD') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Payment Status:</span>
                        <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                            {{ ucfirst($order->payment_status ?? 'Pending') }}
                        </span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-1">
                        <span>Subtotal:</span>
                        <span>₹{{ number_format($order->total, 2) }}</span>
                    </div>
                    @if($order->discount > 0)
                        <div class="d-flex justify-content-between mb-1 text-success">
                            <span>Discount:</span>
                            <span>-₹{{ number_format($order->discount, 2) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between fw-bold fs-5 border-top pt-2">
                        <span>Total:</span>
                        <span>₹{{ number_format($order->grand_total, 2) }}</span>
                    </div>
                </div>

                <!-- Support -->
                <div class="order-summary-card animate-fade-in">
                    <h6 class="mb-3">
                        <i class="fas fa-headset me-2 text-primary"></i>
                        Need Help?
                    </h6>
                    <p class="text-muted mb-3">Have questions about your order?</p>
                    <div class="d-grid">
                        <a href="{{ route('support.index') }}" class="btn btn-outline-primary btn-custom">
                            <i class="fas fa-comments me-1"></i>Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div class="toast show" role="alert">
                <div class="toast-header bg-success text-white">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong class="me-auto">Success</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div class="toast show" role="alert">
                <div class="toast-header bg-danger text-white">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong class="me-auto">Error</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">{{ session('error') }}</div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide toasts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                setTimeout(() => {
                    toast.remove();
                }, 5000);
            });
        });
    </script>
</body>
</html>