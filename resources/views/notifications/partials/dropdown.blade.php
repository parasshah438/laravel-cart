<!-- Notification Dropdown -->
<div class="dropdown notification-dropdown">
    <button class="btn btn-link position-relative p-2" type="button" id="notificationDropdown" 
            data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
        <i class="fas fa-bell fs-5 text-muted"></i>
        <span class="notification-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
              id="notificationCount" style="display: none;">
            0
        </span>
    </button>
    
    <div class="dropdown-menu dropdown-menu-end notification-dropdown-menu shadow-lg border-0" 
         aria-labelledby="notificationDropdown">
        <!-- Header -->
        <div class="notification-dropdown-header d-flex justify-content-between align-items-center p-3 border-bottom">
            <h6 class="mb-0 fw-semibold">Notifications</h6>
            <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary">
                View All
            </a>
        </div>
        
        <!-- Notification List -->
        <div class="notification-dropdown-list" id="recentNotificationsList">
            <!-- Loading state -->
            <div class="text-center py-4" id="notificationsLoading">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2 text-muted small">Loading notifications...</div>
            </div>
            
            <!-- Empty state -->
            <div class="text-center py-4 d-none" id="notificationsEmpty">
                <i class="fas fa-bell-slash text-muted mb-2"></i>
                <div class="text-muted small">No new notifications</div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="notification-dropdown-footer border-top">
            <div class="d-grid gap-2 p-3">
                <button type="button" class="btn btn-sm btn-outline-success" id="markAllReadDropdown">
                    <i class="fas fa-check-double me-1"></i>
                    Mark All as Read
                </button>
                <a href="{{ route('notifications.preferences') }}" 
                   class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-cog me-1"></i>
                    Settings
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Notification Dropdown Styles */
.notification-dropdown {
    position: relative;
}

.notification-dropdown .btn {
    border: none;
    background: transparent;
    transition: all 0.3s ease;
}

.notification-dropdown .btn:hover {
    background-color: rgba(0, 0, 0, 0.05);
    border-radius: 50%;
}

.notification-badge {
    font-size: 0.7rem;
    min-width: 18px;
    height: 18px;
    line-height: 18px;
    padding: 0;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
    }
}

.notification-dropdown-menu {
    width: 380px;
    max-height: 500px;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15) !important;
}

.notification-dropdown-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.notification-dropdown-header .btn {
    color: white;
    border-color: rgba(255, 255, 255, 0.3);
}

.notification-dropdown-header .btn:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.5);
}

.notification-dropdown-list {
    max-height: 300px;
    overflow-y: auto;
}

/* Custom scrollbar */
.notification-dropdown-list::-webkit-scrollbar {
    width: 4px;
}

.notification-dropdown-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.notification-dropdown-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.notification-dropdown-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.notification-dropdown-footer {
    background-color: #f8f9fa;
}

/* Individual notification items in dropdown */
.dropdown-notification-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
}

.dropdown-notification-item:hover {
    background-color: #f8f9fa;
    color: inherit;
    text-decoration: none;
}

.dropdown-notification-item.unread {
    background: linear-gradient(to right, rgba(0, 123, 255, 0.03) 0%, transparent 100%);
    border-left: 3px solid #007bff;
}

.dropdown-notification-item:last-child {
    border-bottom: none;
}

