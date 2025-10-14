@if($notifications->count() > 0)
    <div class="notifications-list">
        @foreach($notifications as $notification)
            <div class="notification-item {{ $notification->isRead() ? 'read' : 'unread' }} mb-3" 
                 data-notification-id="{{ $notification->id }}"
                 data-action-url="{{ $notification->action_url }}">
                <div class="card border-0 shadow-sm position-relative">
                    @if(!$notification->isRead())
                        <div class="unread-indicator position-absolute top-0 start-0"></div>
                    @endif
                    
                    @if($notification->is_important)
                        <div class="important-badge position-absolute top-0 end-0">
                            <i class="fas fa-exclamation-circle text-danger"></i>
                        </div>
                    @endif

                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <!-- Notification Icon -->
                            <div class="notification-icon me-3">
                                <div class="icon-wrapper bg-{{ $notification->color }}-subtle text-{{ $notification->color }} rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fas fa-{{ $notification->icon }}"></i>
                                </div>
                            </div>

                            <!-- Notification Content -->
                            <div class="notification-content flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="notification-title mb-1 fw-semibold">
                                        {{ $notification->title }}
                                        @if(!$notification->isRead())
                                            <span class="badge bg-primary ms-2">New</span>
                                        @endif
                                    </h6>
                                    <small class="text-muted notification-time">
                                        {{ $notification->time_ago }}
                                    </small>
                                </div>
                                
                                <p class="notification-message text-muted mb-2">
                                    {{ $notification->message }}
                                </p>

                                <!-- Additional Data -->
                                @if($notification->data)
                                    <div class="notification-meta">
                                        @if(isset($notification->data['order_id']))
                                            <span class="badge bg-light text-dark me-2">
                                                <i class="fas fa-shopping-bag me-1"></i>
                                                Order #{{ $notification->data['order_id'] }}
                                            </span>
                                        @endif
                                        
                                        @if(isset($notification->data['product_name']))
                                            <span class="badge bg-light text-dark me-2">
                                                <i class="fas fa-box me-1"></i>
                                                {{ $notification->data['product_name'] }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                <!-- Action Buttons -->
                                <div class="notification-actions mt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="action-buttons">
                                            @if($notification->action_url)
                                                <a href="{{ $notification->action_url }}" 
                                                   class="btn btn-sm btn-outline-primary me-2">
                                                    <i class="fas fa-external-link-alt me-1"></i>
                                                    View Details
                                                </a>
                                            @endif
                                            
                                            @if(!$notification->isRead())
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success mark-read-btn me-2"
                                                        data-notification-id="{{ $notification->id }}">
                                                    <i class="fas fa-check me-1"></i>
                                                    Mark as Read
                                                </button>
                                            @endif
                                        </div>
                                        
                                        <div class="utility-actions">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger delete-notification-btn"
                                                    data-notification-id="{{ $notification->id }}"
                                                    title="Delete notification">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Type Badge -->
                    <div class="notification-type-badge position-absolute bottom-0 start-0">
                        <span class="badge bg-{{ $notification->color }} text-white rounded-0 rounded-top-end">
                            {{ ucwords(str_replace('_', ' ', $notification->type)) }}
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<style>
    /* Notification Item Styles */
    .notification-item {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .notification-item:hover {
        transform: translateY(-2px);
    }

    .notification-item:hover .card {
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15) !important;
    }

    .notification-item.unread .card {
        border-left: 4px solid #007bff;
        background: linear-gradient(to right, rgba(0, 123, 255, 0.02) 0%, transparent 100%);
    }

    .notification-item.read {
        opacity: 0.8;
    }

    /* Unread Indicator */
    .unread-indicator {
        width: 12px;
        height: 12px;
        background: #007bff;
        border-radius: 50%;
        top: 15px;
        left: 15px;
        z-index: 2;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(0, 123, 255, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
        }
    }

    /* Important Badge */
    .important-badge {
        top: 15px;
        right: 15px;
        z-index: 2;
        font-size: 1.2rem;
        animation: bounce 2s infinite;
    }

    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(0);
        }
        40% {
            transform: translateY(-10px);
        }
        60% {
            transform: translateY(-5px);
        }
    }

    /* Notification Icon */
    .notification-icon {
        flex-shrink: 0;
    }

    .icon-wrapper {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }

    /* Notification Content */
    .notification-title {
        color: #2c3e50;
        font-size: 1rem;
        line-height: 1.4;
    }

    .notification-message {
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 0;
    }

    .notification-time {
        font-size: 0.75rem;
        white-space: nowrap;
    }

    /* Notification Meta */
    .notification-meta {
        margin-top: 0.5rem;
    }

    .notification-meta .badge {
        font-size: 0.7rem;
        padding: 0.4rem 0.6rem;
    }

    /* Action Buttons */
    .notification-actions {
        border-top: 1px solid #f8f9fa;
        padding-top: 1rem;
        margin-top: 1rem;
    }

    .notification-actions .btn {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
    }

    .utility-actions .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        border-radius: 50%;
    }

    /* Type Badge */
    .notification-type-badge {
        z-index: 1;
    }

    .notification-type-badge .badge {
        font-size: 0.65rem;
        padding: 0.3rem 0.6rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    /* Color Variations */
    .bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
    .text-success { color: #198754; }
    
    .bg-info-subtle { background-color: rgba(13, 202, 240, 0.1); }
    .text-info { color: #0dcaf0; }
    
    .bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }
    .text-warning { color: #ffc107; }
    
    .bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1); }
    .text-danger { color: #dc3545; }
    
    .bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1); }
    .text-primary { color: #0d6efd; }
    
    .bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1); }
    .text-secondary { color: #6c757d; }

    .bg-pink-subtle { background-color: rgba(255, 20, 147, 0.1); }
    .text-pink { color: #ff1493; }
    
    .bg-purple-subtle { background-color: rgba(138, 43, 226, 0.1); }
    .text-purple { color: #8a2be2; }

    /* Responsive */
    @media (max-width: 768px) {
        .notification-item .card-body {
            padding: 1rem;
        }

        .icon-wrapper {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .notification-title {
            font-size: 0.9rem;
        }

        .notification-message {
            font-size: 0.8rem;
        }

        .action-buttons {
            flex-direction: column;
            align-items: flex-start;
        }

        .action-buttons .btn {
            margin-bottom: 0.5rem;
            margin-right: 0 !important;
        }
    }

    /* Hover Effects */
    .notification-item:not(.read):hover {
        background: linear-gradient(to right, rgba(0, 123, 255, 0.05) 0%, transparent 100%);
        border-radius: 15px;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    /* Loading Animation */
    .loading-notification {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        0% {
            background-position: 200% 0;
        }
        100% {
            background-position: -200% 0;
        }
    }
</style>