<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - {{ $order->order_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .header .order-number {
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 10px;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .status-badge {
            background: #28a745;
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            display: inline-flex;
            align-items: center;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .order-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .item-row:last-child {
            border-bottom: none;
        }
        .item-info {
            flex: 1;
        }
        .item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }
        .item-details {
            color: #666;
            font-size: 14px;
        }
        .item-price {
            font-weight: 600;
            color: #007bff;
        }
        .summary-section {
            background: #fff;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
        }
        .summary-row.total {
            border-top: 2px solid #007bff;
            font-weight: 600;
            font-size: 18px;
            color: #007bff;
            margin-top: 15px;
            padding-top: 15px;
        }
        .coupon-section {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: center;
        }
        .address-section {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 20px;
            margin: 20px 0;
        }
        .footer {
            background: #343a40;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .social-links {
            margin: 20px 0;
        }
        .social-links a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            font-size: 20px;
        }
        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 8px;
            }
            .header, .content, .footer {
                padding: 20px;
            }
            .item-row {
                flex-direction: column;
                align-items: flex-start;
            }
            .item-price {
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- ✅ PROFESSIONAL HEADER (Amazon Style) -->
        <div class="header">
            <div style="font-size: 32px; margin-bottom: 10px;">🛒</div>
            <h1>Order Confirmed!</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Thank you for your purchase</p>
            <div class="order-number">
                <i class="fas fa-receipt me-2"></i>Order #{{ $order->order_number }}
            </div>
        </div>

        <!-- ✅ MAIN CONTENT -->
        <div class="content">
            <!-- Order Status -->
            <div class="status-badge">
                <i class="fas fa-check-circle me-2"></i>
                Order Confirmed & Processing
            </div>

            <p><strong>Hi {{ $customer->name }},</strong></p>
            <p>Great news! We've received your order and it's being processed. Here are your order details:</p>

            <!-- ✅ ORDER ITEMS SECTION -->
            <div class="order-details">
                <h5 style="margin-bottom: 20px; color: #333;">
                    <i class="fas fa-shopping-bag me-2"></i>Items Ordered ({{ $items->count() }})
                </h5>
                
                @foreach($items as $item)
                <div class="item-row">
                    <div class="item-info">
                        <div class="item-name">{{ $item->product_name }}</div>
                        <div class="item-details">
                            <span class="me-3">Qty: {{ $item->quantity }}</span>
                            <span>₹{{ number_format($item->price, 2) }} each</span>
                        </div>
                    </div>
                    <div class="item-price">
                        ₹{{ number_format($item->total, 2) }}
                    </div>
                </div>
                @endforeach
            </div>

            <!-- ✅ COUPON SECTION (If Applied) -->
            @if($order->coupon_code)
            <div class="coupon-section">
                <div style="font-size: 20px; margin-bottom: 8px;">🎉</div>
                <h6 style="margin: 0; font-weight: 600;">Coupon Applied Successfully!</h6>
                <div style="margin-top: 8px;">
                    <strong>{{ $order->coupon_code }}</strong>
                    @if($order->coupon_title)
                        - {{ $order->coupon_title }}
                    @endif
                </div>
                <div style="font-size: 18px; font-weight: 600; margin-top: 5px;">
                    You saved ₹{{ number_format($order->coupon_discount, 2) }}!
                </div>
            </div>
            @endif

            <!-- ✅ ORDER SUMMARY -->
            <div class="summary-section">
                <h5 style="margin-bottom: 15px; color: #333;">
                    <i class="fas fa-calculator me-2"></i>Order Summary
                </h5>
                
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>₹{{ number_format($order->total, 2) }}</span>
                </div>
                
                @if($order->coupon_discount > 0)
                <div class="summary-row" style="color: #28a745;">
                    <span>
                        <i class="fas fa-tags me-1"></i>Coupon Discount ({{ $order->coupon_code }})
                    </span>
                    <span>-₹{{ number_format($order->coupon_discount, 2) }}</span>
                </div>
                @endif
                
                <div class="summary-row">
                    <span>Shipping</span>
                    <span style="color: #28a745;">FREE</span>
                </div>
                
                <div class="summary-row total">
                    <span>Total Amount</span>
                    <span>₹{{ number_format($order->grand_total, 2) }}</span>
                </div>
            </div>

            <!-- ✅ DELIVERY ADDRESS -->
            <div class="address-section">
                <h5 style="margin-bottom: 15px; color: #333;">
                    <i class="fas fa-map-marker-alt me-2"></i>Delivery Address
                </h5>
                <div>
                    <strong>{{ $address->full_name }}</strong><br>
                    {{ $address->address_line_1 }}<br>
                    @if($address->address_line_2)
                        {{ $address->address_line_2 }}<br>
                    @endif
                    {{ $address->city->name ?? $address->city }}, {{ $address->postal_code }}<br>
                    {{ $address->state->name ?? $address->state }}, {{ $address->country->name ?? $address->country }}<br>
                    @if($address->phone_number)
                        <i class="fas fa-phone me-2"></i>{{ $address->phone_number }}
                    @endif
                </div>
            </div>

            <!-- ✅ NEXT STEPS -->
            <div style="background: #e7f3ff; border: 1px solid #bee5eb; border-radius: 8px; padding: 20px; margin: 20px 0;">
                <h5 style="color: #004085; margin-bottom: 15px;">
                    <i class="fas fa-clock me-2"></i>What happens next?
                </h5>
                <ul style="margin: 0; padding-left: 20px; color: #004085;">
                    <li style="margin-bottom: 8px;">We'll prepare your order for shipping</li>
                    <li style="margin-bottom: 8px;">You'll receive a tracking notification once shipped</li>
                    <li style="margin-bottom: 8px;">Expected delivery: 3-5 business days</li>
                    <li>Cash on Delivery payment will be collected at delivery</li>
                </ul>
            </div>

            <!-- ✅ SUPPORT SECTION -->
            <div style="text-align: center; margin: 30px 0;">
                <p><strong>Need help with your order?</strong></p>
                <p style="margin: 10px 0;">
                    <a href="mailto:support@yourstore.com" style="color: #007bff; text-decoration: none;">
                        <i class="fas fa-envelope me-2"></i>Contact Support
                    </a>
                    <span style="margin: 0 15px;">|</span>
                    <a href="#" style="color: #007bff; text-decoration: none;">
                        <i class="fas fa-phone me-2"></i>Call Us
                    </a>
                </p>
            </div>
        </div>

        <!-- ✅ PROFESSIONAL FOOTER -->
        <div class="footer">
            <h6 style="margin-bottom: 20px;">Thank you for choosing us!</h6>
            <p style="margin-bottom: 20px; opacity: 0.8;">
                We hope you love your purchase. If you have any questions, we're here to help.
            </p>
            
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-whatsapp"></i></a>
            </div>
            
            <div style="border-top: 1px solid rgba(255,255,255,0.2); padding-top: 20px; margin-top: 20px;">
                <p style="margin: 0; font-size: 12px; opacity: 0.7;">
                    © {{ date('Y') }} Your Store Name. All rights reserved.<br>
                    This email was sent to {{ $customer->email }}<br>
                    <a href="#" style="color: rgba(255,255,255,0.7);">Unsubscribe</a> | 
                    <a href="#" style="color: rgba(255,255,255,0.7);">Privacy Policy</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
