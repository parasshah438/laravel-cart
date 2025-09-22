<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Order Confirmation - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .thank-you-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        
        .thank-you-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 600px;
            width: 100%;
        }
        
        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: successPulse 2s ease-in-out infinite;
            position: relative;
        }
        
        .success-icon::before {
            content: '';
            position: absolute;
            width: 140px;
            height: 140px;
            border: 3px solid rgba(40, 167, 69, 0.3);
            border-radius: 50%;
            animation: ripple 2s ease-out infinite;
        }
        
        .success-icon i {
            font-size: 3rem;
            color: white;
        }
        
        @keyframes successPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes ripple {
            0% {
                transform: scale(0.8);
                opacity: 1;
            }
            100% {
                transform: scale(1.2);
                opacity: 0;
            }
        }
        
        .order-details {
            background: rgba(248, 249, 250, 0.8);
            border-radius: 16px;
            padding: 1.5rem;
            margin: 2rem 0;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-outline-secondary {
            border: 2px solid #6c757d;
            border-radius: 12px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .feature-item:hover {
            background: rgba(255, 255, 255, 0.8);
            transform: translateX(5px);
        }
        
        .feature-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: white;
        }

        .step-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .fade-in {
            animation: fadeInUp 0.8s ease-out;
        }
        
        .fade-in-delay {
            animation: fadeInUp 0.8s ease-out 0.3s both;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .floating-elements {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }
        
        .floating-element {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-element:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .floating-element:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 20%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .floating-element:nth-child(3) {
            width: 100px;
            height: 100px;
            bottom: 10%;
            left: 15%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        @media (max-width: 768px) {
            .thank-you-card {
                margin: 1rem;
                border-radius: 16px;
            }
            
            .success-icon {
                width: 100px;
                height: 100px;
            }
            
            .success-icon i {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Background Elements -->
    <div class="floating-elements">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>

    <div class="thank-you-container">
        <div class="thank-you-card fade-in">
            <div class="card-body p-5 text-center">
                <!-- Success Icon -->
                <div class="success-icon">
                    <i class="bi bi-check-lg"></i>
                </div>
                
                <!-- Main Message -->
                <h1 class="display-5 fw-bold text-dark mb-3">Order Placed Successfully!</h1>
                <p class="lead text-muted mb-4">
                    Thank you for your purchase! Your order has been confirmed and is being processed.
                </p>
                
                <!-- Order Details -->
                <div class="order-details fade-in-delay">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="bi bi-receipt"></i>
                                </div>
                                <div class="text-start">
                                    <small class="text-muted d-block">Order Number</small>
                                    <strong>#{{ strtoupper(uniqid()) }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                                <div class="text-start">
                                    <small class="text-muted d-block">Order Date</small>
                                    <strong>{{ now()->format('M d, Y') }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="bi bi-truck"></i>
                                </div>
                                <div class="text-start">
                                    <small class="text-muted d-block">Estimated Delivery</small>
                                    <strong>{{ now()->addDays(5)->format('M d, Y') }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <div class="text-start">
                                    <small class="text-muted d-block">Confirmation Email</small>
                                    <strong>Sent to your email</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- What's Next Section -->
                <div class="mt-4 fade-in-delay">
                    <h5 class="fw-semibold mb-3">What happens next?</h5>
                    <div class="row g-3 text-start">
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="step-icon">
                                    <span>1</span>
                                </div>
                                <div>
                                    <h6 class="mb-1">Order Processing</h6>
                                    <small class="text-muted">We'll prepare your items for shipment</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="step-icon">
                                    <span>2</span>
                                </div>
                                <div>
                                    <h6 class="mb-1">Shipping</h6>
                                    <small class="text-muted">Your order will be shipped within 2-3 days</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="step-icon">
                                    <span>3</span>
                                </div>
                                <div>
                                    <h6 class="mb-1">Delivery</h6>
                                    <small class="text-muted">Receive your order at your doorstep</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="mt-5 fade-in-delay">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="{{ route('front.index') }}" class="btn btn-primary w-100">
                                <i class="bi bi-arrow-left me-2"></i>
                                Continue Shopping
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="#" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-box-seam me-2"></i>
                                Track Your Order
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Support Section -->
                <div class="mt-4 p-3 bg-light rounded-3 fade-in-delay">
                    <h6 class="mb-2">Need Help?</h6>
                    <p class="small text-muted mb-2">
                        If you have any questions about your order, feel free to contact our support team.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="#" class="text-decoration-none">
                            <i class="bi bi-telephone me-1"></i>
                            Call Support
                        </a>
                        <a href="#" class="text-decoration-none">
                            <i class="bi bi-chat-dots me-1"></i>
                            Live Chat
                        </a>
                        <a href="#" class="text-decoration-none">
                            <i class="bi bi-envelope me-1"></i>
                            Email Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Confetti Animation -->
    <script>
        // Simple confetti effect
        function createConfetti() {
            const colors = ['#667eea', '#764ba2', '#28a745', '#ffc107', '#dc3545'];
            const confettiCount = 50;
            
            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.style.position = 'fixed';
                confetti.style.width = '10px';
                confetti.style.height = '10px';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.top = '-10px';
                confetti.style.zIndex = '1000';
                confetti.style.borderRadius = '50%';
                confetti.style.pointerEvents = 'none';
                
                document.body.appendChild(confetti);
                
                const fallDuration = Math.random() * 3 + 2;
                const horizontalMovement = (Math.random() - 0.5) * 200;
                
                confetti.animate([
                    { transform: 'translateY(-10px) translateX(0px) rotate(0deg)', opacity: 1 },
                    { transform: `translateY(100vh) translateX(${horizontalMovement}px) rotate(360deg)`, opacity: 0 }
                ], {
                    duration: fallDuration * 1000,
                    easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)'
                }).onfinish = () => confetti.remove();
            }
        }
        
        // Trigger confetti on page load
        window.addEventListener('load', () => {
            setTimeout(createConfetti, 500);
        });
        
        // Add click effect to buttons
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple-effect');
                
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
        });
    </script>
    
    <style>
        .ripple-effect {
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
    </style>
</body>
</html>

