<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $item->product_name }} — Order #{{ $order->order_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <style>
        :root {
            --primary-color: #2563eb;
            --success-color: #059669;
            --danger-color: #dc2626;
            --info-color: #0891b2;
            --light-bg: #f8fafc;
            --border-color: #e2e8f0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        body { background-color: var(--light-bg); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            box-shadow: var(--shadow);
        }

        .page-card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--shadow);
            background: white;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            color: white;
            padding: 2rem;
        }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-pending   { background-color: #fef3c7; color: #92400e; }
        .status-confirmed { background-color: #dbeafe; color: #1e40af; }
        .status-shipped   { background-color: #e0e7ff; color: #5b21b6; }
        .status-delivered { background-color: #d1fae5; color: #065f46; }
        .status-cancelled { background-color: #fee2e2; color: #991b1b; }
        .status-returned  { background-color: #f3e8ff; color: #6d28d9; }

        /* Product detail card */
        .product-card {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
        }

        .product-image-lg {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            flex-shrink: 0;
        }

        /* Timeline */
        .timeline { position: relative; padding: 1.5rem 0; }
        .timeline::before {
            content: '';
            position: absolute;
            left: 25px; top: 0; bottom: 0;
            width: 3px;
            background: #e2e8f0;
            border-radius: 2px;
        }
        .timeline-item {
            position: relative;
            padding-left: 70px;
            margin-bottom: 1.5rem;
            opacity: 0;
            animation: slideIn 0.5s ease forwards;
        }
        .timeline-item:nth-child(1) { animation-delay: 0.1s; }
        .timeline-item:nth-child(2) { animation-delay: 0.2s; }
        .timeline-item:nth-child(3) { animation-delay: 0.3s; }
        .timeline-item:nth-child(4) { animation-delay: 0.4s; }
        .timeline-item:nth-child(5) { animation-delay: 0.5s; }
        .timeline-icon {
            position: absolute;
            left: -22px; top: 0;
            width: 50px; height: 50px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            z-index: 2;
            border: 3px solid white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.12);
        }
        .timeline-item.completed .timeline-icon  { background: linear-gradient(135deg, var(--success-color), #047857); color: white; }
        .timeline-item.current .timeline-icon    { background: linear-gradient(135deg, var(--primary-color), #1e40af); color: white; animation: pulse 2s infinite; }
        .timeline-item.pending .timeline-icon    { background: #f1f5f9; color: #64748b; }
        .timeline-item.cancelled .timeline-icon  { background: linear-gradient(135deg, var(--danger-color), #b91c1c); color: white; }
        .timeline-content {
            background: white;
            padding: 1rem 1.2rem;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.07);
            border-left: 4px solid #e2e8f0;
        }
        .timeline-item.completed .timeline-content { border-left-color: var(--success-color); }
        .timeline-item.current   .timeline-content { border-left-color: var(--primary-color); }
        .timeline-item.cancelled .timeline-content { border-left-color: var(--danger-color); }

        .order-info-table td { padding: 0.6rem 0.5rem; vertical-align: top; }
        .order-info-table td:first-child { color: #6b7280; font-weight: 500; white-space: nowrap; }

        .btn-custom { border-radius: 8px; font-weight: 500; padding: 0.65rem 1.4rem; transition: all 0.25s ease; }

        .animate-fade-in { animation: fadeIn 0.6s ease-out; }

        @keyframes fadeIn  { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes pulse   { 0%,100% { transform: scale(1); } 50% { transform: scale(1.05); } }

        @media (max-width: 576px) {
            .product-image-lg { width: 90px; height: 90px; }
            .page-header { padding: 1.2rem; }
        }
    </style>
</head>
<body>

    <!-- Toast notifications -->
    @if(session('success'))
        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
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
        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999;">
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

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('front.index') }}">
                <i class="fas fa-shopping-bag me-2"></i>ShopCart
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('front.index') }}"><i class="fas fa-home me-1"></i>Home</a>
                <a class="nav-link" href="{{ route('orders.index') }}"><i class="fas fa-receipt me-1"></i>My Orders</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3 animate-fade-in">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">My Orders</a></li>
                <li class="breadcrumb-item"><a href="{{ route('order.details', $order) }}">Order #{{ $order->order_number }}</a></li>
                <li class="breadcrumb-item active">{{ Str::limit($item->product_name, 35) }}</li>
            </ol>
        </nav>

        <!-- Page header -->
        <div class="page-card animate-fade-in">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="h4 mb-1">
                            <i class="fas fa-box-open me-2"></i>Item Details
                        </h1>
                        <p class="mb-0 opacity-90">
                            Order <strong>#{{ $order->order_number }}</strong> &mdash;
                            {{ $order->created_at->format('d M Y') }}
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-2 mt-md-0">
                        <span class="status-badge status-{{ $item->effective_status }}">
                            {{ ucfirst($item->effective_status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Product Info -->
            <div class="p-4">
                <div class="product-card d-flex gap-4 flex-wrap">
                    {{-- Image --}}
                    @if($item->product && $item->product->image)
                        <img src="{{ asset('storage/' . $item->product->image) }}"
                             alt="{{ $item->product_name }}"
                             class="product-image-lg">
                    @else
                        <div class="product-image-lg bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-image fa-3x text-muted"></i>
                        </div>
                    @endif

                    {{-- Details --}}
                    <div class="flex-grow-1">
                        <h5 class="mb-2 {{ $item->effective_status === 'cancelled' ? 'text-decoration-line-through text-muted' : '' }}">
                            {{ $item->product_name }}
                        </h5>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge {{ $item->item_status_badge_class }} px-3 py-2" style="font-size: 0.8rem;">
                                {{ $item->item_status_label }}
                            </span>
                            @if($item->product && !$item->product->is_return)
                                <span class="badge bg-secondary px-3 py-2" style="font-size: 0.8rem;">
                                    <i class="fas fa-ban me-1"></i>Non-Returnable
                                </span>
                            @endif
                        </div>

                        <table class="order-info-table">
                            <tr>
                                <td><i class="fas fa-tag me-2"></i>Unit Price</td>
                                <td class="ps-3 fw-bold">₹{{ number_format($item->price, 2) }}</td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-cubes me-2"></i>Quantity</td>
                                <td class="ps-3">{{ $item->quantity }}</td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-receipt me-2"></i>Item Total</td>
                                <td class="ps-3 fw-bold text-primary fs-5">₹{{ number_format($item->total, 2) }}</td>
                            </tr>
                            @if($item->effective_status === 'cancelled' && $item->cancelled_at)
                                <tr>
                                    <td><i class="fas fa-times-circle me-2 text-danger"></i>Cancelled On</td>
                                    <td class="ps-3 text-danger">{{ \Carbon\Carbon::parse($item->cancelled_at)->format('d M Y, g:i A') }}</td>
                                </tr>
                            @endif
                            @if($item->cancellation_reason && $item->effective_status === 'cancelled')
                                <tr>
                                    <td><i class="fas fa-comment me-2"></i>Reason</td>
                                    <td class="ps-3 fst-italic text-muted">{{ $item->cancellation_reason }}</td>
                                </tr>
                            @endif
                        </table>

                        {{-- Action buttons for THIS item --}}
                        <div class="d-flex flex-wrap gap-2 mt-4">
                            @if($item->canBeCancelled())
                                <form method="POST"
                                      action="{{ route('order.item.cancel', [$order, $item]) }}"
                                      onsubmit="return confirm('Cancel \'{{ addslashes($item->product_name) }}\'?')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-custom">
                                        <i class="fas fa-times me-2"></i>Cancel This Item
                                    </button>
                                </form>
                            @endif

                            @if($item->effective_status === 'delivered' && $item->canBeReturned())
                                <a href="{{ route('order.details', $order) }}#return"
                                   class="btn btn-outline-warning btn-custom">
                                    <i class="fas fa-undo me-2"></i>Return This Item
                                </a>
                            @endif

                            @if(in_array($item->effective_status, ['shipped', 'delivered']))
                                <a href="{{ route('order.track', $order) }}"
                                   class="btn btn-outline-info btn-custom">
                                    <i class="fas fa-map-marker-alt me-2"></i>Track Package
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Tracking Timeline -->
            <div class="col-lg-8">
                <div class="page-card animate-fade-in p-4">
                    <h5 class="mb-4">
                        <i class="fas fa-route me-2 text-primary"></i>Shipment Progress
                    </h5>

                    <div class="timeline">
                        @if(is_array($timeline) && count($timeline) > 0)
                            @foreach($timeline as $step)
                                @php
                                    $tStatus = 'pending';
                                    if ($step['completed']) {
                                        $tStatus = isset($step['is_current']) && $step['is_current'] ? 'current' : 'completed';
                                        if (isset($step['class']) && str_contains($step['class'], 'danger')) {
                                            $tStatus = 'cancelled';
                                        }
                                    } elseif (isset($step['is_current']) && $step['is_current']) {
                                        $tStatus = 'current';
                                    }
                                @endphp
                                <div class="timeline-item {{ $tStatus }}">
                                    <div class="timeline-icon">
                                        <i class="{{ $step['icon'] }}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div>
                                                <h6 class="mb-1 fw-bold">{{ $step['title'] }}</h6>
                                                <p class="mb-0 text-muted small">{{ $step['description'] }}</p>
                                            </div>
                                            @if($step['date'])
                                                <small class="text-muted text-nowrap">
                                                    {{ $step['date']->format('d M, g:i A') }}
                                                </small>
                                            @endif
                                        </div>
                                        @if($tStatus === 'current')
                                            <div class="mt-2 p-2 bg-light rounded">
                                                <small class="text-primary fw-semibold">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    @if($order->status === 'pending')   Your order is being prepared for shipment.
                                                    @elseif($order->status === 'confirmed') Order confirmed and will ship soon.
                                                    @elseif($order->status === 'shipped')  Your package is on the way!
                                                    @endif
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-info-circle text-muted fs-3 mb-2 d-block"></i>
                                <p class="text-muted">No tracking information available yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order + Address sidebar -->
            <div class="col-lg-4">
                <!-- Order Summary -->
                <div class="page-card animate-fade-in p-4 mb-3">
                    <h6 class="mb-3 fw-bold">
                        <i class="fas fa-file-invoice me-2 text-primary"></i>Order Summary
                    </h6>
                    <table class="order-info-table w-100">
                        <tr>
                            <td>Order Number</td>
                            <td class="ps-3 fw-bold text-primary">{{ $order->order_number }}</td>
                        </tr>
                        <tr>
                            <td>Placed On</td>
                            <td class="ps-3">{{ $order->created_at->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td>Payment</td>
                            <td class="ps-3">
                                <span class="badge bg-{{ $order->payment_method === 'cod' ? 'warning text-dark' : 'success' }}">
                                    {{ strtoupper($order->payment_method) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Order Status</td>
                            <td class="ps-3">
                                <span class="badge status-{{ $order->status }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Order Total</td>
                            <td class="ps-3 fw-bold text-primary">₹{{ number_format($order->grand_total, 2) }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Delivery Address -->
                @if($order->address)
                    <div class="page-card animate-fade-in p-4 mb-3">
                        <h6 class="mb-3 fw-bold">
                            <i class="fas fa-map-marker-alt me-2 text-danger"></i>Delivery Address
                        </h6>
                        <div class="text-muted small lh-lg">
                            <strong class="text-dark d-block">{{ $order->address->full_name ?? auth()->user()->name }}</strong>
                            @if($order->address->address_line1)
                                {{ $order->address->address_line1 }}<br>
                            @endif
                            @if($order->address->address_line2)
                                {{ $order->address->address_line2 }}<br>
                            @endif
                            @if($order->address->city)
                                {{ $order->address->city->name ?? $order->address->city }},
                            @endif
                            @if($order->address->state)
                                {{ $order->address->state->name ?? $order->address->state }}
                            @endif
                            @if($order->address->pincode ?? $order->address->zip_code)
                                &nbsp;{{ $order->address->pincode ?? $order->address->zip_code }}
                            @endif
                            @if($order->address->country)
                                <br>{{ $order->address->country->name ?? $order->address->country }}
                            @endif
                            @if($order->address->phone)
                                <br><i class="fas fa-phone me-1"></i>{{ $order->address->phone }}
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Navigation -->
                <div class="page-card animate-fade-in p-4">
                    <h6 class="mb-3 fw-bold"><i class="fas fa-link me-2 text-secondary"></i>Quick Links</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('order.details', $order) }}" class="btn btn-outline-primary btn-custom">
                            <i class="fas fa-list me-2"></i>All Items in This Order
                        </a>
                        <a href="{{ route('order.track', $order) }}" class="btn btn-outline-info btn-custom">
                            <i class="fas fa-truck me-2"></i>Full Tracking Page
                        </a>
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-custom">
                            <i class="fas fa-arrow-left me-2"></i>Back to My Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss toasts after 4s
        document.querySelectorAll('.toast').forEach(el => {
            setTimeout(() => {
                const t = bootstrap.Toast.getOrCreateInstance(el);
                t.hide();
            }, 4000);
        });
    </script>
</body>
</html>
