<!DOCTYPE html>
<html lang="en">
<head>
    <title>Checkout - Secure Payment</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- Razorpay Checkout Script -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <div class="container py-5">
        <div class="row">
            <!-- Main Checkout Form -->
            <div class="col-lg-8">
                <form id="checkoutForm" action="{{ route('checkout.placeOrder') }}" method="POST">
                    @csrf
                    
                    <!-- Address Selection Section -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                Delivery Address
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($user->addresses->isEmpty())
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    No delivery address found. Please add an address first.
                                    <a href="{{ route('address.create') }}" class="btn btn-sm btn-primary ms-2">Add Address</a>
                                </div>
                                
                                <!-- New Address Form -->
                                <div id="new-address-form">
                                    {{-- TEMPORARILY DISABLED TO FIX FORM SUBMISSION ISSUE --}}
                                    {{-- @include('partials._address_form', ['countries' => $countries]) --}}
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Please use the address management to add addresses before placing an order.
                                        <a href="{{ route('address.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i>Add Address Now
                                        </a>
                                    </div>
                                </div>
                            @else
                                <!-- Existing Addresses -->
                                <div class="row">
                                    @foreach($user->addresses as $address)
                                        <div class="col-md-6 mb-3">
                                            <input type="radio" 
                                                   class="btn-check" 
                                                   name="address_id" 
                                                   id="address_{{ $address->id }}" 
                                                   value="{{ $address->id }}"
                                                   form="checkoutForm"
                                                   {{ $address->is_default ? 'checked' : '' }}
                                                   required>
                                            <label class="btn btn-outline-secondary w-100 text-start p-3" for="address_{{ $address->id }}">
                                                <div class="fw-bold">{{ $address->full_name }}</div>
                                                <div class="text-muted small">
                                                    {{ $address->address_line_1 }}<br>
                                                    {{ $address->city->name ?? '' }}, {{ $address->state->name ?? '' }} {{ $address->postal_code }}<br>
                                                    {{ $address->country->name ?? '' }}
                                                </div>
                                                <div class="text-primary small mt-1">
                                                    <i class="fas fa-phone me-1"></i>{{ $address->phone_number }}
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <!-- Add New Address Option -->
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-outline-primary" onclick="toggleNewAddressForm()">
                                        <i class="fas fa-plus me-2"></i>Add New Address
                                    </button>
                                </div>
                                
                                <!-- New Address Form (Hidden) -->
                                <div id="new-address-form" class="d-none mt-4">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <span>Add New Address</span>
                                        </div>
                                        <div class="card-body">
                                            {{-- TEMPORARILY DISABLED TO FIX FORM SUBMISSION ISSUE --}}
                                            {{-- @include('partials._address_form', ['countries' => $countries]) --}}
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Please use the address management to add new addresses. 
                                                <a href="{{ route('address.create') }}" class="btn btn-sm btn-primary ms-2">
                                                    Manage Addresses
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Delivery Details Section -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-truck me-2"></i>
                                Delivery Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Delivery Date Selection -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Select Delivery Date</h6>
                                <div class="row" id="deliveryDateOptions">
                                    @foreach($deliveryDates as $index => $date)
                                        <div class="col-md-4 col-sm-6 mb-3">
                                            <input type="radio" 
                                                   class="btn-check" 
                                                   name="delivery_date" 
                                                   id="date_{{ $index }}" 
                                                   value="{{ $date['date'] }}"
                                                   form="checkoutForm"
                                                   {{ $index === 0 ? 'checked' : '' }}
                                                   required>
                                            <label class="btn btn-outline-primary w-100 py-3" for="date_{{ $index }}">
                                                <div class="fw-bold">{{ $date['label'] }}</div>
                                                <small class="text-muted">{{ $date['formatted'] }}</small>
                                            </label>
                                        </div>
                                    @endforeach
                                    
                                    <!-- Custom Date Picker -->
                                    <div class="col-md-4 col-sm-6 mb-3">
                                        <input type="radio" 
                                               class="btn-check" 
                                               name="delivery_date" 
                                               id="custom_date" 
                                               value=""
                                               form="checkoutForm">
                                        <label class="btn btn-outline-primary w-100 py-3" for="custom_date">
                                            <div class="fw-bold">Choose Date</div>
                                            <small class="text-muted">Pick custom date</small>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Custom Date Input -->
                                <div id="customDatePicker" class="mt-3" style="display: none;">
                                    <input type="date" 
                                           class="form-control" 
                                           name="custom_delivery_date"
                                           id="custom_date_input" 
                                           form="checkoutForm"
                                           min="{{ date('Y-m-d') }}"
                                           placeholder="Select date">
                                </div>
                                @error('delivery_date')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Shipping Method Selection -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Select Shipping Method</h6>
                                <div class="row" id="shippingMethods">
                                    @foreach($shippingMethods as $key => $method)
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <input type="radio" 
                                                   class="btn-check shipping-method" 
                                                   name="shipping_method" 
                                                   id="shipping_{{ $key }}" 
                                                   value="{{ $key }}"
                                                   data-cost="{{ $method['cost'] }}"
                                                   form="checkoutForm"
                                                   {{ $key === 'standard' ? 'checked' : '' }}
                                                   required>
                                            <label class="btn btn-outline-success w-100 py-3 text-center" for="shipping_{{ $key }}">
                                                <div class="mb-2">
                                                    <i class="{{ $method['icon'] }} fa-2x text-primary"></i>
                                                </div>
                                                <div class="fw-bold">{{ $method['name'] }}</div>
                                                <small class="text-muted d-block">{{ $method['description'] }}</small>
                                                <div class="text-success fw-bold mt-1">₹{{ $method['cost'] }}</div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('shipping_method')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Time Slot Selection -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Select Time Slot</h6>
                                <div class="row" id="timeSlots">
                                    <!-- Time slots will be loaded dynamically based on shipping method -->
                                </div>
                                @error('time_slot')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Delivery Instructions -->
                            <div class="mb-3">
                                <label for="delivery_instructions" class="form-label fw-bold">Delivery Instructions (Optional)</label>
                                <textarea class="form-control" 
                                          id="delivery_instructions" 
                                          name="delivery_instructions" 
                                          rows="3" 
                                          placeholder="Any special instructions for delivery (e.g., Ring bell, Call before delivery, etc.)"
                                          maxlength="500">{{ old('delivery_instructions') }}</textarea>
                                <div class="form-text">Maximum 500 characters</div>
                                @error('delivery_instructions')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Section -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="fas fa-credit-card me-2"></i>
                                Payment Method
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Cash on Delivery -->
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                                <label class="form-check-label fw-bold" for="cod">
                                    <i class="fas fa-money-bill-wave me-2 text-success"></i>
                                    Cash on Delivery (COD)
                                </label>
                                <div class="text-muted small mt-1">Pay when your order is delivered</div>
                            </div>

                            <!-- Razorpay Online Payment -->
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="razorpay" value="razorpay">
                                <label class="form-check-label fw-bold" for="razorpay">
                                    <i class="fas fa-credit-card me-2 text-primary"></i>
                                    Online Payment (Razorpay)
                                </label>
                                <div class="text-muted small mt-1">Pay securely with Credit/Debit Card, Net Banking, UPI, Wallets</div>
                                
                                <!-- Payment Methods Icons -->
                                <div class="mt-2 d-flex flex-wrap gap-2">
                                    <span class="badge bg-light text-dark border"><i class="fab fa-stripe-s me-1"></i>Stripe</span>
                                    <span class="badge bg-light text-dark border"><i class="fab fa-cc-visa me-1"></i>Visa</span>
                                    <span class="badge bg-light text-dark border"><i class="fab fa-cc-mastercard me-1"></i>Mastercard</span>
                                    <span class="badge bg-light text-dark border"><i class="fab fa-cc-amex me-1"></i>Amex</span>
                                </div>
                            </div>

                            <!-- Stripe Online Payment -->
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="stripe" value="stripe">
                                <label class="form-check-label fw-bold" for="stripe">
                                    <i class="fab fa-stripe me-2 text-info"></i>
                                    Online Payment (Stripe)
                                </label>
                                <div class="text-muted small mt-1">Pay securely with Credit/Debit Card worldwide</div>
                                
                                <!-- Payment Methods Icons -->
                                <div class="mt-2 d-flex flex-wrap gap-2">
                                    <span class="badge bg-light text-dark border"><i class="fab fa-cc-visa me-1"></i>Visa</span>
                                    <span class="badge bg-light text-dark border"><i class="fab fa-cc-mastercard me-1"></i>MasterCard</span>
                                    <span class="badge bg-light text-dark border"><i class="fas fa-university me-1"></i>Net Banking</span>
                                    <span class="badge bg-light text-dark border"><i class="fas fa-mobile-alt me-1"></i>UPI</span>
                                    <span class="badge bg-light text-dark border"><i class="fas fa-wallet me-1"></i>Wallets</span>
                                </div>
                                
                                <!-- Razorpay Security Info -->
                                <div class="mt-2 p-2 bg-light rounded">
                                    <small class="text-muted">
                                        <i class="fas fa-shield-alt text-success me-1"></i>
                                        <strong>100% Secure:</strong> Your payment information is encrypted and secure. Powered by Razorpay.
                                    </small>
                                </div>
                            </div>

                            <!-- Security Badge -->
                            <div class="mt-3 p-3 bg-success bg-opacity-10 rounded">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-lock fa-2x text-success me-3"></i>
                                    <div>
                                        <h6 class="mb-1 text-success">Secure Payment</h6>
                                        <small class="text-muted">Your payment information is protected with 256-bit SSL encryption</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Place Order Button -->
                    <div class="d-grid">
                        <!-- Debug: Add hidden inputs to test -->
                        <input type="hidden" name="debug_delivery_date" id="debug_delivery_date" value="">
                        <input type="hidden" name="debug_shipping_method" id="debug_shipping_method" value="">
                        <input type="hidden" name="debug_time_slot" id="debug_time_slot" value="">
                        <input type="hidden" name="debug_address_id" id="debug_address_id" value="">
                        
                        <button type="submit" class="btn btn-primary btn-lg py-3" id="placeOrderBtn">
                            <i class="fas fa-lock me-2"></i>
                            Place Order Securely
                        </button>
                    </div>
                </form>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="col-lg-4">
                <div class="card sticky-top shadow-sm" style="top: 20px;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <!-- Cart Items -->
                        <div class="order-items mb-3">
                            @foreach($cartItems as $item)
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <img src="{{ $item->product->image_url ?? '/images/placeholder.jpg' }}" 
                                         alt="{{ $item->product->name }}" 
                                         class="rounded me-3" 
                                         style="width: 60px; height: 60px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $item->product->name }}</h6>
                                        <small class="text-muted">Qty: {{ $item->quantity }}</small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">₹{{ number_format($item->price_at_time * $item->quantity, 2) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Order Totals -->
                        <div class="order-totals">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>₹{{ number_format($subtotal, 2) }}</span>
                            </div>
                            
                            @if($discount > 0)
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Discount ({{ $appliedCoupon->code ?? '' }}):</span>
                                    <span>-₹{{ number_format($discount, 2) }}</span>
                                </div>
                            @endif
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span id="shippingCost">₹25.00</span>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between fw-bold fs-5 text-primary">
                                <span>Total:</span>
                                <span id="grandTotal">₹{{ number_format($total + 25, 2) }}</span>
                            </div>
                        </div>

                        <!-- Delivery Summary -->
                        <div class="delivery-summary mt-4 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-2">Delivery Summary</h6>
                            <div class="small">
                                <div class="mb-1">
                                    <strong>Date:</strong> <span id="selectedDate">{{ $deliveryDates[0]['formatted'] ?? 'Not selected' }}</span>
                                </div>
                                <div class="mb-1">
                                    <strong>Method:</strong> <span id="selectedMethod">Standard Delivery</span>
                                </div>
                                <div>
                                    <strong>Time:</strong> <span id="selectedTime">Not selected</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- Edit Address Modal --}}
