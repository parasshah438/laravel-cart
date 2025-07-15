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
    <div class="container py-4">
    <h3 class="mb-4">Your Wishlist</h3>
    <div class="mb-3 d-flex justify-content-end gap-2">
        <button id="btn-move-all" class="btn btn-sm btn-success" type="button">Move All to Cart</button>
        <button id="btn-clear-wishlist" class="btn btn-sm btn-danger" type="button">Clear Wishlist</button>
    </div>
    @if($wishlistItems->isEmpty())
        <div class="alert alert-info">You have no items in your wishlist.</div>
    @else
        <div class="row row-cols-1 row-cols-md-3 g-4">
            @foreach($wishlistItems as $item)
                @php $product = $item->product; @endphp
                <div class="col">
                    <div class="card h-100">
                        <img src="{{ $product->image }}" class="card-img-top" alt="{{ $product->name }}">
                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text">₹{{ number_format($product->price, 2) }}</p>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                                <button type="button" class="btn btn-sm btn-primary btn-move-to-cart" data-product-id="{{ $product->id }}">Move to Cart</button>
                            
                           
                               
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-from-wishlist" data-product-id="{{ $product->id }}">Remove</button>
                            
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <a href="{{ url('/') }}" class="btn btn-link mt-4">← Continue Shopping</a>
</div>
<script>
function showToast(message, success = true) {
    Toastify({
        text: message,
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: success ? "#28a745" : "#dc3545",
        stopOnFocus: true,
    }).showToast();
}
$(document).on('click', '.btn-remove-from-wishlist', function () {
    const button = $(this);
    const productId = button.data('product-id');
    const card = button.closest('.col');
    $.ajax({
        method: 'POST',
        url: "{{ route('wishlist.toggle') }}",
        data: {
            product_id: productId,
            _token: "{{ csrf_token() }}"
        },
        success: function (response) {
            if(response.status) {
                showToast(response.message);
                card.fadeOut(300, function () {
                    $(this).remove();
                    if ($('.col').length === 0) {
                        $('.container').append('<div class="alert alert-info">You have no items in your wishlist.</div>');
                    }
                });
            } else {
                showToast(res.message, false);
            }
        },
        error: function () {
            showToast('Failed to remove item', false);
        }
    });
});

$(document).on('click', '.btn-move-to-cart', function () {
    const button = $(this);
    const productId = button.data('product-id');
    const card = button.closest('.col');
    $.ajax({
        method: 'POST',
        url: "{{ route('wishlist.moveToCart') }}",
        data: {
            product_id: productId,
            quantity: 1,
            _token: "{{ csrf_token() }}"
        },
        success: function (response) {
            showToast(response.message);
            card.fadeOut(300, function () {
                $(this).remove();
                if ($('.col').length === 0) {
                    $('.container').append('<div class="alert alert-info">You have no items in your wishlist.</div>');
                }
            });
        },
        error: function (xhr) {
            let msg = 'Failed to move to cart';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            showToast(msg, false);
        }
    });
});

$(document).ready(function () {
    //Move All to Cart
    $('#btn-move-all').on('click', function () {
        const btn = $(this);
        btn.prop('disabled', true).text('Moving...');
        $.ajax({
            type: 'POST',
            url: "{{ route('wishlist.moveAllToCart') }}",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function (response) {
                if (response.status) {
                    showToast(response.message, true);
                    $('.col').fadeOut(400, function () {
                        $(this).remove();
                        if ($('.col').length === 0) {
                            $('.container').prepend('<div class="alert alert-info">You have no items in your wishlist.</div>');
                        }
                    });
                } else {
                    showToast(response.message, false);
                }
            },
            error: function () {
                showToast('Something went wrong.', false);
            },
            complete: function () {
                btn.prop('disabled', false).text('Move All to Cart');
            }
        });
    });

    //Clear Wishlist
    $('#btn-clear-wishlist').on('click', function () {
        bootbox.confirm("Are you sure you want to clear your wishlist?", function (confirmed) {
            if (!confirmed) return;

            const btn = $('#btn-clear-wishlist');
            btn.prop('disabled', true).text('Clearing...');

            $.ajax({
                type: 'POST',
                url: "{{ route('wishlist.clear') }}",
                data: {
                    _method: 'DELETE',
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    if (response.status) {
                        showToast(response.message, true);
                        $('.col').fadeOut(400, function () {
                            $(this).remove();
                            if ($('.col').length === 0) {
                                $('.container').prepend('<div class="alert alert-info">You have no items in your wishlist.</div>');
                            }
                        });
                    } else {
                        showToast(response.message, false);
                    }
                },
                error: function () {
                    showToast('Server error occurred.', false);
                },
                complete: function () {
                    btn.prop('disabled', false).text('Clear Wishlist');
                }
            });
        });
    });
});
</script>
</body>
</html>
