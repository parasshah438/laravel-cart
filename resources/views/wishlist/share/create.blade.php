<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Share Your Wishlist - Laravel Cart</title>
        
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        <style>
            body {
                font-family: 'Inter', sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
                min-height: 100vh;
                position: relative;
            }

            body::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: 
                    radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.3) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(118, 75, 162, 0.3) 0%, transparent 50%),
                    radial-gradient(circle at 40% 80%, rgba(240, 147, 251, 0.2) 0%, transparent 50%);
                pointer-events: none;
                z-index: -1;
            }

            .container {
                position: relative;
                z-index: 1;
            }

            .glass-card {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(15px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: 20px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            }

            .main-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.3);
                border-radius: 20px;
                box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
                transition: all 0.3s ease;
            }

            .main-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 16px 50px rgba(0, 0, 0, 0.2);
            }

            .display-6 {
                background: linear-gradient(135deg, #667eea, #764ba2);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                font-weight: 700;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .lead {
                color: rgba(255, 255, 255, 0.9);
                font-weight: 500;
            }

            .card-header {
                background: linear-gradient(135deg, #667eea, #764ba2) !important;
                border: none;
                border-radius: 20px 20px 0 0 !important;
                padding: 1.5rem 2rem;
            }

            .form-control, .form-select {
                border: 2px solid rgba(102, 126, 234, 0.2);
                border-radius: 12px;
                padding: 12px 16px;
                transition: all 0.3s ease;
                background: rgba(255, 255, 255, 0.9);
            }

            .form-control:focus, .form-select:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
                background: rgba(255, 255, 255, 1);
                transform: translateY(-2px);
            }

            .btn-primary {
                background: linear-gradient(135deg, #667eea, #764ba2);
                border: none;
                border-radius: 25px;
                padding: 12px 30px;
                font-weight: 600;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            }

            .btn-primary:hover {
                background: linear-gradient(135deg, #5a6fd8, #6a4190);
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            }

            .btn-outline-secondary {
                color: #667eea;
                border: 2px solid #667eea;
                border-radius: 25px;
                padding: 10px 28px;
                font-weight: 600;
                transition: all 0.3s ease;
            }

            .btn-outline-secondary:hover {
                background: linear-gradient(135deg, #667eea, #764ba2);
                border-color: #667eea;
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
            }

            .product-preview-card {
                background: rgba(255, 255, 255, 0.95);
                border: 1px solid rgba(102, 126, 234, 0.2);
                border-radius: 15px;
                transition: all 0.3s ease;
                overflow: hidden;
            }

            .product-preview-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
            }

            .share-preview-badge {
                background: linear-gradient(135deg, #667eea, #764ba2);
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-size: 0.875rem;
                font-weight: 600;
            }

            .form-label {
                color: #4a5568;
                font-weight: 600;
                margin-bottom: 0.5rem;
            }

            .form-text {
                color: #718096;
                font-size: 0.875rem;
            }

            .alert {
                border-radius: 15px;
                border: none;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            }

            .floating-animation {
                animation: float 3s ease-in-out infinite;
            }

            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-10px); }
            }

            .gradient-text {
                background: linear-gradient(135deg, #667eea, #764ba2);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .existing-shares-card {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: 15px;
            }

            .share-item {
                background: rgba(255, 255, 255, 0.8);
                border-radius: 12px;
                transition: all 0.3s ease;
                padding: 1rem;
                margin-bottom: 0.5rem;
            }

            .share-item:hover {
                background: rgba(255, 255, 255, 0.95);
                transform: translateX(10px);
            }
        </style>
    </head>
    <body>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Page Header -->
                    <div class="text-center mb-5">
                        <div class="floating-animation mb-3">
                            <i class="fas fa-share-alt fa-4x text-white"></i>
                        </div>
                        <h1 class="display-6 fw-bold mb-3">Share Your Wishlist</h1>
                        <p class="lead">Create a shareable link for your wishlist</p>
                    </div>

                    <!-- Error/Success Messages -->
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Create Share Form -->
                    <div class="main-card mb-5">
                        <div class="card-header text-white">
                            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Create New Share</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('wishlist.share.store') }}" method="POST">
                                @csrf
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Share Name *</label>
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               id="name" 
                                               name="name" 
                                               value="{{ old('name') }}"
                                               placeholder="e.g., My Holiday Wishlist"
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="expires_at" class="form-label">Expires On</label>
                                        <input type="date" 
                                               class="form-control @error('expires_at') is-invalid @enderror" 
                                               id="expires_at" 
                                               name="expires_at" 
                                               value="{{ old('expires_at') }}"
                                               min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                        <div class="form-text">Leave empty for permanent link</div>
                                        @error('expires_at')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="3"
                                              placeholder="Optional description for your shared wishlist">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mt-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="is_public" 
                                               name="is_public" 
                                               value="1"
                                               {{ old('is_public') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_public">
                                            Make this wishlist publicly discoverable
                                        </label>
                                        <div class="form-text">Public wishlists can be found by other users</div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <a href="{{ route('wishlist.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Wishlist
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-share-alt me-2"></i>Create Share Link
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Preview Wishlist Items -->
                    <div class="glass-card">
                        <div class="card-header">
                            <h6 class="mb-0 text-white"><i class="fas fa-eye me-2"></i>Items to Share ({{ $wishlistItems->count() }})</h6>
                        </div>
                        <div class="card-body p-4">
                            @if($wishlistItems->count() > 0)
                                <div class="row g-3">
                                    @foreach($wishlistItems->take(6) as $item)
                                        <div class="col-md-4">
                                            <div class="product-preview-card h-100">
                                                <div class="position-relative">
                                                    @if($item->product->media->count() > 0)
                                                        <img src="{{ asset('storage/' . $item->product->media->first()->file_path) }}" 
                                                             class="card-img-top" 
                                                             style="height: 150px; object-fit: cover;"
                                                             alt="{{ $item->product->name }}">
                                                    @else
                                                        <div class="bg-light d-flex align-items-center justify-content-center" 
                                                             style="height: 150px;">
                                                            <i class="fas fa-image text-muted fa-3x"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="card-body p-3">
                                                    <h6 class="card-title mb-2">{{ Str::limit($item->product->name, 50) }}</h6>
                                                    <p class="text-primary fw-bold mb-0">${{ number_format($item->product->price, 2) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                @if($wishlistItems->count() > 6)
                                    <div class="text-center mt-3">
                                        <span class="share-preview-badge">
                                            And {{ $wishlistItems->count() - 6 }} more items...
                                        </span>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-heart text-white fa-3x mb-3"></i>
                                    <h5 class="text-white">No items in your wishlist</h5>
                                    <p class="text-white-50">Add some items to your wishlist before sharing</p>
                                    <a href="{{ route('shop') }}" class="btn btn-primary">
                                        <i class="fas fa-shopping-bag me-2"></i>Browse Products
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Existing Shares -->
                    @if($existingShares->count() > 0)
                        <div class="existing-shares-card mt-4">
                            <div class="card-header">
                                <h6 class="mb-0 text-white"><i class="fas fa-history me-2"></i>Your Recent Shares</h6>
                            </div>
                            <div class="card-body p-4">
                                @foreach($existingShares->take(3) as $share)
                                    <div class="share-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1 gradient-text">{{ $share->name }}</h6>
                                                <small class="text-muted">
                                                    Created {{ $share->created_at->diffForHumans() }} • 
                                                    {{ $share->view_count }} views
                                                    @if($share->expires_at)
                                                        • Expires {{ $share->expires_at->diffForHumans() }}
                                                    @endif
                                                </small>
                                            </div>
                                            <a href="{{ $share->share_url }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               target="_blank">
                                                <i class="fas fa-external-link-alt me-1"></i>View
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                                
                                @if($existingShares->count() > 3)
                                    <div class="text-center mt-3">
                                        <a href="{{ route('wishlist.shared.index') }}" class="btn btn-sm btn-outline-light">
                                            View All Shares
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Form validation enhancement
                const form = document.querySelector('form');
                const inputs = form.querySelectorAll('input, textarea');
                
                inputs.forEach(input => {
                    input.addEventListener('focus', function() {
                        this.parentElement.style.transform = 'translateY(-2px)';
                        this.parentElement.style.transition = 'transform 0.3s ease';
                    });
                    
                    input.addEventListener('blur', function() {
                        this.parentElement.style.transform = 'translateY(0)';
                    });
                });
                
                // Product card animations
                const productCards = document.querySelectorAll('.product-preview-card');
                productCards.forEach((card, index) => {
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
