<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bootstrap 5 Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Glide.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/css/glide.core.min.css">

    <!-- Glide.js Theme (Optional) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/css/glide.theme.min.css">
    
    <style>
        /* Quantity Controls Styling */
        .btn-qty-decrease,
        .btn-qty-increase,
        .btn-qty-decrease-new,
        .btn-qty-increase-new {
            border: 1px solid #dee2e6;
            background: #f8f9fa;
            color: #495057;
            font-weight: bold;
            transition: all 0.2s ease;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .btn-qty-decrease:hover,
        .btn-qty-increase:hover,
        .btn-qty-decrease-new:hover,
        .btn-qty-increase-new:hover {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .cart-qty-input,
        .new-product-qty {
            border-left: none !important;
            border-right: none !important;
            text-align: center;
            font-weight: bold;
        }

        .qty-icon {
            font-size: 16px;
            line-height: 1;
        }
        
        .wishlist-toggle-btn {
            transition: all 0.3s ease;
        }
        
        .wishlist-toggle-btn:hover {
            transform: translateY(-2px);
        }

        /* Glide Slider Styles */
        .glide {
            position: relative;
        }

        .glide__slide {
            padding: 0 10px;
        }

        .glide__arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 123, 255, 0.9);
            color: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 123, 255, 0.3);
        }

        .glide__arrow:hover {
            background: #007bff;
            transform: translateY(-50%) scale(1.15);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.5);
        }

        .glide__arrow:focus {
            outline: none;
            background: #007bff;
        }

        .glide__arrow--left {
            left: -25px;
        }

        .glide__arrow--right {
            right: -25px;
        }



        .product-card-slider {
            transition: all 0.3s ease;
            border: 1px solid #e3e6f0;
        }

        .product-card-slider:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            border-color: #007bff;
        }

        .product-card-slider .card-body {
            min-height: 120px;
        }

        @media (max-width: 768px) {
            .glide__arrow--left {
                left: -10px;
            }
            
            .glide__arrow--right {
                right: -10px;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- Glide.js JS -->
    <script src="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/glide.min.js"></script>
</head>
<body>
<div class="container py-4">
    {{-- Page title --}}
    <h1 class="mb-4 h3">{{ $product->name }}</h1>

    <div class="row g-4">
        {{-- Left: Product Image --}}
        <div class="col-md-6">
            <div class="border rounded p-3 text-center bg-white shadow-sm">
                <img src="{{ $product->image }}" class="img-fluid" alt="{{ $product->name }}">
            </div>
        </div>

        {{-- Right: Product Info --}}
        <div class="col-md-6">
            <div class="bg-white p-4 rounded shadow-sm">
                {{-- Price --}}
                <h4 class="text-success fw-bold">₹{{ number_format($product->price, 2) }}</h4>

                {{-- Stock Status --}}
                @php 
                    $stock = $product->stocks->first(); 
                    $cartItem = null;
                    if(auth()->check()) {
                        $cart = auth()->user()->cart;
                        if($cart) {
                            $cartItem = $cart->items()->where('product_id', $product->id)->first();
                        }
                    }
                @endphp

                @if($stock?->isOutOfStock())
                    <div class="alert alert-danger">Out of Stock</div>
                @elseif($stock?->isLowStock())
                    <div class="alert alert-warning">Only {{ $stock->qty }} left in stock!</div>
                @else
                    <div class="alert alert-success">In Stock</div>
                @endif

                {{-- Description --}}
                <p class="text-muted mt-3">{{ $product->description }}</p>

                {{-- Add to Cart Section --}}
                <div class="mt-4" id="cartSection">
                    @if($stock?->isOutOfStock())
                        {{-- Out of Stock --}}
                        <button class="btn btn-secondary btn-lg w-100 mb-3" disabled>Out of Stock</button>
                    @elseif($cartItem)
                        {{-- Product already in cart - show quantity controls --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quantity in Cart:</label>
                            <div class="input-group" style="max-width: 200px;">
                                <button class="btn btn-outline-secondary btn-qty-decrease" type="button" data-product-id="{{ $product->id }}">
                                    <span class="qty-icon">{{ $cartItem->quantity <= 1 ? '🗑️' : '−' }}</span>
                                </button>
                                <input type="number"
                                    class="form-control text-center cart-qty-input"
                                    value="{{ $cartItem->quantity }}"
                                    data-product-id="{{ $product->id }}"
                                    data-max="{{ $stock ? $stock->qty : 999 }}"
                                    min="1"
                                    readonly>
                                <button class="btn btn-outline-secondary btn-qty-increase" type="button" data-product-id="{{ $product->id }}">
                                    <span class="qty-icon">+</span>
                                </button>
                            </div>
                            @if($stock && $stock->qty <= 3)
                                <div class="text-danger small mt-1">Only {{ $stock->qty }} left in stock!</div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-success btn-lg w-100 mb-3" onclick="window.location.href='{{ route('cart.view') }}'">
                            <i class="fas fa-shopping-cart"></i> View Cart
                        </button>
                    @else
                        {{-- Product not in cart - show add to cart --}}
                        @auth
                        <form method="POST" action="{{ route('cart.ajaxAdd') }}" class="add-to-cart-form" id="productAddToCartForm">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Quantity:</label>
                                <div class="input-group" style="max-width: 200px;">
                                    <button class="btn btn-outline-secondary btn-qty-decrease-new" type="button">
                                        <span class="qty-icon">−</span>
                                    </button>
                                    <input type="number" name="quantity" value="1" min="1" max="{{ $stock ? $stock->qty : 999 }}" 
                                           class="form-control text-center new-product-qty">
                                    <button class="btn btn-outline-secondary btn-qty-increase-new" type="button">
                                        <span class="qty-icon">+</span>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </form>
                        @else
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary btn-lg w-100 mb-3" onclick="window.location.href='{{ route('login') }}'">
                                <i class="fas fa-sign-in-alt"></i> Login to Add to Cart
                            </button>
                        </div>
                        @endauth
                    @endif
                </div>

                {{-- Other Buttons --}}
                <div class="d-grid gap-2">
                    <a href="{{ route('checkout') }}" class="btn btn-warning btn-lg w-100">
                        <i class="fas fa-bolt"></i> Buy Now
                    </a>

                    @auth
                    <button type="button" class="btn btn-outline-danger w-100 wishlist-toggle-btn" data-product-id="{{ $product->id }}">
                        <span class="wishlist-icon">{{ $wishlistProductIds->contains($product->id) ? '❤️' : '🤍' }}</span>
                        <span class="wishlist-text">{{ $wishlistProductIds->contains($product->id) ? 'Remove from Wishlist' : 'Add to Wishlist' }}</span>
                    </button>
                    @else
                    <button type="button" class="btn btn-outline-danger w-100" onclick="window.location.href='{{ route('login') }}'">
                        <span class="wishlist-icon">🤍</span>
                        <span class="wishlist-text">Login to Add to Wishlist</span>
                    </button>
                    @endauth

                    <a href="#inquiryModal" class="btn btn-outline-secondary w-100" data-bs-toggle="modal">
                        <i class="fas fa-envelope"></i> Contact for Inquiry
                    </a>
                </div>

                {{-- Rating (placeholder for future use) --}}
                <div class="mt-4">
                    <strong>Rating:</strong>
                    ★★★★☆ (Coming Soon)
                </div>
            </div>
        </div>
    </div>

    {{-- Product Inquiry Modal --}}
    <div class="modal fade" id="inquiryModal" tabindex="-1" aria-labelledby="inquiryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" action="" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="inquiryModalLabel">Product Inquiry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Your Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Your Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Send Inquiry</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Similar Products --}}
    @if($similarProducts->count())
    <div class="mt-5">
        <h5 class="mb-4">Similar Products</h5>
        <div class="glide" id="similarProductsSlider">
            <div class="glide__track" data-glide-el="track">
                <ul class="glide__slides">
                    @foreach($similarProducts as $product)
                        <li class="glide__slide">
                            <div class="h-100">
                                @include('partials._single_product_card', ['product' => $product, 'wishlistProductIds' => $wishlistProductIds])
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="glide__arrows" data-glide-el="controls">
                <button class="glide__arrow glide__arrow--left" data-glide-dir="<">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="glide__arrow glide__arrow--right" data-glide-dir=">">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Initialize similar products slider
    if (document.getElementById('similarProductsSlider')) {
        const similarProductsGlide = new Glide('#similarProductsSlider', {
            type: 'carousel',
            startAt: 0,
            perView: 4,
            gap: 20,
            autoplay: 4000,
            hoverpause: true,
            keyboard: true,
            animationDuration: 600,
            animationTimingFunc: 'ease-in-out',
            breakpoints: {
                1200: { 
                    perView: 3, 
                    gap: 15 
                },
                992: { 
                    perView: 3, 
                    gap: 15 
                },
                768: { 
                    perView: 2, 
                    gap: 10 
                },
                576: { 
                    perView: 1, 
                    gap: 5 
                }
            }
        });

        // Mount the slider with error handling
        try {
            similarProductsGlide.mount();
            console.log('Similar products slider mounted successfully');
            
            // Pause/resume autoplay on hover
            const sliderElement = document.getElementById('similarProductsSlider');
            sliderElement.addEventListener('mouseenter', function() {
                similarProductsGlide.pause();
            });
            
            sliderElement.addEventListener('mouseleave', function() {
                similarProductsGlide.play();
            });

            // Add manual click handlers as backup
            $(document).on('click', '.glide__arrow--left', function(e) {
                e.preventDefault();
                e.stopPropagation();
                similarProductsGlide.go('<');
                console.log('Left arrow clicked');
            });

            $(document).on('click', '.glide__arrow--right', function(e) {
                e.preventDefault();
                e.stopPropagation();
                similarProductsGlide.go('>');
                console.log('Right arrow clicked');
            });
            
        } catch (error) {
            console.error('Error mounting similar products slider:', error);
        }
    }

    // Toast notification function
    function showToast(message, isSuccess = true) {
        Toastify({
            text: message,
            duration: 3000,
            gravity: "top",
            position: "right",
            style: {
                background: isSuccess ? "linear-gradient(to right, #00b09b, #96c93d)" : "linear-gradient(to right, #ff5f6d, #ffc371)",
            },
        }).showToast();
    }

    $(document).ready(function() {
        // Quantity controls for new products (before adding to cart)
        $(document).on('click', '.btn-qty-increase-new', function() {
            const input = $(this).siblings('.new-product-qty');
            const currentVal = parseInt(input.val()) || 1;
            const maxVal = parseInt(input.attr('max')) || 999;
            
            if (currentVal < maxVal) {
                input.val(currentVal + 1);
            } else {
                showToast('Maximum quantity reached!', false);
            }
        });

        $(document).on('click', '.btn-qty-decrease-new', function() {
            const input = $(this).siblings('.new-product-qty');
            const currentVal = parseInt(input.val()) || 1;
            
            if (currentVal > 1) {
                input.val(currentVal - 1);
            }
        });

        // Quantity controls for products already in cart
        $(document).on('click', '.btn-qty-increase', function() {
            const button = $(this);
            const input = button.siblings('.cart-qty-input');
            const productId = button.data('product-id');
            const currentQty = parseInt(input.val()) || 1;
            const maxQty = parseInt(input.data('max')) || 999;
            
            if (currentQty >= maxQty) {
                showToast('Maximum quantity reached!', false);
                return;
            }

            const newQty = currentQty + 1;
            updateCartQuantity(productId, newQty, input, button);
        });

        $(document).on('click', '.btn-qty-decrease', function() {
            const button = $(this);
            const input = button.siblings('.cart-qty-input');
            const productId = button.data('product-id');
            const currentQty = parseInt(input.val()) || 1;
            
            if (currentQty <= 1) {
                // Remove from cart
                if (confirm('Remove this item from cart?')) {
                    removeFromCart(productId);
                }
                return;
            }

            const newQty = currentQty - 1;
            updateCartQuantity(productId, newQty, input, button);
        });

        function updateCartQuantity(productId, quantity, input, button) {
            const originalQty = input.val();
            input.val(quantity);
            
            // Update the decrease button icon
            const decreaseBtn = button.siblings('.btn-qty-decrease');
            const decreaseIcon = decreaseBtn.find('.qty-icon');
            decreaseIcon.text(quantity <= 1 ? '🗑️' : '−');
            
            $.ajax({
                url: "{{ route('cart.ajaxUpdate') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    product_id: productId,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.status) {
                        showToast(response.message, true);
                    } else {
                        input.val(originalQty);
                        showToast(response.message, false);
                    }
                },
                error: function(xhr) {
                    input.val(originalQty);
                    showToast('Failed to update quantity', false);
                }
            });
        }

        function removeFromCart(productId) {
            $.ajax({
                url: "{{ route('cart.ajaxRemove') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    product_id: productId
                },
                success: function(response) {
                    if (response.status) {
                        showToast(response.message, true);
                        // Reload the page to refresh the cart section
                        location.reload();
                    } else {
                        showToast(response.message, false);
                    }
                },
                error: function(xhr) {
                    showToast('Failed to remove item', false);
                }
            });
        }

        // Add to cart form submission
        $(document).on('submit', '.add-to-cart-form', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.html();

            // Show loading state
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');

            $.ajax({
                type: 'POST',
                url: "{{ route('cart.ajaxAdd') }}",
                data: form.serialize(),
                success: function(response) {
                    // Show success toast
                    showToast(response.message);

                    // Reset button
                    submitBtn.prop('disabled', false).html(originalText);

                    // Reload page to update cart section state
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    // Reset button
                    submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        Object.values(errors).forEach(e => showToast(e[0], false));
                    } else {
                        showToast('Something went wrong!', false);
                    }
                }
            });
        });

        // Wishlist toggle functionality
        $('.wishlist-toggle-btn').on('click', function() {
            @auth
            const btn = $(this);
            const productId = btn.data('product-id');
            const icon = btn.find('.wishlist-icon');
            const text = btn.find('.wishlist-text');

            $.post("{{ route('wishlist.toggle') }}", {
                _token: "{{ csrf_token() }}",
                product_id: productId
            }, function(response) {
                if (response.status) {
                    showToast(response.message, true);
                    if (icon.text().trim() === '❤️') {
                        icon.text('🤍');
                        text.text('Add to Wishlist');
                    } else {
                        icon.text('❤️');
                        text.text('Remove from Wishlist');
                    }
                } else {
                    showToast(response.message, false);
                }
            }).fail(function() {
                showToast("Failed to update wishlist", false);
            });
            @else
            showToast("Please login to add items to wishlist", false);
            setTimeout(() => {
                window.location.href = "{{ route('login') }}";
            }, 1500);
            @endauth
        });
    });

     $(document).on('click', '.guest-wishlist', function(e) {
        e.preventDefault();
        showToast("Please login to save items for later.", false);
        setTimeout(() => {
            window.location.href = "{{ route('login') }}";
        }, 1500);
    });

    $(document).on('click', '.wishlist-toggle', function() {
        const btn = $(this);
        const icon = btn.find('.wishlist-icon');
        const productId = btn.data('product-id');

        $.post("{{ route('wishlist.toggle') }}", {
            _token: "{{ csrf_token() }}",
            product_id: productId
        }, function(response) {
            if (response.status) {
                showToast(response.message, true);
                $('#wishlistCount').text(response.wishlist_count);
                if (icon.text().trim() === '❤️') {
                    icon.text('🤍');
                } else {
                    icon.text('❤️');
                }
            } else {
                showToast(response.message, false);
            }
        }).fail(function() {
            showToast("Failed to update wishlist", false);
        });
    });
</script>
</body>
</html>