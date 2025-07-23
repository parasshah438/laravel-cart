@php $total = 0; @endphp
@foreach($items as $item)
@php $subtotal = $item->quantity * $item->price_at_time; $total += $subtotal; @endphp
@php
    $stock = $item->product->stocks->first();
    $maxStock = $stock ? $stock->qty : 0;
@endphp
<tr data-product-row="{{ $item->product_id }}" class="cart-item-row">
    <td>
       <img src="{{ filter_var($item->product->image, FILTER_VALIDATE_URL) ? trim($item->product->image, '"') : asset($item->product->image) }}" width="60" class="me-2">

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
