<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - {{ $order->order_number ?? 'ORD-' . str_pad($order->id ?? 1, 6, '0', STR_PAD_LEFT) }}</title>
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
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: white !important;
            transform: translateY(-1px);
        }

        .order-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .order-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-bottom: 2px solid var(--border-color);
            font-weight: 600;
            padding: 1.25rem 1.5rem;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.05);
            border-color: var(--primary-color);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: white; }
        .status-processing { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; }
        .status-shipped { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; }
        .status-delivered { background: linear-gradient(135deg, #10b981, #059669); color: white; }
        .status-cancelled { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }

        .order-timeline {
            position: relative;
            padding-left: 2rem;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -2rem;
            top: 0.5rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--border-color);
        }

        .timeline-item.active::before {
            background: var(--success-color);
            box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.2);
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            left: -1.75rem;
            top: 1.25rem;
            width: 2px;
            height: calc(100% - 0.75rem);
            background: var(--border-color);
        }

        .timeline-item:last-child::after {
            display: none;
        }

        .price-breakdown {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 12px;
            padding: 1.5rem;
        }

        .total-amount {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .btn-custom {
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(102, 126, 234, 0.4);
        }

        .address-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-left: 4px solid var(--info-color);
        }

        .payment-info {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-left: 4px solid var(--success-color);
        }

        @media (max-width: 768px) {
            .order-header {
                padding: 1.5rem;
                text-align: center;
            }
            
            .product-image {
                width: 60px;
                height: 60px;
            }
            
            .order-timeline {
                padding-left: 1.5rem;
            }
            
            .timeline-item::before {
                left: -1.5rem;
            }
            
            .timeline-item::after {
                left: -1.25rem;
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') ?? '/' }}">
                <i class="fas fa-shopping-bag me-2"></i>ShopCart
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') ?? '/' }}">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-store me-1"></i>Shop
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">
                            <i class="fas fa-receipt me-1"></i>My Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('cart.view') ?? '#' }}">
                            <i class="fas fa-shopping-cart me-1"></i>Cart
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Order Header -->
        <div class="order-header animate-fade-in">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h2 mb-2">
                        <i class="fas fa-receipt me-2"></i>
                        Order #{{ $order->order_number ?? 'ORD-' . str_pad($order->id ?? 1, 6, '0', STR_PAD_LEFT) }}
                    </h1>
                    <p class="mb-0 opacity-90">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Placed on {{ $order->created_at->format('F d, Y \a\t g:i A') ?? 'January 15, 2024 at 2:30 PM' }}
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">
                        <button class="btn btn-light btn-custom" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Print Invoice
                        </button>
                        <a href="#" class="btn btn-outline-light btn-custom">
                            <i class="fas fa-download me-2"></i>Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column - Order Items & Timeline -->
            <div class="col-lg-8">
                <!-- Order Status Timeline -->
                <div class="card mb-4 animate-fade-in">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-truck me-2"></i>Order Tracking
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="order-timeline">
                            <div class="timeline-item active">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Order Placed</h6>
                                        <small class="text-muted">{{ $order->created_at->format('M d, Y - g:i A') ?? 'Jan 15, 2024 - 2:30 PM' }}</small>
                                    </div>
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                            </div>
                            <div class="timeline-item {{ in_array($order->status ?? 'pending', ['processing', 'shipped', 'delivered']) ? 'active' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Order Confirmed</h6>
                                        <small class="text-muted">{{ in_array($order->status ?? 'pending', ['processing', 'shipped', 'delivered']) ? ($order->updated_at->format('M d, Y - g:i A') ?? 'Jan 15, 2024 - 3:15 PM') : 'Pending confirmation' }}</small>
                                    </div>
                                    @if(in_array($order->status ?? 'pending', ['processing', 'shipped', 'delivered']))
                                        <i class="fas fa-check-circle text-success"></i>
                                    @else
                                        <i class="fas fa-clock text-warning"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="timeline-item {{ in_array($order->status ?? 'pending', ['shipped', 'delivered']) ? 'active' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Shipped</h6>
                                        <small class="text-muted">{{ in_array($order->status ?? 'pending', ['shipped', 'delivered']) ? 'Jan 16, 2024 - 10:30 AM' : 'Not shipped yet' }}</small>
                                    </div>
                                    @if(in_array($order->status ?? 'pending', ['shipped', 'delivered']))
                                        <i class="fas fa-check-circle text-success"></i>
                                    @else
                                        <i class="fas fa-clock text-muted"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="timeline-item {{ ($order->status ?? 'pending') === 'delivered' ? 'active' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Delivered</h6>
                                        <small class="text-muted">{{ ($order->status ?? 'pending') === 'delivered' ? 'Jan 18, 2024 - 4:45 PM' : 'Estimated: Jan 20, 2024' }}</small>
                                    </div>
                                    @if(($order->status ?? 'pending') === 'delivered')
                                        <i class="fas fa-check-circle text-success"></i>
                                    @else
                                        <i class="fas fa-clock text-muted"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card animate-fade-in">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-box me-2"></i>Items Ordered ({{ $order->items->count() ?? 3 }})
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <tbody>
                                    @if(isset($order->items) && $order->items->count() > 0)
                                        @foreach($order->items as $item)
                                            <tr>
                                                <td class="p-3" style="width: 100px;">
                                                    @if($item->product && $item->product->media && count($item->product->media))
                                                        <img src="{{ asset('storage/' . $item->product->media[0]->file_path) }}" 
                                                             alt="{{ $item->product->name }}" 
                                                             class="product-image">
                                                    @else
                                                        <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=150&h=150&fit=crop&crop=center" 
                                                             alt="Product Image" 
                                                             class="product-image">
                                                    @endif
                                                </td>
                                                <td class="p-3">
                                                    <h6 class="mb-1 fw-bold">{{ $item->product_name ?? ($item->product->name ?? 'Premium Wireless Headphones') }}</h6>
                                                    <p class="text-muted mb-2 small">{{ $item->product->description ?? 'High-quality wireless headphones with noise cancellation' }}</p>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <span class="badge bg-light text-dark">
                                                            <i class="fas fa-tag me-1"></i>₹{{ number_format($item->price ?? 2999, 2) }}
                                                        </span>
                                                        <span class="badge bg-primary">
                                                            <i class="fas fa-cubes me-1"></i>Qty: {{ $item->quantity ?? 1 }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="p-3 text-end">
                                                    <div class="fw-bold h5 mb-0 text-primary">₹{{ number_format($item->total ?? ($item->price ?? 2999) * ($item->quantity ?? 1), 2) }}</div>
                                                    <small class="text-muted">Total</small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <!-- Sample data for demonstration -->
                                        <tr>
                                            <td class="p-3" style="width: 100px;">
                                                <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=150&h=150&fit=crop&crop=center" 
                                                     alt="Premium Wireless Headphones" 
                                                     class="product-image">
                                            </td>
                                            <td class="p-3">
                                                <h6 class="mb-1 fw-bold">Premium Wireless Headphones</h6>
                                                <p class="text-muted mb-2 small">High-quality wireless headphones with active noise cancellation and 30-hour battery life</p>
                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="badge bg-light text-dark">
                                                        <i class="fas fa-tag me-1"></i>₹2,999.00
                                                    </span>
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-cubes me-1"></i>Qty: 1
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="p-3 text-end">
                                                <div class="fw-bold h5 mb-0 text-primary">₹2,999.00</div>
                                                <small class="text-muted">Total</small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="p-3" style="width: 100px;">
                                                <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=150&h=150&fit=crop&crop=center" 
                                                     alt="Running Shoes" 
                                                     class="product-image">
                                            </td>
                                            <td class="p-3">
                                                <h6 class="mb-1 fw-bold">Professional Running Shoes</h6>
                                                <p class="text-muted mb-2 small">Lightweight running shoes with advanced cushioning technology and breathable mesh upper</p>
                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="badge bg-light text-dark">
                                                        <i class="fas fa-tag me-1"></i>₹4,499.00
                                                    </span>
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-cubes me-1"></i>Qty: 1
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="p-3 text-end">
                                                <div class="fw-bold h5 mb-0 text-primary">₹4,499.00</div>
                                                <small class="text-muted">Total</small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="p-3" style="width: 100px;">
                                                <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=150&h=150&fit=crop&crop=center" 
                                                     alt="Smart Watch" 
                                                     class="product-image">
                                            </td>
                                            <td class="p-3">
                                                <h6 class="mb-1 fw-bold">Smart Fitness Watch</h6>
                                                <p class="text-muted mb-2 small">Advanced fitness tracking watch with heart rate monitor, GPS, and 7-day battery life</p>
                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="badge bg-light text-dark">
                                                        <i class="fas fa-tag me-1"></i>₹8,999.00
                                                    </span>
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-cubes me-1"></i>Qty: 1
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="p-3 text-end">
                                                <div class="fw-bold h5 mb-0 text-primary">₹8,999.00</div>
                                                <small class="text-muted">Total</small>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Order Summary & Details -->
            <div class="col-lg-4">
                <!-- Order Summary -->
                <div class="card mb-4 animate-fade-in">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Order Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span><i class="fas fa-hashtag me-2 text-muted"></i>Order Number</span>
                            <span class="fw-bold">{{ $order->order_number ?? 'ORD-000001' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span><i class="fas fa-calendar me-2 text-muted"></i>Order Date</span>
                            <span>{{ $order->created_at->format('M d, Y') ?? 'Jan 15, 2024' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span><i class="fas fa-info-circle me-2 text-muted"></i>Status</span>
                            <span class="status-badge status-{{ strtolower($order->status ?? 'processing') }}">
                                {{ ucfirst($order->status ?? 'Processing') }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span><i class="fas fa-credit-card me-2 text-muted"></i>Payment Method</span>
                            <span class="badge bg-secondary">{{ strtoupper($order->payment_method ?? 'COD') }}</span>
                        </div>

                        <hr>

                        <div class="price-breakdown">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span>₹{{ number_format(($order->subtotal ?? 16497), 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping</span>
                                <span class="text-success">₹{{ number_format(($order->shipping_cost ?? 0), 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (GST)</span>
                                <span>₹{{ number_format(($order->tax_amount ?? 2969), 2) }}</span>
                            </div>
                            @if(isset($order->discount_amount) && $order->discount_amount > 0)
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>Discount</span>
                                <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                            </div>
                            @endif
                        </div>

                        <div class="total-amount text-center">
                            <div class="h4 mb-0">₹{{ number_format(($order->grand_total ?? 19466), 2) }}</div>
                            <small class="opacity-90">Total Amount</small>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="card mb-4 animate-fade-in address-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-shipping-fast me-2"></i>Shipping Address
                        </h5>
                    </div>
                    <div class="card-body">
                        <address class="mb-0">
                            <strong class="d-block mb-2">
                                <i class="fas fa-user me-2"></i>
                                {{ $order->address->full_name ?? 'John Doe' }}
                            </strong>
                            <div class="mb-2">
                                <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                                {{ $order->address->address_line_1 ?? '123 Main Street, Apartment 4B' }}
                            </div>
                            @if(!empty($order->address->address_line_2 ?? 'Near Central Park'))
                                <div class="mb-2">
                                    <i class="fas fa-location-arrow me-2 text-muted"></i>
                                    {{ $order->address->address_line_2 ?? 'Near Central Park' }}
                                </div>
                            @endif
                            <div class="mb-2">
                                <i class="fas fa-city me-2 text-muted"></i>
                                @if($order->address ?? true)
                                    {{ $order->address->city->name ?? 'Mumbai' }}, {{ $order->address->state->name ?? 'Maharashtra' }} - {{ $order->address->postal_code ?? '400001' }}
                                @else
                                    Mumbai, Maharashtra - 400001
                                @endif
                            </div>
                            <div class="mb-0">
                                <i class="fas fa-phone me-2 text-muted"></i>
                                <a href="tel:{{ $order->address->phone ?? '+91 9876543210' }}" class="text-decoration-none">
                                    {{ $order->address->phone ?? '+91 9876543210' }}
                                </a>
                            </div>
                        </address>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="card animate-fade-in payment-info">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-credit-card me-2"></i>Payment Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                @if(strtolower($order->payment_method ?? 'cod') === 'cod')
                                    <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                                @elseif(strtolower($order->payment_method ?? 'cod') === 'card')
                                    <i class="fas fa-credit-card fa-2x text-primary"></i>
                                @elseif(strtolower($order->payment_method ?? 'cod') === 'upi')
                                    <i class="fas fa-mobile-alt fa-2x text-info"></i>
                                @else
                                    <i class="fas fa-wallet fa-2x text-warning"></i>
                                @endif
                            </div>
                            <div>
                                <h6 class="mb-1">{{ strtoupper($order->payment_method ?? 'COD') }}</h6>
                                <small class="text-muted">
                                    @if(strtolower($order->payment_method ?? 'cod') === 'cod')
                                        Cash on Delivery
                                    @elseif(strtolower($order->payment_method ?? 'cod') === 'card')
                                        Credit/Debit Card
                                    @elseif(strtolower($order->payment_method ?? 'cod') === 'upi')
                                        UPI Payment
                                    @else
                                        Digital Wallet
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="alert alert-success mb-0" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Payment Status:</strong> 
                            {{ $order->payment_status ?? 'Pending' }}
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2 mt-4">
                    <!-- Primary Actions -->
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="{{ route('order.track', $order) }}" class="btn btn-primary-custom btn-custom w-100">
                                <i class="fas fa-map-marker-alt me-2"></i>Track Order
                            </a>
                        </div>
                        <div class="col-6">
                            @if($order->status === 'delivered')
                                <form method="POST" action="{{ route('order.reorder', $order) }}" class="d-inline w-100">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-custom w-100">
                                        <i class="fas fa-redo me-2"></i>Reorder
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('order.reorder', $order) }}" class="btn btn-outline-primary btn-custom w-100">
                                    <i class="fas fa-redo me-2"></i>Reorder Items
                                </a>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Document Downloads -->
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="{{ route('order.invoice', $order) }}" class="btn btn-outline-info btn-custom w-100" target="_blank">
                                <i class="fas fa-file-invoice me-2"></i>Download Invoice
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('order.receipt', $order) }}" class="btn btn-outline-info btn-custom w-100" target="_blank">
                                <i class="fas fa-receipt me-2"></i>Download Receipt
                            </a>
                        </div>
                    </div>
                    
                    <!-- Order Management Actions -->
                    @if($order->status === 'delivered')
                        <div class="row g-2">
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-warning btn-custom w-100" data-bs-toggle="modal" data-bs-target="#returnModal">
                                    <i class="fas fa-undo me-2"></i>Return Order
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary btn-custom w-100" data-bs-toggle="modal" data-bs-target="#exchangeModal">
                                    <i class="fas fa-exchange-alt me-2"></i>Exchange Items
                                </button>
                            </div>
                        </div>
                    @elseif(in_array($order->status, ['pending', 'confirmed']))
                        <form method="POST" action="{{ route('order.cancel', $order) }}" 
                              onsubmit="return confirm('Are you sure you want to cancel this order?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-custom w-100">
                                <i class="fas fa-times me-2"></i>Cancel Order
                            </button>
                        </form>
                    @endif
                    
                    <!-- Support -->
                    <a href="{{ route('support.index') }}" class="btn btn-outline-secondary btn-custom">
                        <i class="fas fa-headset me-2"></i>Contact Support
                    </a>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card animate-fade-in">
                    <div class="card-body text-center py-4">
                        <h5 class="mb-3">
                            <i class="fas fa-question-circle me-2"></i>Need Help?
                        </h5>
                        <p class="text-muted mb-4">Our customer support team is here to help you with any questions about your order.</p>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-phone text-primary me-2"></i>
                                    <span>Call: +91 1800-123-4567</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-envelope text-primary me-2"></i>
                                    <span>Email: support@shopcart.com</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-comments text-primary me-2"></i>
                                    <span>Live Chat: Available 24/7</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth scrolling and enhanced interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Animate elements on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe all cards
            document.querySelectorAll('.card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.6s ease';
                observer.observe(card);
            });

            // Add click effects to buttons
            document.querySelectorAll('.btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    let ripple = document.createElement('span');
                    ripple.classList.add('ripple');
                    this.appendChild(ripple);

                    let x = e.clientX - e.target.offsetLeft;
                    let y = e.clientY - e.target.offsetTop;

                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;

                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });

        // Print functionality
        function printInvoice() {
            window.print();
        }

        // Copy order number
        function copyOrderNumber() {
            const orderNumber = '{{ $order->order_number ?? "ORD-000001" }}';
            navigator.clipboard.writeText(orderNumber).then(function() {
                // Show success message
                const toast = document.createElement('div');
                toast.className = 'toast-notification';
                toast.textContent = 'Order number copied to clipboard!';
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.remove();
                }, 3000);
            });
        }
    </script>

    <style>
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.6);
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            z-index: 9999;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media print {
            .navbar, .btn, .card-header .fas, .animate-fade-in {
                display: none !important;
            }
            
            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
            
            body {
                background: white !important;
            }
        }
    </style>

    <!-- Return Order Modal -->
    <div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnModalLabel">
                        <i class="fas fa-undo me-2"></i>Return Order
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('order.return', $order) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Return Policy:</strong> Items can be returned within 30 days of delivery. 
                            Items must be in original condition with all packaging and tags intact.
                        </div>
                        
                        <div class="mb-3">
                            <label for="returnReason" class="form-label">Reason for Return *</label>
                            <select class="form-select" id="returnReason" name="reason" required>
                                <option value="">Select a reason...</option>
                                <option value="defective">Product is defective/damaged</option>
                                <option value="not_as_described">Product not as described</option>
                                <option value="wrong_item">Received wrong item</option>
                                <option value="size_issue">Size/fit issue</option>
                                <option value="quality_issue">Quality not satisfactory</option>
                                <option value="changed_mind">Changed my mind</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="returnDetails" class="form-label">Additional Details</label>
                            <textarea class="form-control" id="returnDetails" name="details" rows="3" 
                                      placeholder="Please provide additional details about the return..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Items to Return</label>
                            @foreach($order->items as $item)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="items[]" 
                                           value="{{ $item->id }}" id="returnItem{{ $item->id }}" checked>
                                    <label class="form-check-label" for="returnItem{{ $item->id }}">
                                        {{ $item->product_name }} (Qty: {{ $item->quantity }})
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-undo me-1"></i>Submit Return Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Exchange Order Modal -->
    <div class="modal fade" id="exchangeModal" tabindex="-1" aria-labelledby="exchangeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exchangeModalLabel">
                        <i class="fas fa-exchange-alt me-2"></i>Exchange Items
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('order.exchange', $order) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Exchange Policy:</strong> Items can be exchanged within 15 days of delivery. 
                            Exchanges are subject to product availability.
                        </div>
                        
                        <div class="mb-3">
                            <label for="exchangeReason" class="form-label">Reason for Exchange *</label>
                            <select class="form-select" id="exchangeReason" name="reason" required>
                                <option value="">Select a reason...</option>
                                <option value="size_issue">Size/fit issue</option>
                                <option value="color_preference">Color preference</option>
                                <option value="defective">Product is defective</option>
                                <option value="upgrade">Want to upgrade</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="exchangeFor" class="form-label">What would you like to exchange for? *</label>
                            <textarea class="form-control" id="exchangeFor" name="exchange_reason" rows="3" required
                                      placeholder="Please specify what you'd like to exchange for (size, color, model, etc.)..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Items to Exchange</label>
                            @foreach($order->items as $item)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="items[]" 
                                           value="{{ $item->id }}" id="exchangeItem{{ $item->id }}">
                                    <label class="form-check-label" for="exchangeItem{{ $item->id }}">
                                        {{ $item->product_name }} (Qty: {{ $item->quantity }})
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-exchange-alt me-1"></i>Submit Exchange Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>