.dropdown-notification-icon {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.dropdown-notification-content {
    flex: 1;
    min-width: 0;
}

.dropdown-notification-title {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    line-height: 1.3;
}

.dropdown-notification-message {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.dropdown-notification-time {
    font-size: 0.7rem;
    color: #adb5bd;
}

.dropdown-notification-unread {
    width: 8px;
    height: 8px;
    background-color: #007bff;
    border-radius: 50%;
    flex-shrink: 0;
}

/* Color variations for dropdown */
.bg-success-light { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
.bg-info-light { background-color: rgba(13, 202, 240, 0.1); color: #0dcaf0; }
.bg-warning-light { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }
.bg-danger-light { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
.bg-primary-light { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
.bg-secondary-light { background-color: rgba(108, 117, 125, 0.1); color: #6c757d; }

/* Responsive */
@media (max-width: 768px) {
    .notification-dropdown-menu {
        width: 320px;
    }
}

@media (max-width: 480px) {
    .notification-dropdown-menu {
        width: 280px;
    }
}
</style>

<script>
// Notification Dropdown JavaScript
document.addEventListener('DOMContentLoaded', function() {
    let notificationDropdown = document.getElementById('notificationDropdown');
    let notificationCount = document.getElementById('notificationCount');
    let recentNotificationsList = document.getElementById('recentNotificationsList');
    let notificationsLoading = document.getElementById('notificationsLoading');
    let notificationsEmpty = document.getElementById('notificationsEmpty');
    let markAllReadDropdown = document.getElementById('markAllReadDropdown');

    // Load notifications when dropdown is opened
    notificationDropdown.addEventListener('show.bs.dropdown', function () {
        loadRecentNotifications();
    });

    // Load recent notifications
    function loadRecentNotifications() {
        showLoading();
        
        fetch('{{ route("notifications.recent") }}')
            .then(response => response.json())
            .then(data => {
                hideLoading();
                displayNotifications(data.notifications);
                updateNotificationCount(data.unread_count);
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                hideLoading();
                showEmpty();
            });
    }

    // Display notifications in dropdown
    function displayNotifications(notifications) {
        if (notifications.length === 0) {
            showEmpty();
            return;
        }

        let html = '';
        notifications.forEach(notification => {
            const isUnread = !notification.read_at;
            const iconClass = getNotificationIconClass(notification.type);
            const colorClass = getNotificationColorClass(notification.type);
            
            html += `
                <a href="#" class="dropdown-notification-item ${isUnread ? 'unread' : ''}" 
                   data-notification-id="${notification.id}"
                   data-action-url="${notification.action_url || ''}">
                    <div class="d-flex align-items-start gap-3">
                        <div class="dropdown-notification-icon bg-${colorClass}-light">
                            <i class="fas fa-${iconClass}"></i>
                        </div>
                        <div class="dropdown-notification-content">
                            <div class="dropdown-notification-title">
                                ${notification.title}
                            </div>
                            <div class="dropdown-notification-message">
                                ${notification.message}
                            </div>
                            <div class="dropdown-notification-time">
                                ${formatTimeAgo(notification.created_at)}
                            </div>
                        </div>
                        ${isUnread ? '<div class="dropdown-notification-unread"></div>' : ''}
                    </div>
                </a>
            `;
        });

        recentNotificationsList.innerHTML = html;

        // Add click handlers to notification items
        document.querySelectorAll('.dropdown-notification-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const notificationId = this.dataset.notificationId;
                const actionUrl = this.dataset.actionUrl;
                
                if (this.classList.contains('unread')) {
                    markNotificationAsRead(notificationId, actionUrl);
                } else if (actionUrl) {
                    window.location.href = actionUrl;
                }
            });
        });
    }

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
                    const unreadDot = notificationItem.querySelector('.dropdown-notification-unread');
                    if (unreadDot) {
                        unreadDot.remove();
                    }
                }

                // Update count
                updateNotificationCount(data.unread_count);

                // Redirect if URL provided
                if (redirectUrl || data.action_url) {
                    setTimeout(() => {
                        window.location.href = redirectUrl || data.action_url;
                    }, 300);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Mark all as read from dropdown
    markAllReadDropdown.addEventListener('click', function() {
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
                // Update UI
                document.querySelectorAll('.dropdown-notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                    const unreadDot = item.querySelector('.dropdown-notification-unread');
                    if (unreadDot) {
                        unreadDot.remove();
                    }
                });
                
                updateNotificationCount(0);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

    // Update notification count badge
    function updateNotificationCount(count) {
        if (count > 0) {
            notificationCount.textContent = count > 99 ? '99+' : count;
            notificationCount.style.display = 'inline-block';
        } else {
            notificationCount.style.display = 'none';
        }
    }

    // Show loading state
    function showLoading() {
        notificationsLoading.classList.remove('d-none');
        notificationsEmpty.classList.add('d-none');
        recentNotificationsList.innerHTML = '';
    }

    // Hide loading state
    function hideLoading() {
        notificationsLoading.classList.add('d-none');
    }

    // Show empty state
    function showEmpty() {
        notificationsEmpty.classList.remove('d-none');
        recentNotificationsList.innerHTML = '';
    }

    // Get notification icon class
    function getNotificationIconClass(type) {
        const iconMap = {
            'order_placed': 'shopping-bag',
            'order_shipped': 'truck',
            'order_delivered': 'check-circle',
            'order_cancelled': 'x-circle',
            'payment_success': 'credit-card',
            'payment_failed': 'alert-circle',
            'review_request': 'star',
            'review_response': 'message-circle',
            'wishlist_sale': 'heart',
            'promotion': 'tag',
            'welcome': 'user',
            'system': 'settings'
        };
        return iconMap[type] || 'bell';
    }

    // Get notification color class
    function getNotificationColorClass(type) {
        const colorMap = {
            'order_placed': 'success',
            'order_shipped': 'info',
            'order_delivered': 'success',
            'order_cancelled': 'danger',
            'payment_success': 'success',
            'payment_failed': 'danger',
            'review_request': 'warning',
            'review_response': 'info',
            'wishlist_sale': 'primary',
            'promotion': 'primary',
            'welcome': 'primary',
            'system': 'secondary'
        };
        return colorMap[type] || 'primary';
    }

    // Format time ago
    function formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) {
            return 'Just now';
        } else if (diffInSeconds < 3600) {
            const minutes = Math.floor(diffInSeconds / 60);
            return `${minutes}m ago`;
        } else if (diffInSeconds < 86400) {
            const hours = Math.floor(diffInSeconds / 3600);
            return `${hours}h ago`;
        } else {
            const days = Math.floor(diffInSeconds / 86400);
            return `${days}d ago`;
        }
    }

    // Poll for new notifications every 30 seconds
    setInterval(function() {
        fetch('{{ route("notifications.unreadCount") }}')
            .then(response => response.json())
            .then(data => {
                updateNotificationCount(data.count);
            })
            .catch(error => {
                console.error('Error polling notifications:', error);
            });
    }, 30000);

    // Load initial count
    fetch('{{ route("notifications.unreadCount") }}')
        .then(response => response.json())
        .then(data => {
            updateNotificationCount(data.count);
        })
        .catch(error => {
            console.error('Error loading initial count:', error);
        });
});
</script>