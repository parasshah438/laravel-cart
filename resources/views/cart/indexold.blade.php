<!DOCTYPE html>
<html lang="en">
<head>
  <title>Bootstrap 5 Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container py-4">
    
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
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach($items as $item)
                    @php $subtotal = $item->quantity * $item->price_at_time; $total += $subtotal; @endphp
                    <tr>
                        <td>
                            <img src="{{ $item->product->image }}" width="60" class="me-2">
                            {{ $item->product->name }}
                        </td>
                        <td>
                            <form method="POST" action="{{ route('cart.update') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" style="width: 60px;" class="form-control d-inline-block">
                                <button type="submit" class="btn btn-sm btn-success">Update</button>
                            </form>
                        </td>
                        <td>₹{{ $item->price_at_time }}</td>
                        <td>₹{{ $subtotal }}</td>
                        <td>
                            <form method="POST" action="{{ route('cart.remove') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="3"><strong>Total</strong></td>
                    <td colspan="2"><strong>₹{{ $total }}</strong></td>
                </tr>
            </tbody>
        </table>
    @endif
    <a href="{{ url('/') }}" class="btn btn-primary">Continue Shopping</a>
</div>
</body>
</html>
