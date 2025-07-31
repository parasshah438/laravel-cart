
    <div class="col-md-7">
    
<form method="POST" action="{{ route('checkout.address.save') }}" id="address-form">
    @csrf

    {{-- Address Type Selection --}}
    <div class="bg-white p-4 rounded shadow-sm mb-4">
        <h4 class="mb-3">Address Type</h4>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Address Type</label>
                <select name="type" class="form-select" required>
                    <option value="home" selected>🏠 Home</option>
                    <option value="work">🏢 Work</option>
                    <option value="other">📍 Other</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Address Label <span class="text-muted">(Optional)</span></label>
                <input type="text" name="label" class="form-control" placeholder="e.g., Mom's House, Office">
            </div>
        </div>
    </div>

    {{-- Personal Details --}}
    <div class="bg-white p-4 rounded shadow-sm mb-4">
        <h4 class="mb-3">Personal Details</h4>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="full_name" class="form-label">Full Name *</label>
                <input type="text" name="full_name" id="full_name" class="form-control" required 
                       placeholder="Enter your full name">
                @error('full_name')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="phone_number" class="form-label">Mobile Number *</label>
                <input type="tel" name="phone_number" id="phone_number" class="form-control" required
                       placeholder="10-digit mobile number" maxlength="10" pattern="[0-9]{10}">
                @error('phone_number')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="mb-3">
            <label for="alternate_phone" class="form-label">Alternate Phone <span class="text-muted">(Optional)</span></label>
            <input type="tel" name="alternate_phone" id="alternate_phone" class="form-control"
                   placeholder="Alternate contact number" maxlength="10">
        </div>
    </div>

    {{-- Address Details --}}
    <div class="bg-white p-4 rounded shadow-sm mb-4">
        <h4 class="mb-3">Address Details</h4>
        
        {{-- PIN Code First (Amazon Style) --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="postal_code" class="form-label">PIN Code *</label>
                <input type="text" name="postal_code" id="postal_code" class="form-control" required
                       placeholder="6-digit PIN code" maxlength="6" pattern="[0-9]{6}">
                <small class="text-muted">Delivery options and charges will be shown based on this PIN code</small>
                @error('postal_code')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Address Lines --}}
        <div class="mb-3">
            <label for="address_line_1" class="form-label">Flat, House no., Building, Company, Apartment *</label>
            <input type="text" name="address_line_1" id="address_line_1" class="form-control" required
                   placeholder="e.g., Flat 101, Galaxy Apartment">
            @error('address_line_1')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label for="address_line_2" class="form-label">Area, Street, Sector, Village <span class="text-muted">(Optional)</span></label>
            <input type="text" name="address_line_2" id="address_line_2" class="form-control"
                   placeholder="e.g., Sector 15, Near Metro Station">
        </div>
        
        <div class="mb-3">
            <label for="landmark" class="form-label">Landmark <span class="text-muted">(Optional)</span></label>
            <input type="text" name="landmark" id="landmark" class="form-control"
                   placeholder="e.g., Near City Mall, Opposite Bus Stand">
        </div>

        {{-- Location Fields --}}
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="country_id" class="form-label">Country *</label>
                <select name="country_id" id="country_id" class="form-select" required>
                    <option value="">Select Country</option>
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}" {{ $country->code == 'IN' ? 'selected' : '' }}>
                            {{ $country->name }}
                        </option>
                    @endforeach
                </select>
                @error('country_id')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="state_id" class="form-label">State *</label>
                <select name="state_id" id="state_id" class="form-select" required disabled>
                    <option value="">First select country</option>
                </select>
                @error('state_id')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="city_id" class="form-label">City *</label>
                <select name="city_id" id="city_id" class="form-select" required disabled>
                    <option value="">First select state</option>
                </select>
                @error('city_id')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    {{-- Business Details (Optional) --}}
    <div class="bg-white p-4 rounded shadow-sm mb-4">
        <h4 class="mb-3">Business Details <span class="text-muted">(Optional)</span></h4>
        
        <div class="mb-3">
            <label for="gst_number" class="form-label">GST Number <span class="text-muted">(For Business)</span></label>
            <input type="text" name="gst_number" id="gst_number" class="form-control"
                   placeholder="e.g., 22AAAAA0000A1Z5" maxlength="15">
            <small class="text-muted">GST number for business purchases (15 characters)</small>
        </div>
    </div>

    {{-- Delivery Instructions --}}
    <div class="bg-white p-4 rounded shadow-sm mb-4">
        <h4 class="mb-3">Delivery Instructions <span class="text-muted">(Optional)</span></h4>
        
        <div class="mb-3">
            <label for="delivery_instructions" class="form-label">Special Instructions</label>
            <textarea name="delivery_instructions" id="delivery_instructions" class="form-control" rows="3"
                      placeholder="e.g., Call before delivery, Ring doorbell twice, Leave with security"></textarea>
            <small class="text-muted">Help delivery person find you easily</small>
        </div>
    </div>

    {{-- Address Preferences --}}
    <div class="bg-white p-4 rounded shadow-sm mb-4">
        <h4 class="mb-3">Set as Default</h4>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_default_billing" id="is_default_billing" value="1">
                    <label class="form-check-label" for="is_default_billing">
                        💳 Default Billing Address
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_default_shipping" id="is_default_shipping" value="1">
                    <label class="form-check-label" for="is_default_shipping">
                        📦 Default Shipping Address
                    </label>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_default" id="is_default" value="1">
                <label class="form-check-label" for="is_default">
                    ⭐ Make this my default address
                </label>
            </div>
        </div>
    </div>

    {{-- Hidden Fields for Location Data --}}
    <input type="hidden" name="latitude" id="latitude">
    <input type="hidden" name="longitude" id="longitude">

    {{-- Submit Buttons --}}
    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary btn-lg">
            📍 Save Address & Continue
        </button>
        <button type="button" class="btn btn-outline-secondary" onclick="getCurrentLocation()">
            🌍 Use My Current Location
        </button>
    </div>
