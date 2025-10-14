<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Preferences - Laravel Cart</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Custom styles for preferences page */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        /* Hero Section */
        .preferences-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        .preferences-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><defs><radialGradient id="a" cx="50%" cy="0%"><stop offset="0%" stop-color="rgba(255,255,255,.1)"/><stop offset="100%" stop-color="rgba(255,255,255,0)"/></radialGradient></defs><rect width="100" height="20" fill="url(%23a)"/></svg>');
            opacity: 0.1;
        }

        .hero-icon {
            background: rgba(255, 255, 255, 0.2);
            padding: 1.5rem;
            border-radius: 50%;
            backdrop-filter: blur(10px);
        }

        /* Preferences Container */
        .preferences-container .card {
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1) !important;
        }

        /* Preference Sections */
        .preference-section {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 2rem;
        }

        .preference-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .section-header {
            margin-bottom: 1.5rem;
        }

        .section-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .section-description {
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        /* Preference Items */
        .preference-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.25rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .preference-item:hover {
            background: #ffffff;
            border-color: #e9ecef;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .preference-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        /* Form Switches */
        .form-check-input {
            width: 3rem;
            height: 1.5rem;
            border-radius: 1rem;
            background-color: #dee2e6;
            border: none;
            transition: all 0.3s ease;
        }

        .form-check-input:checked {
            background-color: #28a745;
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        /* Form Selects */
        .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-outline-warning {
            border-color: #ffc107;
            color: #ffc107;
        }

        .btn-outline-warning:hover {
            background-color: #ffc107;
            border-color: #ffc107;
        }

        /* Tips Section */
        .tips-section .card {
            border-radius: 15px;
        }

        .tip-item {
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .tip-item:last-child {
            margin-bottom: 0;
        }

        /* Breadcrumb */
        .breadcrumb {
            background: transparent;
            padding: 0;
        }

        .breadcrumb-item a {
            color: #667eea;
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            text-decoration: underline;
        }

        /* Purple color variant */
        .text-purple {
            color: #8a2be2 !important;
        }

        /* Loading state */
        .saving {
            opacity: 0.7;
            pointer-events: none;
        }

        /* Animation */
        .preference-item {
            animation: fadeInUp 0.6s ease-out;
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

        /* Responsive */
        @media (max-width: 768px) {
            .preferences-hero {
                text-align: center;
            }

            .hero-content .d-flex {
                flex-direction: column;
                text-align: center;
            }

            .hero-icon {
                margin-bottom: 1rem;
            }

            .preference-item {
                padding: 1rem;
            }

            .preference-actions .d-flex {
                flex-direction: column;
                gap: 1rem;
            }

            .action-buttons {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .action-buttons .btn {
                width: 100%;
            }
        }

        /* Toast */
        .toast {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: none;
        }
    </style>
</head>
<body>

<div class="container-fluid px-0">
    <!-- Hero Section -->
    <div class="preferences-hero bg-gradient-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="hero-content">
                        <div class="d-flex align-items-center mb-3">
                            <div class="hero-icon">
                                <i class="fas fa-cog fa-3x"></i>
                            </div>
                            <div class="ms-4">
                                <h1 class="display-4 fw-bold mb-2">Notification Preferences</h1>
                                <p class="lead mb-0">Customize how and when you receive notifications</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="hero-illustration text-center">
                        <i class="fas fa-bell fa-5x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Navigation Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('notifications.index') }}">
                                <i class="fas fa-bell me-1"></i>
                                Notifications
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Preferences</li>
                    </ol>
                </nav>

                <!-- Preferences Form -->
                <div class="preferences-container">
                    <div class="card shadow-lg border-0 rounded-3">
                        <div class="card-header bg-transparent border-0 py-4">
                            <h3 class="card-title mb-1">
                                <i class="fas fa-sliders-h me-2 text-primary"></i>
                                Notification Settings
                            </h3>
                            <p class="text-muted mb-0">Choose which notifications you want to receive and how</p>
                        </div>

                        <div class="card-body p-4">
                            <form id="preferencesForm">
                                @csrf
                                
                                <!-- Order Notifications -->
                                <div class="preference-section mb-5">
                                    <div class="section-header mb-4">
                                        <h5 class="section-title">
                                            <i class="fas fa-shopping-bag me-2 text-success"></i>
                                            Order Notifications
                                        </h5>
                                        <p class="section-description text-muted">
                                            Stay updated about your orders and purchases
                                        </p>
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="preference-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="preference-info">
                                                        <h6 class="preference-title mb-1">Order Updates</h6>
                                                        <small class="text-muted">Order placed, shipped, delivered</small>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" 
                                                               id="orderUpdates" name="order_updates" checked>
                                                        <label class="form-check-label" for="orderUpdates"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="preference-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="preference-info">
                                                        <h6 class="preference-title mb-1">Payment Alerts</h6>
                                                        <small class="text-muted">Payment success/failure notifications</small>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" 
                                                               id="paymentAlerts" name="payment_alerts" checked>
                                                        <label class="form-check-label" for="paymentAlerts"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Review Notifications -->
                                <div class="preference-section mb-5">
                                    <div class="section-header mb-4">
                                        <h5 class="section-title">
                                            <i class="fas fa-star me-2 text-warning"></i>
                                            Review Notifications
                                        </h5>
                                        <p class="section-description text-muted">
                                            Notifications about product reviews and feedback
                                        </p>
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="preference-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="preference-info">
                                                        <h6 class="preference-title mb-1">Review Reminders</h6>
                                                        <small class="text-muted">Reminders to review purchased products</small>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" 
                                                               id="reviewReminders" name="review_reminders" checked>
                                                        <label class="form-check-label" for="reviewReminders"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="preference-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="preference-info">
                                                        <h6 class="preference-title mb-1">Review Responses</h6>
                                                        <small class="text-muted">When someone responds to your reviews</small>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" 
                                                               id="reviewResponses" name="review_responses" checked>
                                                        <label class="form-check-label" for="reviewResponses"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Promotional Notifications -->
                                <div class="preference-section mb-5">
                                    <div class="section-header mb-4">
                                        <h5 class="section-title">
                                            <i class="fas fa-tag me-2 text-purple"></i>
                                            Promotional Notifications
                                        </h5>
                                        <p class="section-description text-muted">
                                            Special offers, sales, and promotional updates
                                        </p>
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="preference-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="preference-info">
                                                        <h6 class="preference-title mb-1">Promotional Emails</h6>
                                                        <small class="text-muted">Sales, discounts, and special offers</small>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" 
                                                               id="promotionalEmails" name="promotional_emails">
                                                        <label class="form-check-label" for="promotionalEmails"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="preference-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="preference-info">
                                                        <h6 class="preference-title mb-1">Wishlist Sales</h6>
                                                        <small class="text-muted">When wishlist items go on sale</small>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" 
                                                               id="wishlistSales" name="wishlist_sales" checked>
                                                        <label class="form-check-label" for="wishlistSales"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delivery Methods -->
                                <div class="preference-section mb-5">
                                    <div class="section-header mb-4">
                                        <h5 class="section-title">
                                            <i class="fas fa-paper-plane me-2 text-info"></i>
                                            Delivery Methods
                                        </h5>
                                        <p class="section-description text-muted">
                                            Choose how you want to receive notifications
                                        </p>
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="preference-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="preference-info">
                                                        <h6 class="preference-title mb-1">Email Notifications</h6>
                                                        <small class="text-muted">Receive notifications via email</small>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" 
                                                               id="emailNotifications" name="email_notifications" checked>
                                                        <label class="form-check-label" for="emailNotifications"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="preference-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="preference-info">
                                                        <h6 class="preference-title mb-1">Push Notifications</h6>
                                                        <small class="text-muted">Browser and mobile push notifications</small>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" 
                                                               id="pushNotifications" name="push_notifications">
                                                        <label class="form-check-label" for="pushNotifications"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Notification Frequency -->
                                <div class="preference-section mb-5">
                                    <div class="section-header mb-4">
                                        <h5 class="section-title">
                                            <i class="fas fa-clock me-2 text-secondary"></i>
                                            Notification Frequency
                                        </h5>
                                        <p class="section-description text-muted">
                                            Control how often you receive notifications
                                        </p>
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label for="emailFrequency" class="form-label fw-semibold">Email Digest</label>
                                            <select class="form-select" id="emailFrequency" name="email_frequency">
                                                <option value="instant">Instant (as they happen)</option>
                                                <option value="daily" selected>Daily digest</option>
                                                <option value="weekly">Weekly digest</option>
                                                <option value="never">Never</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="quietHours" class="form-label fw-semibold">Quiet Hours</label>
                                            <select class="form-select" id="quietHours" name="quiet_hours">
                                                <option value="none">No quiet hours</option>
                                                <option value="evening">Evening (6 PM - 9 AM)</option>
                                                <option value="night" selected>Night time (10 PM - 8 AM)</option>
                                                <option value="weekend">Weekends</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="preference-actions">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left me-2"></i>
                                            Back to Notifications
                                        </a>
                                        
                                        <div class="action-buttons">
                                            <button type="button" class="btn btn-outline-warning me-2" id="resetDefaults">
                                                <i class="fas fa-undo me-2"></i>
                                                Reset to Defaults
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>
                                                Save Preferences
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Quick Tips -->
                    <div class="tips-section mt-5">
                        <div class="card border-0 bg-light">
                            <div class="card-body p-4">
                                <h5 class="card-title">
                                    <i class="fas fa-lightbulb me-2 text-warning"></i>
                                    Tips for Better Notifications
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="tip-item d-flex">
                                            <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                            <div>
                                                <strong>Keep order updates on</strong> to track your purchases in real-time
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="tip-item d-flex">
                                            <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                            <div>
                                                <strong>Enable wishlist sales</strong> to never miss deals on your favorite items
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="tip-item d-flex">
                                            <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                            <div>
                                                <strong>Set quiet hours</strong> to avoid notifications during rest time
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="tip-item d-flex">
                                            <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                            <div>
                                                <strong>Choose daily digest</strong> to stay informed without overwhelm
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="preferencesToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="fas fa-cog me-2 text-primary"></i>
            <strong class="me-auto">Preferences</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const preferencesForm = document.getElementById('preferencesForm');
    const resetDefaultsBtn = document.getElementById('resetDefaults');

    // Handle form submission
    preferencesForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Add loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        submitBtn.disabled = true;
        
        // Add saving class to form
        this.classList.add('saving');

        // Collect form data
        const formData = new FormData(this);
        const preferences = {};
        
        // Convert FormData to object
        for (let [key, value] of formData.entries()) {
            if (key !== '_token') {
                preferences[key] = value === 'on' ? true : false;
            }
        }

        // Also check for unchecked checkboxes
        const checkboxes = this.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            if (!formData.has(checkbox.name)) {
                preferences[checkbox.name] = false;
            }
        });

        // Add select values
        const selects = this.querySelectorAll('select');
        selects.forEach(select => {
            preferences[select.name] = select.value;
        });

        // Send to server
        fetch('{{ route("notifications.updatePreferences") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(preferences)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                
                // Add success visual feedback
                submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Saved!';
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-success');
                
                // Reset button after delay
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.classList.remove('btn-success');
                    submitBtn.classList.add('btn-primary');
                    submitBtn.disabled = false;
                    preferencesForm.classList.remove('saving');
                }, 2000);
            } else {
                throw new Error(data.message || 'Failed to save preferences');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to save preferences. Please try again.', 'error');
            
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            preferencesForm.classList.remove('saving');
        });
    });

    // Handle reset to defaults
    resetDefaultsBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to reset all preferences to default values?')) {
            // Reset form to default values
            resetToDefaults();
            showToast('Preferences reset to defaults', 'info');
        }
    });

    // Reset form to default values
    function resetToDefaults() {
        // Default checkbox states
        const defaultStates = {
            'order_updates': true,
            'payment_alerts': true,
            'review_reminders': true,
            'review_responses': true,
            'promotional_emails': false,
            'wishlist_sales': true,
            'email_notifications': true,
            'push_notifications': false
        };

        // Set checkbox states
        Object.keys(defaultStates).forEach(name => {
            const checkbox = document.querySelector(`input[name="${name}"]`);
            if (checkbox) {
                checkbox.checked = defaultStates[name];
            }
        });

        // Set select defaults
        document.getElementById('emailFrequency').value = 'daily';
        document.getElementById('quietHours').value = 'night';
    }

    // Show toast notification
    function showToast(message, type = 'info') {
        const toast = document.getElementById('preferencesToast');
        const toastBody = toast.querySelector('.toast-body');
        const toastIcon = toast.querySelector('.toast-header i');
        
        toastBody.textContent = message;
        
        // Update icon and color based on type
        toastIcon.className = type === 'success' ? 'fas fa-check-circle me-2 text-success' : 
                             type === 'error' ? 'fas fa-exclamation-circle me-2 text-danger' :
                             'fas fa-info-circle me-2 text-primary';
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
    }

    // Add smooth animations to form interactions
    const preferenceItems = document.querySelectorAll('.preference-item');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationDelay = 
                    (Array.from(preferenceItems).indexOf(entry.target) * 0.1) + 's';
                entry.target.classList.add('animate');
            }
        });
    });

    preferenceItems.forEach(item => {
        observer.observe(item);
    });

    // Add interactive feedback to switches
    const switches = document.querySelectorAll('.form-check-input');
    switches.forEach(switchInput => {
        switchInput.addEventListener('change', function() {
            const preferenceItem = this.closest('.preference-item');
            if (preferenceItem) {
                preferenceItem.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    preferenceItem.style.transform = '';
                }, 200);
            }
        });
    });
});
</script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>