<div class="modal fade" id="editAddressModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">✏️ Edit Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-address-form" method="POST">
                    @csrf
                    @method('PUT')
                    
                    {{-- Address Type Selection --}}
                    <div class="mb-4">
                        <h6 class="mb-3">Address Type</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address Type</label>
                                <select name="type" id="edit_type" class="form-select" required>
                                    <option value="home">🏠 Home</option>
                                    <option value="work">🏢 Work</option>
                                    <option value="other">📍 Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address Label <span class="text-muted">(Optional)</span></label>
                                <input type="text" name="label" id="edit_label" class="form-control" 
                                       placeholder="e.g., Mom's House, Office">
                            </div>
                        </div>
                    </div>

                    {{-- Personal Details --}}
                    <div class="mb-4">
                        <h6 class="mb-3">Personal Details</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_full_name" class="form-label">Full Name *</label>
                                <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_phone_number" class="form-label">Mobile Number *</label>
                                <input type="tel" name="phone_number" id="edit_phone_number" class="form-control" 
                                       required maxlength="10" pattern="[0-9]{10}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_alternate_phone" class="form-label">Alternate Phone <span class="text-muted">(Optional)</span></label>
                            <input type="tel" name="alternate_phone" id="edit_alternate_phone" class="form-control" maxlength="10">
                        </div>
                    </div>

                    {{-- Address Details --}}
                    <div class="mb-4">
                        <h6 class="mb-3">Address Details</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_postal_code" class="form-label">PIN Code *</label>
                                <input type="text" name="postal_code" id="edit_postal_code" class="form-control" 
                                       required maxlength="6" pattern="[0-9]{6}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_address_line_1" class="form-label">Flat, House no., Building *</label>
                            <input type="text" name="address_line_1" id="edit_address_line_1" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_address_line_2" class="form-label">Area, Street <span class="text-muted">(Optional)</span></label>
                            <input type="text" name="address_line_2" id="edit_address_line_2" class="form-control">
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_landmark" class="form-label">Landmark <span class="text-muted">(Optional)</span></label>
                            <input type="text" name="landmark" id="edit_landmark" class="form-control">
                        </div>

                        {{-- Location Fields --}}
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="edit_country_id" class="form-label">Country *</label>
                                <select name="country_id" id="edit_country_id" class="form-select" required>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="edit_state_id" class="form-label">State *</label>
                                <select name="state_id" id="edit_state_id" class="form-select" required>
                                    <option value="">Select State</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="edit_city_id" class="form-label">City *</label>
                                <select name="city_id" id="edit_city_id" class="form-select" required>
                                    <option value="">Select City</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Business Details --}}
                    <div class="mb-4">
                        <h6 class="mb-3">Business Details <span class="text-muted">(Optional)</span></h6>
                        <div class="mb-3">
                            <label for="edit_gst_number" class="form-label">GST Number</label>
                            <input type="text" name="gst_number" id="edit_gst_number" class="form-control" 
                                   placeholder="22AAAAA0000A1Z5" maxlength="15">
                        </div>
                    </div>

                    {{-- Delivery Instructions --}}
                    <div class="mb-4">
                        <h6 class="mb-3">Delivery Instructions <span class="text-muted">(Optional)</span></h6>
                        <textarea name="delivery_instructions" id="edit_delivery_instructions" 
                                  class="form-control" rows="3"></textarea>
                    </div>

                    {{-- Default Settings --}}
                    <div class="mb-4">
                        <h6 class="mb-3">Default Settings</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_default_billing" 
                                           id="edit_is_default_billing" value="1">
                                    <label class="form-check-label" for="edit_is_default_billing">
                                        💳 Default Billing
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_default_shipping" 
                                           id="edit_is_default_shipping" value="1">
                                    <label class="form-check-label" for="edit_is_default_shipping">
                                        📦 Default Shipping
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_default" 
                                       id="edit_is_default" value="1">
                                <label class="form-check-label" for="edit_is_default">
                                    ⭐ Make this my default address
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Location Detection Button --}}
                    <div class="mb-4">
                        <h6 class="mb-3">Quick Fill</h6>
                        <div class="d-grid">
                            <button type="button" class="btn btn-outline-primary" onclick="getCurrentLocationForEdit()">
                                🌍 Use My Current Location
                            </button>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            💡 Automatically detect and fill your current address details
                        </small>
                    </div>

                    {{-- Hidden Fields for Location Data --}}
                    <input type="hidden" name="latitude" id="edit_latitude">
                    <input type="hidden" name="longitude" id="edit_longitude">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateAddress()">
                    💾 Update Address
                </button>
            </div>
        </div>
    </div>