</form>

{{-- JavaScript for Form Functionality --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // PIN Code Auto-complete
    document.getElementById('postal_code').addEventListener('input', function() {
        const pinCode = this.value;
        
        // Remove non-numeric characters and limit to 6 digits
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
        
        // If 6 digits entered, fetch location
        if (this.value.length === 6) {
            fetchLocationByPinCode(this.value);
        }
    });

    // Country Change Handler
    document.getElementById('country_id').addEventListener('change', function() {
        const countryId = this.value;
        loadStates(countryId);
    });

     document.getElementById('city_id').addEventListener('change', function() {
        const cityId = this.value;
        if (cityId) {
            fetchPostalCodesForCity(cityId);
        }
    });

    // State Change Handler
    // Enhanced State Change Handler - Only show modal for manual changes
document.getElementById('state_id').addEventListener('change', function() {
    const stateId = this.value;
    
    if (stateId) {
        // Always load cities for the selected state
        loadCities(stateId);
        
        // Only fetch postal codes if user manually changed state AFTER entering PIN code
        const postalCodeField = document.getElementById('postal_code');
        const hasExistingPinCode = postalCodeField.value.length === 6;
        const isManualSelection = !this.classList.contains('auto-filling');
        
        if (hasExistingPinCode && isManualSelection) {
            // User manually changed state after having a PIN code - show options
            fetchPostalCodesForState(stateId);
        }
    } else {
        // Reset everything when no state selected
        resetLocationFields();
    }
});

    // Load states for default country (India)
    const defaultCountry = document.getElementById('country_id').value;
    if (defaultCountry) {
        loadStates(defaultCountry);
    }
});

