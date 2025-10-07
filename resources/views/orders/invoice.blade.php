<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Order #{{ $order->order_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .invoice-container { box-shadow: none !important; }
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .invoice-header {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            padding: 2rem;
        }
        
        .invoice-body {
            padding: 2rem;
        }
        
        .company-logo {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .invoice-number {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .billing-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        .invoice-table {
            margin-bottom: 2rem;
        }
        
        .invoice-table th {
            background: #f8f9fa;
            border-top: none;
            font-weight: 600;
        }
        
        .total-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
        }
        
        .invoice-footer {
            background: #f8f9fa;
            padding: 1.5rem;
            text-align: center;
            margin-top: 2rem;
            border-top: 2px solid #e9ecef;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-paid { background-color: #d1fae5; color: #065f46; }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-cancelled { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body class="bg-light">
    <div class="invoice-container">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="company-logo">
                        <i class="fas fa-shopping-bag me-2"></i>ShopCart
                    </div>
                    <p class="mb-0 opacity-90">Your Premium Shopping Destination</p>
                    <small class="opacity-75">
                        123 Business Street, City 12345<br>
                        Phone: +91 1800-123-4567<br>
                        Email: info@shopcart.com
                    </small>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="invoice-number">INVOICE</div>
                    <div class="mb-2">
                        <strong>Invoice #: </strong>INV-{{ $order->order_number }}
                    </div>
                    <div class="mb-2">
                        <strong>Date: </strong>{{ $order->created_at->format('F d, Y') }}
                    </div>
                    <div class="mb-2">
                        <strong>Due Date: </strong>{{ $order->created_at->format('F d, Y') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Body -->
        <div class="invoice-body">
            <!-- Billing Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="billing-info">
                        <h5 class="mb-3">
                            <i class="fas fa-user me-2 text-primary"></i>Bill To:
                        </h5>
                        @if($order->address)
                            <strong>{{ $order->address->full_name }}</strong><br>
                            {{ $order->address->address_line_1 }}<br>
                            @if($order->address->address_line_2)
                                {{ $order->address->address_line_2 }}<br>
                            @endif
                            {{ $order->address->city->name ?? 'N/A' }}, {{ $order->address->state->name ?? 'N/A' }}<br>
                            {{ $order->address->postal_code }}<br>
                            @if($order->address->phone)
                                Phone: {{ $order->address->phone }}
                            @endif
                        @else
                            <em>Address information not available</em>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="billing-info">
                        <h5 class="mb-3">
                            <i class="fas fa-info-circle me-2 text-primary"></i>Order Details:
                        </h5>
                        <strong>Order Number:</strong> {{ $order->order_number }}<br>
                        <strong>Order Date:</strong> {{ $order->created_at->format('F d, Y g:i A') }}<br>
                        <strong>Payment Method:</strong> {{ strtoupper($order->payment_method ?? 'COD') }}<br>
                        <strong>Payment Status:</strong> 
                        <span class="status-badge status-{{ $order->payment_status === 'paid' ? 'paid' : 'pending' }}">
                            {{ ucfirst($order->payment_status ?? 'Pending') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="invoice-table">
                <h5 class="mb-3">
                    <i class="fas fa-box me-2 text-primary"></i>Order Items
                </h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th width="50%">Item Description</th>
                                <th width="15%" class="text-center">Quantity</th>
                                <th width="15%" class="text-end">Unit Price</th>
                                <th width="20%" class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product_name }}</strong>
                                        @if($item->product)
                                            <br><small class="text-muted">SKU: {{ $item->product->sku ?? 'N/A' }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">₹{{ number_format($item->price, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Totals Section -->
            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <div class="total-section">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>₹{{ number_format($order->total, 2) }}</span>
                        </div>
                        @if($order->discount > 0)
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>
                                    Discount:
                                    @if($order->coupon_code)
                                        <small>({{ $order->coupon_code }})</small>
                                    @endif
                                </span>
                                <span>-₹{{ number_format($order->discount, 2) }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span>₹0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (GST):</span>
                            <span>₹0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fs-5 fw-bold">
                            <span>Total Amount:</span>
                            <span>₹{{ number_format($order->grand_total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms & Conditions -->
            <div class="mt-4">
                <h6><i class="fas fa-file-contract me-2 text-primary"></i>Terms & Conditions:</h6>
                <ul class="small text-muted">
                    <li>Payment is due within 30 days of invoice date.</li>
                    <li>Late payments may incur additional charges.</li>
                    <li>Returns accepted within 30 days of delivery.</li>
                    <li>Items must be in original condition for returns.</li>
                    <li>Warranty terms apply as per manufacturer guidelines.</li>
                </ul>
            </div>
        </div>

        <!-- Invoice Footer -->
        <div class="invoice-footer">
            <p class="mb-2">
                <strong>Thank you for your business!</strong>
            </p>
            <p class="mb-0 small text-muted">
                This is a computer-generated invoice. No signature required.<br>
                For any queries, contact us at support@shopcart.com or +91 1800-123-4567
            </p>
        </div>
    </div>

    <!-- Print Button -->
    <div class="text-center mb-4 no-print">
        <button onclick="window.print()" class="btn btn-primary me-2">
            <i class="fas fa-print me-1"></i>Print Invoice
        </button>
        <a href="{{ route('order.details', $order) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Order
        </a>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
</body>
</html>