</div>                   


    <script>
        // Ship different address functionality (only if elements exist)
        const shipCheckbox = document.getElementById('ship_different');
        const shipForm = document.getElementById('shipping-address-section');
        
        if (shipCheckbox && shipForm) {
            shipCheckbox.addEventListener('change', () => {
                shipForm.style.display = shipCheckbox.checked ? 'block' : 'none';
            });
        }


// Delete address function
async function deleteAddress(addressId) {
    // Confirm deletion
    if (!confirm('Are you sure you want to delete this address? This action cannot be undone.')) {
        return;
    }
    
    try {
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                         document.querySelector('input[name="_token"]')?.value;
        
        console.log(`Attempting to delete address ID: ${addressId}`); // Debug log
        
        const response = await fetch(`/address/${addressId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        console.log('Delete response:', data); // Debug log
        
        if (data.success) {
            showSuccess(data.message || 'Address deleted successfully!');
            
            // Reload the page after a short delay to show updated address list
            setTimeout(() => {
                location.reload();
            }, 1000);
            
        } else {
            showError(data.message || 'Failed to delete address');
            console.error('Delete failed:', data);
        }
        
    } catch (error) {
        console.error('Error deleting address:', error);
        showError('Network error while deleting address. Please try again.');
    }
}

// Open edit modal and populate with address data
async function openEditModal(addressId) {
   
    try {
        // Use API route for fetching address data
        const response = await fetch(`/api/load-address/${addressId}`);
        const data = await response.json();
        if (data.success) {
            const address = data.address;
            
            // Populate form fields
            document.getElementById('edit_type').value = address.type;
            document.getElementById('edit_label').value = address.label || '';
            document.getElementById('edit_full_name').value = address.full_name;
            document.getElementById('edit_phone_number').value = address.phone_number;
            document.getElementById('edit_alternate_phone').value = address.alternate_phone || '';
            document.getElementById('edit_postal_code').value = address.postal_code;
            document.getElementById('edit_address_line_1').value = address.address_line_1;
            document.getElementById('edit_address_line_2').value = address.address_line_2 || '';
            document.getElementById('edit_landmark').value = address.landmark || '';
            document.getElementById('edit_gst_number').value = address.gst_number || '';
            document.getElementById('edit_delivery_instructions').value = address.delivery_instructions || '';
            
            // Set checkboxes
            document.getElementById('edit_is_default_billing').checked = address.is_default_billing;
            document.getElementById('edit_is_default_shipping').checked = address.is_default_shipping;
            document.getElementById('edit_is_default').checked = address.is_default;
            
            // Set location fields
            document.getElementById('edit_country_id').value = address.country_id;
            await loadEditStates(address.country_id, address.state_id);
            await loadEditCities(address.state_id, address.city_id);
            
            // Set form action to use resource route for UPDATE
            document.getElementById('edit-address-form').action = `/address/${addressId}`;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editAddressModal'));
            modal.show();
        } else {
            showError(data.message || 'Failed to load address');
        }
    } catch (error) {
        alert(error.message);
        console.error('Error loading address:', error);
        showError('Network error while loading address');
    }
}

// Load states for edit modal
async function loadEditStates(countryId, selectedStateId = null) {
    const stateSelect = document.getElementById('edit_state_id');
    
    try {
        const response = await fetch(`/api/states/${countryId}`);
        const states = await response.json();
        
        stateSelect.innerHTML = '<option value="">Select State</option>';
        states.forEach(state => {
            const selected = selectedStateId && state.id == selectedStateId ? 'selected' : '';
            stateSelect.innerHTML += `<option value="${state.id}" ${selected}>${state.name}</option>`;
        });
        
    } catch (error) {
        console.error('Error loading states:', error);
    }
}

// Load cities for edit modal
async function loadEditCities(stateId, selectedCityId = null) {
    const citySelect = document.getElementById('edit_city_id');
    
    try {
        const response = await fetch(`/api/cities/${stateId}`);
        const cities = await response.json();
        
        citySelect.innerHTML = '<option value="">Select City</option>';
        cities.forEach(city => {
            const selected = selectedCityId && city.id == selectedCityId ? 'selected' : '';
            citySelect.innerHTML += `<option value="${city.id}" ${selected}>${city.name}</option>`;
        });
        
    } catch (error) {
        console.error('Error loading cities:', error);
    }
}

// Update address function
// Enhanced update address function

// Fixed updateAddress function
async function updateAddress() {
    const form = document.getElementById('edit-address-form');
    const formData = new FormData(form);
    const updateBtn = document.querySelector('#editAddressModal .btn-primary');
    
    // Clear previous errors
    clearFormErrors();
    
    // Show loading
    updateBtn.disabled = true;
    updateBtn.innerHTML = '⏳ Updating...';
    //alert(form.action);
    //return false;
    try {
        console.log('Form action:', form.action); // Debug log instead of alert
        
        const response = await fetch(form.action, {
            method: 'POST', // Laravel handles method spoofing with _method field
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message || 'Address updated successfully!');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editAddressModal'));
            //modal.hide();
            
            // Refresh page to show updated address
            // setTimeout(() => {
            //     window.location.reload();
            // }, 1000);
        } else {
            if (data.errors) {
                showErrors(data.errors);
            }
            showError(data.message || 'Please fix the errors and try again');
        }
        
    } catch (error) {
        console.error('Error updating address:', error);
        showError('Network error. Please check your connection and try again.');
    } finally {
        updateBtn.disabled = false;
        updateBtn.innerHTML = '💾 Update Address';
    }
}


// Add these helper functions after your existing scripts
function clearFormErrors() {
    // Remove existing error messages
    document.querySelectorAll('.error-message').forEach(el => {
        el.remove();
    });
    
    // Remove error styling
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
}

function showErrors(errors) {
    clearFormErrors();
    
    Object.keys(errors).forEach(field => {
        const input = document.querySelector(`#edit_${field}`) || document.querySelector(`[name="${field}"]`);
        if (input) {
            // Add error styling
            input.classList.add('is-invalid');
            
            // Add error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'text-danger small error-message mt-1';
            errorDiv.textContent = errors[field][0];
            
            // Insert after the input
            input.parentNode.appendChild(errorDiv);
        }
    });
}

