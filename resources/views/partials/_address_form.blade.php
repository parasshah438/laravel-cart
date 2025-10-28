
<!-- Enhanced Location Detection Styles -->
<style>
    .auto-filling {
        background: linear-gradient(90deg, #e3f2fd, #bbdefb) !important;
        animation: autoFillPulse 1s ease-in-out infinite alternate;
    }
    
    .location-filled {
        background-color: #e8f5e8 !important;
        border-color: #28a745 !important;
        transition: all 0.3s ease;
    }
    
    .location-detected {
        animation: formSuccess 2s ease-in-out;
    }
    
    @keyframes autoFillPulse {
        from { background-color: #e3f2fd; }
        to { background-color: #bbdefb; }
    }
    
    @keyframes formSuccess {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }
    
    .btn-location {
        transition: all 0.3s ease;
    }
    
    .btn-location:disabled {
        opacity: 0.7;
        transform: scale(0.98);
    }
    
    .location-success-indicator {
        position: absolute;
        top: -10px;
        right: -10px;
        background: #28a745;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        animation: successPop 0.5s ease-out;
    }
    
    @keyframes successPop {
        0% { transform: scale(0); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
</style>

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
                       placeholder="Enter your full name" value="{{ Auth::user()->name }}">
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
    const postalCodeField = document.getElementById('postal_code');
    if (postalCodeField) {
        postalCodeField.addEventListener('input', function() {
            const pinCode = this.value;
            
            console.log('🎯 PIN Code input event triggered:', {
                originalValue: pinCode,
                inputLength: pinCode.length
            });
            
            // Remove non-numeric characters and limit to appropriate length based on country
            const countrySelect = document.getElementById('country_id');
            let maxLength = 6; // Default for India
            let allowedPattern = /[^0-9]/g; // Default numeric only
            
            // Get current country BEFORE any modifications
            const currentCountryValue = countrySelect?.value;
            const currentCountryText = countrySelect?.options[countrySelect?.selectedIndex]?.textContent?.trim()?.toLowerCase();
            
            console.log('🌍 Current country info:', {
                countryValue: currentCountryValue,
                countryText: currentCountryText,
                hasCountrySelect: !!countrySelect,
                optionsCount: countrySelect?.options?.length || 0
            });
            
            if (countrySelect && countrySelect.value) {
                const selectedOption = countrySelect.options[countrySelect.selectedIndex];
                if (selectedOption) {
                    const countryName = selectedOption.textContent.trim().toLowerCase();
                    
                    console.log('🔍 Analyzing country:', countryName);
                    
                    // Adjust validation based on country
                    if (countryName.includes('united states') || countryName.includes('usa')) {
                        maxLength = 5; // US ZIP codes
                        allowedPattern = /[^0-9]/g;
                        console.log('🇺🇸 US country detected - maxLength set to 5');
                    } else if (countryName.includes('united kingdom') || countryName.includes('uk')) {
                        maxLength = 8; // UK postcodes (e.g., SW1A 1AA)
                        allowedPattern = /[^A-Za-z0-9]/g; // Allow letters and numbers
                        console.log('🇬🇧 UK country detected - maxLength set to 8');
                    } else if (countryName.includes('canada')) {
                        maxLength = 7; // Canadian postal codes (e.g., K1A 0A6)
                        allowedPattern = /[^A-Za-z0-9]/g;
                        console.log('🇨🇦 Canada country detected - maxLength set to 7');
                    } else if (countryName.includes('germany')) {
                        maxLength = 5; // German postal codes
                        allowedPattern = /[^0-9]/g;
                        console.log('🇩🇪 Germany country detected - maxLength set to 5');
                    } else if (countryName.includes('france')) {
                        maxLength = 5; // French postal codes
                        allowedPattern = /[^0-9]/g;
                        console.log('🇫🇷 France country detected - maxLength set to 5');
                    } else if (countryName.includes('india')) {
                        maxLength = 6; // Indian PIN codes
                        allowedPattern = /[^0-9]/g;
                        console.log('🇮🇳 India country detected - maxLength set to 6');
                    } else {
                        console.log('🌐 Unknown country, using default settings');
                    }
                }
            } else {
                console.log('⚠️ No country selected or country select not found');
            }
            
            // Apply validation - but be more flexible with lengths
            const beforeValidation = this.value;
            this.value = this.value.replace(allowedPattern, '').slice(0, maxLength);
            const afterValidation = this.value;
            
            console.log('✂️ Validation applied:', {
                before: beforeValidation,
                after: afterValidation,
                maxLength: maxLength,
                pattern: allowedPattern.toString(),
                finalLength: this.value.length
            });
            
            // Get current country name for validation
            const countryName = countrySelect?.options[countrySelect.selectedIndex]?.textContent?.trim()?.toLowerCase() || 'india';
            
            // Debug logging
            console.log('🔍 PIN Code validation:', {
                enteredValue: this.value,
                countryName: countryName,
                maxLength: maxLength,
                currentLength: this.value.length
            });
            
            // Fetch location based on country-specific criteria - SIMPLIFIED AND MORE FLEXIBLE
            const shouldFetch = (countryName, value) => {
                let shouldCall = false;
                let reason = '';
                
                // Remove length restrictions - just check minimum requirements
                if (value.length >= 5) {
                    shouldCall = true;
                    reason = `Value has ${value.length} characters (>=5), calling API`;
                } else {
                    shouldCall = false;
                    reason = `Value has ${value.length} characters (<5), not calling API`;
                }
                
                console.log('🚀 Should fetch API?', {
                    country: countryName,
                    value: value,
                    length: value.length,
                    shouldCall: shouldCall,
                    reason: reason
                });
                
                return shouldCall;
            };
            
            if (shouldFetch(countryName, this.value)) {
                console.log('✅ Calling fetchLocationByPinCode with:', this.value);
                fetchLocationByPinCode(this.value);
            } else {
                console.log('❌ Not calling API - criteria not met');
            }
        });
    } else {
        console.error('❌ postal_code field not found!');
    }

    // Country Change Handler
    const countryField = document.getElementById('country_id');
    if (countryField) {
        countryField.addEventListener('change', function() {
            const countryId = this.value;
            const isLocationAutoFill = this.hasAttribute('data-location-autofill');
            const isAutoFilling = this.classList.contains('auto-filling');
            
            // Check if this is a manual country change (not auto-fill)
            const isManualChange = !isLocationAutoFill && !isAutoFilling;
            const postalCodeField = document.getElementById('postal_code');
            const hasExistingData = postalCodeField && postalCodeField.value.length === 6;
            
            if (isManualChange && hasExistingData) {
                // User manually changed country after auto-fill - clear all location-specific data
                console.log('🌍 Manual country change detected - clearing all location-specific fields');
                
                // Clear PIN code
                postalCodeField.value = '';
                
                // Clear address fields
                const addressLine2Field = document.getElementById('address_line_2');
                if (addressLine2Field && addressLine2Field.value) {
                    addressLine2Field.value = '';
                }
                
                // Clear landmark
                const landmarkField = document.getElementById('landmark');
                if (landmarkField && landmarkField.value) {
                    landmarkField.value = '';
                }
                
                // Show helpful message
                showMessage('🌍 Country changed! All location data cleared. Please fill address details for the new country.', true);
                
                // Add visual indication
                postalCodeField.classList.add('border-warning');
                if (addressLine2Field) addressLine2Field.classList.add('border-warning');
                
                setTimeout(() => {
                    postalCodeField.classList.remove('border-warning');
                    if (addressLine2Field) addressLine2Field.classList.remove('border-warning');
                }, 5000);
            }
            
            loadStates(countryId);
        });
    }

    const cityField = document.getElementById('city_id');
    if (cityField) {
        cityField.addEventListener('change', function() {
            const cityId = this.value;
            const isLocationAutoFill = this.hasAttribute('data-location-autofill');
            const isAutoFilling = this.classList.contains('auto-filling');
            const postalCodeField = document.getElementById('postal_code');
            const address_line_1 = document.getElementById('address_line_1');
            const address_line_2 = document.getElementById('address_line_2');
            const hasExistingPinCode = postalCodeField && postalCodeField.value.length === 6;
            
            // Check if this is a manual city change (not auto-fill)
            const isManualChange = !isLocationAutoFill && !isAutoFilling;
            
            if (isManualChange && hasExistingPinCode) {
                // User manually changed city after auto-fill - clear related fields
                console.log('🏙️ Manual city change detected - clearing PIN code and address fields');
                
                // Clear PIN code
                postalCodeField.value = '';
                
                // Clear address fields that might be location-specific
                const addressLine2Field = document.getElementById('address_line_2');
                if (addressLine2Field && addressLine2Field.value) {
                    // Only clear if it looks like it was auto-filled (no manual input)
                    addressLine2Field.value = '';
                }

                if (address_line_1 && address_line_1.value) {
                    address_line_1.value = '';
                }
                
                // Clear landmark if it was auto-filled
                const landmarkField = document.getElementById('landmark');
                if (landmarkField && landmarkField.value) {
                    landmarkField.value = '';
                }
                
                // Show helpful message
                showMessage('🏙️ City changed! PIN code and address cleared. Please update them for the new city.', true);
                
                // Add visual indication that fields need to be refilled
                postalCodeField.classList.add('border-warning');
                if (addressLine2Field) addressLine2Field.classList.add('border-warning');
                
                // Remove warning styling after user starts typing
                setTimeout(() => {
                    postalCodeField.classList.remove('border-warning');
                    if (addressLine2Field) addressLine2Field.classList.remove('border-warning');
                }, 5000);
            }
            
            // Only fetch postal codes if:
            // 1. City is selected
            // 2. Not an auto-fill operation
            // 3. User doesn't already have a PIN code from location detection
            if (cityId && !isLocationAutoFill && !hasExistingPinCode) {
                fetchPostalCodesForCity(cityId);
            }
            
            // Clean up the auto-fill flag after processing
            if (isLocationAutoFill) {
                this.removeAttribute('data-location-autofill');
            }
        });
    }

    // State Change Handler
    // Enhanced State Change Handler - Only show modal for manual changes
    const stateField = document.getElementById('state_id');
    if (stateField) {
        stateField.addEventListener('change', function() {
            const stateId = this.value;
            
            if (stateId) {
                // Always load cities for the selected state
                loadCities(stateId);
                
                // Check if this is a manual state change (not auto-fill)
                const postalCodeField = document.getElementById('postal_code');
                const hasExistingPinCode = postalCodeField && postalCodeField.value.length === 6;
                const isManualSelection = !this.classList.contains('auto-filling');
                const isLocationAutoFill = this.hasAttribute('data-location-autofill');
                
                if (hasExistingPinCode && isManualSelection && !isLocationAutoFill) {
                    // User manually changed state after auto-fill - clear location-specific data
                    console.log('🏛️ Manual state change detected - clearing location-specific fields');
                    
                    // Clear PIN code
                    postalCodeField.value = '';
                    
                    // Clear city selection (will be reset by loadCities anyway)
                    const cityField = document.getElementById('city_id');
                    if (cityField) {
                        cityField.value = '';
                    }
                    
                    // Clear address fields that might be location-specific
                    const addressLine2Field = document.getElementById('address_line_2');
                    if (addressLine2Field && addressLine2Field.value) {
                        addressLine2Field.value = '';
                    }
                    
                    // Clear landmark if it was auto-filled
                    const landmarkField = document.getElementById('landmark');
                    if (landmarkField && landmarkField.value) {
                        landmarkField.value = '';
                    }
                    
                    // Show helpful message
                    showMessage('🏛️ State changed! PIN code and address cleared. Please select city and update address.', true);
                    
                    // Add visual indication that fields need to be refilled
                    postalCodeField.classList.add('border-warning');
                    if (addressLine2Field) addressLine2Field.classList.add('border-warning');
                    
                    // Remove warning styling after user starts typing
                    setTimeout(() => {
                        postalCodeField.classList.remove('border-warning');
                        if (addressLine2Field) addressLine2Field.classList.remove('border-warning');
                    }, 5000);
                    
                    // Don't show postal code modal since we cleared the PIN code
                } else if (hasExistingPinCode && isManualSelection && !isLocationAutoFill) {
                    // User manually changed state after having a PIN code - show options
                    fetchPostalCodesForState(stateId);
                }
                
                // Clean up the auto-fill flag after processing
                if (isLocationAutoFill) {
                    this.removeAttribute('data-location-autofill');
                }
            } else {
                // Reset everything when no state selected
                resetLocationFields();
            }
        });
    }

    // Load states for default country (India)
    const defaultCountryField = document.getElementById('country_id');
    if (defaultCountryField && defaultCountryField.value) {
        loadStates(defaultCountryField.value);
    }
});

// Postal code change handler (outside DOMContentLoaded)
const postalCodeChangeField = document.getElementById('postal_code');
if (postalCodeChangeField) {
    postalCodeChangeField.addEventListener('change', function() {
        const selectedCode = this.value;
        const isLocationAutoFill = this.hasAttribute('data-location-autofill');
        
        // Only process if it's not an auto-fill and the code is valid
        if (selectedCode.length === 6 && !isLocationAutoFill) {
            // Find the selected postal code data
            const datalist = document.getElementById('postal-code-list');
            if (datalist) {
                const selectedOption = Array.from(datalist.options).find(option => option.value === selectedCode);
                if (selectedOption) {
                    // Extract area from the option text and update address_line_2
                    const areaMatch = selectedOption.textContent.match(/- (.+)$/);
                    if (areaMatch && areaMatch[1] !== 'Area') {
                        const addressLine2Field = document.getElementById('address_line_2');
                        if (addressLine2Field) {
                            addressLine2Field.value = areaMatch[1];
                        }
                    }
                }
            }
        }
        
        // Clean up the auto-fill flag after processing
        if (isLocationAutoFill) {
            this.removeAttribute('data-location-autofill');
        }
    });
}

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
// Updated fetchLocationByPinCode with auto-filling markers and country code support
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
        
        // Get country code from selected country
        const countrySelect = document.getElementById('country_id');
        let countryCode = 'IN'; // Default to India
        
        if (countrySelect && countrySelect.value) {
            // Get country code from selected option text or data attribute
            const selectedOption = countrySelect.options[countrySelect.selectedIndex];
            if (selectedOption) {
                // Try to extract country code from option data or text
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
                    'france': 'FR',
                    'japan': 'JP',
                    'china': 'CN',
                    'brazil': 'BR',
                    'russia': 'RU',
                    'italy': 'IT',
                    'spain': 'ES',
                    'mexico': 'MX',
                    'south korea': 'KR',
                    'netherlands': 'NL',
                    'sweden': 'SE',
                    'norway': 'NO',
                    'denmark': 'DK',
                    'finland': 'FI'
                };
                
                countryCode = countryMapping[countryName] || 'IN';
                console.log(`🌍 fetchLocationByPinCode - Country detected: "${countryName}" -> ${countryCode}`);
            }
        }
        
        console.log(`📡 Making API call: /api/pincode-details?pincode=${pinCode}&country_code=${countryCode}`);
        
        // Fetch postal code data with country code
        const response = await fetch(`/api/pincode-details?pincode=${pinCode}&country_code=${countryCode}`);
        
        console.log(`📋 API Response status: ${response.status}`, response);
        
        if (!response.ok) {
            console.error(`❌ API Response failed with status: ${response.status}`);
            console.error(`❌ Response text:`, await response.text());
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        
        console.log(`📋 API Response:`, {
            status: response.status,
            success: data.success,
            data: data.data,
            error: data.error || 'No error'
        });
        
        if (data.success) {
            console.log('PIN Code Data:', data.data);
            
            // Set country if it's different
            const countrySelect = document.getElementById('country_id');
            if (countrySelect.value !== data.data.country_id.toString()) {
                countrySelect.value = data.data.country_id;
            }

            document.getElementById('address_line_1').value = "";
            
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

    // Preserve current city selection if it exists and this is part of auto-fill
    const currentCityId = citySelect.value;
    const currentCityText = citySelect.options[citySelect.selectedIndex]?.textContent;
    const isAutoFilling = citySelect.classList.contains('auto-filling') || citySelect.hasAttribute('data-location-autofill');
    
    console.log('🏙️ loadCities called:', {
        stateId,
        currentCityId,
        currentCityText,
        isAutoFilling,
        preserveSelection: isAutoFilling && currentCityId
    });

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
        
        // Restore city selection if it was preserved during auto-fill
        if (isAutoFilling && currentCityId) {
            // Find the option that matches our preserved city
            const cityOption = Array.from(citySelect.options).find(option => option.value === currentCityId);
            if (cityOption) {
                citySelect.value = currentCityId;
                console.log(`✅ City selection restored: "${cityOption.textContent}" (${currentCityId})`);
            } else {
                console.warn(`⚠️ Could not restore city selection - city ${currentCityId} not found in new options`);
            }
        }
        
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
// Enhanced getCurrentLocation using GeolocationManager approach
function getCurrentLocation() {
    // Check if geolocation is supported
    if (!navigator.geolocation) {
        showMessage('🚫 Geolocation is not supported by this browser. Please fill the form manually.', false);
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
        showMessage('🔒 Location access requires HTTPS. Using IP-based location instead...', false);
        tryLocationFromIP();
        return;
    }

    // Show loading state
    const locationBtn = document.querySelector('button[onclick="getCurrentLocation()"]');
    const originalText = locationBtn.innerHTML;
    locationBtn.disabled = true;
    locationBtn.innerHTML = '🌍 Getting Location...';

    showMessage('📍 Requesting high-accuracy location access... Please allow when prompted.', true);

    // Use the same options as GeolocationManager for better accuracy
    const options = {
        enableHighAccuracy: true,
        timeout: 10000,          // 10 seconds (same as GeolocationManager)
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
                const latField = document.getElementById('latitude');
                const lngField = document.getElementById('longitude');
                if (latField) latField.value = coords.latitude;
                if (lngField) lngField.value = coords.longitude;

                showMessage(`📍 GPS lock achieved! Accuracy: ±${Math.round(coords.accuracy)}m. Getting address details...`, true);

                // Use the same API call as GeolocationManager
                const locationData = await getLocationDetailsFromCoords(coords.latitude, coords.longitude);
                
                if (locationData) {
                    // Apply the same form filling logic as location-integration
                    await applyLocationDataToForm(locationData, coords);
                    showMessage('✅ Perfect! Your current location detected and form auto-filled!', true);
                    
                    // Debug: Show what was detected
                    console.log('🎯 Location detected successfully:', {
                        country: locationData.country || locationData.country_name,
                        state: locationData.state || locationData.state_name,
                        city: locationData.city || locationData.city_name,
                        pincode: locationData.pincode || locationData.postal_code,
                        area: locationData.area || locationData.neighbourhood
                    });
                    
                    // Show detected location in a friendly message
                    setTimeout(() => {
                        const detectedLocation = [
                            locationData.area || locationData.neighbourhood,
                            locationData.city || locationData.city_name,
                            locationData.state || locationData.state_name,
                            locationData.country || locationData.country_name
                        ].filter(Boolean).join(', ');
                        
                        if (detectedLocation) {
                            showMessage(`📍 Detected: ${detectedLocation}`, true);
                        }
                    }, 2000);
                    
                } else {
                    throw new Error('Unable to get detailed address from coordinates');
                }

            } catch (error) {
                console.error('Location processing error:', error);
                showMessage('⚠️ GPS coordinates obtained but address lookup failed: ' + error.message, false);
                
                // Try enhanced fallback similar to GeolocationManager
                setTimeout(() => {
                    showMessage('🔄 Trying alternative location methods...', true);
                    tryEnhancedLocationFallback(position.coords.latitude, position.coords.longitude);
                }, 2000);
            } finally {
                // Reset button state for success case
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
            
            showMessage(errorMessage, false);
            
            // Use the same fallback strategy as GeolocationManager
            setTimeout(() => {
                tryEnhancedLocationFallback();
            }, 1000);
            
            // Reset button state for error case
            setTimeout(() => {
                locationBtn.disabled = false;
                locationBtn.innerHTML = originalText;
            }, 2000);
        },
        options
    );
}

// Get location details from coordinates (same as GeolocationManager)
async function getLocationDetailsFromCoords(latitude, longitude) {
    try {
        // Get CSRF token from multiple sources
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                         document.querySelector('input[name="_token"]')?.value ||
                         '{{ csrf_token() }}';
        
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
        console.error('Error getting location details:', error);
        throw error;
    }
}

// Apply location data to form (same logic as location-integration fillAddressForm)
async function applyLocationDataToForm(locationData, coords = null) {
    try {
        console.log('Applying location data to form:', locationData);

        // Store coordinates if provided
        if (coords) {
            const latField = document.getElementById('latitude');
            const lngField = document.getElementById('longitude');
            if (latField) latField.value = coords.latitude;
            if (lngField) lngField.value = coords.longitude;
        }

        // Normalize location data to handle different API response formats
        const normalizedData = normalizeLocationData(locationData);
        console.log('Normalized location data:', normalizedData);

        // Direct form field filling (like location-integration)
        const fieldMappings = [
            { source: ['area', 'neighbourhood', 'suburb', 'locality', 'sublocality'], target: 'address_line_2' },
            { source: ['pincode', 'postal_code', 'zip_code', 'postcode'], target: 'postal_code' },
        ];

        // Fill direct text fields first
        fieldMappings.forEach(mapping => {
            const field = document.getElementById(mapping.target);
            if (field) {
                const value = mapping.source.find(key => normalizedData[key]);
                if (value && normalizedData[value]) {
                    field.value = normalizedData[value];
                    // Add visual feedback
                    field.classList.add('location-filled');
                    setTimeout(() => field.classList.remove('location-filled'), 3000);
                }
            }
        });

        // Handle PIN code specially with validation (like location-integration)
        const pincodeValue = normalizedData.pincode || normalizedData.postal_code || normalizedData.zip_code || normalizedData.postcode;
        if (pincodeValue && /^\d{6}$/.test(pincodeValue)) {
            const postalCodeField = document.getElementById('postal_code');
            if (postalCodeField) {
                // Mark as location auto-fill to prevent unwanted triggers
                postalCodeField.setAttribute('data-location-autofill', 'true');
                postalCodeField.value = pincodeValue;
                
                // Add success styling
                postalCodeField.classList.add('border-success', 'location-filled');
                setTimeout(() => {
                    postalCodeField.classList.remove('border-success', 'location-filled');
                    // Clean up the auto-fill flag after styling
                    postalCodeField.removeAttribute('data-location-autofill');
                }, 3000);
                
                // Use pincode to get and fill additional location data
                try {
                    await fillLocationFromPincode(pincodeValue);
                } catch (error) {
                    console.warn('Pincode lookup failed:', error);
                }
            }
        }

        // Fill dropdowns with enhanced matching
        await fillLocationDropdowns(normalizedData);

        // Check if dropdowns were filled successfully and offer manual correction if needed
        setTimeout(() => {
            checkAndOfferManualCorrection(normalizedData);
            
            // Debug: Show final form state
            console.log('📋 Final form state:', {
                country: document.getElementById('country_id')?.value || 'Not selected',
                countryText: document.getElementById('country_id')?.options[document.getElementById('country_id')?.selectedIndex]?.textContent || 'Not selected',
                state: document.getElementById('state_id')?.value || 'Not selected', 
                stateText: document.getElementById('state_id')?.options[document.getElementById('state_id')?.selectedIndex]?.textContent || 'Not selected',
                city: document.getElementById('city_id')?.value || 'Not selected',
                cityText: document.getElementById('city_id')?.options[document.getElementById('city_id')?.selectedIndex]?.textContent || 'Not selected',
                pincode: document.getElementById('postal_code')?.value || 'Not filled',
                area: document.getElementById('address_line_2')?.value || 'Not filled'
            });
        }, 3000);

        // Fill address fields with detailed information
        fillAddressFieldsFromLocation(normalizedData);

        showLocationSuccessAnimation();

    } catch (error) {
        console.error('Error applying location data to form:', error);
        showMessage('⚠️ Location detected but form filling had issues: ' + error.message, false);
    }
}

// Normalize location data to handle different API response formats
function normalizeLocationData(locationData) {
    const normalized = { ...locationData };
    
    // Normalize country variations
    if (locationData.country) {
        normalized.country = locationData.country;
        normalized.country_name = locationData.country;
    }
    if (locationData.country_code) {
        normalized.country_code = locationData.country_code.toUpperCase();
    }
    
    // Normalize state variations
    if (locationData.state || locationData.state_name || locationData.administrative_area_level_1) {
        normalized.state = locationData.state || locationData.state_name || locationData.administrative_area_level_1;
        normalized.state_name = normalized.state;
    }
    
    // Normalize city variations
    if (locationData.city || locationData.city_name || locationData.locality || locationData.administrative_area_level_2) {
        normalized.city = locationData.city || locationData.city_name || locationData.locality || locationData.administrative_area_level_2;
        normalized.city_name = normalized.city;
    }
    
    // Normalize area/locality variations
    if (locationData.area || locationData.neighbourhood || locationData.suburb || locationData.sublocality) {
        normalized.area = locationData.area || locationData.neighbourhood || locationData.suburb || locationData.sublocality;
    }
    
    // Normalize postal code variations
    if (locationData.pincode || locationData.postal_code || locationData.zip_code || locationData.postcode) {
        const postal = locationData.pincode || locationData.postal_code || locationData.zip_code || locationData.postcode;
        normalized.pincode = postal;
        normalized.postal_code = postal;
    }
    
    return normalized;
}

// Fill location from pincode (like location-integration pincode input)
async function fillLocationFromPincode(pincode) {
    try {
        // Get country code from selected country
        const countrySelect = document.getElementById('country_id');
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
                    'france': 'FR',
                    'japan': 'JP',
                    'china': 'CN',
                    'brazil': 'BR',
                    'russia': 'RU',
                    'italy': 'IT',
                    'spain': 'ES',
                    'mexico': 'MX',
                    'south korea': 'KR',
                    'netherlands': 'NL',
                    'sweden': 'SE',
                    'norway': 'NO',
                    'denmark': 'DK',
                    'finland': 'FI'
                };
                
                countryCode = countryMapping[countryName] || 'IN';
            }
        }
        
        // Use the same API as location-integration with country code
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
                console.log('Pincode data received:', pincodeData);
                
                // Fill form fields directly from pincode data
                if (pincodeData.area) {
                    const areaField = document.getElementById('address_line_2');
                    if (areaField && !areaField.value) {
                        areaField.value = pincodeData.area;
                    }
                }
                
                // Use pincode data to help with dropdown selection
                await fillLocationDropdowns(pincodeData);
                
                showMessage(`📮 PIN code ${pincode} validated and location updated!`, true);
                return pincodeData;
            }
        }
    } catch (error) {
        console.error('Pincode lookup failed:', error);
        throw error;
    }
}

