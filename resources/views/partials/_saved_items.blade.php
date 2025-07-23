@foreach($savedItems as $item)
<tr data-product-row="{{ $item->product_id }}" class="saved-item-row">
    <td>
        <img src="{{ asset($item->product->image) }}" width="60" class="me-2">
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