function showError(message) {
    Toastify({
        text: message,
        duration: 4000,
        gravity: "top",
        position: "right",
        style: {
            background: "#dc3545"
        },
        stopOnFocus: true,
    }).showToast();
}

function showSuccess(message) {
    Toastify({
        text: message,
        duration: 3000,
        gravity: "top",
        position: "right",
        style: {
            background: "#28a745"
        },
        stopOnFocus: true,
    }).showToast();
}


// Optional: Update address card without page reload
function updateAddressCardInPage(address) {
    // Find the address card and update its content
    const addressCard = document.querySelector(`#address_${address.id}`).closest('.address-card');
    if (addressCard) {
        // Update the card content with new address data
        // This is optional - you can implement this for better UX
        console.log('Address updated:', address);
    }
}
// Add event listeners for edit modal
document.addEventListener('DOMContentLoaded', function() {
    // Country change handler for edit modal
    document.getElementById('edit_country_id').addEventListener('change', function() {
        const countryId = this.value;
        if (countryId) {
            loadEditStates(countryId);
            document.getElementById('edit_city_id').innerHTML = '<option value="">Select City</option>';
        }
    });
    
    // State change handler for edit modal
    document.getElementById('edit_state_id').addEventListener('change', function() {
        const stateId = this.value;
        if (stateId) {
            loadEditCities(stateId);
        }
    });
});

// Enhanced getCurrentLocation function for Edit Modal
function getCurrentLocationForEdit() {
    // Check if geolocation is supported
    if (!navigator.geolocation) {
        showError('🚫 Geolocation is not supported by this browser. Please fill the form manually.');
        return;
    }

    // Check if we're in a secure context
    const isSecure = location.protocol === 'https:' || 
                    location.hostname === 'localhost' || 
                    location.hostname === '127.0.0.1' ||
                    location.hostname.includes('192.168.') ||
                    location.hostname.includes('10.') ||
                    location.hostname.includes('172.');
    
    if (!isSecure) {
        showError('🔒 Location access requires HTTPS. Using IP-based location instead...');
        tryLocationFromIPForEdit();
        return;
    }

    // Show loading state
    const locationBtn = document.querySelector('button[onclick="getCurrentLocationForEdit()"]');
    const originalText = locationBtn.innerHTML;
    locationBtn.disabled = true;
    locationBtn.innerHTML = '🌍 Getting Location...';

    showSuccess('📍 Requesting high-accuracy location access... Please allow when prompted.');

    // Use the same options as GeolocationManager for better accuracy
    const options = {
        enableHighAccuracy: true,
        timeout: 10000,          // 10 seconds
        maximumAge: 60000        // 1 minute cache for fresh data
    };

    navigator.geolocation.getCurrentPosition(
        async function(position) {
            try {
                const coords = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy
                };

                console.log(`📍 High-accuracy location acquired: ${coords.latitude}, ${coords.longitude} (±${coords.accuracy}m)`);

                // Store coordinates in hidden fields immediately
                const latField = document.getElementById('edit_latitude');
                const lngField = document.getElementById('edit_longitude');
                if (latField) latField.value = coords.latitude;
                if (lngField) lngField.value = coords.longitude;

                showSuccess(`📍 GPS lock achieved! Accuracy: ±${Math.round(coords.accuracy)}m. Getting address details...`);

                // Use the same API call as GeolocationManager
                const locationData = await getLocationDetailsFromCoordsForEdit(coords.latitude, coords.longitude);
                
                if (locationData) {
                    // Apply the same form filling logic as location-integration
                    await applyLocationDataToEditForm(locationData, coords);
                    showSuccess('✅ Perfect! Your current location detected and form auto-filled!');
                    
                    // Debug: Show what was detected
                    console.log('🎯 Edit location detected successfully:', {
                        country: locationData.country || locationData.country_name,
                        state: locationData.state || locationData.state_name,
                        city: locationData.city || locationData.city_name,
                        pincode: locationData.pincode || locationData.postal_code,
                        area: locationData.area || locationData.neighbourhood
                    });
                    
                } else {
                    throw new Error('Unable to get detailed address from coordinates');
                }

            } catch (error) {
                console.error('Location processing error:', error);
                showError('⚠️ GPS coordinates obtained but address lookup failed: ' + error.message);
                
                // Try enhanced fallback
                setTimeout(() => {
                    showSuccess('🔄 Trying alternative location methods...');
                    tryEnhancedLocationFallbackForEdit(position.coords.latitude, position.coords.longitude);
                }, 2000);
            } finally {
                // Reset button state
                setTimeout(() => {
                    locationBtn.disabled = false;
                    locationBtn.innerHTML = originalText;
                }, 1500);
            }
        },
        function(error) {
            console.error('Geolocation error:', error);
            
            let errorMessage = '';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage = '🚫 Location access denied. Trying network-based detection...';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage = '📍 GPS unavailable. Using alternative location methods...';
                    break;
                case error.TIMEOUT:
                    errorMessage = '⏰ GPS timeout. Switching to network location...';
                    break;
                default:
                    errorMessage = '❓ GPS error. Using fallback location detection...';
                    break;
            }
            
            showError(errorMessage);
            
            // Use the same fallback strategy as GeolocationManager
            setTimeout(() => {
                tryEnhancedLocationFallbackForEdit();
            }, 1000);
            
            // Reset button state
            setTimeout(() => {
                locationBtn.disabled = false;
                locationBtn.innerHTML = originalText;
            }, 2000);
        },
        options
    );
}