// Fill location dropdowns with enhanced smart matching
async function fillLocationDropdowns(locationData) {
    try {
        console.log('Filling dropdowns with location data:', locationData);

        // Debug: Show available options in dropdowns
        debugDropdownOptions();

        // Enhanced country matching
        const countrySelect = document.getElementById('country_id');
        if (countrySelect && (locationData.country || locationData.country_code)) {
            // Check if country is already selected during auto-fill
            const currentCountryValue = countrySelect.value;
            const currentCountryText = countrySelect.options[countrySelect.selectedIndex]?.textContent?.trim();
            
            if (currentCountryValue && currentCountryText && currentCountryText !== 'Select Country') {
                console.log(`🌍 Country already selected: "${currentCountryText}" (${currentCountryValue}), skipping country selection`);
            } else {
                countrySelect.classList.add('auto-filling');
                
                // Try multiple country matching strategies
                const countrySearchTerms = [
                    locationData.country,
                    locationData.country_code,
                    locationData.country_name,
                    // Common country name variations
                    locationData.country === 'United States' ? 'USA' : null,
                    locationData.country === 'USA' ? 'United States' : null,
                    locationData.country === 'UK' ? 'United Kingdom' : null,
                    locationData.country === 'United Kingdom' ? 'UK' : null,
                    locationData.country_code === 'IN' ? 'India' : null,
                    locationData.country_code === 'US' ? 'United States' : null,
                ].filter(Boolean);

                let countryFound = false;
                for (const searchTerm of countrySearchTerms) {
                    const countryOption = Array.from(countrySelect.options).find(option => {
                        const optionText = option.textContent.toLowerCase().trim();
                    const optionValue = option.value.toLowerCase();
                    const searchLower = searchTerm.toLowerCase().trim();
                    
                    // Priority 1: Exact match
                    if (optionText === searchLower || optionValue === searchLower) {
                        return true;
                    }
                    
                    // Priority 2: Exact word match (not partial)
                    const optionWords = optionText.split(/\s+/);
                    const searchWords = searchLower.split(/\s+/);
                    if (optionWords.some(word => searchWords.includes(word) && word.length > 2)) {
                        // Ensure it's not a substring match like "India" in "Indian"
                        return optionWords.includes(searchLower) || searchWords.includes(optionText);
                    }
                    
                    // Priority 3: Country codes (2-3 letters)
                    if (searchTerm.length <= 3 && optionValue === searchTerm.toLowerCase()) {
                        return true;
                    }
                    
                    // Priority 4: Starts with (for common abbreviations)
                    if (optionText.startsWith(searchLower) && searchLower.length >= 3) {
                        return true;
                    }
                    
                    return false;
                });
                
                if (countryOption) {
                    console.log(`Country matched: "${searchTerm}" -> "${countryOption.textContent}"`);
                    countrySelect.value = countryOption.value;
                    countrySelect.dispatchEvent(new Event('change'));
                    countryFound = true;
                    break;
                }
            }

            if (countryFound) {
                // Wait longer for state dropdown to populate
                await new Promise(resolve => setTimeout(resolve, 1500));
            } else {
                console.warn('No country match found for:', countrySearchTerms);
            }
            
                setTimeout(() => countrySelect.classList.remove('auto-filling'), 500);
            }
        }

        // Enhanced state matching
        const stateSelect = document.getElementById('state_id');
        if (stateSelect && (locationData.state || locationData.state_name)) {
            // Check if state is already selected during auto-fill
            const currentStateValue = stateSelect.value;
            const currentStateText = stateSelect.options[stateSelect.selectedIndex]?.textContent?.trim();
            
            if (currentStateValue && currentStateText && currentStateText !== 'First select country') {
                console.log(`🏛️ State already selected: "${currentStateText}" (${currentStateValue}), skipping state selection`);
            } else {
                stateSelect.classList.add('auto-filling');
                
                const stateSearchTerms = [
                    locationData.state,
                    locationData.state_name,
                    locationData.state_code,
                    // Handle state name variations
                    locationData.state?.replace(' State', ''),
                    locationData.state?.replace('State of ', ''),
                    // Handle abbreviations
                    locationData.state_code?.toUpperCase(),
                    locationData.state_code?.toLowerCase(),
                ].filter(Boolean);

                let stateFound = false;
                for (const searchTerm of stateSearchTerms) {
                    const stateOption = Array.from(stateSelect.options).find(option => {
                        const optionText = option.textContent.toLowerCase().trim();
                        const searchLower = searchTerm.toLowerCase().trim();
                        
                        // Priority 1: Exact match
                        if (optionText === searchLower) {
                            return true;
                        }
                        
                        // Priority 2: Exact word match for multi-word states
                        const optionWords = optionText.split(/\s+/);
                        const searchWords = searchLower.split(/\s+/);
                        if (searchWords.length > 1 && searchWords.every(word => optionWords.includes(word))) {
                            return true;
                        }
                        
                        // Priority 3: Single word exact match
                        if (optionWords.includes(searchLower) || searchWords.includes(optionText)) {
                            return true;
                        }
                    
                    // Priority 4: Starts with (for state abbreviations)
                    if (optionText.startsWith(searchLower) && searchLower.length >= 2) {
                        return true;
                    }
                    
                    // Priority 5: Contains (but only for longer names to avoid false matches)
                    if (searchLower.length >= 4 && optionText.includes(searchLower)) {
                        return true;
                    }
                    
                    return false;
                });
                
                if (stateOption) {
                    console.log(`State matched: "${searchTerm}" -> "${stateOption.textContent}"`);
                    
                    // Mark as location auto-fill to prevent modal from opening
                    stateSelect.setAttribute('data-location-autofill', 'true');
                    stateSelect.value = stateOption.value;
                    stateSelect.dispatchEvent(new Event('change'));
                    stateFound = true;
                    break;
                }
            }

                if (stateFound) {
                    // Wait longer for city dropdown to populate
                    await new Promise(resolve => setTimeout(resolve, 1500));
                } else {
                    console.warn('No state match found for:', stateSearchTerms);
                }
                
                setTimeout(() => stateSelect.classList.remove('auto-filling'), 500);
            }
        }

        // Enhanced city matching
        const citySelect = document.getElementById('city_id');
        if (citySelect && (locationData.city || locationData.city_name)) {
            // Check if city is already selected (from previous pincode lookup)
            const currentCityValue = citySelect.value;
            const currentCityText = citySelect.options[citySelect.selectedIndex]?.textContent?.trim();
            
            if (currentCityValue && currentCityText && !['Select City', 'First select state'].includes(currentCityText)) {
                console.log(`🏙️ City already selected: "${currentCityText}" (${currentCityValue}), skipping city selection`);
                setTimeout(() => citySelect.classList.remove('auto-filling'), 500);
                // Continue with the rest of the function without overwriting city
            } else {
                citySelect.classList.add('auto-filling');
                
                // Wait a bit more for city dropdown to be populated after state selection
                await new Promise(resolve => setTimeout(resolve, 500));
                
                const citySearchTerms = [
                    locationData.city,
                    locationData.city_name,
                    locationData.locality,
                    locationData.administrative_area_level_2,
                    // Handle city name variations
                    locationData.city?.replace(' City', ''),
                    locationData.city?.replace('City of ', ''),
                    locationData.city?.split('-')[0], // Handle hyphenated names
                    locationData.city?.split(' ')[0], // Handle multi-word names
                ].filter(Boolean);

                console.log('🏙️ Attempting city selection:', {
                    searchTerms: citySearchTerms,
                    availableOptions: citySelect.options.length,
                    firstFewOptions: Array.from(citySelect.options).slice(0, 5).map(opt => opt.textContent.trim())
                });

                let cityFound = false;
                for (const searchTerm of citySearchTerms) {
                    const cityOption = Array.from(citySelect.options).find(option => {
                        const optionText = option.textContent.toLowerCase().trim();
                        const searchLower = searchTerm.toLowerCase().trim();
                        
                        // Priority 1: Exact match
                        if (optionText === searchLower) {
                            return true;
                        }
                        
                        // Priority 2: Exact word match for multi-word cities
                        const optionWords = optionText.split(/\s+/);
                        const searchWords = searchLower.split(/\s+/);
                        if (searchWords.length > 1 && searchWords.every(word => optionWords.includes(word))) {
                            return true;
                        }
                        
                        // Priority 3: Single word exact match
                        if (optionWords.includes(searchLower) || searchWords.includes(optionText)) {
                            return true;
                        }
                        
                        // Priority 4: Starts with (for city prefixes)
                        if (optionText.startsWith(searchLower) && searchLower.length >= 3) {
                            return true;
                        }
                        
                        // Priority 5: Contains (but only for longer names)
                        if (searchLower.length >= 4 && optionText.includes(searchLower)) {
                            return true;
                        }
                        
                        // Priority 6: Fuzzy matching for similar names (last resort)
                        if (searchTerm.length > 4 && optionText.includes(searchLower.substring(0, 4))) {
                            return true;
                        }
                        
                        return false;
                    });
                    
                    if (cityOption) {
                        console.log(`City matched: "${searchTerm}" -> "${cityOption.textContent}"`);
                        
                        // Mark as location auto-fill to prevent unwanted modals
                        citySelect.setAttribute('data-location-autofill', 'true');
                        citySelect.value = cityOption.value;
                        
                        // Verify the selection was applied
                        console.log('City selection applied:', {
                            selectedValue: citySelect.value,
                            selectedText: citySelect.options[citySelect.selectedIndex]?.textContent,
                            isSelected: citySelect.value === cityOption.value
                        });
                        
                        citySelect.dispatchEvent(new Event('change'));
                        cityFound = true;
                        break;
                    }
                }

                if (!cityFound) {
                    console.warn('No city match found for:', citySearchTerms);
                    console.log('Available city options:', Array.from(citySelect.options).map(opt => opt.textContent.trim()).slice(0, 20));
                    
                    // If no exact match, try to match the closest city
                    const fuzzyMatch = await tryClosestCityMatch(citySelect, citySearchTerms[0]);
                    if (fuzzyMatch) {
                        cityFound = true;
                    }
                    
                    // If still no match and we have fewer than 5 options, the dropdown might not be loaded yet
                    if (!cityFound && citySelect.options.length < 5) {
                        console.log('City dropdown seems not fully loaded, retrying in 1 second...');
                        setTimeout(async () => {
                            console.log('Retrying city selection with', citySelect.options.length, 'options available');
                            await retryCitySelection(citySelect, citySearchTerms);
                        }, 1000);
                    }
                }
                
                setTimeout(() => citySelect.classList.remove('auto-filling'), 500);
            }
        } else if (citySelect) {
            // Debug: Log when city selection is skipped due to empty data
            const currentCityValue = citySelect.value;
            const currentCityText = citySelect.options[citySelect.selectedIndex]?.textContent?.trim();
            console.log(`🚫 City selection skipped - no city data provided. Current city: "${currentCityText}" (${currentCityValue})`);
        }

    } catch (error) {
        console.error('Error filling location dropdowns:', error);
    }
}

