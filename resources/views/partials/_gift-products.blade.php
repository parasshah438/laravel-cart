
<div class="gift-products-container">
    <div class="text-center mb-4">
        <h6 class="text-muted">✨ <strong>Complete your order with these amazing deals!</strong> ✨</h6>
        <p class="small text-secondary">Select items to add as gifts or treats</p>
    </div>

    <div class="row g-3">
        @foreach($giftProducts as $gift)
        <div class="col-md-6">
            <div class="gift-product card h-100 p-3" data-product-id="{{ $gift->id }}">
                <div class="row g-2 align-items-center">
                    <!-- Product Image -->
                    <div class="col-4">
                        <img src="{{ $gift->image }}" 
                             alt="{{ $gift->name }}" 
                             class="img-fluid rounded"
                             style="height: 80px; width: 100%; object-fit: cover;">
                    </div>
                    
                    <!-- Product Info -->
                   <div class="col-5">
                        <h6 class="mb-1 small">{{ Str::limit($gift->name, 30) }}</h6>
                        <p class="text-primary fw-bold mb-2">₹{{ number_format($gift->price) }}</p>
                        
                        <!-- Quantity Controls -->
                        <div class="gift-qty-container d-flex align-items-center justify-content-center">
                            <button type="button" class="gift-qty-btn decrement" title="Decrease quantity">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" class="gift-qty" value="0" min="0" max="10" readonly>
                            <button type="button" class="gift-qty-btn increment" title="Increase quantity">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Checkbox -->
                    <div class="col-3 text-center">
                        <div class="form-check">
                            <input class="form-check-input gift-checkbox" 
                                   type="checkbox" 
                                   id="gift-{{ $gift->id }}">
                            <label class="form-check-label small" for="gift-{{ $gift->id }}">
                                Select
                            </label>
                        </div>
                        
                        @if($gift->price < 200)
                        <span class="badge bg-success small">Best Deal!</span>
                        @elseif($gift->price < 400)
                        <span class="badge bg-warning small">Great Value</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($giftProducts->isEmpty())
    <div class="text-center py-4">
        <i class="fas fa-gift fa-3x text-muted mb-3"></i>
        <h6 class="text-muted">No gift products available right now</h6>
        <p class="small">Check back later for amazing deals!</p>
    </div>
    @endif
</div>

<script>
// Auto-select products when quantity changes
$(document).ready(function() {
    $('.gift-checkbox').on('change', function() {
        const card = $(this).closest('.gift-product');
        if ($(this).is(':checked')) {
            card.addClass('selected');
        } else {
            card.removeClass('selected');
        }
    });
});
</script>