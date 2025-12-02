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
    <!-- Stripe.js -->
    <script src="https://js.stripe.com/v3/"></script>
    
    <style>
        .payment-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #635bff 0%, #4f46e5 100%);
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
            background: linear-gradient(135deg, #635bff 0%, #4f46e5 100%);
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
            background: linear-gradient(135deg, #635bff 0%, #4f46e5 100%);
            border: none;
            border-radius: 50px;
            padding: 15px 30px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(99, 91, 255, 0.3);
            color: white;
        }
        
        .btn-pay:disabled {
            opacity: 0.6;
            transform: none;
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
            border-color: #635bff;
            transform: translateY(-2px);
        }

        /* Stripe Elements styling */
        .stripe-element {
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            background: white;
            margin-bottom: 20px;
        }

        .stripe-element--focus {
            border-color: #635bff;
            box-shadow: 0 0 0 3px rgba(99, 91, 255, 0.1);
        }

        .stripe-element--invalid {
            border-color: #dc3545;
        }

        .card-errors {
            color: #dc3545;
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-card">
            <!-- Payment Header -->
            <div class="payment-header">
                <i class="fab fa-stripe fa-3x mb-3"></i>
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
                        <span>
                            <i class="fas fa-tag me-1"></i>
                            Discount:
                        </span>
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

                <!-- Stripe Payment Form -->
                <form id="payment-form">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Card Information</label>
                        <div id="card-element" class="stripe-element">
                            <!-- Stripe Elements will create form elements here -->
                        </div>
                        <div id="card-errors" class="card-errors" role="alert"></div>
                    </div>

                    <!-- Customer Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Name on Card</label>
                            <input type="text" class="form-control" id="cardholder-name" value="{{ $user->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="cardholder-email" value="{{ $user->email }}" required>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="payNowBtn" class="btn btn-pay">
                        <i class="fas fa-lock me-2"></i>
                        Pay ₹{{ number_format($order->grand_total, 2) }} Securely
                    </button>
                </form>

                <!-- Payment Methods -->
                <div class="payment-methods">
                    <div class="method-icon">
                        <i class="fab fa-cc-visa fa-2x text-primary"></i>
                    </div>
                    <div class="method-icon">
                        <i class="fab fa-cc-mastercard fa-2x text-danger"></i>
                    </div>
                    <div class="method-icon">
                        <i class="fab fa-cc-amex fa-2x text-info"></i>
                    </div>
                    <div class="method-icon">
                        <i class="fab fa-stripe fa-2x" style="color: #635bff;"></i>
                    </div>
                </div>

                <!-- Security Badge -->
                <div class="security-badge">
                    <i class="fas fa-shield-alt me-2 text-success"></i>
                    <small class="text-muted">
                        Your payment is secured with 256-bit SSL encryption
                    </small>
                </div>

                <!-- Back to Checkout -->
                <div class="text-center mt-3">
                    <a href="{{ route('checkout.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Checkout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Stripe configuration from backend
        const stripeConfig = @json($stripeConfig);
        const orderInfo = {
            id: {{ $order->id }},
            order_number: '{{ $order->order_number }}',
            total: {{ $order->grand_total }},
            payment_intent_id: '{{ $order->stripe_payment_intent_id }}'
        };
        
        console.log('Stripe payment page loaded', { stripeConfig, orderInfo });
        
        // Initialize Stripe
        const stripe = Stripe(stripeConfig.publishable_key);
        const elements = stripe.elements();

        // Create card element
        const cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#424770',
                    '::placeholder': {
                        color: '#aab7c4',
                    },
                },
            },
        });

        cardElement.mount('#card-element');

        // Handle real-time validation errors from the card Element
        cardElement.on('change', ({error}) => {
            const displayError = document.getElementById('card-errors');
            if (error) {
                displayError.textContent = error.message;
            } else {
                displayError.textContent = '';
            }
        });

        // Handle form submission
        const form = document.getElementById('payment-form');
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const payBtn = document.getElementById('payNowBtn');
            payBtn.disabled = true;
            payBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing Payment...';

            try {
                const {error, paymentIntent} = await stripe.confirmCardPayment(stripeConfig.client_secret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name: document.getElementById('cardholder-name').value,
                            email: document.getElementById('cardholder-email').value,
                        },
                    }
                });

                if (error) {
                    // Show error to your customer (e.g., insufficient funds)
                    console.error('Payment failed:', error);
                    showError(error.message);
                    
                    // Reset button
                    payBtn.disabled = false;
                    payBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay ₹{{ number_format($order->grand_total, 2) }} Securely';
                } else {
                    // Payment succeeded
                    console.log('Payment succeeded:', paymentIntent);
                    showSuccess('Payment successful! Redirecting to confirmation page...');
                    
                    // Send success to backend
                    const successData = {
                        payment_intent_id: paymentIntent.id,
                        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    };
                    
                    try {
                        const response = await fetch('{{ route("payment.stripe.success") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': successData._token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(successData)
                        });

                        if (response.ok) {
                            // Redirect to thank you page
                            setTimeout(() => {
                                window.location.href = '{{ route("checkout.thankyou", ["order" => $order->id]) }}';
                            }, 2000);
                        } else {
                            throw new Error('Payment verification failed');
                        }
                    } catch (verifyError) {
                        console.error('Payment verification error:', verifyError);
                        showError('Payment completed but verification failed. Please contact support.');
                    }
                }
            } catch (error) {
                console.error('Payment error:', error);
                showError('Payment failed: ' + error.message);
                
                // Reset button
                payBtn.disabled = false;
                payBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay ₹{{ number_format($order->grand_total, 2) }} Securely';
            }
        });

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
    </script>
</body>
</html>