// Try to find the closest city match using fuzzy matching
async function tryClosestCityMatch(citySelect, searchCity) {
    if (!searchCity || searchCity.length < 3) return false;
    
    const searchLower = searchCity.toLowerCase();
    let bestMatch = null;
    let bestScore = 0;
    
    Array.from(citySelect.options).forEach(option => {
        if (option.value === '') return; // Skip empty option
        
        const optionText = option.textContent.toLowerCase();
        let score = 0;
        
        // Calculate similarity score
        if (optionText.includes(searchLower)) {
            score = 0.8;
        } else if (optionText.startsWith(searchLower.substring(0, 3))) {
            score = 0.6;
        } else if (searchLower.startsWith(optionText.substring(0, 3))) {
            score = 0.5;
        }
        
        // Boost score for shorter names (more likely to be correct)
        if (score > 0 && optionText.length < searchLower.length + 5) {
            score += 0.1;
        }
        
        if (score > bestScore) {
            bestScore = score;
            bestMatch = option;
        }
    });
    
    if (bestMatch && bestScore > 0.5) {
        console.log(`Fuzzy city match: "${searchCity}" -> "${bestMatch.textContent}" (score: ${bestScore})`);
        citySelect.value = bestMatch.value;
        citySelect.dispatchEvent(new Event('change'));
        return true;
    }
    
    return false;
}

