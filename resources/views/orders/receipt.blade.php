<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Order #{{ $order->order_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .receipt-container { box-shadow: none !important; margin: 0 !important; }
        }
        
        .receipt-container {
            max-width: 400px;
            margin: 2rem auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
            font-family: 'Courier New', monospace;
        }
        
        .receipt-header {
            background: #2563eb;
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .receipt-body {
            padding: 1.5rem;
        }
        
        .store-name {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .receipt-line {
            border-bottom: 1px dashed #ccc;
            margin: 1rem 0;
            padding-bottom: 1rem;
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .item-name {
            font-weight: bold;
        }
        
        .item-details {
            font-size: 0.9rem;
            color: #666;
            margin-left: 1rem;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 1.1rem;
            margin: 0.5rem 0;
        }
        
        .receipt-footer {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-top: 2px dashed #ccc;
        }
        
        .receipt-number {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="receipt-container">
        <!-- Receipt Header -->
        <div class="receipt-header">
            <div class="store-name">SHOPCART</div>
            <div style="font-size: 0.9rem;">Your Premium Shopping Destination</div>
            <div style="font-size: 0.8rem; margin-top: 0.5rem;">
                123 Business Street, City 12345<br>
                Phone: +91 1800-123-4567
            </div>
        </div>

        <!-- Receipt Body -->
        <div class="receipt-body">
            <!-- Receipt Info -->
            <div class="text-center">
                <div class="receipt-number">RECEIPT</div>
                <div><strong>Receipt #:</strong> RCP-{{ $order->order_number }}</div>
                <div><strong>Date:</strong> {{ $order->created_at->format('d/m/Y H:i:s') }}</div>
                <div><strong>Cashier:</strong> System</div>
            </div>

            <div class="receipt-line"></div>

            <!-- Customer Info -->
            @if($order->address)
                <div>
                    <strong>Customer:</strong><br>
                    {{ $order->address->full_name }}<br>
                    @if($order->address->phone)
                        {{ $order->address->phone }}<br>
                    @endif
                    {{ $order->address->city->name ?? 'N/A' }}, {{ $order->address->state->name ?? 'N/A' }}
                </div>
                <div class="receipt-line"></div>
            @endif

            <!-- Order Items -->
            <div>
                <strong>ITEMS PURCHASED:</strong>
            </div>
            @foreach($order->items as $item)
                <div class="item-row">
                    <div style="flex: 1;">
                        <div class="item-name">{{ $item->product_name }}</div>
                        <div class="item-details">
                            {{ $item->quantity }} x ₹{{ number_format($item->price, 2) }}
                        </div>
                    </div>
                    <div>₹{{ number_format($item->total, 2) }}</div>
                </div>
            @endforeach

            <div class="receipt-line"></div>

            <!-- Totals -->
            <div class="item-row">
                <div>Subtotal:</div>
                <div>₹{{ number_format($order->total, 2) }}</div>
            </div>
            
            @if($order->discount > 0)
                <div class="item-row" style="color: green;">
                    <div>
                        Discount:
                        @if($order->coupon_code)
                            <br><small>({{ $order->coupon_code }})</small>
                        @endif
                    </div>
                    <div>-₹{{ number_format($order->discount, 2) }}</div>
                </div>
            @endif
            
            <div class="item-row">
                <div>Shipping:</div>
                <div>₹0.00</div>
            </div>
            
            <div class="item-row">
                <div>Tax (GST):</div>
                <div>₹0.00</div>
            </div>

            <div class="receipt-line"></div>

            <!-- Final Total -->
            <div class="total-row">
                <div>TOTAL:</div>
                <div>₹{{ number_format($order->grand_total, 2) }}</div>
            </div>

            <div class="receipt-line"></div>

            <!-- Payment Info -->
            <div>
                <strong>PAYMENT DETAILS:</strong><br>
                <div class="item-row">
                    <div>Method:</div>
                    <div>{{ strtoupper($order->payment_method ?? 'COD') }}</div>
                </div>
                <div class="item-row">
                    <div>Status:</div>
                    <div>{{ ucfirst($order->payment_status ?? 'Pending') }}</div>
                </div>
                @if($order->payment_method === 'cod')
                    <div class="item-row">
                        <div>Amount Due:</div>
                        <div>₹{{ number_format($order->grand_total, 2) }}</div>
                    </div>
                @endif
            </div>

            <div class="receipt-line"></div>

            <!-- Order Status -->
            <div>
                <strong>ORDER STATUS:</strong><br>
                <div class="item-row">
                    <div>Current Status:</div>
                    <div>{{ ucfirst($order->status) }}</div>
                </div>
                <div class="item-row">
                    <div>Order #:</div>
                    <div>{{ $order->order_number }}</div>
                </div>
            </div>
        </div>

        <!-- Receipt Footer -->
        <div class="receipt-footer">
            <div style="font-size: 0.9rem; margin-bottom: 1rem;">
                <strong>Thank you for shopping with us!</strong>
            </div>
            
            <div style="font-size: 0.8rem; color: #666;">
                Returns accepted within 30 days<br>
                with original receipt<br><br>
                Track your order at:<br>
                www.shopcart.com/order/{{ $order->id }}/track<br><br>
                Customer Support:<br>
                support@shopcart.com<br>
                +91 1800-123-4567
            </div>
            
            <div style="margin-top: 1rem; font-size: 0.7rem; color: #999;">
                This is a computer-generated receipt<br>
                Generated on: {{ now()->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>

    <!-- Print Button -->
    <div class="text-center mb-4 no-print">
        <button onclick="window.print()" class="btn btn-primary me-2">
            <i class="fas fa-print me-1"></i>Print Receipt
        </button>
        <a href="{{ route('order.details', $order) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Order
        </a>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
</body>
</html>