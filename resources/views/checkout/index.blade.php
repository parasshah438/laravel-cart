<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bootstrap 5 Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- Glide.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/css/glide.core.min.css">
    <!-- Glide.js Theme (Optional) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/css/glide.theme.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- Glide.js JS -->
    <script src="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/glide.min.js"></script>
</head>
<body>
    <div class="container py-5">
        <div class="mb-4">
            <h5 class="mb-3">Select a Shipping Address</h5>
            <div class="row g-4">
                @if($user->addresses->isEmpty())
                    @include('partials._address_form', [
                    'countries' => $countries,
                    'cartItems' => $cartItems,
                    'savedItems' => $savedItems
                    ])
                @else
                <!-- Left Column: Addresses -->
                <div class="col-md-7">
                    @foreach($user->addresses as $address)
                    <div class="card p-3 mb-3 {{ $address->is_default ? 'border-primary' : 'border' }}">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <div>
                                <strong>{{ $address->full_name }}</strong><br>
                                {{ $address->address_line_1 }}<br>
                                {{ $address->city->name ?? '' }}, {{ $address->state->name ?? '' }}<br>
                                {{ $address->country->name ?? '' }} - {{ $address->postal_code }}<br>
                                <span class="text-muted">Phone: {{ $address->phone_number }}</span>
                            </div>

                            <div class="text-end">
                                @if(!$address->is_default)
                                <form action="{{ route('address.setDefault', $address->id) }}" method="POST" class="mb-2">
                                    @csrf
                                    <button class="btn btn-outline-primary btn-sm">Set as Default</button>
                                </form>
                                @else
                                <span class="badge bg-success mb-2">Default Address</span><br>
                                @endif

                                <a href="{{ route('address.edit', $address->id) }}" class="btn btn-warning btn-sm mb-1">Edit</a>
                                <form action="{{ route('address.destroy', $address->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button onclick="return confirm('Are you sure?')" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Right Column: Your Order Summary -->
                <div class="col-md-5">
                    <div class="bg-white p-4 rounded shadow-sm">
                        <h4 class="mb-3">Your Order</h4>

                        @php $subtotal = 0; @endphp
                        @foreach($cartItems as $item)
                        @php $lineTotal = $item->product->price * $item->quantity; @endphp
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong>{{ $item->product->name }}</strong><br>
                                <small class="text-muted">x {{ $item->quantity }}</small>
                            </div>
                            <div>₹{{ number_format($lineTotal, 2) }}</div>
                        </div>
                        @php $subtotal += $lineTotal; @endphp
                        @endforeach

                        <div class="border-top pt-3 mt-3">
                            <div class="d-flex justify-content-between">
                                <span>Subtotal</span>
                                <span>₹{{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold mt-2">
                                <span>Total</span>
                                <span>₹{{ number_format($subtotal, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    <script>
        const shipCheckbox = document.getElementById('ship_different');
        const shipForm = document.getElementById('shipping-address-section');
        shipCheckbox.addEventListener('change', () => {
            shipForm.style.display = shipCheckbox.checked ? 'block' : 'none';
        });
    </script>
</body>
</html>