// Retry city selection if dropdown wasn't loaded initially
async function retryCitySelection(citySelect, citySearchTerms) {
    console.log('🔄 Retrying city selection...', {
        terms: citySearchTerms,
        optionsAvailable: citySelect.options.length
    });
    
    for (const searchTerm of citySearchTerms) {
        const cityOption = Array.from(citySelect.options).find(option => {
            const optionText = option.textContent.toLowerCase().trim();
            const searchLower = searchTerm.toLowerCase().trim();
            
            return optionText === searchLower || 
                   optionText.includes(searchLower) ||
                   optionText.startsWith(searchLower);
        });
        
        if (cityOption) {
            console.log(`🎯 Retry city match: "${searchTerm}" -> "${cityOption.textContent}"`);
            citySelect.setAttribute('data-location-autofill', 'true');
            citySelect.value = cityOption.value;
            citySelect.dispatchEvent(new Event('change'));
            
            // Show success message
            showMessage(`🏙️ City auto-selected: ${cityOption.textContent.trim()}`, true);
            return true;
        }
    }
    
    console.warn('🚫 Retry city selection failed');
    return false;
}

// Check if dropdowns were filled successfully and offer manual correction
function checkAndOfferManualCorrection(detectedData) {
    const countrySelect = document.getElementById('country_id');
    const stateSelect = document.getElementById('state_id');
    const citySelect = document.getElementById('city_id');
    
    const unfilledFields = [];
    
    if (countrySelect && !countrySelect.value && detectedData.country) {
        unfilledFields.push(`Country: ${detectedData.country}`);
    }
    if (stateSelect && !stateSelect.value && detectedData.state) {
        unfilledFields.push(`State: ${detectedData.state}`);
    }
    if (citySelect && !citySelect.value && detectedData.city) {
        unfilledFields.push(`City: ${detectedData.city}`);
    }
    
    if (unfilledFields.length > 0) {
        console.warn('Some dropdowns could not be auto-filled:', unfilledFields);
        showMessage(`⚠️ Please manually select: ${unfilledFields.join(', ')}`, false);
        
        // Highlight unfilled dropdowns
        if (countrySelect && !countrySelect.value && detectedData.country) {
            highlightUnfilledDropdown(countrySelect, detectedData.country);
        }
        if (stateSelect && !stateSelect.value && detectedData.state) {
            highlightUnfilledDropdown(stateSelect, detectedData.state);
        }
        if (citySelect && !citySelect.value && detectedData.city) {
            highlightUnfilledDropdown(citySelect, detectedData.city);
        }
    } else {
        // All dropdowns filled successfully
        showMessage('🎯 All location fields auto-filled successfully!', true);
    }
}

