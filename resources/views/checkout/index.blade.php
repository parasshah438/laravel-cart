<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bootstrap 5 Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                 {{-- Left Column: Address Selection --}}
                <div class="col-md-7">
                    <form method="POST" action="{{ route('checkout.address.select') }}" id="address-selection-form">
                        @csrf
                        
                        {{-- Existing Addresses with Radio Selection --}}
                        <div class="mb-4">
                            <h6 class="mb-3">📋 Your Saved Addresses</h6>
                            
                            @foreach($user->addresses as $index => $address)
                            <div class="card mb-3 address-card {{ $address->is_default ? 'border-primary' : '' }}" 
                                 onclick="selectAddress({{ $address->id }})">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        {{-- Radio Button --}}
                                        <div class="me-3 mt-1">
                                            <input type="radio" 
                                                   name="selected_address_id" 
                                                   value="{{ $address->id }}"
                                                   id="address_{{ $address->id }}"
                                                   class="form-check-input"
                                                   {{ $address->is_default ? 'checked' : '' }}
                                                   onchange="updateAddressSelection({{ $address->id }})">
                                        </div>
                                        
                                        {{-- Address Details --}}
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <strong class="text-primary">{{ $address->full_name }}</strong>
                                                    <div class="d-flex gap-2 mt-1">
                                                        {{-- Address Type Badge --}}
                                                        <span class="badge bg-{{ $address->type == 'home' ? 'success' : ($address->type == 'work' ? 'info' : 'secondary') }}">
                                                            {{ $address->type == 'home' ? '🏠 Home' : ($address->type == 'work' ? '🏢 Work' : '📍 Other') }}
                                                        </span>
                                                        
                                                        {{-- Default Badge --}}
                                                        @if($address->is_default)
                                                            <span class="badge bg-primary">⭐ Default</span>
                                                        @endif
                                                        
                                                        {{-- Custom Label --}}
                                                        @if($address->label)
                                                            <span class="badge bg-light text-dark">{{ $address->label }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                {{-- Action Buttons --}}
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                            type="button" data-bs-toggle="dropdown">
                                                        ⚙️
                                                    </button>

                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item" href="javascript:void(0)" onclick="openEditModal({{ $address->id }})">
                                                                ✏️ Edit
                                                            </a>
                                                        </li>
                                                        @if(!$address->is_default)
                                                        <li>
                                                            <form action="{{ route('address.setDefault', $address->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button class="dropdown-item" type="submit">
                                                                    ⭐ Set as Default
                                                                </button>
                                                            </form>
                                                        </li>
                                                        @endif
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="javascript:void(0)" 
                                                               onclick="deleteAddress({{ $address->id }})">
                                                                🗑️ Delete
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            
                                            {{-- Address Lines --}}
                                            <div class="text-muted mb-2">
                                                <div>📍 {{ $address->address_line_1 }}</div>
                                                @if($address->address_line_2)
                                                    <div>{{ $address->address_line_2 }}</div>
                                                @endif
                                                @if($address->landmark)
                                                    <div><small>Near: {{ $address->landmark }}</small></div>
                                                @endif
                                                <div>
                                                    {{ $address->city->name ?? '' }}, {{ $address->state->name ?? '' }} {{ $address->postal_code }}
                                                </div>
                                                <div>{{ $address->country->name ?? '' }}</div>
                                            </div>
                                            
                                            {{-- Contact Info --}}
                                            <div class="text-muted small">
                                                📞 {{ $address->phone_number }}
                                                @if($address->alternate_phone)
                                                    , {{ $address->alternate_phone }}
                                                @endif
                                            </div>
                                            
                                            {{-- Delivery Instructions --}}
                                            @if($address->delivery_instructions)
                                                <div class="alert alert-info mt-2 mb-0 py-2">
                                                    <small><strong>📝 Delivery Note:</strong> {{ $address->delivery_instructions }}</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        {{-- Add New Address Option --}}
                        <div class="card mb-3 border-dashed" onclick="toggleNewAddressForm()">
                            <div class="card-body text-center py-4">
                                <div class="text-primary">
                                    <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                    <div><strong>➕ Add a New Address</strong></div>
                                    <small class="text-muted">Add a new delivery address</small>
                                </div>
                            </div>
                        </div>
                        
                        {{-- New Address Form (Hidden by default) --}}
                        <div id="new-address-form" class="d-none">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>📍 Add New Address</span>
                                        <button type="button" class="btn-close btn-close-white" onclick="toggleNewAddressForm()"></button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @include('partials._address_form', ['countries' => $countries])
                                </div>
                            </div>
                        </div>
                        
                        {{-- Continue Button --}}
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="continue-btn">
                                🚚 Deliver to This Address
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Right Column: Order Summary --}}
                <div class="col-md-5">
                    <div class="position-sticky" style="top: 20px;">
                        {{-- Selected Address Summary --}}
                        <div class="card mb-3" id="selected-address-summary">
                            <div class="card-header">
                                <h6 class="mb-0">📍 Delivery Address</h6>
                            </div>
                            <div class="card-body" id="address-summary-content">
                                @php $defaultAddress = $user->addresses->where('is_default', true)->first() @endphp
                                @if($defaultAddress)
                                    <div class="text-muted">
                                        <strong>{{ $defaultAddress->full_name }}</strong><br>
                                        {{ $defaultAddress->address_line_1 }}<br>
                                        {{ $defaultAddress->city->name ?? '' }}, {{ $defaultAddress->postal_code }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        {{-- ✅ PROFESSIONAL ORDER SUMMARY (With Applied Coupons) --}}
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">🛒 Your Order ({{ $cartItems->count() }} items)</h6>
                            </div>
                            <div class="card-body">
                                {{-- Cart Items --}}
                                @foreach($cartItems as $item)
                                    @php $lineTotal = $item->price_at_time * $item->quantity; @endphp
                                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                        <div class="flex-grow-1">
                                            <strong>{{ $item->product->name }}</strong><br>
                                            <small class="text-muted">
                                                ₹{{ number_format($item->price_at_time, 2) }} × {{ $item->quantity }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="fw-bold">₹{{ number_format($lineTotal, 2) }}</span>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Price Breakdown --}}
                                <div class="border-top pt-3 mt-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal</span>
                                        <span>₹{{ number_format($subtotal, 2) }}</span>
                                    </div>
                                    
                                    {{-- ✅ APPLIED COUPON DISPLAY --}}
                                    @if($appliedCoupon && $discount > 0)
                                    <div class="d-flex justify-content-between mb-2 text-success">
                                        <span>
                                            <i class="fas fa-tags me-1"></i>Coupon Applied 
                                            <strong>({{ $appliedCoupon->code }})</strong>
                                            <br><small class="text-muted">{{ $appliedCoupon->title }}</small>
                                        </span>
                                        <span class="fw-bold">-₹{{ number_format($discount, 2) }}</span>
                                    </div>
                                    @endif
                                    
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Shipping</span>
                                        <span class="text-success">FREE</span>
                                    </div>
                                    
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold h5">
                                        <span>Total Amount</span>
                                        <span class="text-primary">₹{{ number_format($total, 2) }}</span>
                                    </div>
                                    
                                    {{-- Savings Display --}}
                                    @if($appliedCoupon && $discount > 0)
                                    <div class="text-center">
                                        <small class="badge bg-success">
                                            <i class="fas fa-piggy-bank me-1"></i>You saved ₹{{ number_format($discount, 2) }}!
                                        </small>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                            <!-- Payment Options and Place Order Form -->
                            <form method="POST" action="{{ route('checkout.placeOrder') }}" class="mt-4">
                                @csrf
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">💳 Payment Options</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                                            <label class="form-check-label fw-bold" for="cod">
                                                Cash on Delivery (COD)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success btn-lg w-100">Place Order</button>
                            </form>
                    </div>
                </div>
                @endif
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
</style>
</body>
</html>