document.getElementById('postal_code').addEventListener('change', function() {
    const selectedCode = this.value;
    if (selectedCode.length === 6) {
        // Find the selected postal code data
        const datalist = document.getElementById('postal-code-list');
        if (datalist) {
            const selectedOption = Array.from(datalist.options).find(option => option.value === selectedCode);
            if (selectedOption) {
                // Extract area from the option text and update address_line_2
                const areaMatch = selectedOption.textContent.match(/- (.+)$/);
                if (areaMatch && areaMatch[1] !== 'Area') {
                    document.getElementById('address_line_2').value = areaMatch[1];
                }
            }
        }
    }
});

function resetLocationFields() {
    const citySelect = document.getElementById('city_id');
    citySelect.innerHTML = '<option value="">First select state</option>';
    citySelect.disabled = true;
    
    document.getElementById('postal_code').value = '';
    document.getElementById('address_line_2').value = '';
}

async function fetchPostalCodesForCity(cityId) {
    try {
        // Add loading state to city dropdown
        const citySelect = document.getElementById('city_id');
        citySelect.classList.add('city-loading');
        
        const response = await fetch(`/api/postal-codes/city/${cityId}`);
        const data = await response.json();
        
        // Remove loading state
        citySelect.classList.remove('city-loading');
        
        if (data.success && data.postal_codes.length > 0) {
            const postalCodes = data.postal_codes;
            
            if (postalCodes.length === 1) {
                // If only one postal code, auto-fill it
                document.getElementById('postal_code').value = postalCodes[0].code;
                if (postalCodes[0].area) {
                    document.getElementById('address_line_2').value = postalCodes[0].area;
                }
                showMessage(`PIN code auto-updated to ${postalCodes[0].code}`, true);
                
                // Add success styling
                const postalField = document.getElementById('postal_code');
                postalField.classList.add('border-success');
                setTimeout(() => postalField.classList.remove('border-success'), 2000);
                
            } else {
                // Multiple postal codes - show modal selection
                showPostalCodeSelection(postalCodes);
            }
        } else {
            // No postal codes found
            document.getElementById('postal_code').value = '';
            showMessage('No postal codes found for selected city. You can enter manually.', false);
        }
    } catch (error) {
        console.error('Error fetching postal codes:', error);
        // Remove loading state on error
        document.getElementById('city_id').classList.remove('city-loading');
        showMessage('Error loading postal codes', false);
    }
}

