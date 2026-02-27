<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Order History</title>
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

        .order-card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px -8px rgba(0, 0, 0, 0.15);
        }

        .order-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 12px 12px 0 0;
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .status-badge {
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-confirmed { background-color: #dbeafe; color: #1e40af; }
        .status-shipped { background-color: #e0e7ff; color: #5b21b6; }
        .status-delivered { background-color: #d1fae5; color: #065f46; }
        .status-cancelled { background-color: #fee2e2; color: #991b1b; }

        .order-item {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item.bg-light {
            background-color: #f8fafc !important;
        }

        .product-image {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
        }

        .item-actions .btn {
            white-space: nowrap;
        }

        @media (max-width: 576px) {
            .item-actions {
                width: 100%;
                margin-top: 0.5rem;
            }
        }

        .btn-custom {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            transition: all 0.3s ease;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            border: none;
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        }

        .search-filters {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .order-header {
                padding: 1rem;
            }
            
            .order-card {
                margin-bottom: 1rem;
            }
            
            .search-filters {
                padding: 1rem;
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
                        <a class="nav-link active" href="{{ route('orders.index') }}">
                            <i class="fas fa-receipt me-1"></i>My Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('cart.view') }}">
                            <i class="fas fa-shopping-cart me-1"></i>Cart
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('wishlist.index') }}">
                            <i class="fas fa-heart me-1"></i>Wishlist
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in">
            <div>
                <h1 class="h2 mb-2">
                    <i class="fas fa-receipt me-2 text-primary"></i>My Orders
                </h1>
                <p class="text-muted mb-0">Track and manage your orders</p>
            </div>
            <div class="d-none d-md-block">
                <span class="badge bg-primary fs-6">{{ $orders->total() }} Total Orders</span>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="search-filters animate-fade-in">
            <form method="GET" action="{{ route('orders.index') }}">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   name="search" 
                                   placeholder="Search by order number..." 
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="status" onchange="this.form.submit()">
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Orders</option>
                            @foreach($orderStatuses as $status)
                                @if($status !== 'all')
                                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Orders List -->
        @if($orders->count() > 0)
            @foreach($orders as $order)
                <div class="order-card animate-fade-in">
                    <!-- Order Header -->
                    <div class="order-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center">
                                    <h5 class="mb-2 mb-md-0 me-md-3">
                                        <i class="fas fa-hashtag me-1"></i>{{ $order->order_number }}
                                    </h5>
                                    <span class="status-badge status-{{ $order->status }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        Placed on {{ $order->created_at->format('M d, Y \a\t g:i A') }}
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <h5 class="mb-0 text-primary">
                                    <i class="fas fa-rupee-sign me-1"></i>{{ number_format($order->grand_total, 2) }}
                                </h5>
                                @if($order->discount > 0)
                                    <small class="text-success">
                                        <i class="fas fa-tag me-1"></i>Saved ₹{{ number_format($order->discount, 2) }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Order Items — Amazon style: each item has its own status + actions -->
                    <div class="card-body p-0">
                        @foreach($order->items as $item)
                            @php $item->setRelation('order', $order); @endphp
                            <div class="order-item {{ $item->effective_status === 'cancelled' ? 'bg-light opacity-75' : '' }}"
                                 style="flex-wrap: wrap; gap: 0.5rem;">

                                {{-- Product image --}}
                                @if($item->product && $item->product->image)
                                    <img src="{{ asset('storage/' . $item->product->image) }}"
                                         alt="{{ $item->product_name }}"
                                         class="product-image">
                                @else
                                    <div class="product-image bg-light d-flex align-items-center justify-content-center flex-shrink-0">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif

                                {{-- Product info + item actions --}}
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                                        {{-- Name & price --}}
                                        <div>
                                            <h6 class="mb-1 {{ $item->effective_status === 'cancelled' ? 'text-decoration-line-through text-muted' : '' }}">
                                                {{ $item->product_name }}
                                            </h6>
                                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                                <small class="text-muted">
                                                    Qty: {{ $item->quantity }} × ₹{{ number_format($item->price, 2) }}
                                                </small>
                                                <span class="fw-bold text-primary">
                                                    ₹{{ number_format($item->total, 2) }}
                                                </span>
                                            </div>
                                            {{-- Item status badge --}}
                                            <div class="d-flex flex-wrap gap-1">
                                                <span class="badge {{ $item->item_status_badge_class }}">
                                                    {{ $item->item_status_label }}
                                                </span>
                                                @if($item->product && !$item->product->is_return)
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-ban me-1"></i>Non-Returnable
                                                    </span>
                                                @endif
                                            </div>
                                            @if($item->effective_status === 'cancelled' && $item->cancelled_at)
                                                <small class="text-danger d-block mt-1">
                                                    <i class="fas fa-times-circle me-1"></i>
                                                    Cancelled {{ \Carbon\Carbon::parse($item->cancelled_at)->format('M d, Y') }}
                                                </small>
                                            @endif
                                        </div>

                                        {{-- Per-item action buttons --}}
                                        <div class="d-flex flex-wrap gap-1 align-items-start item-actions">
                                            {{-- View Details — always shown per item --}}
                                            <a href="{{ route('order.item.detail', [$order, $item]) }}"
                                               class="btn btn-outline-primary btn-sm"
                                               style="font-size: 0.75rem; padding: 0.25rem 0.6rem;">
                                                <i class="fas fa-eye me-1"></i>View Details
                                            </a>

                                            @if($item->canBeCancelled())
                                                <form method="POST"
                                                      action="{{ route('order.item.cancel', [$order, $item]) }}"
                                                      onsubmit="return confirm('Cancel \'{{ addslashes($item->product_name) }}\'?')">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-outline-danger btn-sm"
                                                            style="font-size: 0.75rem; padding: 0.25rem 0.6rem;">
                                                        <i class="fas fa-times me-1"></i>Cancel
                                                    </button>
                                                </form>
                                            @endif

                                            @if($item->effective_status === 'delivered' && $item->canBeReturned())
                                                <a href="{{ route('order.details', $order) }}#return"
                                                   class="btn btn-outline-warning btn-sm"
                                                   style="font-size: 0.75rem; padding: 0.25rem 0.6rem;">
                                                    <i class="fas fa-undo me-1"></i>Return
                                                </a>
                                            @endif

                                            @if(in_array($item->effective_status, ['shipped', 'delivered']))
                                                <a href="{{ route('order.track', $order) }}"
                                                   class="btn btn-outline-info btn-sm"
                                                   style="font-size: 0.75rem; padding: 0.25rem 0.6rem;">
                                                    <i class="fas fa-map-marker-alt me-1"></i>Track
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Order-level footer actions -->
                    <div class="card-footer bg-transparent p-3">
                        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                            <div class="d-flex flex-wrap gap-2">
                                @if($order->status === 'delivered')
                                    <form method="POST" action="{{ route('order.reorder', $order) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-custom btn-sm">
                                            <i class="fas fa-redo me-1"></i>Reorder All
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('order.invoice', $order) }}"
                                   class="btn btn-outline-secondary btn-custom btn-sm" target="_blank">
                                    <i class="fas fa-file-invoice me-1"></i>Invoice
                                </a>
                            </div>

                            @if($order->address)
                                <div class="text-end">
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $order->address->city->name ?? 'N/A' }}, {{ $order->address->state->name ?? 'N/A' }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="empty-state animate-fade-in">
                <i class="fas fa-receipt"></i>
                <h3 class="mt-3 mb-2">No Orders Found</h3>
                <p class="text-muted mb-4">
                    @if(request('search') || request('status') !== 'all')
                        No orders match your search criteria. Try adjusting your filters.
                    @else
                        You haven't placed any orders yet. Start shopping to see your orders here!
                    @endif
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    @if(request('search') || request('status') !== 'all')
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-custom">
                            <i class="fas fa-times me-1"></i>Clear Filters
                        </a>
                    @endif
                    <a href="{{ route('front.index') }}" class="btn btn-primary-custom">
                        <i class="fas fa-shopping-bag me-1"></i>Start Shopping
                    </a>
                </div>
            </div>
        @endif
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