// Get location details from coordinates for Edit Modal
async function getLocationDetailsFromCoordsForEdit(latitude, longitude) {
    try {
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                         document.querySelector('input[name="_token"]')?.value;
        
        const response = await fetch('/api/location-details', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ 
                latitude: latitude, 
                longitude: longitude 
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            return data.data;
        } else {
            throw new Error(data.error || 'Failed to get location details');
        }
    } catch (error) {
        console.error('Error getting location details for edit:', error);
        throw error;
    }
}

// Apply location data to Edit Form
async function applyLocationDataToEditForm(locationData, coords = null) {
    try {
        console.log('Applying location data to edit form:', locationData);

        // Store coordinates if provided
        if (coords) {
            const latField = document.getElementById('edit_latitude');
            const lngField = document.getElementById('edit_longitude');
            if (latField) latField.value = coords.latitude;
            if (lngField) lngField.value = coords.longitude;
        }

        // Normalize location data
        const normalizedData = normalizeLocationDataForEdit(locationData);
        console.log('Normalized location data for edit:', normalizedData);

        // Fill direct text fields
        const fieldMappings = [
            { source: ['area', 'neighbourhood', 'suburb', 'locality', 'sublocality'], target: 'edit_address_line_2' },
            { source: ['pincode', 'postal_code', 'zip_code', 'postcode'], target: 'edit_postal_code' },
        ];

        fieldMappings.forEach(mapping => {
            const field = document.getElementById(mapping.target);
            if (field) {
                const value = mapping.source.find(key => normalizedData[key]);
                if (value && normalizedData[value]) {
                    field.value = normalizedData[value];
                    // Add visual feedback
                    field.classList.add('border-success');
                    setTimeout(() => field.classList.remove('border-success'), 3000);
                }
            }
        });

        // Handle PIN code with validation and enhanced data
        const pincodeValue = normalizedData.pincode || normalizedData.postal_code || normalizedData.zip_code || normalizedData.postcode;
        if (pincodeValue && /^\d{6}$/.test(pincodeValue)) {
            const postalCodeField = document.getElementById('edit_postal_code');
            if (postalCodeField) {
                postalCodeField.value = pincodeValue;
                postalCodeField.classList.add('border-success');
                setTimeout(() => postalCodeField.classList.remove('border-success'), 3000);
                
                // Try to fill additional location data from pincode
                try {
                    console.log('Getting enhanced location data from pincode:', pincodeValue);
                    const pincodeData = await fillEditLocationFromPincode(pincodeValue);
                    
                    // Merge pincode data with GPS data (pincode data takes priority for missing fields)
                    if (pincodeData) {
                        // Update normalized data with pincode results for better dropdown matching
                        if (pincodeData.city && !normalizedData.city) {
                            normalizedData.city = pincodeData.city;
                            normalizedData.city_name = pincodeData.city;
                            console.log('Updated city from pincode:', pincodeData.city);
                        }
                        if (pincodeData.state && !normalizedData.state) {
                            normalizedData.state = pincodeData.state;
                            normalizedData.state_name = pincodeData.state;
                            console.log('Updated state from pincode:', pincodeData.state);
                        }
                        if (pincodeData.country && !normalizedData.country) {
                            normalizedData.country = pincodeData.country;
                            normalizedData.country_name = pincodeData.country;
                            console.log('Updated country from pincode:', pincodeData.country);
                        }
                        if (pincodeData.area && !normalizedData.area) {
                            normalizedData.area = pincodeData.area;
                            console.log('Updated area from pincode:', pincodeData.area);
                        }
                    }
                } catch (error) {
                    console.warn('Edit pincode lookup failed:', error);
                }
            }
        }

        // Fill dropdowns with enhanced matching (now with merged pincode data)
        await fillEditLocationDropdowns(normalizedData);

        // Show success message
        showSuccess('🎯 Edit form auto-filled with current location!');

    } catch (error) {
        console.error('Error applying location data to edit form:', error);
        showError('⚠️ Location detected but edit form filling had issues: ' + error.message);
    }
}

// Normalize location data for Edit form
function normalizeLocationDataForEdit(locationData) {
    const normalized = { ...locationData };
    
    // Same normalization logic as main form
    if (locationData.country) {
        normalized.country = locationData.country;
        normalized.country_name = locationData.country;
    }
    if (locationData.country_code) {
        normalized.country_code = locationData.country_code.toUpperCase();
    }
    
    if (locationData.state || locationData.state_name) {
        normalized.state = locationData.state || locationData.state_name;
        normalized.state_name = normalized.state;
    }
    
    if (locationData.city || locationData.city_name) {
        normalized.city = locationData.city || locationData.city_name;
        normalized.city_name = normalized.city;
    }
    
    if (locationData.area || locationData.neighbourhood) {
        normalized.area = locationData.area || locationData.neighbourhood;
    }
    
    if (locationData.pincode || locationData.postal_code) {
        const postal = locationData.pincode || locationData.postal_code;
        normalized.pincode = postal;
        normalized.postal_code = postal;
    }
    
    return normalized;
}

