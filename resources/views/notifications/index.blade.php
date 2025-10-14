<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Laravel Cart</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Hero Section */
        .notification-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        .notification-hero::before {
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

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-label {
            font-size: 0.75rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Filter Sidebar */
        .filter-sidebar .card {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
        }

        .filter-option {
            color: #6c757d;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 1px solid transparent;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .filter-option:hover {
            background: #f8f9fa;
            color: #495057;
            transform: translateX(5px);
        }

        .filter-option.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: 1px solid #667eea;
        }

        .filter-option.active .badge {
            background: rgba(255, 255, 255, 0.2) !important;
            color: white !important;
        }

        .filter-title {
            color: #495057;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        /* Notifications Container */
        .notifications-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        /* Empty State */
        .empty-state {
            padding: 4rem 2rem;
        }

        .empty-icon {
            margin-bottom: 2rem;
        }

        /* Toast Notifications */
        .toast {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .notification-hero {
                text-align: center;
            }

            .hero-content .d-flex {
                flex-direction: column;
                text-align: center;
            }

            .hero-icon {
                margin-bottom: 1rem;
            }

            .notifications-container {
                padding: 1rem;
                border-radius: 15px;
            }

            .filter-sidebar {
                margin-bottom: 2rem;
            }
        }

        /* Animation */
        .notification-item {
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-0">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                <i class="fas fa-shopping-bag me-2"></i>Laravel Cart
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                <i class="fas fa-home me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('shop') }}">
                                <i class="fas fa-store me-1"></i>Shop
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('notifications.index') }}">
                                <i class="fas fa-bell me-1"></i>Notifications
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>{{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
<div class="container-fluid px-0">
    <!-- Hero Section -->
    <div class="notification-hero bg-gradient-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="hero-content">
                        <div class="d-flex align-items-center mb-3">
                            <div class="hero-icon">
                                <i class="fas fa-bell fa-3x"></i>
                            </div>
                            <div class="ms-4">
                                <h1 class="display-4 fw-bold mb-2">Notifications</h1>
                                <p class="lead mb-0">Stay updated with your orders, reviews, and important updates</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="stats-cards">
                        <div class="row g-3">
                            <div class="col-4">
                                <div class="stat-card text-center">
                                    <div class="stat-number">{{ $stats['total'] }}</div>
                                    <div class="stat-label">Total</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-card text-center">
                                    <div class="stat-number text-warning">{{ $stats['unread'] }}</div>
                                    <div class="stat-label">Unread</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-card text-center">
                                    <div class="stat-number text-danger">{{ $stats['important'] }}</div>
                                    <div class="stat-label">Important</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3 mb-4">
                <div class="filter-sidebar">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header bg-transparent border-0 py-3">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-filter me-2 text-primary"></i>
                                Filter Notifications
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <!-- Filter by Status -->
                            <div class="filter-section p-3 border-bottom">
                                <h6 class="filter-title mb-3">Status</h6>
                                <div class="filter-options">
                                    <a href="{{ route('notifications.index', ['filter' => 'all']) }}" 
                                       class="filter-option d-block p-2 rounded mb-2 {{ $filter === 'all' ? 'active' : '' }}">
                                        <i class="fas fa-list me-2"></i>
                                        All Notifications
                                        <span class="badge bg-light text-dark ms-auto">{{ $stats['total'] }}</span>
                                    </a>
                                    <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
                                       class="filter-option d-block p-2 rounded mb-2 {{ $filter === 'unread' ? 'active' : '' }}">
                                        <i class="fas fa-circle me-2 text-warning"></i>
                                        Unread Only
                                        <span class="badge bg-warning text-dark ms-auto">{{ $stats['unread'] }}</span>
                                    </a>
                                    <a href="{{ route('notifications.index', ['filter' => 'important']) }}" 
                                       class="filter-option d-block p-2 rounded mb-2 {{ $filter === 'important' ? 'active' : '' }}">
                                        <i class="fas fa-exclamation-circle me-2 text-danger"></i>
                                        Important
                                        <span class="badge bg-danger ms-auto">{{ $stats['important'] }}</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Filter by Type -->
                            @if($types->count() > 0)
                            <div class="filter-section p-3">
                                <h6 class="filter-title mb-3">Type</h6>
                                <div class="filter-options">
                                    <a href="{{ route('notifications.index', ['filter' => $filter]) }}" 
                                       class="filter-option d-block p-2 rounded mb-2 {{ !$type ? 'active' : '' }}">
                                        <i class="fas fa-globe me-2"></i>
                                        All Types
                                    </a>
                                    @foreach($types as $typeKey => $typeName)
                                    <a href="{{ route('notifications.index', ['filter' => $filter, 'type' => $typeKey]) }}" 
                                       class="filter-option d-block p-2 rounded mb-2 {{ $type === $typeKey ? 'active' : '' }}">
                                        <i class="fas fa-{{ $typeKey === 'order_placed' ? 'shopping-bag' : ($typeKey === 'review_request' ? 'star' : 'bell') }} me-2"></i>
                                        {{ $typeName }}
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow-sm border-0 rounded-3 mt-4">
                        <div class="card-header bg-transparent border-0 py-3">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bolt me-2 text-warning"></i>
                                Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <button type="button" 
                                    class="btn btn-outline-success w-100 mb-3" 
                                    id="markAllReadBtn"
                                    {{ $stats['unread'] === 0 ? 'disabled' : '' }}>
                                <i class="fas fa-check-double me-2"></i>
                                Mark All as Read
                            </button>
                            <a href="{{ route('notifications.preferences') }}" 
                               class="btn btn-outline-primary w-100">
                                <i class="fas fa-cog me-2"></i>
                                Notification Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="col-lg-9">
                <div class="notifications-container">
                    <!-- Header with Actions -->
                    <div class="notifications-header mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1">Your Notifications</h3>
                                <p class="text-muted mb-0">
                                    Showing {{ $notifications->count() }} of {{ $notifications->total() }} notifications
                                </p>
                            </div>
                            <div class="header-actions">
                                @if($stats['unread'] > 0)
                                <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                                    {{ $stats['unread'] }} unread
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Notifications List -->
                    <div id="notificationsList">
                        @include('notifications.partials.notification-list', ['notifications' => $notifications])
                    </div>

                    <!-- Pagination -->
                    @if($notifications->hasPages())
                    <div class="d-flex justify-content-center mt-5">
                        {{ $notifications->links() }}
                    </div>
                    @endif

                    <!-- Empty State -->
                    @if($notifications->count() === 0)
                    <div class="empty-state text-center py-5">
                        <div class="empty-icon mb-4">
                            <i class="fas fa-bell-slash fa-5x text-muted opacity-50"></i>
                        </div>
                        <h4 class="text-muted mb-3">No notifications found</h4>
                        <p class="text-muted">
                            @if($filter === 'unread')
                                You're all caught up! No unread notifications.
                            @elseif($filter === 'important')
                                No important notifications at the moment.
                            @else
                                You don't have any notifications yet. They'll appear here when you have updates about your orders, reviews, and more.
                            @endif
                        </p>
                        <a href="{{ route('shop') }}" class="btn btn-primary mt-3">
                            <i class="fas fa-shopping-bag me-2"></i>
                            Start Shopping
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="fas fa-bell me-2 text-primary"></i>
            <strong class="me-auto">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="fas fa-bell me-2 text-primary"></i>
            <strong class="me-auto">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark all as read functionality
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to mark all notifications as read?')) {
                fetch('{{ route("notifications.readAll") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        // Reload page to update counts
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                });
            }
        });
    }

    // Individual notification actions
    document.addEventListener('click', function(e) {
        // Mark as read
        if (e.target.classList.contains('mark-read-btn')) {
            e.preventDefault();
            const notificationId = e.target.dataset.notificationId;
            markNotificationAsRead(notificationId);
        }

        // Delete notification
        if (e.target.classList.contains('delete-notification-btn')) {
            e.preventDefault();
            const notificationId = e.target.dataset.notificationId;
            deleteNotification(notificationId);
        }

        // Notification click (mark as read and redirect)
        if (e.target.closest('.notification-item') && !e.target.closest('.notification-actions')) {
            const notificationItem = e.target.closest('.notification-item');
            const notificationId = notificationItem.dataset.notificationId;
            const actionUrl = notificationItem.dataset.actionUrl;
            
            if (notificationItem.classList.contains('unread')) {
                markNotificationAsRead(notificationId, actionUrl);
            } else if (actionUrl) {
                window.location.href = actionUrl;
            }
        }
    });

    // Mark notification as read
    function markNotificationAsRead(notificationId, redirectUrl = null) {
        fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationItem) {
                    notificationItem.classList.remove('unread');
                    notificationItem.classList.add('read');
                    
                    // Update unread indicator
                    const unreadIndicator = notificationItem.querySelector('.unread-indicator');
                    if (unreadIndicator) {
                        unreadIndicator.remove();
                    }
                }

                // Update unread count in stats
                updateUnreadCount(data.unread_count);

                // Redirect if URL provided
                if (redirectUrl || data.action_url) {
                    setTimeout(() => {
                        window.location.href = redirectUrl || data.action_url;
                    }, 500);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    }

    // Delete notification
    function deleteNotification(notificationId) {
        if (confirm('Are you sure you want to delete this notification?')) {
            fetch(`/notifications/${notificationId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove notification from UI
                    const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
                    if (notificationItem) {
                        notificationItem.style.transition = 'all 0.3s ease';
                        notificationItem.style.opacity = '0';
                        notificationItem.style.transform = 'translateX(-100%)';
                        
                        setTimeout(() => {
                            notificationItem.remove();
                        }, 300);
                    }

                    showToast(data.message, 'success');
                    
                    // Update stats
                    updateStats(data.stats);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred', 'error');
            });
        }
    }

    // Update unread count
    function updateUnreadCount(count) {
        // Update badge in header
        const unreadBadge = document.querySelector('.header-actions .badge');
        if (unreadBadge) {
            if (count === 0) {
                unreadBadge.style.display = 'none';
            } else {
                unreadBadge.textContent = `${count} unread`;
                unreadBadge.style.display = 'inline-block';
            }
        }

        // Update sidebar stats
        const unreadStatNumber = document.querySelector('.stat-card .stat-number.text-warning');
        if (unreadStatNumber) {
            unreadStatNumber.textContent = count;
        }
    }

    // Update stats
    function updateStats(stats) {
        // Update all stat numbers
        const statNumbers = document.querySelectorAll('.stat-number');
        statNumbers[0].textContent = stats.total; // Total
        statNumbers[1].textContent = stats.unread; // Unread
        statNumbers[2].textContent = stats.important; // Important

        // Update filter badges
        const filterOptions = document.querySelectorAll('.filter-option .badge');
        filterOptions.forEach(badge => {
            const text = badge.textContent.trim();
            if (text.includes('unread')) {
                badge.textContent = stats.unread;
            }
        });
    }

    // Show toast notification
    function showToast(message, type = 'info') {
        const toast = document.getElementById('notificationToast');
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
});
</script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>