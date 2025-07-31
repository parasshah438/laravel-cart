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
                                                            <form action="{{ route('address.destroy', $address->id) }}" method="POST" class="d-inline">
                                                                @csrf @method('DELETE')
                                                                <button class="dropdown-item text-danger" 
                                                                        onclick="return confirm('Delete this address?')" 
                                                                        type="submit">
                                                                    🗑️ Delete
                                                                </button>
                                                            </form>
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
                        
                        {{-- Order Summary --}}
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">🛒 Your Order</h6>
                            </div>
                            <div class="card-body">
                                @php $subtotal = 0; @endphp
                                @foreach($cartItems as $item)
                                    @php $lineTotal = $item->product->price * $item->quantity; @endphp
                                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                        <div>
                                            <strong>{{ $item->product->name }}</strong><br>
                                            <small class="text-muted">Qty: {{ $item->quantity }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="fw-bold">₹{{ number_format($lineTotal, 2) }}</span>
                                        </div>
                                    </div>
                                    @php $subtotal += $lineTotal; @endphp
                                @endforeach

                                <div class="border-top pt-3 mt-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal</span>
                                        <span>₹{{ number_format($subtotal, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Shipping</span>
                                        <span class="text-success">FREE</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold h5">
                                        <span>Total</span>
                                        <span class="text-primary">₹{{ number_format($subtotal, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
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
        const shipCheckbox = document.getElementById('ship_different');
        const shipForm = document.getElementById('shipping-address-section');
        shipCheckbox.addEventListener('change', () => {
            shipForm.style.display = shipCheckbox.checked ? 'block' : 'none';
        });


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
        backgroundColor: "#dc3545",
        stopOnFocus: true,
    }).showToast();
}

function showSuccess(message) {
    Toastify({
        text: message,
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "#28a745",
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