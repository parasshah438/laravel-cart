<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Laravel Cart</title>
    
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            border: none;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.15) !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #5a6fd8, #6a42a0);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .bg-primary.bg-opacity-10 {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1)) !important;
        }

        .bg-success.bg-opacity-10 {
            background: linear-gradient(135deg, rgba(25, 135, 84, 0.1), rgba(16, 100, 65, 0.1)) !important;
        }

        .bg-info.bg-opacity-10 {
            background: linear-gradient(135deg, rgba(13, 202, 240, 0.1), rgba(8, 145, 178, 0.1)) !important;
        }

        .contact-header {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            backdrop-filter: blur(10px);
            border-radius: 20px 20px 0 0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .floating-label {
            position: relative;
        }

        .floating-label input:focus + label,
        .floating-label input:not(:placeholder-shown) + label,
        .floating-label select:focus + label,
        .floating-label textarea:focus + label,
        .floating-label textarea:not(:placeholder-shown) + label {
            transform: translateY(-25px) scale(0.85);
            color: #667eea;
        }

        .floating-label label {
            position: absolute;
            top: 12px;
            left: 15px;
            transition: all 0.3s ease;
            pointer-events: none;
            background: white;
            padding: 0 5px;
        }

        .alert {
            border-radius: 15px;
            border: none;
        }

        .contact-info-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
        }

        .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        @media (max-width: 768px) {
            .display-5 {
                font-size: 2rem;
            }
            
            .card-body {
                padding: 2rem !important;
            }
            
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                background-attachment: fixed;
            }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Page Header -->
                <div class="text-center mb-5">
                    <h1 class="display-5 fw-bold text-white mb-3">Contact Us</h1>
                    <p class="lead text-white-50">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
                </div>

                <!-- Contact Form Card -->
                <div class="card shadow-lg">
                    <div class="contact-header p-4 text-center">
                        <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                        <h3 class="text-primary fw-bold">Get In Touch</h3>
                    </div>
                    
                    <div class="card-body p-5">
                        
                        <!-- Success Message -->
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Error Message -->
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('contact.submit') }}" method="POST" id="contactForm" novalidate>
                            @csrf
                            
                            <div class="row">
                                <!-- Name Field -->
                                <div class="col-md-6 mb-4">
                                    <label for="name" class="form-label fw-semibold">
                                        <i class="fas fa-user text-primary me-2"></i>Full Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg rounded-3 @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           placeholder="Enter your full name"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Email Field -->
                                <div class="col-md-6 mb-4">
                                    <label for="email" class="form-label fw-semibold">
                                        <i class="fas fa-envelope text-primary me-2"></i>Email Address <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" 
                                           class="form-control form-control-lg rounded-3 @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email') }}" 
                                           placeholder="Enter your email address"
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <!-- Phone Field -->
                                <div class="col-md-6 mb-4">
                                    <label for="phone" class="form-label fw-semibold">
                                        <i class="fas fa-phone text-primary me-2"></i>Phone Number
                                    </label>
                                    <input type="tel" 
                                           class="form-control form-control-lg rounded-3 @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone') }}" 
                                           placeholder="Enter your phone number">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Subject Field -->
                                <div class="col-md-6 mb-4">
                                    <label for="subject" class="form-label fw-semibold">
                                        <i class="fas fa-tag text-primary me-2"></i>Subject <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-lg rounded-3 @error('subject') is-invalid @enderror" 
                                            id="subject" 
                                            name="subject" 
                                            required>
                                        <option value="">Select a subject</option>
                                        <option value="General Inquiry" {{ old('subject') == 'General Inquiry' ? 'selected' : '' }}>General Inquiry</option>
                                        <option value="Product Support" {{ old('subject') == 'Product Support' ? 'selected' : '' }}>Product Support</option>
                                        <option value="Order Issue" {{ old('subject') == 'Order Issue' ? 'selected' : '' }}>Order Issue</option>
                                        <option value="Return/Exchange" {{ old('subject') == 'Return/Exchange' ? 'selected' : '' }}>Return/Exchange</option>
                                        <option value="Billing Question" {{ old('subject') == 'Billing Question' ? 'selected' : '' }}>Billing Question</option>
                                        <option value="Technical Issue" {{ old('subject') == 'Technical Issue' ? 'selected' : '' }}>Technical Issue</option>
                                        <option value="Partnership" {{ old('subject') == 'Partnership' ? 'selected' : '' }}>Partnership</option>
                                        <option value="Feedback" {{ old('subject') == 'Feedback' ? 'selected' : '' }}>Feedback</option>
                                        <option value="Other" {{ old('subject') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Message Field -->
                            <div class="mb-4">
                                <label for="message" class="form-label fw-semibold">
                                    <i class="fas fa-comment text-primary me-2"></i>Message <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control form-control-lg rounded-3 @error('message') is-invalid @enderror" 
                                          id="message" 
                                          name="message" 
                                          rows="6" 
                                          placeholder="Please describe your inquiry in detail..."
                                          required>{{ old('message') }}</textarea>
                                <div class="form-text">
                                    <small class="text-muted">
                                        <span id="messageCount">0</span>/2000 characters
                                    </small>
                                </div>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg fw-semibold" id="submitBtn">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    <span class="btn-text">Send Message</span>
                                </button>
                            </div>

                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Your information is secure and will never be shared with third parties.
                                </small>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="row mt-5">
                    <div class="col-md-4 mb-4">
                        <div class="contact-info-card text-center h-100 p-4">
                            <div class="icon-circle">
                                <i class="fas fa-envelope fa-2x"></i>
                            </div>
                            <h5 class="text-white fw-bold">Email Us</h5>
                            <p class="text-white-50">support@laravel-cart.com</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="contact-info-card text-center h-100 p-4">
                            <div class="icon-circle">
                                <i class="fas fa-phone fa-2x"></i>
                            </div>
                            <h5 class="text-white fw-bold">Call Us</h5>
                            <p class="text-white-50">+1 (555) 123-4567</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="contact-info-card text-center h-100 p-4">
                            <div class="icon-circle">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                            <h5 class="text-white fw-bold">Business Hours</h5>
                            <p class="text-white-50">Mon-Fri: 9AM-6PM EST</p>
                        </div>
                    </div>
                </div>

                <!-- FAQ Link -->
                <div class="text-center mt-4">
                    <div class="contact-info-card p-4">
                        <p class="text-white mb-3">
                            Looking for quick answers? Check our 
                            <a href="{{ route('faq') }}" class="text-warning text-decoration-none fw-semibold">Frequently Asked Questions</a>
                        </p>
                        <a href="{{ route('help') }}" class="btn btn-outline-light rounded-pill me-2">
                            <i class="fas fa-question-circle me-2"></i>Help Center
                        </a>
                        <a href="{{ route('faq') }}" class="btn btn-outline-light rounded-pill">
                            <i class="fas fa-list me-2"></i>FAQ
                        </a>
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
        const form = document.getElementById('contactForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const messageTextarea = document.getElementById('message');
        const messageCount = document.getElementById('messageCount');

        // Character counter for message
        function updateMessageCount() {
            const count = messageTextarea.value.length;
            messageCount.textContent = count;
            
            if (count > 2000) {
                messageCount.classList.add('text-danger');
            } else if (count > 1800) {
                messageCount.classList.remove('text-danger');
                messageCount.classList.add('text-warning');
            } else {
                messageCount.classList.remove('text-danger', 'text-warning');
            }
        }

        messageTextarea.addEventListener('input', updateMessageCount);
        updateMessageCount(); // Initial count

        // Form submission handling
        form.addEventListener('submit', function(e) {
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><span class="btn-text">Sending...</span>';
            
            // Remove any existing validation errors
            const invalidInputs = form.querySelectorAll('.is-invalid');
            invalidInputs.forEach(input => {
                input.classList.remove('is-invalid');
            });

            // Basic client-side validation
            let isValid = true;
            const requiredFields = ['name', 'email', 'subject', 'message'];
            
            requiredFields.forEach(fieldName => {
                const field = form.querySelector(`[name="${fieldName}"]`);
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });

            // Email validation
            const emailField = form.querySelector('[name="email"]');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailField.value && !emailRegex.test(emailField.value)) {
                emailField.classList.add('is-invalid');
                isValid = false;
            }

            // Message length validation
            if (messageTextarea.value.length < 10 || messageTextarea.value.length > 2000) {
                messageTextarea.classList.add('is-invalid');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i><span class="btn-text">Send Message</span>';
                
                // Show error message
                const errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-danger alert-dismissible fade show mb-4';
                errorAlert.innerHTML = `
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Please fix the errors above and try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                form.insertBefore(errorAlert, form.firstChild);
                
                // Auto dismiss after 5 seconds
                setTimeout(() => {
                    if (errorAlert.parentNode) {
                        errorAlert.remove();
                    }
                }, 5000);
            }
        });

        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });

            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid') && this.value.trim()) {
                    this.classList.remove('is-invalid');
                }
            });
        });

        // Smooth scroll animations
        const cards = document.querySelectorAll('.card, .contact-info-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    });
    </script>
</body>
</html>