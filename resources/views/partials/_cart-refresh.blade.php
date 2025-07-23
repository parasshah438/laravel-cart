<div id="saved-items-section">
    <h5 class="mt-5">Saved for Later</h5>
    @if($savedItems->isNotEmpty())
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