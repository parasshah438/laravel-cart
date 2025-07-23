<div id="cart-items-section">
    @if($cartItems->isEmpty())
        <div class="alert alert-warning">Your cart is empty.</div>
    @else
        <table class="table table-bordered">
            <tbody>
                @foreach($cartItems as $item)
                    <tr data-product-row>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₹{{ $item->product->price }}</td>
                        <!-- Add remove button, quantity update etc. -->
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
