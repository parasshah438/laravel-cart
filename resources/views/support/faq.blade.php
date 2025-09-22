<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Laravel Cart</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #2196F3 0%, #21CBF3 100%);
            min-height: 100vh;
        }
        
        .card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            border: none;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
        }

        .faq-filter {
            border-radius: 25px;
            padding: 8px 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }

        .faq-filter:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
        }

        .faq-filter.active {
            background: linear-gradient(45deg, #fff, #f8f9fa);
            color: #2196F3;
            border-color: white;
            font-weight: 600;
        }

        .btn-link {
            text-decoration: none !important;
            color: #333;
            font-weight: 600;
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-link:hover {
            background: linear-gradient(45deg, #2196F3, #21CBF3);
            color: white !important;
        }

        .btn-link:not(.collapsed) {
            background: linear-gradient(45deg, #2196F3, #21CBF3);
            color: white;
        }

        .form-control:focus {
            border-color: #2196F3;
            box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
        }

        .btn-primary {
            background: linear-gradient(45deg, #2196F3, #21CBF3);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #1976D2, #0277BD);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(33, 150, 243, 0.3);
        }

        .search-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .accordion-button {
            background: transparent;
            border: none;
            box-shadow: none;
        }

        .accordion-button:not(.collapsed) {
            background: linear-gradient(45deg, #2196F3, #21CBF3);
            color: white;
        }

        .contact-cta {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .icon-badge {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #2196F3, #21CBF3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 15px;
        }

        @media (max-width: 768px) {
            .display-5 {
                font-size: 2rem;
            }
            
            .card-body {
                padding: 1.5rem !important;
            }
            
            .faq-filter {
                margin-bottom: 10px;
                font-size: 14px;
                padding: 6px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Page Header -->
                <div class="text-center mb-5">
                    <h1 class="display-5 fw-bold text-white mb-3">Frequently Asked Questions</h1>
                    <p class="lead text-white-50">Find quick answers to common questions about our products and services.</p>
                </div>

                <!-- Search Bar -->
                <div class="row justify-content-center mb-5">
                    <div class="col-md-8">
                        <div class="search-container p-3">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-transparent border-0">
                                    <i class="fas fa-search text-white"></i>
                                </span>
                                <input type="text" 
                                       class="form-control bg-transparent border-0 text-white" 
                                       placeholder="Search FAQs..."
                                       id="faqSearch"
                                       style="box-shadow: none;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ Categories -->
                <div class="row mb-5">
                    <div class="col-12">
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <button class="btn faq-filter active" data-category="all">
                                All Questions
                            </button>
                            <button class="btn faq-filter" data-category="orders">
                                Orders & Shipping
                            </button>
                            <button class="btn faq-filter" data-category="products">
                                Products
                            </button>
                            <button class="btn faq-filter" data-category="returns">
                                Returns & Exchanges
                            </button>
                            <button class="btn faq-filter" data-category="account">
                                Account
                            </button>
                            <button class="btn faq-filter" data-category="payment">
                                Payment
                            </button>
                        </div>
                    </div>
                </div>

                <!-- FAQ Items -->
                <div class="accordion" id="faqAccordion">
                    
                    <!-- Orders & Shipping -->
                    <div class="card shadow-sm mb-3 faq-item" data-category="orders">
                        <div class="card-header bg-transparent border-0 p-0">
                            <h2 class="mb-0">
                                <button class="btn btn-link text-start w-100 collapsed" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#faq1">
                                    <div class="icon-badge">
                                        <i class="fas fa-shipping-fast"></i>
                                    </div>
                                    How long does shipping take?
                                </button>
                            </h2>
                        </div>
                        <div id="faq1" class="collapse" data-bs-parent="#faqAccordion">
                            <div class="card-body">
                                <p>We offer several shipping options:</p>
                                <ul>
                                    <li><strong>Standard Shipping:</strong> 5-7 business days</li>
                                    <li><strong>Express Shipping:</strong> 2-3 business days</li>
                                    <li><strong>Overnight Shipping:</strong> Next business day</li>
                                </ul>
                                <p>Orders placed before 2 PM EST are typically processed the same day.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-3 faq-item" data-category="orders">
                        <div class="card-header bg-transparent border-0 p-0">
                            <h2 class="mb-0">
                                <button class="btn btn-link text-start w-100 collapsed" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#faq2">
                                    <div class="icon-badge">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    How can I track my order?
                                </button>
                            </h2>
                        </div>
                        <div id="faq2" class="collapse" data-bs-parent="#faqAccordion">
                            <div class="card-body">
                                <p>You can track your order in several ways:</p>
                                <ol>
                                    <li>Log in to your account and go to "My Orders"</li>
                                    <li>Use the tracking number sent to your email</li>
                                    <li>Click the tracking link in your shipping confirmation email</li>
                                </ol>
                                <p>Tracking information is usually available within 24 hours of shipment.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Products -->
                    <div class="card shadow-sm mb-3 faq-item" data-category="products">
                        <div class="card-header bg-transparent border-0 p-0">
                            <h2 class="mb-0">
                                <button class="btn btn-link text-start w-100 collapsed" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#faq3">
                                    <div class="icon-badge">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    Are your products authentic?
                                </button>
                            </h2>
                        </div>
                        <div id="faq3" class="collapse" data-bs-parent="#faqAccordion">
                            <div class="card-body">
                                <p>Yes, all our products are 100% authentic. We source directly from authorized distributors and manufacturers. Each product comes with:</p>
                                <ul>
                                    <li>Official manufacturer warranty</li>
                                    <li>Certificate of authenticity (when applicable)</li>
                                    <li>Original packaging and documentation</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-3 faq-item" data-category="products">
                        <div class="card-header bg-transparent border-0 p-0">
                            <h2 class="mb-0">
                                <button class="btn btn-link text-start w-100 collapsed" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#faq4">
                                    <div class="icon-badge">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    How do product reviews work?
                                </button>
                            </h2>
                        </div>
                        <div id="faq4" class="collapse" data-bs-parent="#faqAccordion">
                            <div class="card-body">
                                <p>Our review system ensures authentic feedback:</p>
                                <ul>
                                    <li>Only verified purchasers can leave reviews</li>
                                    <li>Reviews are moderated for quality and authenticity</li>
                                    <li>You can add photos to your reviews</li>
                                    <li>Reviews help other customers make informed decisions</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Returns & Exchanges -->
                    <div class="card shadow-sm mb-3 faq-item" data-category="returns">
                        <div class="card-header bg-transparent border-0 p-0">
                            <h2 class="mb-0">
                                <button class="btn btn-link text-start w-100 collapsed" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#faq5">
                                    <div class="icon-badge">
                                        <i class="fas fa-undo"></i>
                                    </div>
                                    What is your return policy?
                                </button>
                            </h2>
                        </div>
                        <div id="faq5" class="collapse" data-bs-parent="#faqAccordion">
                            <div class="card-body">
                                <p>We offer a 30-day return policy:</p>
                                <ul>
                                    <li>Items must be in original condition</li>
                                    <li>Original packaging and tags required</li>
                                    <li>Free return shipping for defective items</li>
                                    <li>Refunds processed within 5-7 business days</li>
                                </ul>
                                <p><strong>Note:</strong> Some items like electronics may have different return periods.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Account -->
                    <div class="card shadow-sm mb-3 faq-item" data-category="account">
                        <div class="card-header bg-transparent border-0 p-0">
                            <h2 class="mb-0">
                                <button class="btn btn-link text-start w-100 collapsed" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#faq6">
                                    <div class="icon-badge">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    How do I reset my password?
                                </button>
                            </h2>
                        </div>
                        <div id="faq6" class="collapse" data-bs-parent="#faqAccordion">
                            <div class="card-body">
                                <p>To reset your password:</p>
                                <ol>
                                    <li>Go to the login page</li>
                                    <li>Click "Forgot Password?"</li>
                                    <li>Enter your email address</li>
                                    <li>Check your email for reset instructions</li>
                                    <li>Follow the link to create a new password</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div class="card shadow-sm mb-3 faq-item" data-category="payment">
                        <div class="card-header bg-transparent border-0 p-0">
                            <h2 class="mb-0">
                                <button class="btn btn-link text-start w-100 collapsed" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#faq7">
                                    <div class="icon-badge">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    What payment methods do you accept?
                                </button>
                            </h2>
                        </div>
                        <div id="faq7" class="collapse" data-bs-parent="#faqAccordion">
                            <div class="card-body">
                                <p>We accept the following payment methods:</p>
                                <ul>
                                    <li>Credit Cards (Visa, MasterCard, American Express, Discover)</li>
                                    <li>PayPal</li>
                                    <li>Apple Pay</li>
                                    <li>Google Pay</li>
                                    <li>Bank Transfer (for large orders)</li>
                                </ul>
                                <p>All payments are processed securely with SSL encryption.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact CTA -->
                <div class="text-center mt-5">
                    <div class="contact-cta p-5">
                        <h4 class="text-white fw-bold mb-3">Still have questions?</h4>
                        <p class="text-white-50 mb-4">Our customer support team is here to help!</p>
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <a href="{{ route('contact') }}" class="btn btn-light btn-lg rounded-pill">
                                <i class="fas fa-envelope me-2"></i>Contact Us
                            </a>
                            <a href="{{ route('help') }}" class="btn btn-outline-light btn-lg rounded-pill">
                                <i class="fas fa-question-circle me-2"></i>Help Center
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('faqSearch');
        const filterButtons = document.querySelectorAll('.faq-filter');
        const faqItems = document.querySelectorAll('.faq-item');

        // Search functionality
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            faqItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Category filtering
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const category = this.dataset.category;
                
                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Filter items
                faqItems.forEach(item => {
                    if (category === 'all' || item.dataset.category === category) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                // Clear search
                searchInput.value = '';
            });
        });

        // Smooth animations for cards
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
    </script>
</body>
</html>
