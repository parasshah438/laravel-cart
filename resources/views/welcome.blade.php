<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bootstrap 5 Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" 
          onerror="this.onerror=null; document.querySelector('.fa-fallback').style.display='block';">
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
        @if ($products->hasMorePages())
        <div class="text-center mt-4">
            <button id="loadMoreBtn" class="btn btn-outline-primary" data-next-page="{{ $products->currentPage() + 1 }}">Load More</button>
        </div>
        @endif
    </div>


<!-- Gift Products Modal -->
<div class="modal fade" id="giftProductsModal" tabindex="-1" aria-labelledby="giftProductsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="giftProductsModalLabel">
                    🎁 <strong>Add Something Extra Special!</strong>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Gift products will be loaded here via AJAX -->
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2">Loading gift products...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Skip
                </button>
                <button type="button" class="btn btn-primary" id="addGiftsToCart">
                    <i class="fas fa-gift"></i> Continue Shopping
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.gift-product {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.gift-product:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.gift-product.selected {
    border-color: #007bff;
    background-color: #f8f9ff;
}

.gift-qty-container {
    background: #f8f9fa;
    border-radius: 20px;
    padding: 2px;
}

.gift-qty-btn {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: none;
    background: #007bff;
    color: white;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.gift-qty-btn:hover {
    background: #0056b3;
    transform: scale(1.1);
}

.gift-qty {
    border: none;
    background: transparent;
    text-align: center;
    font-weight: bold;
    width: 40px;
}

.gift-checkbox {
    transform: scale(1.3);
    accent-color: #007bff;
}
</style>



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

    $(document).on('submit', '.add-to-cart-form1111', function(e) {
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
            const nextPage = button.data('next-page');
            button.prop('disabled', true).text('Loading...');
            $.ajax({
                url: `?page=${nextPage}`,
                method: 'GET',
                success: function(response) {
                    $('#productGrid').append(response.html);
                    if (response.hasMorePages) {
                        button.data('next-page', response.nextPage).prop('disabled', false).text('Load More');
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


    $(document).on('submit', '.add-to-cart-form', function(e) {
    e.preventDefault();
    const form = $(this);
    const submitBtn = form.find('button[type="submit"]');
    
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
            submitBtn.prop('disabled', false).html('Add to Cart');
            
            // Show gift products modal
            showGiftProductsModal(response.product_id);
        },
        error: function(xhr) {
            // Reset button
            submitBtn.prop('disabled', false).html('Add to Cart');
            
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                Object.values(errors).forEach(e => showToast(e[0], false));
            } else {
                showToast('Something went wrong!', false);
            }
        }
    });
});

// Show Gift Products Modal
function showGiftProductsModal(productId) {
    $.ajax({
        url: "{{ route('cart.giftProducts') }}",
        type: 'GET',
        data: { product_id: productId },
        success: function(response) {
            $('#giftProductsModal .modal-body').html(response.html);
            $('#giftProductsModal').modal('show');
        },
        error: function() {
            console.log('Error loading gift products');
        }
    });
}

// Handle gift products quantity change
$(document).on('click', '.gift-qty-btn', function() {
    const input = $(this).siblings('input');
    const currentVal = parseInt(input.val()) || 0;
    const isIncrement = $(this).hasClass('increment');
    
    if (isIncrement) {
        input.val(currentVal + 1);
    } else if (currentVal > 0) {
        input.val(currentVal - 1);
    }
    
    // Auto-check checkbox if quantity > 0
    const checkbox = $(this).closest('.gift-product').find('.gift-checkbox');
    checkbox.prop('checked', parseInt(input.val()) > 0);
});

// Handle gift checkbox change
$(document).on('change', '.gift-checkbox', function() {
    const qtyInput = $(this).closest('.gift-product').find('.gift-qty');
    if (!$(this).is(':checked')) {
        qtyInput.val(0);
    } else if (parseInt(qtyInput.val()) === 0) {
        qtyInput.val(1);
    }
});

// Add selected gifts to cart
$(document).on('click', '#addGiftsToCart', function() {
    const selectedGifts = [];
    
    $('.gift-product').each(function() {
        const checkbox = $(this).find('.gift-checkbox');
        const qty = parseInt($(this).find('.gift-qty').val()) || 0;
        
        if (checkbox.is(':checked') && qty > 0) {
            selectedGifts.push({
                product_id: $(this).data('product-id'),
                quantity: qty
            });
        }
    });
    
    if (selectedGifts.length > 0) {
        // Show loading
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding Gifts...');
        
        $.ajax({
            url: "{{ route('cart.addGifts') }}",
            type: 'POST',
            data: {
                gifts: selectedGifts,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showToast(response.message);
                $('#giftProductsModal').modal('hide');
            },
            error: function() {
                showToast('Error adding gifts to cart', false);
            },
            complete: function() {
                $('#addGiftsToCart').prop('disabled', false).html('Continue Shopping');
            }
        });
    } else {
        $('#giftProductsModal').modal('hide');
    }
});
</script>