<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Shared Wishlists - Laravel Cart</title>
    
    <!-- Bootstrap 5.3.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6.4.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #FF6B6B 0%, #4ECDC4 50%, #45B7D1 100%);
            --accent-gradient: linear-gradient(135deg, #FF6B6B20, #4ECDC420);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--primary-gradient);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 107, 107, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(78, 205, 196, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(69, 183, 209, 0.2) 0%, transparent 50%);
            animation: gradientShift 8s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes gradientShift {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        /* Glass morphism containers */
        .glass-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: 
                0 8px 32px rgba(31, 38, 135, 0.37),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.1),
                0 8px 25px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin: 2rem 0;
            overflow: hidden;
        }

        /* Header styling */
        .page-header {
            background: var(--accent-gradient);
            padding: 3rem 2rem;
            text-align: center;
            border-radius: 25px 25px 0 0;
            position: relative;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.1), rgba(78, 205, 196, 0.1));
            border-radius: 25px 25px 0 0;
        }

        .page-header .content {
            position: relative;
            z-index: 2;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #FF6B6B, #4ECDC4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            text-shadow: none;
        }

        .page-subtitle {
            font-size: 1.2rem;
            color: #6c757d;
            margin-bottom: 2rem;
        }

        /* Button styling */
        .btn-gradient {
            background: linear-gradient(135deg, #FF6B6B, #4ECDC4);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
            color: white;
        }

        .btn-glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #333;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            color: #333;
        }

        /* Share card styling */
        .share-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .share-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            border-color: rgba(255, 107, 107, 0.3);
        }

        .card-header-custom {
            background: linear-gradient(135deg, #FF6B6B, #4ECDC4);
            color: white;
            padding: 1.25rem 1.5rem;
            border-radius: 20px 20px 0 0;
            border: none;
        }

        /* Stats styling */
        .stats-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .stats-card:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #FF6B6B, #4ECDC4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
        }

        /* Status badges */
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-active {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .status-expired {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .status-public {
            background: linear-gradient(135deg, #17a2b8, #6f42c1);
            color: white;
        }

        .status-private {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
        }

        /* Empty state */
        .empty-state {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            padding: 4rem 2rem;
            text-align: center;
            color: white;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 2rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* Alert styling */
        .alert-success {
            background: rgba(40, 167, 69, 0.9);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 15px;
            color: white;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }

        /* Copy input styling */
        .copy-input {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(255, 107, 107, 0.3);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .copy-input:focus {
            border-color: #FF6B6B;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 107, 0.25);
            background: rgba(255, 255, 255, 1);
        }

        /* Animation for cards */
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.6s ease forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Toast notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            border-radius: 15px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
            
            .main-container {
                margin: 1rem;
                border-radius: 20px;
            }
            
            .page-header {
                padding: 2rem 1rem;
                border-radius: 20px 20px 0 0;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="main-container">
                    <!-- Page Header -->
                    <div class="page-header">
                        <div class="content">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h1 class="page-title">My Shared Wishlists</h1>
                                    <p class="page-subtitle">Manage your shared wishlist links</p>
                                </div>
                                <a href="{{ route('wishlist.share.create') }}" class="btn btn-gradient btn-lg">
                                    <i class="fas fa-plus me-2"></i>Create New Share
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <!-- Success Alert -->
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                @if(session('share_url'))
                                    <div class="mt-3">
                                        <strong>Share URL:</strong>
                                        <div class="input-group mt-2">
                                            <input type="text" 
                                                   class="form-control copy-input" 
                                                   value="{{ session('share_url') }}" 
                                                   id="shareUrl" 
                                                   readonly>
                                            <button class="btn btn-glass" 
                                                    type="button" 
                                                    onclick="copyToClipboard('shareUrl')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Shares List -->
                        @if($shares->count() > 0)
                            <div class="row g-4">
                                @foreach($shares as $index => $share)
                                    <div class="col-lg-6">
                                        <div class="share-card h-100 fade-in-up" style="animation-delay: {{ $index * 0.1 }}s">
                                            <div class="card-header-custom d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0 fw-bold">
                                                    <i class="fas fa-heart me-2"></i>{{ $share->name }}
                                                </h6>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-light rounded-circle" 
                                                            type="button" 
                                                            data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ $share->share_url }}" target="_blank">
                                                                <i class="fas fa-external-link-alt me-2"></i>View Share
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item" 
                                                                    onclick="copyToClipboard('share-url-{{ $share->id }}')">
                                                                <i class="fas fa-copy me-2"></i>Copy Link
                                                            </button>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('wishlist.share.destroy', $share) }}" 
                                                                  method="POST" 
                                                                  onsubmit="return confirm('Delete this shared wishlist?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="fas fa-trash me-2"></i>Delete
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            
                                            <div class="card-body p-4">
                                                @if($share->description)
                                                    <p class="text-muted mb-3">{{ $share->description }}</p>
                                                @endif
                                                
                                                <!-- Share Stats -->
                                                <div class="row g-3 mb-4">
                                                    <div class="col-6">
                                                        <div class="stats-card">
                                                            <div class="stats-number">{{ $share->items_count }}</div>
                                                            <div class="stats-label">Items</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="stats-card">
                                                            <div class="stats-number">{{ $share->view_count }}</div>
                                                            <div class="stats-label">Views</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Status Badges -->
                                                <div class="d-flex flex-wrap gap-2 mb-3">
                                                    @if($share->expires_at)
                                                        @if($share->is_expired)
                                                            <span class="status-badge status-expired">
                                                                <i class="fas fa-clock"></i>Expired
                                                            </span>
                                                        @else
                                                            <span class="status-badge status-active">
                                                                <i class="fas fa-clock"></i>{{ $share->expires_at_formatted }}
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="status-badge status-active">
                                                            <i class="fas fa-infinity"></i>Never Expires
                                                        </span>
                                                    @endif
                                                    
                                                    @if($share->is_public)
                                                        <span class="status-badge status-public">
                                                            <i class="fas fa-globe"></i>Public
                                                        </span>
                                                    @else
                                                        <span class="status-badge status-private">
                                                            <i class="fas fa-lock"></i>Private
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                <!-- Share Details -->
                                                <div class="small text-muted mb-3">
                                                    <div class="d-flex justify-content-between">
                                                        <span><i class="fas fa-calendar me-1"></i>Created:</span>
                                                        <span>{{ $share->created_at->format('M j, Y') }}</span>
                                                    </div>
                                                </div>
                                                
                                                <!-- Hidden input for copying -->
                                                <input type="hidden" 
                                                       id="share-url-{{ $share->id }}" 
                                                       value="{{ $share->share_url }}">
                                            </div>
                                            
                                            <div class="card-footer bg-transparent border-0 p-4 pt-0">
                                                <div class="d-flex gap-2">
                                                    <a href="{{ $share->share_url }}" 
                                                       class="btn btn-sm btn-outline-primary flex-fill" 
                                                       target="_blank">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </a>
                                                    <button class="btn btn-sm btn-glass" 
                                                            onclick="copyToClipboard('share-url-{{ $share->id }}')">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <!-- Pagination -->
                            @if($shares->hasPages())
                                <div class="d-flex justify-content-center mt-5">
                                    <div class="glass-container p-3">
                                        {{ $shares->links() }}
                                    </div>
                                </div>
                            @endif
                        @else
                            <!-- Empty State -->
                            <div class="empty-state">
                                <i class="fas fa-share-alt"></i>
                                <h3 class="mb-3">No Shared Wishlists Yet</h3>
                                <p class="mb-4">Create your first shared wishlist to start sharing your favorite items with friends and family.</p>
                                <a href="{{ route('wishlist.share.create') }}" class="btn btn-gradient btn-lg">
                                    <i class="fas fa-plus me-2"></i>Create Your First Share
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container"></div>

    <!-- Bootstrap 5.3.2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            navigator.clipboard.writeText(element.value).then(function() {
                showToast('Link copied to clipboard!', 'success');
            }).catch(function() {
                // Fallback for older browsers
                element.select();
                element.setSelectionRange(0, 99999);
                document.execCommand('copy');
                showToast('Link copied to clipboard!', 'success');
            });
        }

        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'} me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            // Create toast container if it doesn't exist
            let container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container position-fixed top-0 end-0 p-3';
                document.body.appendChild(container);
            }
            
            container.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Remove toast after it's hidden
            toast.addEventListener('hidden.bs.toast', () => {
                if (container.contains(toast)) {
                    container.removeChild(toast);
                }
            });
        }

        // Enhanced animations and interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on load
            const shareCards = document.querySelectorAll('.share-card');
            shareCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 150);
            });

            // Enhanced hover effects for stats cards
            const statsCards = document.querySelectorAll('.stats-card');
            statsCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px) scale(1.05)';
                    this.style.boxShadow = '0 15px 35px rgba(0, 0, 0, 0.1)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                    this.style.boxShadow = 'none';
                });
            });
        });
    </script>

</body>
</html>