// Highlight unfilled dropdown with detected value as placeholder
function highlightUnfilledDropdown(selectElement, detectedValue) {
    selectElement.classList.add('border-warning');
    selectElement.style.borderWidth = '2px';
    
    // Add a temporary option showing what was detected
    const tempOption = document.createElement('option');
    tempOption.value = '';
    tempOption.textContent = `⚠️ Please select (Detected: ${detectedValue})`;
    tempOption.style.color = '#856404';
    tempOption.style.fontStyle = 'italic';
    selectElement.insertBefore(tempOption, selectElement.firstChild);
    selectElement.selectedIndex = 0; // Select the temp option
    
    // Remove highlighting after selection
    selectElement.addEventListener('change', function() {
        if (this.value) {
            this.classList.remove('border-warning');
            this.style.borderWidth = '';
            if (tempOption.parentNode) {
                tempOption.remove();
            }
        }
    }, { once: true });
    
    // Remove temp option after 15 seconds
    setTimeout(() => {
        if (tempOption.parentNode) {
            tempOption.remove();
            selectElement.classList.remove('border-warning');
            selectElement.style.borderWidth = '';
        }
    }, 15000);
}

// Debug function to show available dropdown options
function debugDropdownOptions() {
    const dropdowns = [
        { id: 'country_id', name: 'Country' },
        { id: 'state_id', name: 'State' },
        { id: 'city_id', name: 'City' }
    ];
    
    dropdowns.forEach(dropdown => {
        const select = document.getElementById(dropdown.id);
        if (select && select.options.length > 1) {
            console.log(`${dropdown.name} options:`, 
                Array.from(select.options)
                    .filter(opt => opt.value !== '')
                    .slice(0, 10) // Show first 10 options
                    .map(opt => `"${opt.textContent.trim()}" (value: ${opt.value})`)
            );
        }
    });
}