// Fetch location details by PIN code
// Updated fetchLocationByPinCode with auto-filling markers
async function fetchLocationByPinCode(pinCode) {
    try {
        // Show loading state
        const stateSelect = document.getElementById('state_id');
        const citySelect = document.getElementById('city_id');
        
        // Mark elements as auto-filling to prevent unwanted modal triggers
        stateSelect.classList.add('auto-filling');
        citySelect.classList.add('auto-filling');
        
        stateSelect.innerHTML = '<option value="">Loading states...</option>';
        citySelect.innerHTML = '<option value="">Loading cities...</option>';
        
        // Fetch postal code data
        const response = await fetch(`/api/postal-code/${pinCode}`);
        const data = await response.json();
        
        if (data.success) {
            console.log('PIN Code Data:', data.data);
            
            // Set country if it's different
            const countrySelect = document.getElementById('country_id');
            if (countrySelect.value !== data.data.country_id.toString()) {
                countrySelect.value = data.data.country_id;
            }
            
            // Load states and wait for completion
            await loadStates(data.data.country_id);
            
            // Set state value (won't trigger modal due to auto-filling class)
            stateSelect.value = data.data.state_id;
            
            // Load cities and wait for completion
            await loadCities(data.data.state_id);
            
            // Set city value
            citySelect.value = data.data.city_id;
            
            // Update the area field if available
            if (data.data.area && document.getElementById('address_line_2')) {
                document.getElementById('address_line_2').value = data.data.area;
            }
            
            showMessage('Location auto-filled based on PIN code', true);
            
        } else {
            // Reset to default state
            stateSelect.innerHTML = '<option value="">Select State</option>';
            citySelect.innerHTML = '<option value="">Select City</option>';
            showMessage(data.message || 'Postal code not found', false);
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Error fetching location details', false);
        
        // Reset dropdowns on error
        document.getElementById('state_id').innerHTML = '<option value="">Select State</option>';
        document.getElementById('city_id').innerHTML = '<option value="">Select City</option>';
    } finally {
        // Remove auto-filling markers after a short delay
        setTimeout(() => {
            document.getElementById('state_id').classList.remove('auto-filling');
            document.getElementById('city_id').classList.remove('auto-filling');
        }, 500);
    }
}

// Load states based on country
async function loadStates(countryId) {
    const stateSelect = document.getElementById('state_id');
    const citySelect = document.getElementById('city_id');
    
    if (!countryId) {
        stateSelect.innerHTML = '<option value="">First select country</option>';
        stateSelect.disabled = true;
        citySelect.innerHTML = '<option value="">First select state</option>';
        citySelect.disabled = true;
        return;
    }

    try {
        const response = await fetch(`/api/states/${countryId}`);
        const states = await response.json();
        
        stateSelect.innerHTML = '<option value="">Select State</option>';
        states.forEach(state => {
            stateSelect.innerHTML += `<option value="${state.id}">${state.name}</option>`;
        });
        stateSelect.disabled = false;
        
        // Reset cities
        citySelect.innerHTML = '<option value="">First select state</option>';
        citySelect.disabled = true;
        
    } catch (error) {
        console.error('Error loading states:', error);
    }
}

// Load cities based on state
// Enhanced loadCities function with postal code info
async function loadCities(stateId) {
    const citySelect = document.getElementById('city_id');
    
    if (!stateId) {
        citySelect.innerHTML = '<option value="">First select state</option>';
        citySelect.disabled = true;
        return;
    }

    try {
        const response = await fetch(`/api/cities/${stateId}`);
        const cities = await response.json();
        
        citySelect.innerHTML = '<option value="">Select City</option>';
        
        // Group cities: Major cities first
        const majorCities = cities.filter(city => city.is_major);
        const otherCities = cities.filter(city => !city.is_major);
        
        if (majorCities.length > 0) {
            citySelect.innerHTML += '<optgroup label="🏙️ Major Cities">';
            majorCities.forEach(city => {
                citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
            });
            citySelect.innerHTML += '</optgroup>';
        }
        
        if (otherCities.length > 0) {
            citySelect.innerHTML += '<optgroup label="🏘️ Other Cities">';
            otherCities.forEach(city => {
                citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
            });
            citySelect.innerHTML += '</optgroup>';
        }
        
        citySelect.disabled = false;
        
    } catch (error) {
        console.error('Error loading cities:', error);
    }
}

async function fetchPostalCodesForState(stateId) {
    try {
        // Add loading state to state dropdown
        const stateSelect = document.getElementById('state_id');
        stateSelect.classList.add('state-loading');
        
        const response = await fetch(`/api/postal-codes/state/${stateId}`);
        const data = await response.json();
        
        // Remove loading state
        stateSelect.classList.remove('state-loading');
        
        if (data.success && data.postal_codes.length > 0) {
            const postalCodes = data.postal_codes;
            
            if (postalCodes.length === 1) {
                // If only one postal code, auto-fill it
                document.getElementById('postal_code').value = postalCodes[0].code;
                if (postalCodes[0].area) {
                    document.getElementById('address_line_2').value = postalCodes[0].area;
                }
                showMessage(`PIN code auto-updated to ${postalCodes[0].code}`, true);
                
                // Add success styling
                const postalField = document.getElementById('postal_code');
                postalField.classList.add('border-success');
                setTimeout(() => postalField.classList.remove('border-success'), 2000);
                
            } else {
                // Multiple postal codes - show modal selection (same as city)
                showStatePostalCodeSelection(postalCodes);
            }
        } else {
            // No postal codes found
            document.getElementById('postal_code').value = '';
            showMessage('No postal codes found for selected state. Please select a city.', false);
        }
    } catch (error) {
        console.error('Error fetching postal codes for state:', error);
        // Remove loading state on error
        document.getElementById('state_id').classList.remove('state-loading');
        showMessage('Error loading postal codes', false);
    }
}

function showStatePostalCodeSelection(postalCodes) {
    const modalHtml = `
        <div class="modal fade" id="statePostalCodeModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">📍 Select PIN Code from State</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Multiple PIN codes available for this state. Please select one:</p>
                        <div class="list-group">
                            ${postalCodes.map(postal => `
                                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" 
                                        onclick="selectStatePostalCode('${postal.code}', '${postal.area || ''}', '${postal.city_id}')">
                                    <div>
                                        <strong class="d-block">${postal.code}</strong>
                                        <small class="text-muted">${postal.area || 'General Area'} - ${postal.city_name}</small>
                                    </div>
                                    <i class="fas fa-chevron-right text-muted"></i>
                                </button>
                            `).join('')}
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                💡 Selecting a PIN code will also auto-select the corresponding city
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal
    const existingModal = document.getElementById('statePostalCodeModal');
    if (existingModal) existingModal.remove();
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('statePostalCodeModal'));
    modal.show();
}

function selectStatePostalCode(code, area, cityId) {
    // Update PIN code field
    document.getElementById('postal_code').value = code;
    
    // Update area field if available
    if (area && area !== '') {
        const addressLine2 = document.getElementById('address_line_2');
        if (addressLine2.value === '' || confirm('Replace existing area with: ' + area + '?')) {
            addressLine2.value = area;
        }
    }
    
    // Auto-select the corresponding city
    document.getElementById('city_id').value = cityId;
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('statePostalCodeModal'));
    modal.hide();
    
    // Show success message
    showMessage(`PIN code updated to ${code} and city auto-selected`, true);
    
    // Add visual feedback
    const postalCodeField = document.getElementById('postal_code');
    const cityField = document.getElementById('city_id');
    
    postalCodeField.classList.add('border-success');
    cityField.classList.add('border-success');
    
    setTimeout(() => {
        postalCodeField.classList.remove('border-success');
        cityField.classList.remove('border-success');
    }, 2000);
}

function showPostalCodeSelectionbk(postalCodes) {
    // Create a modal or update the PIN code field with a datalist
    const postalCodeInput = document.getElementById('postal_code');
    
    // Option 1: Create datalist for autocomplete
    let datalist = document.getElementById('postal-code-list');
    if (!datalist) {
        datalist = document.createElement('datalist');
        datalist.id = 'postal-code-list';
        postalCodeInput.parentNode.appendChild(datalist);
        postalCodeInput.setAttribute('list', 'postal-code-list');
    }
    
    // Clear existing options
    datalist.innerHTML = '';
    
    // Add postal codes as options
    postalCodes.forEach(postal => {
        const option = document.createElement('option');
        option.value = postal.code;
        option.textContent = `${postal.code} - ${postal.area || 'Area'}`;
        datalist.appendChild(option);
    });
    
    // Set placeholder text
    postalCodeInput.placeholder = `Select from ${postalCodes.length} available PIN codes`;
    
    // Show message
    showMessage(`Found ${postalCodes.length} PIN codes for this city`, true);
}
// Replace the existing showPostalCodeSelection function with this modal version
function showPostalCodeSelection(postalCodes) {
    const modalHtml = `
        <div class="modal fade" id="postalCodeModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">📍 Select PIN Code</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Multiple PIN codes available for this city. Please select one:</p>
                        <div class="list-group">
                            ${postalCodes.map(postal => `
                                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" 
                                        onclick="selectPostalCode('${postal.code}', '${postal.area || ''}')">
                                    <div>
                                        <strong class="d-block">${postal.code}</strong>
                                        ${postal.area ? `<small class="text-muted">${postal.area}</small>` : '<small class="text-muted">General Area</small>'}
                                    </div>
                                    <i class="fas fa-chevron-right text-muted"></i>
                                </button>
                            `).join('')}
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                💡 Tip: Select the PIN code that matches your exact location for accurate delivery
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal
    const existingModal = document.getElementById('postalCodeModal');
    if (existingModal) existingModal.remove();
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('postalCodeModal'));
    modal.show();
}