// Fill edit location from pincode
async function fillEditLocationFromPincode(pincode) {
    try {
        // Get country code from selected country in edit form
        const countrySelect = document.getElementById('edit_country_id');
        let countryCode = 'IN'; // Default to India
        
        if (countrySelect && countrySelect.value) {
            const selectedOption = countrySelect.options[countrySelect.selectedIndex];
            if (selectedOption) {
                const countryName = selectedOption.textContent.trim().toLowerCase();
                const countryMapping = {
                    'india': 'IN',
                    'united states': 'US',
                    'usa': 'US',
                    'united kingdom': 'GB',
                    'uk': 'GB',
                    'canada': 'CA',
                    'australia': 'AU',
                    'germany': 'DE',
                    'france': 'FR'
                };
                
                countryCode = countryMapping[countryName] || 'IN';
            }
        }
        
        // Use the same API as main form
        const response = await fetch(`/api/pincode-details?pincode=${pincode}&country_code=${countryCode}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            
            if (data.success) {
                const pincodeData = data.data;
                console.log('Edit pincode data received:', pincodeData);
                
                // Fill edit form fields from pincode data
                if (pincodeData.area) {
                    const areaField = document.getElementById('edit_address_line_2');
                    if (areaField && !areaField.value) {
                        areaField.value = pincodeData.area;
                    }
                }
                
                showSuccess(`📮 PIN code ${pincode} validated for edit form!`);
                return pincodeData; // Return the data for merging
            }
        }
        
        return null; // Return null if unsuccessful
    } catch (error) {
        console.error('Edit pincode lookup failed:', error);
        throw error;
    }
}

// Fill edit location dropdowns
async function fillEditLocationDropdowns(locationData) {
    try {
        console.log('Filling edit dropdowns with location data:', locationData);

        // Fill country dropdown with precise matching
        const countrySelect = document.getElementById('edit_country_id');
        if (countrySelect && (locationData.country || locationData.country_code)) {
            const countrySearchTerms = [
                locationData.country,
                locationData.country_code,
                locationData.country_name,
            ].filter(Boolean);

            for (const searchTerm of countrySearchTerms) {
                const countryOption = Array.from(countrySelect.options).find(option => {
                    const optionText = option.textContent.toLowerCase().trim();
                    const searchLower = searchTerm.toLowerCase().trim();
                    
                    // Exact match first (most reliable)
                    if (optionText === searchLower) {
                        return true;
                    }
                    
                    // Exact match with option value
                    if (option.value === searchTerm) {
                        return true;
                    }
                    
                    // For country code matching (2-letter codes)
                    if (searchTerm.length === 2 && option.dataset.countryCode === searchTerm.toUpperCase()) {
                        return true;
                    }
                    
                    // For full country names, use word boundary matching to avoid partial matches
                    if (searchTerm.length > 2) {
                        // Create word boundaries regex for exact word matching
                        const wordBoundaryRegex = new RegExp(`\\b${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\b`, 'i');
                        return wordBoundaryRegex.test(optionText);
                    }
                    
                    return false;
                });
                
                if (countryOption) {
                    console.log(`Edit country matched: "${searchTerm}" -> "${countryOption.textContent}"`);
                    countrySelect.value = countryOption.value;
                    
                    // Load states
                    await loadEditStates(countryOption.value);
                    break;
                }
            }
        }

        // Wait for states to load
        await new Promise(resolve => setTimeout(resolve, 1000));

        // Fill state dropdown
        const stateSelect = document.getElementById('edit_state_id');
        if (stateSelect && (locationData.state || locationData.state_name)) {
            const stateSearchTerms = [
                locationData.state,
                locationData.state_name,
            ].filter(Boolean);

            for (const searchTerm of stateSearchTerms) {
                const stateOption = Array.from(stateSelect.options).find(option => {
                    const optionText = option.textContent.toLowerCase().trim();
                    const searchLower = searchTerm.toLowerCase().trim();
                    
                    return optionText === searchLower || 
                           optionText.includes(searchLower);
                });
                
                if (stateOption) {
                    console.log(`Edit state matched: "${searchTerm}" -> "${stateOption.textContent}"`);
                    stateSelect.value = stateOption.value;
                    
                    // Load cities
                    await loadEditCities(stateOption.value);
                    break;
                }
            }
        }

        // Wait for cities to load
        await new Promise(resolve => setTimeout(resolve, 1000));

        // Fill city dropdown
        const citySelect = document.getElementById('edit_city_id');
        if (citySelect && (locationData.city || locationData.city_name)) {
            const citySearchTerms = [
                locationData.city,
                locationData.city_name,
            ].filter(Boolean);

            for (const searchTerm of citySearchTerms) {
                const cityOption = Array.from(citySelect.options).find(option => {
                    const optionText = option.textContent.toLowerCase().trim();
                    const searchLower = searchTerm.toLowerCase().trim();
                    
                    return optionText === searchLower || 
                           optionText.includes(searchLower);
                });
                
                if (cityOption) {
                    console.log(`Edit city matched: "${searchTerm}" -> "${cityOption.textContent}"`);
                    citySelect.value = cityOption.value;
                    break;
                }
            }
        }

    } catch (error) {
        console.error('Error filling edit location dropdowns:', error);
    }
}

// Try enhanced location fallback for Edit
async function tryEnhancedLocationFallbackForEdit(latitude = null, longitude = null) {
    try {
        // If we have coordinates, try them first
        if (latitude && longitude) {
            const locationData = await getLocationDetailsFromCoordsForEdit(latitude, longitude);
            if (locationData) {
                await applyLocationDataToEditForm(locationData, {latitude, longitude});
                showSuccess('✅ Location detected using fallback method!');
                return;
            }
        }
        
        // Try IP-based location
        await tryLocationFromIPForEdit();
        
    } catch (error) {
        console.error('Enhanced fallback failed for edit:', error);
        showError('⚠️ All location detection methods failed. Please fill the form manually.');
    }
}

// Try location from IP for Edit
async function tryLocationFromIPForEdit() {
    try {
        showSuccess('🌐 Trying to detect location from your internet connection...');
        
        const response = await fetch('/api/location-from-ip', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            
            if (data.success) {
                await applyLocationDataToEditForm(data.data);
                showSuccess('🌐 Location detected from your IP address and edit form filled!');
            } else {
                throw new Error(data.error || 'IP location failed');
            }
        } else {
            throw new Error(`HTTP ${response.status}`);
        }
        
    } catch (error) {
        console.error('IP location failed for edit:', error);
        showError('⚠️ IP-based location detection failed. Please fill the edit form manually.');
    }
}
    </script>

    <script>
function selectAddress(addressId) {
    // Check the radio button
    document.getElementById(`address_${addressId}`).checked = true;
    updateAddressSelection(addressId);
}

function updateAddressSelection(addressId) {
    // Remove active class from all cards
    document.querySelectorAll('.address-card').forEach(card => {
        card.classList.remove('border-primary', 'bg-light');
    });
    
    // Add active class to selected card
    const selectedCard = document.querySelector(`#address_${addressId}`).closest('.address-card');
    selectedCard.classList.add('border-primary', 'bg-light');
    
    // Update address summary
    updateAddressSummary(addressId);
    
    // Update continue button
    document.getElementById('continue-btn').textContent = '🚚 Deliver to This Address';
}

function updateAddressSummary(addressId) {
    // This would fetch address details via AJAX and update the summary
    // For now, we'll update based on the selected card content
    const selectedCard = document.querySelector(`#address_${addressId}`).closest('.card-body');
    const addressText = selectedCard.querySelector('.text-muted').innerHTML;
    
    document.getElementById('address-summary-content').innerHTML = `<div class="text-muted">${addressText}</div>`;
}

function toggleNewAddressForm() {
    const form = document.getElementById('new-address-form');
    form.classList.toggle('d-none');
    
    if (!form.classList.contains('d-none')) {
        form.scrollIntoView({ behavior: 'smooth' });
    }
}
</script>

{{-- Custom CSS --}}
<style>
.address-card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.address-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.border-dashed {
    border: 2px dashed #dee2e6 !important;
    transition: all 0.3s ease;
}

.border-dashed:hover {
    border-color: #0d6efd !important;
    background-color: #f8f9ff;
}

.position-sticky {
    position: -webkit-sticky;
    position: sticky;
}

.badge {
    font-size: 0.75rem;
}

.dropdown-toggle::after {
    display: none;
}

/* New Delivery Styles */
.btn-check:checked + .btn-outline-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.btn-check:checked + .btn-outline-success {
    background-color: #198754;
    border-color: #198754;
    color: white;
}

.btn-check:checked + .btn-outline-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}

.btn-check:checked + .btn-outline-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: black;
}

.sticky-top {
    z-index: 1020;
}

.order-items {
    max-height: 300px;
    overflow-y: auto;
}

.card {
    border: none;
    border-radius: 12px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
}

.btn {
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.form-control, .form-select {
    border-radius: 8px;
    border: 2px solid #e9ecef;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
</style>

<!-- Enhanced Delivery JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Time slots for each shipping method
    const timeSlots = @json(collect($shippingMethods)->map(function($method, $key) { 
        return app(App\Http\Controllers\CheckoutController::class)->getTimeSlots($key); 
    }));
    
    // Initialize time slots for default shipping method
    updateTimeSlots('standard');
    
    // Handle custom date selection
    const customDateRadio = document.getElementById('custom_date');
    const customDatePicker = document.getElementById('customDatePicker');
    const customDateInput = document.getElementById('custom_date_input');
    
    // Show/hide custom date picker
    document.querySelectorAll('input[name="delivery_date"]').forEach(radio => {
        radio.addEventListener('change', function() {
            console.log('Delivery date changed to:', this.value);
            
            // Update debug field
            document.getElementById('debug_delivery_date').value = this.value;
            
            if (this.id === 'custom_date') {
                customDatePicker.style.display = 'block';
                customDateInput.required = true;
                updateDeliverySummary();
            } else {
                customDatePicker.style.display = 'none';
                customDateInput.required = false;
                customDateInput.value = '';
                updateDeliverySummary();
            }
        });
    });
    
    // Handle custom date input
    if (customDateInput) {
        customDateInput.addEventListener('change', function() {
            if (this.value) {
                customDateRadio.value = this.value;
                updateDeliverySummary();
            }
        });
    }
    
    // Handle shipping method change
    document.querySelectorAll('.shipping-method').forEach(radio => {
        radio.addEventListener('change', function() {
            console.log('Shipping method changed to:', this.value);
            
            // Update debug field
            document.getElementById('debug_shipping_method').value = this.value;
            
            updateTimeSlots(this.value);
            updateShippingCost(this.value);
            updateDeliverySummary();
        });
    });
    
    // Handle time slot selection
    document.addEventListener('change', function(e) {
        if (e.target.name === 'time_slot') {
            console.log('Time slot changed to:', e.target.value);
            
            // Update debug field
            document.getElementById('debug_time_slot').value = e.target.value;
            
            updateDeliverySummary();
        }
        
        // Handle address selection
        if (e.target.name === 'address_id') {
            console.log('Address changed to:', e.target.value);
            
            // Update debug field
            document.getElementById('debug_address_id').value = e.target.value;
        }
    });
    
    function updateTimeSlots(shippingMethod) {
        const timeSlotsContainer = document.getElementById('timeSlots');
        const slots = timeSlots[shippingMethod] || [];
        
        let html = '';
        slots.forEach((slot, index) => {
            html += `
                <div class="col-md-6 mb-2">
                    <input type="radio" 
                           class="btn-check" 
                           name="time_slot" 
                           id="time_${shippingMethod}_${index}" 
                           value="${slot}"
                           form="checkoutForm"
                           ${index === 0 ? 'checked' : ''}
                           required>
                    <label class="btn btn-outline-warning w-100" for="time_${shippingMethod}_${index}">
                        ${slot}
                    </label>
                </div>
            `;
        });
        
        timeSlotsContainer.innerHTML = html;
        
        // Update delivery summary after time slots are updated
        setTimeout(updateDeliverySummary, 100);
    }
    
    function updateShippingCost(shippingMethod) {
        const costs = {
            'morning': 50,
            'standard': 25,
            'express': 100,
            'midnight': 75
        };
        
        const cost = costs[shippingMethod] || 25;
        const shippingCostElement = document.getElementById('shippingCost');
        if (shippingCostElement) {
            shippingCostElement.textContent = `₹${cost.toFixed(2)}`;
        }
        
        // Update grand total
        const subtotal = {{ $subtotal }};
        const discount = {{ $discount }};
        const grandTotal = subtotal - discount + cost;
        const grandTotalElement = document.getElementById('grandTotal');
        if (grandTotalElement) {
            grandTotalElement.textContent = `₹${grandTotal.toFixed(2)}`;
        }
    }
    
    function updateDeliverySummary() {
        // Update selected date
        const selectedDateRadio = document.querySelector('input[name="delivery_date"]:checked');
        const selectedDateElement = document.getElementById('selectedDate');
        
        if (selectedDateRadio && selectedDateElement) {
            let dateText = '';
            if (selectedDateRadio.id === 'custom_date' && customDateInput && customDateInput.value) {
                const date = new Date(customDateInput.value);
                dateText = date.toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric', 
                    year: 'numeric' 
                });
            } else if (selectedDateRadio.id !== 'custom_date') {
                const labelElement = selectedDateRadio.nextElementSibling.querySelector('.fw-bold');
                const formattedElement = selectedDateRadio.nextElementSibling.querySelector('.text-muted');
                if (labelElement && formattedElement) {
                    const label = labelElement.textContent;
                    const formatted = formattedElement.textContent;
                    dateText = `${label} (${formatted})`;
                }
            }
            selectedDateElement.textContent = dateText || 'Not selected';
        }
        
        // Update selected method
        const selectedMethodRadio = document.querySelector('input[name="shipping_method"]:checked');
        const selectedMethodElement = document.getElementById('selectedMethod');
        
        if (selectedMethodRadio && selectedMethodElement) {
            const methodElement = selectedMethodRadio.nextElementSibling.querySelector('.fw-bold');
            if (methodElement) {
                const methodText = methodElement.textContent;
                selectedMethodElement.textContent = methodText;
            }
        }
        
        // Update selected time
        const selectedTimeRadio = document.querySelector('input[name="time_slot"]:checked');
        const selectedTimeElement = document.getElementById('selectedTime');
        
        if (selectedTimeRadio && selectedTimeElement) {
            selectedTimeElement.textContent = selectedTimeRadio.value;
        }
    }
    
    // Form validation with debugging and Razorpay payment handling
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        // DEBUG: Log form structure
        console.log('Checkout form found:', checkoutForm);
        console.log('Form action:', checkoutForm.action);
        console.log('Form method:', checkoutForm.method);
        
        // DEBUG: Check payment method inputs
        const paymentInputs = checkoutForm.querySelectorAll('input[name="payment_method"]');
        console.log('Payment method inputs found:', paymentInputs.length);
        paymentInputs.forEach((input, index) => {
            console.log(`Payment input ${index}:`, input.value, input.checked);
        });
        
        checkoutForm.addEventListener('submit', function(e) {
            console.log('Form submit triggered'); // Debug
            
            // Check payment method
            const selectedPaymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
            console.log('Selected payment method:', selectedPaymentMethod);
            
            // If Razorpay is selected, prevent form submission and handle payment
            if (selectedPaymentMethod === 'razorpay') {
                e.preventDefault(); // Prevent form submission
                console.log('Razorpay payment selected, preventing form submission');
                
                // Validate form before payment
                if (validateForm()) {
                    initiateRazorpayPayment();
                } else {
                    console.log('Form validation failed');
                }
                return;
            }

            // If Stripe is selected, prevent form submission and handle payment
            if (selectedPaymentMethod === 'stripe') {
                e.preventDefault(); // Prevent form submission
                console.log('Stripe payment selected, preventing form submission');
                
                // Validate form before payment
                if (validateForm()) {
                    initiateStripePayment();
                } else {
                    console.log('Form validation failed');
                }
                return;
            }
            
            // For COD, continue with normal form submission
            console.log('COD payment selected, allowing form submission');
            
            // Log all form data before submission
            const formData = new FormData(this);
            console.log('Form data being submitted:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
            
            // Also check actual field values
            console.log('Field value checks:');
            console.log('delivery_date value:', document.querySelector('input[name="delivery_date"]:checked')?.value);
            console.log('address_id value:', document.querySelector('input[name="address_id"]:checked')?.value);
            console.log('shipping_method value:', document.querySelector('input[name="shipping_method"]:checked')?.value);
            console.log('time_slot value:', document.querySelector('input[name="time_slot"]:checked')?.value);
            console.log('custom_delivery_date value:', document.querySelector('input[name="custom_delivery_date"]')?.value);
            
            // TEMPORARILY DISABLED - Let form submit without validation
            console.log('Allowing form submission without client-side validation...');
        });
    }
    
    // Initial setup
    updateDeliverySummary();
    
    // Debug: Add click handler to place order button
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', function(e) {
            console.log('Place Order button clicked');
            console.log('Button type:', placeOrderBtn.type);
            console.log('Form element:', checkoutForm);
            
            // Check payment method before form submission
            const selectedPaymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
            console.log('Selected payment method:', selectedPaymentMethod);
            
            // If Razorpay is selected, handle payment separately
            if (selectedPaymentMethod === 'razorpay') {
                e.preventDefault();
                if (validateForm()) {
                    initiateRazorpayPayment();
                }
                return;
            }
            
            // For COD, try to manually trigger form submission if needed
            if (checkoutForm) {
                console.log('Manually submitting form for COD...');
                checkoutForm.submit();
            }
        });
    }
});

