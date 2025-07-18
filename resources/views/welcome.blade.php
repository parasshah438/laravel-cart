<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bootstrap 5 Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Our Products</h2>
        <!-- Display error messages if available -->
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <!-- Display success message if available -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <!-- Product Cards -->
        <div class="row g-4"  id="productGrid">
           @include('partials._product_cards', ['products' => $products, 'wishlistProductIds' => $wishlistProductIds])
        </div>
        <!-- Load More Button -->
        <div class="text-center mt-4">
            <button id="loadMoreBtn" class="btn btn-outline-primary" data-page="2">Load More</button>
        </div>
    </div>
</body>
</html>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

    $(document).on('submit', '.add-to-cart-form', function(e) {
        e.preventDefault();
        const form = $(this);
        $.ajax({
            type: 'POST',
            url: "{{ route('cart.ajaxAdd') }}",
            data: form.serialize(),
            success: function(response) {
                showToast(response.message);
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.values(errors).forEach(e => showToast(e[0], false));
                }
            }
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

    $(document).ready(function() {
        $('#loadMoreBtn').on('click', function() {
            const button = $(this);
            const nextPage = button.data('page');
            button.prop('disabled', true).text('Loading...');
            $.ajax({
                url: `?page=${nextPage}`,
                method: 'GET',
                success: function(response) {
                    $('#productGrid').append(response.html);
                    if (response.hasMorePages) {
                        button.data('page', response.nextPage).prop('disabled', false).text('Load More');
                    } else {
                        button.remove();
                        showToast('No more products to display.', false);
                    }
                },
                error: function() {
                    alert('Something went wrong. Please try again.');
                    button.prop('disabled', false).text('Load More');
                }
            });
        });
    });
</script>