function selectPostalCode(code, area) {
    // Update PIN code field
    document.getElementById('postal_code').value = code;
    
    // Update area field if available
    if (area && area !== '') {
        const addressLine2 = document.getElementById('address_line_2');
        if (addressLine2.value === '' || confirm('Replace existing area with: ' + area + '?')) {
            addressLine2.value = area;
        }
    }
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('postalCodeModal'));
    modal.hide();
    
    // Show success message
    showMessage(`PIN code updated to ${code}`, true);
    
    // Add visual feedback to the PIN code field
    const postalCodeField = document.getElementById('postal_code');
    postalCodeField.classList.add('border-success');
    setTimeout(() => {
        postalCodeField.classList.remove('border-success');
    }, 2000);
}
// Get current location
function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;
            
            showMessage('Location captured successfully!', 'success');
        }, function() {
            showMessage('Unable to get your location', 'error');
        });
    } else {
        showMessage('Geolocation is not supported by this browser', 'error');
    }
}


function showMessage(message, success = true) {
    if (typeof Toastify !== 'undefined') {
        Toastify({
            text: message,
            duration: 3000,
            close: false,
            gravity: "top",
            position: "right",
            backgroundColor: success ? "#28a745" : "#dc3545",
            stopOnFocus: true,
        }).showToast();
    } else {
        // Fallback if Toastify is not available
        alert(message);
    }
}

