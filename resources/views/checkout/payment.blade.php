<!DOCTYPE html>
<html lang="en">
<head>
    <title>Complete Payment - {{ config('app.name') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- Razorpay Checkout Script -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    
    <style>
        .payment-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .payment-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        
        .payment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .payment-body {
            padding: 2rem;
        }
        
        .order-summary {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .btn-pay {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 50px;
            padding: 15px 30px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
            color: white;
        }
        
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e8f5e8;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .payment-methods {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 1rem 0;
            flex-wrap: wrap;
        }
        
        .method-icon {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 10px 15px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .method-icon:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-card">
            <!-- Payment Header -->
            <div class="payment-header">
                <i class="fas fa-credit-card fa-3x mb-3"></i>
                <h2 class="mb-0">Complete Your Payment</h2>
                <p class="mb-0 opacity-75">Order #{{ $order->order_number }}</p>
            </div>
            
            <!-- Payment Body -->
            <div class="payment-body">
                <!-- Order Summary -->
                <div class="order-summary">
                    <h5 class="mb-3">
                        <i class="fas fa-receipt me-2 text-primary"></i>
                        Order Summary
                    </h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Items Total:</span>
                        <span>₹{{ number_format($order->total, 2) }}</span>
                    </div>
                    
                    @if($order->discount > 0)
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Discount:</span>
                        <span>-₹{{ number_format($order->discount, 2) }}</span>
                    </div>
                    @endif
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span>₹{{ number_format($order->shipping_cost, 2) }}</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total Amount:</span>
                        <span class="text-primary">₹{{ number_format($order->grand_total, 2) }}</span>
                    </div>
                </div>
                
                <!-- Delivery Information -->
                <div class="mb-3">
                    <h6 class="fw-bold mb-2">
                        <i class="fas fa-truck me-2 text-success"></i>
                        Delivery Information
                    </h6>
                    <div class="small text-muted">
                        <div><strong>Date:</strong> {{ $order->delivery_date->format('M d, Y') }}</div>
                        <div><strong>Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->shipping_method)) }} Delivery</div>
                        <div><strong>Time:</strong> {{ $order->time_slot }}</div>
                        <div><strong>Address:</strong> {{ $address->address_line_1 }}, {{ $address->city->name ?? '' }}</div>
                    </div>
                </div>
                
                <!-- Payment Methods -->
                <div class="text-center mb-3">
                    <h6 class="fw-bold mb-3">Accepted Payment Methods</h6>
                    <div class="payment-methods">
                        <div class="method-icon">
                            <i class="fab fa-cc-visa fa-lg text-primary"></i>
                        </div>
                        <div class="method-icon">
                            <i class="fab fa-cc-mastercard fa-lg text-warning"></i>
                        </div>
                        <div class="method-icon">
                            <i class="fas fa-university fa-lg text-info"></i>
                        </div>
                        <div class="method-icon">
                            <i class="fas fa-mobile-alt fa-lg text-success"></i>
                        </div>
                        <div class="method-icon">
                            <i class="fas fa-wallet fa-lg text-purple"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Pay Now Button -->
                <div class="d-grid mb-3">
                    <button type="button" class="btn btn-pay btn-lg" id="payNowBtn" onclick="initiatePayment()">
                        <i class="fas fa-lock me-2"></i>
                        Pay ₹{{ number_format($order->grand_total, 2) }} Securely
                    </button>
                </div>
                
                <!-- Security Information -->
                <div class="security-badge">
                    <i class="fas fa-shield-alt text-success me-2"></i>
                    <small class="text-muted">
                        <strong>256-bit SSL Encrypted</strong> - Your payment is completely secure
                    </small>
                </div>
                
                <!-- Cancel Payment -->
                <div class="text-center mt-3">
                    <a href="{{ route('checkout') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Checkout
                    </a>
                </div>
                
                <!-- Powered by Razorpay -->
                <div class="text-center mt-3">
                    <small class="text-muted">
                        Powered by <strong>Razorpay</strong> - India's most trusted payment gateway
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Razorpay Configuration
        const razorpayConfig = @json($razorpayConfig);
        
        // Store order information
        const orderInfo = {
            id: {{ $order->id }},
            order_number: '{{ $order->order_number }}',
            total: {{ $order->grand_total }},
            razorpay_order_id: '{{ $order->razorpay_order_id }}'
        };
        
        console.log('Payment page loaded', { razorpayConfig, orderInfo });
        
        /**
         * Initiate Razorpay payment
         */
        function initiatePayment() {
            try {
                console.log('Initiating Razorpay payment...');
                
                // Show loading
                const payBtn = document.getElementById('payNowBtn');
                payBtn.disabled = true;
                payBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading Payment...';
                
                // Configure Razorpay options
                const options = {
                    "key": razorpayConfig.key,
                    "amount": razorpayConfig.amount,
                    "currency": razorpayConfig.currency,
                    "name": razorpayConfig.name,
                    "description": `Payment for Order #${orderInfo.order_number}`,
                    "image": razorpayConfig.image,
                    "order_id": razorpayConfig.order_id,
                    "prefill": razorpayConfig.prefill,
                    "notes": razorpayConfig.notes,
                    "theme": razorpayConfig.theme,
                    "modal": {
                        "ondismiss": function() {
                            console.log('Payment modal dismissed');
                            resetPayButton();
                        }
                    },
                    "handler": function(response) {
                        console.log('Payment successful', response);
                        handlePaymentSuccess(response);
                    },
                    "error": function(error) {
                        console.error('Payment failed', error);
                        handlePaymentFailure(error);
                    }
                };
                
                console.log('Razorpay options:', options);
                
                // Create Razorpay instance and open
                const rzp = new Razorpay(options);
                rzp.open();
                
                // Reset button after modal opens
                setTimeout(() => {
                    resetPayButton();
                }, 1000);
                
            } catch (error) {
                console.error('Error initiating payment:', error);
                showError('Failed to initialize payment. Please try again.');
                resetPayButton();
            }
        }
        
        /**
         * Handle payment success
         */
        function handlePaymentSuccess(response) {
            console.log('Processing payment success...', response);
            
            showSuccess('Payment successful! Verifying...');
            
            // Submit payment details to backend for verification
            const verificationData = {
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_order_id: response.razorpay_order_id,
                razorpay_signature: response.razorpay_signature,
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            };
            
            fetch('{{ route("payment.razorpay.success") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': verificationData._token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(verificationData)
            })
            .then(response => {
                if (response.redirected) {
                    // If redirected, follow the redirect
                    window.location.href = response.url;
                    return;
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    showSuccess('Payment verified successfully! Redirecting...');
                    
                    // Redirect to thank you page
                    setTimeout(() => {
                        window.location.href = data.redirect_url || '{{ route("checkout.thankyou", ["order" => $order->id]) }}';
                    }, 2000);
                } else {
                    throw new Error(data?.message || 'Payment verification failed');
                }
            })
            .catch(error => {
                console.error('Payment verification error:', error);
                showError('Payment verification failed: ' + error.message);
            });
        }
        
        /**
         * Handle payment failure
         */
        function handlePaymentFailure(error) {
            console.error('Payment failed:', error);
            
            showError('Payment failed: ' + (error.description || error.message || 'Unknown error'));
            
            // Submit failure details to backend
            const failureData = {
                error: error,
                order_id: orderInfo.id,
                razorpay_order_id: orderInfo.razorpay_order_id,
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            };
            
            fetch('{{ route("payment.razorpay.failure") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': failureData._token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(failureData)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Failure logged to backend', data);
            })
            .catch(error => {
                console.error('Error logging payment failure:', error);
            });
            
            resetPayButton();
        }
        
        /**
         * Reset pay button to original state
         */
        function resetPayButton() {
            const payBtn = document.getElementById('payNowBtn');
            payBtn.disabled = false;
            payBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay ₹{{ number_format($order->grand_total, 2) }} Securely';
        }
        
        /**
         * Show success message
         */
        function showSuccess(message) {
            Toastify({
                text: message,
                duration: 4000,
                gravity: "top",
                position: "right",
                style: {
                    background: "#28a745"
                },
                stopOnFocus: true,
            }).showToast();
        }
        
        /**
         * Show error message
         */
        function showError(message) {
            Toastify({
                text: message,
                duration: 6000,
                gravity: "top",
                position: "right",
                style: {
                    background: "#dc3545"
                },
                stopOnFocus: true,
            }).showToast();
        }
        
        // Auto-initiate payment when page loads (optional)
        // Uncomment the line below if you want payment to auto-start
        // document.addEventListener('DOMContentLoaded', initiatePayment);
    </script>
</body>
</html>