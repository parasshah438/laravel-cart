<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bootstrap 5 Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootbox@5.5.2/bootbox.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <div id="saved-items-section">
            @if($savedItems->isNotEmpty())
            <h5 class="mt-5">Saved for Later</h5>
            <table class="table table-bordered">
                @foreach($savedItems as $item)
                <tr>
                    <td>
                        <img src="{{ $item->product->image }}" width="60" class="me-2">
                        {{ $item->product->name }}
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <form method="POST" action="{{ route('cart.moveToCartFromSaved') }}" class="move-to-cart-form">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                <button type="submit" class="btn btn-sm btn-primary">Move to Cart</button>
                            </form>
                            <form method="POST" action="{{ route('cart.ajaxRemove') }}" class="remove-cart-form">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </table>
            @else
            <div class="alert alert-info">No items saved for later.</div>
            @endif
        </div>
    </div>
    <div class="container py-4">
        <div id="cart-items-section">
            @if($items->isEmpty())
            <div class="alert alert-info">Your cart is empty.</div>
            @else
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @foreach($items as $item)
                    @php $subtotal = $item->quantity * $item->price_at_time; $total += $subtotal; @endphp
                    @php
                        $stock = $item->product->stocks->first();
                        $maxStock = $stock ? $stock->qty : 0;
                    @endphp
                    <tr>
                        <td>
                            <img src="{{ $item->product->image }}" width="60" class="me-2">
                            {{ $item->product->name }}
                        </td>
                        <td>
                            <div class="input-group input-group-sm quantity-group" style="max-width: 140px;">
                                <button class="btn btn-outline-secondary btn-qty-decrease" type="button">
                                    <span class="qty-icon">{{ $item->quantity <= 1 ? '🗑️' : '−' }}</span>
                                </button>
                                <input type="number"
                                    class="form-control text-center cart-qty-input"
                                    value="{{ $item->quantity }}"
                                    data-initial="{{ $item->quantity }}"
                                    data-product-id="{{ $item->product_id }}"
                                    data-max="{{ $maxStock }}"
                                    min="1"
                                    readonly>
                                <button class="btn btn-outline-secondary btn-qty-increase" type="button">
                                    <span class="qty-icon">+</span>
                                </button>
                            </div>
                            @if($maxStock <= 3)
                                <div class="text-danger small">Only {{ $maxStock }} left in stock!</div>
                            @endif
                        </td>
                        <td>₹{{ number_format($item->price_at_time,2) }}</td>
                        <td class="item-subtotal" data-subtotal="{{ $subtotal }}" data-price="{{ $item->price_at_time }}">
                            ₹{{ number_format($subtotal, 2) }}
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <form method="POST" action="{{ route('cart.moveToWishlist') }}" class="move-to-wishlist-form">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100">♡ Move to Wishlist</button>
                                </form>
                                <form method="POST" action="{{ route('cart.ajaxRemove') }}" class="remove-cart-form">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                    <button type="submit" class="btn btn-danger btn-sm w-100">Remove</button>
                                </form>
                                <form method="POST" action="{{ route('cart.saveForLater') }}" class="save-for-later-form">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                    <button type="submit" class="btn btn-sm btn-outline-warning w-100">Save for Later</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    <tr class="cart-total-row">
                        <td colspan="3"><strong>Total</strong></td>
                        <td colspan="2"><strong>₹<span id="cart-total">{{ number_format($total, 2) }}</span></strong></td>
                    </tr>
                </tbody>
            </table>
            @endif
            @if(!$items->isEmpty())
            <div class="mb-3">
                <button class="btn btn-danger btn-sm" id="clear-cart-btn">
                    <i class="bi bi-trash"></i> Clear Cart
                </button>
            </div>
            @endif
            <a href="{{ url('/') }}" class="btn btn-primary">Continue Shopping</a>
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

    function updateCartTotal() {
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

    function refreshCartSections() {
        $('#cart-items-section').load(location.href + ' #cart-items-section > *');
        $('#saved-items-section').load(location.href + ' #saved-items-section > *');
        $('#cart-total').load(location.href + ' #cart-total');
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
                                updateCartTotal();
                                if ($('tbody tr').length <= 1) {
                                    $('table.table').remove();
                                    $('.container').prepend('<div class="alert alert-info">Your cart is now empty.</div>');
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
                            updateCartTotal();
                            if ($('tbody tr').length == 2) {
                                $('table.table').remove();  
                                $('#clear-cart-btn').hide();
                                $('.container').prepend('<div class="alert alert-info">Your cart is now empty.</div>');
                            }
                            row.fadeOut(400, function() {
                                $(this).remove();
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
                        $('table.table').remove();
                        $('#clear-cart-btn').hide();
                        $('.container').prepend('<div class="alert alert-info">Your cart is now empty.</div>');
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

                    //Remove row from DOM
                    row.fadeOut(300, function() {
                        $(this).remove();
                        updateCartTotal(); // optional
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
        button.prop('disabled', true).text('Processing...');

        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            success: function(response) {
                if (response.status) {
                    showToast(response.message, true);
                    row.fadeOut(300, function() {
                        $(this).remove();
                        refreshCartSections();
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
</script>