// Phone number validation
document.getElementById('phone_number').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
});

document.getElementById('alternate_phone').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
});

// PIN code validation
document.getElementById('postal_code').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
});

// GST number validation
document.getElementById('gst_number').addEventListener('input', function() {
    this.value = this.value.toUpperCase().slice(0, 15);
});





// Update your form submission handler
document.getElementById('address-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '⏳ Saving Address...';
    
    // Clear previous errors
    clearFormErrors();
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            // Redirect to checkout or refresh page
            setTimeout(() => {
                window.location.href = '/checkout';
            }, 1500);
        } else {
            showErrors(data.errors || {});
            showError(data.message || 'Please fix the errors below');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Something went wrong. Please try again.');
    })
    .finally(() => {
        // Reset button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '📍 Save Address & Continue';
    });
});

// Helper functions for error display
function clearFormErrors() {
    // Remove existing error messages
    document.querySelectorAll('.text-danger').forEach(el => {
        if (el.classList.contains('error-message')) {
            el.remove();
        }
    });
    
    // Remove error styling
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
}

function showErrors(errors) {
    Object.keys(errors).forEach(field => {
        const input = document.querySelector(`[name="${field}"]`);
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
    showToast(message, 'error');
}

function showSuccess(message) {
    showToast(message, 'success');
}

function showToast(message, type = 'info') {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
}



</script>

{{-- Custom CSS for better UX --}}

<style>
/* Add to your existing styles */
.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.error-message {
    display: block;
    font-size: 0.875rem;
    color: #dc3545;
}

.alert {
    border-radius: 0.375rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.form-label {
    font-weight: 600;
    color: #333;
}

.text-muted {
    font-size: 0.875rem;
}

.form-check-label {
    font-weight: 500;
}

.bg-white {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-lg {
    padding: 12px 24px;
    font-size: 16px;
    font-weight: 600;
}

.form-select:disabled {
    background-color: #f8f9fa;
    opacity: 0.6;
}

.text-danger.small {
    margin-top: 0.25rem;
}

.form-control:focus,
.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
</style>
    </div>
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