// Fill address fields from location data
function fillAddressFieldsFromLocation(locationData) {
    // Fill address line 1 if available
    const addressLine1 = document.getElementById('address_line_1');
    if (addressLine1 && !addressLine1.value) {
        const addressComponents = [
            locationData.house_number,
            locationData.road,
            locationData.street
        ].filter(Boolean);
        
        if (addressComponents.length > 0) {
            addressLine1.value = addressComponents.join(' ');
        } else if (locationData.formatted_address) {
            addressLine1.placeholder = `Near ${locationData.formatted_address.split(',')[0]}`;
        }
    }

    // Fill landmark if available
    const landmarkField = document.getElementById('landmark');
    if (landmarkField && !landmarkField.value && locationData.landmark) {
        landmarkField.value = locationData.landmark;
    }
}

// Enhanced fallback location strategies (based on GeolocationManager)
async function tryEnhancedLocationFallback(fallbackLat = null, fallbackLng = null) {
    const strategies = [
        { name: 'Network Position API', method: tryNetworkGeolocation },
        { name: 'IP-based Location', method: tryLocationFromIP },
        { name: 'Browser Location API', method: tryBrowserLocationAPI }
    ];

    showMessage('🔄 Trying enhanced fallback location detection...', true);

    for (const strategy of strategies) {
        try {
            console.log(`Trying ${strategy.name}...`);
            showMessage(`🔍 Attempting ${strategy.name}...`, true);
            
            const result = await strategy.method(fallbackLat, fallbackLng);
            if (result) {
                showMessage(`✅ ${strategy.name} successful! Form auto-filled.`, true);
                return true;
            }
        } catch (error) {
            console.warn(`${strategy.name} failed:`, error);
        }
        
        // Wait between attempts
        await new Promise(resolve => setTimeout(resolve, 1000));
    }

    showMessage('⚠️ All location detection methods failed. Please fill the form manually.', false);
    return false;
}

// Network-based geolocation (similar to GeolocationManager fallback)
async function tryNetworkGeolocation(fallbackLat, fallbackLng) {
    try {
        // If we have coordinates from failed GPS, try to use them
        if (fallbackLat && fallbackLng) {
            console.log('Using fallback coordinates from previous GPS attempt');
            const locationData = await getLocationDetailsFromCoords(fallbackLat, fallbackLng);
            if (locationData) {
                await applyLocationDataToForm(locationData, { latitude: fallbackLat, longitude: fallbackLng });
                return true;
            }
        }

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                         document.querySelector('input[name="_token"]')?.value ||
                         '{{ csrf_token() }}';

        // Try to get network-based location
        const response = await fetch('/api/network-location', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                await applyLocationDataToForm(data.data);
                return true;
            }
        }
        
        return false;
    } catch (error) {
        console.error('Network geolocation failed:', error);
        return false;
    }
}

// IP-based location detection (enhanced) 
async function tryLocationFromIP() {
    try {
        console.log('Trying IP-based location detection...');
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                         document.querySelector('input[name="_token"]')?.value ||
                         '{{ csrf_token() }}';
        
        const response = await fetch('/api/ip-location', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const result = await response.json();
        
        if (result.success && result.data) {
            console.log('IP location data received:', result.data);
            await applyLocationDataToForm(result.data);
            showMessage('📡 Network-based location detected and form filled!', true);
            return true;
        } else {
            throw new Error(result.error || 'IP location detection failed');
        }
    } catch (error) {
        console.error('IP location failed:', error);
        return false;
    }
}

// Browser location API fallback
async function tryBrowserLocationAPI() {
    return new Promise((resolve) => {
        if (!navigator.geolocation) {
            resolve(false);
            return;
        }

        // Lower accuracy, faster timeout for fallback
        const fallbackOptions = {
            enableHighAccuracy: false,
            timeout: 5000,
            maximumAge: 300000 // 5 minutes cache
        };

        navigator.geolocation.getCurrentPosition(
            async (position) => {
                try {
                    const locationData = await getLocationDetailsFromCoords(
                        position.coords.latitude, 
                        position.coords.longitude
                    );
                    
                    if (locationData) {
                        await applyLocationDataToForm(locationData, position.coords);
                        resolve(true);
                    } else {
                        resolve(false);
                    }
                } catch (error) {
                    console.error('Browser API location processing failed:', error);
                    resolve(false);
                }
            },
            (error) => {
                console.error('Browser API geolocation failed:', error);
                resolve(false);
            },
            fallbackOptions
        );
    });
}

// Show success animation (like location-integration)
function showLocationSuccessAnimation() {
    const form = document.querySelector('form');
    if (form) {
        form.classList.add('location-detected');
        setTimeout(() => {
            form.classList.remove('location-detected');
        }, 2000);
    }

    // Highlight filled fields briefly
    const filledFields = ['postal_code', 'address_line_2', 'country_id', 'state_id', 'city_id'];
    filledFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field && field.value) {
            field.classList.add('location-filled');
            setTimeout(() => {
                field.classList.remove('location-filled');
            }, 3000);
        }
    });

        console.error('Enhanced IP location error:', error);
        showMessage('⚠️ Network location detection failed. Please fill the form manually.', false);
        throw error;
    
}