// ================================================================================================
// 💳 RAZORPAY PAYMENT FUNCTIONS
// ================================================================================================

/**
 * Validate form before payment
 */
function validateForm() {
    const requiredFields = [
        'address_id',
        'delivery_date',
        'shipping_method',
        'time_slot'
    ];
    
    let isValid = true;
    const errors = [];
    
    requiredFields.forEach(field => {
        let fieldValue;
        
        if (field === 'delivery_date') {
            const selectedDate = document.querySelector('input[name="delivery_date"]:checked');
            if (selectedDate) {
                if (selectedDate.id === 'custom_date') {
                    fieldValue = document.querySelector('input[name="custom_delivery_date"]')?.value;
                } else {
                    fieldValue = selectedDate.value;
                }
            }
        } else {
            const fieldElement = document.querySelector(`input[name="${field}"]:checked`);
            fieldValue = fieldElement ? fieldElement.value : null;
        }
        
        if (!fieldValue) {
            isValid = false;
            errors.push(`Please select ${field.replace('_', ' ')}`);
        }
    });
    
    if (!isValid) {
        showError('Please fill all required fields: ' + errors.join(', '));
    }
    
    return isValid;
}

/**
 * Initiate Razorpay payment
 */
function initiateRazorpayPayment() {
    try {
        console.log('Starting Razorpay payment process...');
        
        // Show loading
        const placeOrderBtn = document.getElementById('placeOrderBtn');
        placeOrderBtn.disabled = true;
        placeOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing Payment...';
        
        // For Razorpay, simply submit the form normally since the backend
        // will redirect to the payment page
        console.log('Submitting form for Razorpay payment...');
        document.getElementById('checkoutForm').submit();
        
    } catch (error) {
        console.error('Razorpay payment error:', error);
        showError('Payment failed: ' + error.message);
        
        // Reset button
        const placeOrderBtn = document.getElementById('placeOrderBtn');
        placeOrderBtn.disabled = false;
        placeOrderBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Place Order Securely';
    }
}

