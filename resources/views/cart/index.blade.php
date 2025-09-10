<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bootstrap 5 Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootbox@5.5.2/bootbox.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mt-4">Your Cart</h2>
        Total Items:
        <span class="badge bg-primary">
            <i class="bi bi-cart"></i>
            <span id="cart-count">{{ $cartCount ?? 0 }}</span>
        </span>
    </div>
    <div class="container mt-4">
        <div id="saved-items-section">
            @include('partials.cart-saved-refresh', ['savedItems' => $savedItems])
        </div>
    </div>

    <div class="container py-4">
        <div id="cart-items-section">
            @include('partials.cart-items-refresh', [
                'items' => $items,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'cart' => $cart,
            ])
        </div>
    </div>
</body>
</html>

<script>
    function showToast(message, success = true) {
        Toastify({
            text: message,
            duration: 3000,
            close: false,
            gravity: "top",
            position: "right",
            backgroundColor: success ? "#28a745" : "#dc3545",
            stopOnFocus: true,
        }).showToast();
    }

    function updateCartCount() {
        $.get("{{ route('cart.count') }}", function (data) {
            $('#cart-count').text(data.count);
        });
    }  
    
    function updateCartTotal() {
        $.get("{{ route('cart.summary') }}", function(response) {
            if (response.status) {
                $('#cart-subtotal').text(response.subtotal);
                $('#cart-total').text(response.total);
                if (response.coupon_code) {
                    $('#cart-discount-row').show();
                    $('#coupon-code').text(response.coupon_code);
                    $('#cart-discount').text(response.discount);
                } else {
                    $('#cart-discount-row').hide();
                }
            }
        });
    }


    function setButtonLoading(button, loading = true, nextIcon = null) {
        const icon = button.find('.qty-icon');
        if (loading) {
            button.data('original-icon', icon.html());
            icon.html('<span class="spinner-border spinner-border-sm"></span>');
            button.prop('disabled', true);
        } else {
            const newIcon = nextIcon ?? button.data('original-icon');
            icon.html(newIcon);
            button.prop('disabled', false);
        }
    }

    function updateCartTotalbk() {
        $.get("{{ route('cart.total') }}", function(response) {
            if (response.status) {
                $('#cart-total').text(response.formatted);
            }
        });
    }

    function updateCartQty(productId, newQty, inputElement, doneCallback = () => {}) {
        $.ajax({
            type: 'POST',
            url: "{{ route('cart.ajaxUpdate') }}",
            data: {
                _token: '{{ csrf_token() }}',
                product_id: productId,
                quantity: newQty
            },
            success: function(response) {
                showToast(response.message, true);

                // Update subtotal
                const row = inputElement.closest('tr');
                const price = parseFloat(row.find('.item-subtotal').data('price'));
                const newSubtotal = newQty * price;

                const formatted = "₹" + newSubtotal.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                row.find('.item-subtotal')
                    .text(formatted)
                    .data('subtotal', newSubtotal);

                updateCartTotal();

                // Update minus button icon (− or 🗑️)
                const minusBtn = row.find('.btn-qty-decrease .qty-icon');

                minusBtn.text(newQty == "1" ? '🗑️' : '−');

                inputElement.data('initial', newQty);
            },
            error: function(xhr) {
                showToast("Failed to update quantity", false);
            },
            complete: doneCallback
        });
    }

    function refreshSavedItems() {
        $.get('/cart/saved-items-refresh', function (html) {
            $('#saved-items-section').html(html);
            // Optionally: auto-hide the section if now empty
            if ($('#saved-items-section').find('table tbody tr[data-product-row]').length === 0) {
                showEmptySavedMessage();
            }
        });
    }

    function refreshCart() {
        $.get('/cart/items/refresh', function(html) {
            $('#cart-items-section').html(html);
            // Optional: check if cart is now empty
            if ($('#cart-items-section table tbody tr[data-product-row]').length === 0) {
                showEmptyCartMessage();
            }
        });
    }

    function showEmptyCartMessage() {
        $('#cart-items-section').html(`
            <h5 class="mb-3">Cart Items</h5>
            <div class="text-center py-4">
                <h5>Your cart is empty.</h5>
                <a href="/shop" class="btn btn-primary mt-3">Continue Shopping</a>
            </div>
        `);
    }

    function showEmptySavedMessage() {
        $('#saved-items-section').html(`
            <div class="text-center py-4">
                <h5>No items in Saved for Later.</h5>
            </div>
        `);
    }

    

    $(document).on('click', '.btn-qty-increase', function() {
        const button = $(this);
        const input = $(this).siblings('.cart-qty-input');
        let qty = parseInt(input.val());
        const maxQty = parseInt(input.data('max'));
        const productId = input.data('product-id');

        if (qty >= maxQty) {
            button.prop('disabled', true).addClass('disabled');
            showToast("You cannot add more than " + maxQty + " of this item.", false);
            return;
        }

        qty += 1;
        input.val(qty);
        setButtonLoading(button, true);
        updateCartQty(productId, qty, input, function() {
            setButtonLoading(button, false);
        });
    });

    $(document).on('click', '.btn-qty-decrease', function() {
        const button = $(this);
        const input = $(this).siblings('.cart-qty-input');
        const plusButton = button.siblings('.btn-qty-increase');
        let qty = parseInt(input.val());
        const productId = input.data('product-id');
        const maxQty = parseInt(input.data('max'));

        if (qty > 1) {
            qty -= 1;
            input.val(qty);
            if (qty < maxQty) {
                plusButton.prop('disabled', false).removeClass('disabled');
            }
            setButtonLoading(button, true);
            updateCartQty(productId, qty, input, function() {
                const newIcon = qty === 1 ? '🗑️' : '−';
                setButtonLoading(button, false, newIcon);
            });
        } else {
            bootbox.confirm("Remove this product from cart?", function(result) {
                if (result) {
                    setButtonLoading(button, true);
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('cart.ajaxRemove') }}",
                        data: {
                            _token: '{{ csrf_token() }}',
                            product_id: productId
                        },
                        success: function(response) {
                            showToast(response.message, true);
                            input.closest('tr').fadeOut(400, function() {
                                $(this).remove();
                                updateCartCount();
                                updateCartTotal();
                                if ($('tbody tr[data-product-row]').length === 0) {
                                    showToast('Cart is now empty.', false);
                                    $('table.table').fadeOut(500, function () {
                                        $('table.table').remove();
                                        setTimeout(function () {
                                            window.location.reload();
                                        }, 1000);
                                    });
                                    
                                }
                            });
                        },
                        error: function(xhr) {
                            showToast("Failed to remove item", false);
                        },
                        complete: function() {
                            setButtonLoading(button, false);
                        }
                    });
                }
            });
        }
    });

    $(document).on('submit', '.update-cart-form', function(e) {
        e.preventDefault();
        const form = $(this);
        const input = form.find('input[name="quantity"]');
        const qty = parseInt(input.val());
        const subtotalCell = form.closest('tr').find('.item-subtotal');
        const price = parseFloat(subtotalCell.data('price'));
        const updateBtn = form.find('button[type="submit"]');
        const currentQty = parseInt(input.val());
        const initialQty = parseInt(input.data('initial'));
        const newSubtotal = qty * price;

        if (currentQty === initialQty) {
            showToast("Quantity unchanged.", false);
            return;
        }

        subtotalCell.hide().text("₹" + newSubtotal.toFixed(2)).fadeIn('fast').data('subtotal', newSubtotal);
        input.data('initial', qty);
        updateBtn.prop('disabled', true).text('Updating...');

        $.ajax({
            type: 'POST',
            url: "{{ route('cart.ajaxUpdate') }}",
            data: form.serialize(),
            success: function(response) {
                showToast(response.message, true);
                updateCartTotal();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.values(errors).forEach(e => showToast(e[0], false));
                }
            },
            complete: function() {
                updateBtn.prop('disabled', false).text('Update');
            }
        });
    });

    $(document).on('submit', '.remove-cart-form', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const row = form.closest('tr'); // get the row to remove on success

        bootbox.confirm({
            title: "Confirm Removal",
            message: "Are you sure you want to remove this item from the cart?",
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> Cancel'
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> Yes, remove it',
                    className: 'btn-danger'
                }
            },
            callback: function(result) {
                if (result) {
                    submitBtn.prop('disabled', true).text('Removing...');
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('cart.ajaxRemove') }}",
                        data: form.serialize(),
                        success: function(response) {
                            showToast(response.message, true);
                            row.fadeOut(400, function() {
                                $(this).remove();
                                updateCartCount();
                                updateCartTotal();
                                if ($('tbody tr[data-product-row]').length === 0) {
                                    showToast('Cart is now empty.', false);
                                    $('table.table').fadeOut(500, function () {
                                        $('table.table').remove();
                                        setTimeout(function () {
                                            window.location.reload();
                                        }, 1000);
                                    });
                                    
                                }
                            });
                        },
                        error: function(xhr) {
                            showToast("Error removing item", false);
                        },
                        complete: function() {
                            submitBtn.prop('disabled', false).text('Remove');
                        }
                    });
                }
            }
        });
    });

    $(document).on('click', '#clear-cart-btn', function() {
        bootbox.confirm("Are you sure you want to clear your cart?", function(result) {
            if (result) {
                $.ajax({
                    type: 'POST',
                    url: "{{ route('cart.clear') }}",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        showToast(response.message, true);
                        setTimeout(function () {
                            window.location.reload();
                        }, 2000);
                    },
                    error: function() {
                        showToast('Failed to clear cart.', false);
                    }
                });
            }
        });
    });

    $(document).on('submit', '.move-to-wishlist-form', function(e) {
        e.preventDefault();
        const form = $(this);
        const row = form.closest('tr');
        const button = form.find('button');
        button.prop('disabled', true).text('Moving...');
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            success: function(response) {
                if (response.status) {
                    showToast(response.message, true);
                    row.fadeOut(400, function() {
                        $(this).remove();
                        updateCartCount();
                        updateCartTotal();
                        if ($('tbody tr[data-product-row]').length === 0) {
                            showToast('Cart is now empty.', false);
                            $('table.table').fadeOut(500, function () {
                                $('table.table').remove();
                                setTimeout(function () {
                                    window.location.reload();
                                }, 1000);
                            });
                        }
                    });
                } else {
                    showToast(response.message || 'Failed to move item.', false);
                }
            },
            error: function(xhr) {
                showToast('Something went wrong.', false);
            },
            complete: function() {
                button.prop('disabled', false).text('♡ Move to Wishlist');
            }
        });
    });

    $(document).on('submit', '.save-for-later-form, .move-to-cart-form', function(e) {
        e.preventDefault();
        const form = $(this);
        const button = form.find('button[type="submit"]');
        const row = form.closest('tr');
        const isMoveToCart = form.hasClass('move-to-cart-form');
        const isSaveForLater = form.hasClass('save-for-later-form');

        button.prop('disabled', true).text('Processing...');

        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            success: function(response) {
                if (response.status) {
                    showToast(response.message, true);
                    row.fadeOut(300, function () {
                        $(this).remove();
                        
                        // Update both cart and saved items
                        refreshCart();
                        refreshSavedItems();
                        updateCartCount();
                        updateCartTotal();

                        // Check and handle empty cart case (no page reload!)
                        if (isSaveForLater && $('#cart-items-section table tbody tr[data-product-row]').length === 0) {
                            showEmptyCartMessage();
                        }

                        if (isMoveToCart && $('#saved-items-section table tbody tr[data-product-row]').length === 0) {
                            showEmptySavedMessage();
                        }
                    });
                } else {
                    showToast(response.message, false);
                }
            },
            error: function(xhr) {
                showToast("Something went wrong.", false);
            },
            complete: function() {
                button.prop('disabled', false).text('Done');
            }
        });
    });

    $(document).on('click', '#load-more-cart', function () {
        const button = $(this);
        const nextPage = button.data('next-page');
        $.ajax({
            url: "{{ route('cart.loadMore') }}",
            type: 'GET',
            data: { page: nextPage },
            beforeSend: function () {
                button.prop('disabled', true).text('Loading...');
            },
            success: function (response) {
                $('#load-more-cart').data('next-page', response.nextPage);
                if (!response.hasMorePages) $('#load-more-cart').hide();
                if (response.hasMorePages) {
                    button.data('next-page', response.nextPage).prop('disabled', false).text('Load More');
                } else {
                    button.remove();
                    showToast('No more items in cart.', false);
                }

                // Optional: Update total dynamically
                if (response.newTotal !== undefined) {
                    $('#cart-total').text(response.newTotal);
                }
            }
        });
    });

    $(document).ready(function () {
        $('#applyCouponBtn').on('click', function () {
            let code = $('#couponCode').val().trim();
            if (!code) return;

            $.ajax({
                url: '/cart/apply-coupon',
                method: 'POST',
                data: {
                    code: code,
                    _token: '{{ csrf_token() }}',
                },
                success: function (res) {
                    showToast(res.message, true);
                    $('#cart-items-container').html(res.updatedCartHtml);
                    $('#cart-totals-container').html(res.totalsHtml);
                    $('#removeCouponContainer').removeClass('d-none');
                    updateCartTotals(res.total, res.discount); // optional
                },
                error: function (err) {
                    let message = err.responseJSON?.message || "Failed to apply coupon.";
                    showToast(message, false);
                }
            });
        });

        $('#removeCouponBtn').on('click', function () {
            $.ajax({
                url: '/cart/remove-coupon',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function (res) {
                    showToast(res.message, true);
                    $('#couponMessage').removeClass('d-none text-danger').addClass('text-success').text(res.message);
                    $('#removeCouponContainer').addClass('d-none');
                    $('#couponCode').val('');
                    updateCartTotals(res.total, 0); // optional
                }
            });
        });

        function updateCartTotals(total, discount = 0) {
            $('#cartTotal').text(`₹${total.toFixed(2)}`);
            $('#discountSection').text(discount > 0 ? `Discount Applied: ₹${discount}` : '');
        }
    });

    $(document).on('click', '.remove-coupon-btn', function () {
        $.ajax({
            url: "{{ route('cart.removeCoupon') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.success) {
                    $('#cart-items-container').html(response.updatedCartHtml);
                    $('#cart-totals-container').html(response.updatedTotalsHtml);
                    showToast(response.message, true);
                } else {
                    alert(response.message || 'Something went wrong');
                }
            },
            error: function () {
                alert('Server error removing coupon');
            }
        });
    });

    // ✅ MANUAL COUPON SYSTEM (No Auto-Load)

    // View all coupons modal
    $(document).on('click', '#viewAllCouponsBtn', function() {
        loadAllCouponsModal();
    });

    // Load ALL coupons in modal (comprehensive view)
    function loadAllCouponsModal() {
        $('#couponsModal').modal('show');
        $('#available-coupons-container').html(`
            <div class="text-center py-4">
                <div class="spinner-border" role="status"></div>
                <div class="mt-2">Loading all available offers...</div>
            </div>
        `);
        
        $.get("{{ route('cart.availableCoupons') }}?mode=all", function(response) {
            if (response.status) {
                displayAllCouponsModal(response);
            }
        });
    }

    // Display comprehensive coupon view in modal
    function displayAllCouponsModal(response) {
        let html = '';

        // Available Coupons Section
        if (response.available_coupons.length > 0) {
            html += `<div class="mb-4">
                <h6 class="text-success d-flex align-items-center mb-3">
                    <i class="fas fa-check-circle me-2"></i>
                    Ready to Use (${response.available_coupons.length})
                </h6>`;
            response.available_coupons.forEach(coupon => {
                html += createCouponCard(coupon, true);
            });
            html += '</div>';
        }

        // Near Miss Coupons (Smart Upsell)
        if (response.near_miss_coupons && response.near_miss_coupons.length > 0) {
            html += `<div class="mb-4">
                <h6 class="text-warning d-flex align-items-center mb-3">
                    <i class="fas fa-target me-2"></i>
                    Almost There (${response.near_miss_coupons.length})
                </h6>`;
            response.near_miss_coupons.forEach(coupon => {
                html += createCouponCard(coupon, false, true); // true for near-miss styling
            });
            html += '</div>';
        }

        // Other Unavailable Coupons
        if (response.other_coupons && response.other_coupons.length > 0) {
            html += `<div class="mb-4">
                <h6 class="text-muted d-flex align-items-center mb-3">
                    <i class="fas fa-lock me-2"></i>
                    Other Offers (${response.other_coupons.length})
                </h6>`;
            response.other_coupons.forEach(coupon => {
                html += createCouponCard(coupon, false);
            });
            html += '</div>';
        }

        if (!html) {
            html = '<div class="text-center py-4 text-muted">No coupons available at the moment.</div>';
        }

        $('#available-coupons-container').html(html);
    }

    // Create professional coupon card with smart styling
    function createCouponCard(coupon, isAvailable, isNearMiss = false) {
        let cardClass = 'card mb-3 coupon-card';
        let opacity = '1';
        let buttonHtml = '';
        
        if (isAvailable) {
            cardClass += ' border-success';
            buttonHtml = `<button class="btn btn-primary btn-sm apply-coupon-btn" data-code="${coupon.code}">
                <i class="fas fa-tag me-1"></i>Apply Now
            </button>`;
        } else if (isNearMiss) {
            cardClass += ' border-warning';
            opacity = '0.9';
            buttonHtml = `<button class="btn btn-outline-warning btn-sm">
                <i class="fas fa-shopping-cart me-1"></i>Add More Items
            </button>`;
        } else {
            cardClass += ' border-light';
            opacity = '0.6';
            buttonHtml = `<button class="btn btn-secondary btn-sm" disabled>
                <i class="fas fa-lock me-1"></i>Not Available
            </button>`;
        }

        const statusIcon = isAvailable 
            ? '<i class="fas fa-check-circle text-success"></i>'
            : isNearMiss 
                ? '<i class="fas fa-exclamation-triangle text-warning"></i>'
                : '<i class="fas fa-times-circle text-muted"></i>';

        return `
            <div class="${cardClass}" style="opacity: ${opacity}">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-2">
                                <div class="coupon-badge me-3" style="background: ${coupon.banner_color}">
                                    <span class="text-white fw-bold">${coupon.code}</span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center">
                                        <h6 class="mb-0 me-2">${coupon.title}</h6>
                                        ${statusIcon}
                                    </div>
                                    <span class="text-primary fw-bold">${coupon.display_info.discount_text}</span>
                                </div>
                            </div>
                            <p class="small text-muted mb-2">${coupon.description}</p>
                            <div class="small mb-2">
                                ${coupon.display_info.min_order_text ? `<span class="badge bg-light text-dark me-2">${coupon.display_info.min_order_text}</span>` : ''}
                                <span class="badge bg-light text-dark">${coupon.display_info.expires_text}</span>
                                ${coupon.category ? `<span class="badge bg-info text-white ms-2">${coupon.category.replace('_', ' ').toUpperCase()}</span>` : ''}
                            </div>
                            ${coupon.terms ? `<div class="small text-muted mb-1"><strong>T&C:</strong> ${coupon.terms}</div>` : ''}
                            ${coupon.reason ? `<div class="small ${isNearMiss ? 'text-warning' : 'text-danger'} fw-bold">${coupon.reason}</div>` : ''}
                            ${coupon.savings_text ? `<div class="small text-success fw-bold mt-1">${coupon.savings_text}</div>` : ''}
                        </div>
                        <div class="col-md-4 text-end">
                            ${buttonHtml}
                            ${isAvailable && coupon.discount_amount ? `<div class="small text-success mt-2 fw-bold">💰 Save ₹${Math.round(coupon.discount_amount)}</div>` : ''}
                            ${isNearMiss && coupon.gap_amount ? `<div class="small text-warning mt-2">+₹${Math.round(coupon.gap_amount)} needed</div>` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Apply coupon from cards
    $(document).on('click', '.apply-coupon-btn', function() {
        const code = $(this).data('code');
        $('#couponCode').val(code);
        $('#applyCouponBtn').click();
        $('#couponsModal').modal('hide');
    });

    // ✅ PROFESSIONAL CHECKOUT BUTTON (Cart → Checkout)
    $(document).on('click', '#checkoutButton', function(e) {
        e.preventDefault();
        
        const btn = $(this);
        const originalText = btn.html();
        
        // Check if cart has items
        const cartItems = $('#cart-items-container tr[data-product-row]').length;
        if (cartItems === 0) {
            showToast('Your cart is empty. Add some items first!', false);
            return;
        }
        
        // Show loading state
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Proceeding...');
        
        // Redirect to checkout with applied coupon preserved
        setTimeout(() => {
            window.location.href = "{{ route('checkout') }}";
        }, 500);
    });

</script>

<style>
    .coupon-preview {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .coupon-preview:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transform: translateY(-1px);
    }
    
    .coupon-badge {
        padding: 8px 12px;
        border-radius: 8px;
        min-width: 80px;
        text-align: center;
    }
    
    .coupon-card {
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
    }
    
    .coupon-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .near-miss-coupon {
        background: linear-gradient(135deg, #fff3cd, #ffffff);
        border-left: 4px solid #ffc107 !important;
    }
    
    .coupon-card.border-success {
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.2);
    }
    
    .coupon-card.border-warning {
        box-shadow: 0 2px 8px rgba(255, 193, 7, 0.2);
    }
</style>