// Fill form with location data
async function fillFormWithLocationData(locationData) {
    try {
        console.log('Location data received:', locationData);

        // Store coordinates if available
        if (locationData.latitude && locationData.longitude) {
            document.getElementById('latitude').value = locationData.latitude;
            document.getElementById('longitude').value = locationData.longitude;
        }

        // Auto-fill country first
        if (locationData.country_code) {
            const countrySelect = document.getElementById('country_id');
            const countryCode = locationData.country_code.toUpperCase();
            
            // Find the country option
            const countryOption = Array.from(countrySelect.options).find(option => {
                return option.textContent.toLowerCase().includes(locationData.country?.toLowerCase() || '') ||
                       option.value === countryCode ||
                       option.dataset.code === countryCode;
            });

            if (countryOption) {
                countrySelect.value = countryOption.value;
                // Trigger change event to load states
                countrySelect.dispatchEvent(new Event('change'));
                
                // Wait a bit for states to load
                await new Promise(resolve => setTimeout(resolve, 1000));
            }
        }

        // Auto-fill state
        if (locationData.state) {
            const stateSelect = document.getElementById('state_id');
            
            // Mark as auto-filling to prevent unwanted modal triggers
            stateSelect.classList.add('auto-filling');
            
            // Try to find matching state
            let stateFound = false;
            Array.from(stateSelect.options).forEach(option => {
                if (option.textContent.toLowerCase().includes(locationData.state.toLowerCase()) ||
                    locationData.state.toLowerCase().includes(option.textContent.toLowerCase())) {
                    stateSelect.value = option.value;
                    stateFound = true;
                    return;
                }
            });

            if (stateFound) {
                // Trigger change event to load cities
                stateSelect.dispatchEvent(new Event('change'));
                
                // Wait for cities to load
                await new Promise(resolve => setTimeout(resolve, 1000));
            }

            // Remove auto-filling marker
            setTimeout(() => stateSelect.classList.remove('auto-filling'), 500);
        }

        // Auto-fill city
        if (locationData.city) {
            const citySelect = document.getElementById('city_id');
            
            // Mark as auto-filling
            citySelect.classList.add('auto-filling');
            
            // Try to find matching city
            Array.from(citySelect.options).forEach(option => {
                if (option.textContent.toLowerCase().includes(locationData.city.toLowerCase()) ||
                    locationData.city.toLowerCase().includes(option.textContent.toLowerCase())) {
                    citySelect.value = option.value;
                    return;
                }
            });

            // Remove auto-filling marker
            setTimeout(() => citySelect.classList.remove('auto-filling'), 500);
        }

        // Auto-fill PIN code if available
        if (locationData.pincode) {
            const postalCodeField = document.getElementById('postal_code');
            postalCodeField.value = locationData.pincode;
            
            // Add success styling
            postalCodeField.classList.add('border-success');
            setTimeout(() => postalCodeField.classList.remove('border-success'), 3000);
            
            // Trigger pincode validation to update area info
            if (locationData.pincode.length === 6) {
                fetchLocationByPinCode(locationData.pincode);
            }
        }

        // Auto-fill area/neighborhood in address line 2
        if (locationData.area && locationData.area !== locationData.city) {
            const addressLine2 = document.getElementById('address_line_2');
            if (!addressLine2.value) { // Only fill if empty
                addressLine2.value = locationData.area;
            }
        }

        // Auto-fill road/street information if available
        if (locationData.road && document.getElementById('address_line_1')) {
            const addressLine1 = document.getElementById('address_line_1');
            if (!addressLine1.value && locationData.house_number) {
                addressLine1.value = (locationData.house_number + ' ' + locationData.road).trim();
            } else if (!addressLine1.value) {
                addressLine1.placeholder = 'Near ' + locationData.road;
            }
        }

        // Show success animation
        showLocationSuccessAnimation();

    } catch (error) {
        console.error('Error filling form with location data:', error);
        showMessage('Location detected but could not auto-fill some fields: ' + error.message, false);
    }
}

// Show success animation for location detection
function showLocationSuccessAnimation() {
    const form = document.getElementById('address-form');
    
    // Add a subtle success animation
    form.classList.add('location-detected');
    
    // Create a temporary success indicator
    const successIndicator = document.createElement('div');
    successIndicator.className = 'alert alert-success alert-dismissible fade show position-fixed';
    successIndicator.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    successIndicator.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="me-2">
                <div class="spinner-border text-success" role="status" style="width: 1rem; height: 1rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div>
                <strong>📍 Location Detected!</strong><br>
                <small>Form auto-filled with your location details</small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(successIndicator);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (successIndicator.parentNode) {
            successIndicator.remove();
        }
        form.classList.remove('location-detected');
    }, 5000);
}

// Test location services (development only)
function testLocationServices() {
    console.log('Testing location services...');
    
    // Test 1: IP-based location
    fetch('/api/location-from-ip')
        .then(response => response.json())
        .then(data => {
            console.log('IP Location Test:', data);
            showMessage('IP Location: ' + (data.success ? 'Working ✅' : 'Failed ❌'), data.success);
        })
        .catch(error => {
            console.error('IP Location Test Failed:', error);
            showMessage('IP Location Test Failed', false);
        });

    // Test 2: Geolocation availability
    if (navigator.geolocation) {
        showMessage('Browser Geolocation: Available ✅', true);
    } else {
        showMessage('Browser Geolocation: Not Available ❌', false);
    }

    // Test 3: Check if on HTTPS or localhost
    const isSecure = location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
    showMessage('Secure Context: ' + (isSecure ? 'Yes ✅' : 'No ❌ (HTTPS required for GPS)'), isSecure);

    // Test 4: Sample coordinates for Mumbai (for testing)
    const sampleLatitude = 19.0760;
    const sampleLongitude = 72.8777;
    
    fetch('/api/location-details', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                           document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            latitude: sampleLatitude,
            longitude: sampleLongitude
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Coordinates Location Test (Mumbai):', data);
        showMessage('Coordinates API: ' + (data.success ? 'Working ✅' : 'Failed ❌'), data.success);
        if (data.success) {
            console.log('Sample location data:', data.data);
        }
    })
    .catch(error => {
        console.error('Coordinates Location Test Failed:', error);
        showMessage('Coordinates API Test Failed', false);
    });
}

// Enhanced error handling for form validation
function validateLocationData(locationData) {
    const issues = [];
    
    if (!locationData.country && !locationData.country_code) {
        issues.push('Country information missing');
    }
    
    if (!locationData.state) {
        issues.push('State information missing');
    }
    
    if (!locationData.city) {
        issues.push('City information missing');
    }
    
    return {
        isValid: issues.length === 0,
        issues: issues
    };
}

// Enhanced smart form filling with better accuracy
async function enhancedSmartFillForm(locationData) {
    try {
        console.log('Enhanced location data received:', locationData);

        // Store coordinates if available
        if (locationData.latitude && locationData.longitude) {
            document.getElementById('latitude').value = locationData.latitude;
            document.getElementById('longitude').value = locationData.longitude;
        }

        // Enhanced country detection with multiple fallbacks
        const countryDetected = await fillCountryField(locationData);
        if (!countryDetected) {
            console.warn('Country detection failed, using fallbacks');
        }

        // Enhanced state detection
        const stateDetected = await fillStateField(locationData, countryDetected);
        if (!stateDetected) {
            console.warn('State detection failed');
        }

        // Enhanced city detection
        const cityDetected = await fillCityField(locationData, stateDetected);
        if (!cityDetected) {
            console.warn('City detection failed');
        }

        // Enhanced PIN code detection with validation
        await fillPinCodeField(locationData);

        // Enhanced area/address fields
        await fillAddressFields(locationData);

        // Validate and report completion
        const validation = validateEnhancedLocationData(locationData);
        
        // Final status check - log all form field values
        const finalStatus = {
            country: document.getElementById('country_id')?.selectedOptions[0]?.textContent || 'Not selected',
            state: document.getElementById('state_id')?.selectedOptions[0]?.textContent || 'Not selected',
            city: document.getElementById('city_id')?.selectedOptions[0]?.textContent || 'Not selected',
            postalCode: document.getElementById('postal_code')?.value || 'Not filled',
            countryValue: document.getElementById('country_id')?.value || '',
            stateValue: document.getElementById('state_id')?.value || '',
            cityValue: document.getElementById('city_id')?.value || ''
        };
        
        console.log('🏁 FINAL FORM STATUS:', finalStatus);
        
        if (validation.isComplete) {
            showLocationSuccessAnimation();
            showMessage('🎯 Perfect! All location fields auto-filled successfully!', true);
        } else {
            showMessage(`✅ Location detected! ${validation.completedFields} fields filled. ${validation.missingFields} may need manual input.`, true);
        }

    } catch (error) {
        console.error('Enhanced form filling error:', error);
        showMessage('⚠️ Location detected but form filling had issues: ' + error.message, false);
    }
}