/**
 * Handle Razorpay payment success
 */
function handleRazorpaySuccess(response) {
    console.log('Razorpay payment successful:', response);
    
    // Submit payment details to backend for verification
    const verificationData = {
        razorpay_payment_id: response.razorpay_payment_id,
        razorpay_order_id: response.razorpay_order_id,
        razorpay_signature: response.razorpay_signature,
        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };
    
    fetch('{{ route("payment.razorpay.success") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': verificationData._token,
            'Accept': 'application/json'
        },
        body: JSON.stringify(verificationData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Payment successful! Redirecting to confirmation page...');
            
            // Redirect to thank you page
            setTimeout(() => {
                window.location.href = data.redirect_url || '{{ route("checkout.thankyou") }}';
            }, 2000);
        } else {
            throw new Error(data.message || 'Payment verification failed');
        }
    })
    .catch(error => {
        console.error('Payment verification error:', error);
        showError('Payment verification failed: ' + error.message);
    });
}

/**
 * Handle Razorpay payment failure
 */
function handleRazorpayFailure(response) {
    console.error('Razorpay payment failed:', response);
    
    // Submit failure details to backend
    const failureData = {
        error: response.error,
        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };
    
    fetch('{{ route("payment.razorpay.failure") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': failureData._token,
            'Accept': 'application/json'
        },
        body: JSON.stringify(failureData)
    })
    .then(response => response.json())
    .then(data => {
        showError(data.message || 'Payment failed. Please try again.');
    })
    .catch(error => {
        console.error('Payment failure handling error:', error);
        showError('Payment failed. Please try again.');
    });
    
    // Reset place order button
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    placeOrderBtn.disabled = false;
    placeOrderBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Place Order Securely';
}

/**
 * Initiate Stripe payment
 */
function initiateStripePayment() {
    try {
        console.log('Starting Stripe payment process...');
        
        // Show loading
        const placeOrderBtn = document.getElementById('placeOrderBtn');
        placeOrderBtn.disabled = true;
        placeOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing Payment...';
        
        // For Stripe, simply submit the form normally since the backend
        // will redirect to the payment page
        console.log('Submitting form for Stripe payment...');
        document.getElementById('checkoutForm').submit();
        
    } catch (error) {
        console.error('Stripe payment error:', error);
        showError('Payment failed: ' + error.message);
        
        // Reset button
        const placeOrderBtn = document.getElementById('placeOrderBtn');
        placeOrderBtn.disabled = false;
        placeOrderBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Place Order Securely';
    }
}

// ================================================================================================
// 🚨 HELPER FUNCTIONS
// ================================================================================================

/**
 * Show success message
 */
function showSuccess(message) {
    Toastify({
        text: message,
        duration: 4000,
        gravity: "top",
        position: "right",
        style: {
            background: "#28a745"
        },
        stopOnFocus: true,
    }).showToast();
}

/**
 * Show error message
 */
function showError(message) {
    Toastify({
        text: message,
        duration: 6000,
        gravity: "top",
        position: "right",
        style: {
            background: "#dc3545"
        },
        stopOnFocus: true,
    }).showToast();
}
</script>
</body>
</html>