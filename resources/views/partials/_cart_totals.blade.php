<tr>
    <td colspan="3"><strong>Subtotal</strong></td>
     <td colspan="2">₹<span id="cart-subtotal">{{ number_format($subtotal, 2) }}</span></td>
</tr>
<tr id="cart-discount-row" @if(!$cart->appliedCoupon) style="display:none;" @endif>
    <td colspan="3">
        Coupon Applied: <strong id="coupon-code">{{ $cart->appliedCoupon->code ?? '' }}</strong>
        <button type="button" class="btn btn-sm btn-link text-danger remove-coupon-btn">Remove</button>
    </td>
    <td colspan="2" class="text-success">- ₹<span id="cart-discount">{{ number_format($discount, 2) }}</span></td>
</tr>
<tr class="cart-total-row">
    <td colspan="3"><strong>Total</strong></td>
    <td colspan="2"><strong>₹<span id="cart-total">{{ number_format($total, 2) }}</span></strong></td>
</tr>
<tr>
    <td colspan="5" class="text-end">
        <button class="btn btn-primary" id="checkoutButton">Proceed to Checkout</button>
    </td>
</tr>