// Enhanced country field filling
async function fillCountryField(locationData) {
    const countrySelect = document.getElementById('country_id');
    if (!countrySelect) return false;

    const countryIdentifiers = [
        locationData.country_code?.toUpperCase(),
        locationData.country?.toLowerCase(),
        'IN' // Default fallback for India
    ].filter(Boolean);

    for (const identifier of countryIdentifiers) {
        const countryOption = Array.from(countrySelect.options).find(option => {
            return option.value === identifier ||
                   option.textContent.toLowerCase().includes(identifier.toLowerCase()) ||
                   option.dataset?.code === identifier;
        });

        if (countryOption) {
            countrySelect.value = countryOption.value;
            countrySelect.dispatchEvent(new Event('change'));
            
            // Wait for state loading
            await new Promise(resolve => setTimeout(resolve, 1500));
            return true;
        }
    }
    
    return false;
}

// Enhanced state field filling
async function fillStateField(locationData, countryDetected) {
    const stateSelect = document.getElementById('state_id');
    if (!stateSelect || !locationData.state) return false;

    // Mark as auto-filling
    stateSelect.classList.add('auto-filling');

    try {
        // Multiple state matching strategies
        const stateSearchTerms = [
            locationData.state,
            locationData.state_abbreviation,
            locationData.region
        ].filter(Boolean);

        let stateFound = false;

        for (const searchTerm of stateSearchTerms) {
            const stateOption = Array.from(stateSelect.options).find(option => {
                const optionText = option.textContent.toLowerCase();
                const searchLower = searchTerm.toLowerCase();
                
                return optionText === searchLower ||
                       optionText.includes(searchLower) ||
                       searchLower.includes(optionText) ||
                       optionText.replace(/\s+/g, '') === searchLower.replace(/\s+/g, '');
            });

            if (stateOption) {
                stateSelect.value = stateOption.value;
                stateSelect.dispatchEvent(new Event('change'));
                stateFound = true;
                break;
            }
        }

        if (stateFound) {
            await new Promise(resolve => setTimeout(resolve, 1500));
        }

        return stateFound;

    } finally {
        setTimeout(() => stateSelect.classList.remove('auto-filling'), 1000);
    }
}

// Enhanced city field filling
async function fillCityField(locationData, stateDetected) {
    const citySelect = document.getElementById('city_id');
    if (!citySelect || !locationData.city) return false;

    // Mark as auto-filling
    citySelect.classList.add('auto-filling');

    try {
        // Multiple city matching strategies
        const citySearchTerms = [
            locationData.city,
            locationData.city_name,
            locationData.locality,
            locationData.administrative_area_level_2
        ].filter(Boolean);

        let cityFound = false;

        for (const searchTerm of citySearchTerms) {
            const cityOption = Array.from(citySelect.options).find(option => {
                const optionText = option.textContent.toLowerCase();
                const searchLower = searchTerm.toLowerCase();
                
                return optionText === searchLower ||
                       optionText.includes(searchLower) ||
                       searchLower.includes(optionText) ||
                       optionText.replace(/\s+/g, '') === searchLower.replace(/\s+/g, '');
            });

            if (cityOption) {
                citySelect.value = cityOption.value;
                citySelect.dispatchEvent(new Event('change'));
                cityFound = true;
                break;
            }
        }

        return cityFound;

    } finally {
        setTimeout(() => citySelect.classList.remove('auto-filling'), 500);
    }
}

// Enhanced PIN code field filling
async function fillPinCodeField(locationData) {
    const postalCodeField = document.getElementById('postal_code');
    if (!postalCodeField) return;

    const pinCodes = [
        locationData.pincode,
        locationData.postal_code,
        locationData.zip_code,
        locationData.postcode
    ].filter(Boolean);

    for (const pinCode of pinCodes) {
        if (/^\d{6}$/.test(pinCode)) {
            postalCodeField.value = pinCode;
            
            // Add success styling
            postalCodeField.classList.add('border-success');
            setTimeout(() => postalCodeField.classList.remove('border-success'), 4000);
            
            // Trigger PIN code validation
            try {
                await fetchLocationByPinCode(pinCode);
            } catch (error) {
                console.warn('PIN code validation failed:', error);
            }
            
            return;
        }
    }
}

// Enhanced address fields filling
async function fillAddressFields(locationData) {
    // Fill area/neighborhood in address line 2
    const addressLine2Field = document.getElementById('address_line_2');
    if (addressLine2Field && !addressLine2Field.value) {
        const areaOptions = [
            locationData.area,
            locationData.neighbourhood,
            locationData.suburb,
            locationData.locality,
            locationData.sublocality
        ].filter(Boolean);

        if (areaOptions.length > 0) {
            addressLine2Field.value = areaOptions[0];
        }
    }

    // Enhanced street address filling
    const addressLine1Field = document.getElementById('address_line_1');
    if (addressLine1Field && !addressLine1Field.value) {
        const streetComponents = [
            locationData.house_number,
            locationData.road,
            locationData.street
        ].filter(Boolean);

        if (streetComponents.length > 0) {
            addressLine1Field.value = streetComponents.join(' ');
        } else if (locationData.formatted_address) {
            // Use part of formatted address as placeholder
            addressLine1Field.placeholder = `Near ${locationData.formatted_address.split(',')[0]}`;
        }
    }

    // Fill landmark if available
    const landmarkField = document.getElementById('landmark');
    if (landmarkField && !landmarkField.value && locationData.landmark) {
        landmarkField.value = locationData.landmark;
    }
}

// Enhanced validation function
function validateEnhancedLocationData(locationData) {
    const fields = [
        { key: 'country', field: 'country_id', name: 'Country' },
        { key: 'state', field: 'state_id', name: 'State' },
        { key: 'city', field: 'city_id', name: 'City' },
        { key: 'pincode', field: 'postal_code', name: 'PIN Code' },
        { key: 'area', field: 'address_line_2', name: 'Area' }
    ];

    const completed = [];
    const missing = [];

    fields.forEach(field => {
        const element = document.getElementById(field.field);
        const hasData = locationData[field.key] && element && element.value;
        
        if (hasData) {
            completed.push(field.name);
        } else {
            missing.push(field.name);
        }
    });

    return {
        isComplete: missing.length === 0,
        completedFields: completed.join(', '),
        missingFields: missing.join(', '),
        completionRate: Math.round((completed.length / fields.length) * 100)
    };
}


function showMessage(message, success = true) {
    if (typeof Toastify !== 'undefined') {
        Toastify({
            text: message,
            duration: 3000,
            close: false,
            gravity: "top",
            position: "right",
            style: {
                background: success ? "#28a745" : "#dc3545"
            },
            stopOnFocus: true,
        }).showToast();
    } else {
        // Fallback if Toastify is not available
        alert(message);
    }
}

// Phone number validation
const phoneNumberField = document.getElementById('phone_number');
if (phoneNumberField) {
    phoneNumberField.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
    });
}

const alternatePhoneField = document.getElementById('alternate_phone');
if (alternatePhoneField) {
    alternatePhoneField.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
    });
}

// PIN code validation
const postalCodeValidationField = document.getElementById('postal_code');
if (postalCodeValidationField) {
    postalCodeValidationField.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
    });
}

// GST number validation
const gstNumberField = document.getElementById('gst_number');
if (gstNumberField) {
    gstNumberField.addEventListener('input', function() {
        this.value = this.value.toUpperCase().slice(0, 15);
    });
}





// Update your form submission handler
const addressForm = document.getElementById('address-form');
if (addressForm) {
    addressForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        
        // Show loading state
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '⏳ Saving Address...';
        }
        
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
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '📍 Save Address & Continue';
            }
        });
    });
}// Helper functions for error display
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

/* Location Detection Styles */
.location-detected {
    animation: locationPulse 0.6s ease-in-out;
}

@keyframes locationPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

.border-success {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
}

/* Loading states for dropdowns */
.state-loading,
.city-loading {
    background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='%23333' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' fill='none' d='M16 1v6m7 1 4-4M1 16h6m1-7-4-4m25 8h-6m-1 7 4 4M16 31v-6m-7-1-4 4'/%3E%3C/svg%3E");
    background-position: right 0.75rem center;
    background-size: 16px;
    background-repeat: no-repeat;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Enhanced button styling */
button[onclick="getCurrentLocation()"] {
    background: linear-gradient(45deg, #28a745, #20c997);
    border: none;
    color: white;
    transition: all 0.3s ease;
}

button[onclick="getCurrentLocation()"]:hover {
    background: linear-gradient(45deg, #20c997, #28a745);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
}

button[onclick="getCurrentLocation()"]:disabled {
    background: #6c757d;
    transform: none;
    box-shadow: none;
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
