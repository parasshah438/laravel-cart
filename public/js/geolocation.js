/**
 * Geolocation Utility Class
 * Provides comprehensive location detection and management functionality
 * Similar to BigBasket's location system
 */
class GeolocationManager {
    constructor(options = {}) {
        this.options = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 300000,
            fallbackToIP: true,
            autoDetect: false,
            ...options
        };
        
        this.currentLocation = null;
        this.pincodeTimeout = null; // For debouncing pincode input
        this.callbacks = {
            onLocationDetected: [],
            onLocationError: [],
            onLocationChanged: []
        };
        
        // Initialize CSRF token
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (this.options.autoDetect) {
            this.detectLocation();
        }
    }
    
    /**
     * Add event listener
     */
    on(event, callback) {
        if (this.callbacks[event]) {
            this.callbacks[event].push(callback);
        }
    }
    
    /**
     * Trigger event callbacks
     */
    trigger(event, data) {
        if (this.callbacks[event]) {
            this.callbacks[event].forEach(callback => callback(data));
        }
    }
    
    /**
     * Detect user's current location using GPS
     */
    detectLocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                const error = new Error('Geolocation is not supported by this browser');
                this.trigger('onLocationError', error);
                
                if (this.options.fallbackToIP) {
                    this.getLocationFromIP().then(resolve).catch(reject);
                } else {
                    reject(error);
                }
                return;
            }
            
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const coords = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy
                    };
                    
                    this.getLocationDetails(coords.latitude, coords.longitude)
                        .then((locationData) => {
                            // Merge coordinates with location data and normalize
                            const fullLocation = { ...locationData, ...coords };
                            const normalizedLocation = this.normalizeLocationData(fullLocation);
                            this.currentLocation = normalizedLocation;
                            this.trigger('onLocationDetected', this.currentLocation);
                            resolve(this.currentLocation);
                        })
                        .catch(reject);
                },
                (error) => {
                    this.trigger('onLocationError', error);
                    
                    if (this.options.fallbackToIP) {
                        this.getLocationFromIP().then(resolve).catch(reject);
                    } else {
                        reject(error);
                    }
                },
                {
                    enableHighAccuracy: this.options.enableHighAccuracy,
                    timeout: this.options.timeout,
                    maximumAge: this.options.maximumAge
                }
            );
        });
    }
    
    /**
     * Get location details from coordinates
     */
    async getLocationDetails(latitude, longitude) {
        try {
            const response = await fetch('/api/location-details', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({ latitude, longitude })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Normalize location data to fill missing fields
                return this.normalizeLocationData(data.data);
            } else {
                throw new Error(data.error || 'Failed to get location details');
            }
        } catch (error) {
            console.error('Error getting location details:', error);
            throw error;
        }
    }
    
    /**
     * Normalize location data to extract missing fields from formatted_address
     */
    normalizeLocationData(location) {
        console.log('🔧 Normalizing location data:', location);
        
        // If city is missing but formatted_address exists, try to extract it
        if ((!location.city || location.city.trim() === '') && location.formatted_address) {
            const cityFromAddress = this.extractCityFromAddress(location.formatted_address);
            if (cityFromAddress) {
                console.log('🏙️ Extracted city from address:', cityFromAddress);
                location.city = cityFromAddress;
            }
        }
        
        // If area is missing but we have road, use road as area
        if ((!location.area || location.area.trim() === '') && location.road) {
            location.area = location.road;
        }
        
        console.log('✅ Normalized location data:', location);
        return location;
    }
    
    /**
     * Extract city from formatted address (simplified approach)
     */
    extractCityFromAddress(formattedAddress) {
        console.log('🔍 Extracting city from:', formattedAddress);
        
        // Split by commas and clean up
        const parts = formattedAddress.split(',').map(part => part.trim());
        
        // Simple pattern-based approach for Indian addresses
        if (parts.length >= 4) {
            for (let i = 0; i < parts.length; i++) {
                const part = parts[i];
                
                // Skip obvious non-cities
                if (/^\d+$/.test(part)) continue; // Skip pincodes like "382481"
                if (part.toLowerCase().includes('taluka')) continue; // Skip "Ghatlodiya Taluka"
                if (part.toLowerCase().includes('district')) continue; // Skip districts
                if (part.toLowerCase() === 'india') continue; // Skip country
                if (/^[A-Z]{2,3}$/.test(part)) continue; // Skip state abbreviations
                if (part.length < 3) continue; // Skip very short parts
                
                // Look for cities in typical positions (usually 3rd or 4th in Indian addresses)
                // "Road, Area, Sub-district, [CITY], State, Pincode, Country"
                if (i >= 2 && i <= 4 && part.length >= 4) {
                    // Additional check: if it's not obviously a state name
                    if (!part.toLowerCase().endsWith('pradesh') && 
                        !part.toLowerCase().endsWith('bengal') &&
                        !part.toLowerCase().includes('nadu')) {
                        console.log('✅ Extracted city from position:', part);
                        return part;
                    }
                }
            }
        }
        
        console.log('❌ Could not extract city from address');
        return null;
    }
    
    /**
     * Get location from IP address
     */
    async getLocationFromIP() {
        try {
            const response = await fetch('/api/location-from-ip');
            const data = await response.json();
            
            if (data.success) {
                this.currentLocation = this.normalizeLocationData(data.data);
                this.trigger('onLocationDetected', this.currentLocation);
                return this.currentLocation;
            } else {
                throw new Error(data.error || 'Failed to get location from IP');
            }
        } catch (error) {
            console.error('Error getting location from IP:', error);
            this.trigger('onLocationError', error);
            throw error;
        }
    }
    
    /**
     * Search locations by query
     */
    async searchLocations(query) {
        try {
            const response = await fetch(`/api/search-locations?query=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.error || 'Failed to search locations');
            }
        } catch (error) {
            console.error('Error searching locations:', error);
            throw error;
        }
    }
    
    /**
     * Check if pincode is valid for API lookup
     */
    isValidPincodeForLookup(pincode) {
        if (!pincode || pincode.length < 5) return false;
        
        // US ZIP codes: 5 digits exactly (10001, 90210)
        if (pincode.length === 5 && /^\d{5}$/.test(pincode)) {
            return true;
        }
        
        // US ZIP+4: 9 digits (123456789)
        if (pincode.length === 9 && /^\d{9}$/.test(pincode)) {
            return true;
        }
        
        // Indian PIN codes: 6 digits exactly (380001, 110001)
        if (pincode.length === 6 && /^\d{6}$/.test(pincode)) {
            return true;
        }
        
        // Canadian postal codes: A1A1A1 format
        if (pincode.length === 6 && /^[A-Z]\d[A-Z]\d[A-Z]\d$/i.test(pincode)) {
            return true;
        }
        
        // UK postal codes: Various formats (SW1A1AA, M11AA, etc.)
        if (pincode.length >= 5 && pincode.length <= 8 && /^[A-Z]{1,2}\d[A-Z\d]?\s?\d[A-Z]{2}$/i.test(pincode)) {
            return true;
        }
        
        // Don't lookup incomplete or invalid patterns
        return false;
    }
    
    /**
     * Get validation reason for debugging
     */
    getPincodeValidationReason(pincode) {
        if (!pincode) return 'Empty pincode';
        if (pincode.length < 5) return 'Too short (minimum 5 characters)';
        if (pincode.length > 10) return 'Too long (maximum 10 characters)';
        
        // Check for incomplete patterns
        if (pincode.length === 5 && !/^\d{5}$/.test(pincode)) {
            return '5-digit codes must be all numeric (US ZIP)';
        }
        if (pincode.length === 6 && !/^\d{6}$/.test(pincode)) {
            return '6-digit codes must be all numeric (Indian PIN)';
        }
        
        return 'Invalid format for known postal code patterns';
    }
    
    /**
     * Handle pincode change with proper error handling
     */
    async handlePincodeChange(pincode, locationInfo, locationDisplay) {
        try {
            // Detect country from pincode pattern
            let countryCode = this.detectCountryFromPincode(pincode);
            
            console.log('🌍 Detected country for pincode:', {
                pincode: pincode,
                detectedCountry: countryCode
            });
            
            const location = await this.getPincodeDetails(pincode, countryCode);
            console.log('✅ Pincode lookup successful:', location);
            this.setLocation(location);
            this.showLocationInfo(locationInfo, locationDisplay, location);
            
        } catch (error) {
            console.error('❌ Initial pincode lookup failed:', error);
            
            // Try with fallback countries
            const fallbackCountries = this.getFallbackCountries(pincode);
            
            if (fallbackCountries.length > 0) {
                console.log('🔄 Trying fallback countries:', fallbackCountries);
                await this.tryPincodeWithFallbacks(pincode, fallbackCountries, locationInfo, locationDisplay);
            } else {
                console.warn('⚠️ No fallback countries available for pincode:', pincode);
                this.showLocationError(locationInfo, locationDisplay, `Invalid postal code: ${pincode}`);
            }
        }
    }
    
    /**
     * Detect country from pincode pattern
     */
    detectCountryFromPincode(pincode) {
        // US ZIP codes: 5 or 9 digits
        if (/^\d{5}$/.test(pincode) || /^\d{9}$/.test(pincode)) {
            return 'US';
        }
        
        // Indian PIN codes: 6 digits
        if (/^\d{6}$/.test(pincode)) {
            return 'IN';
        }
        
        // Canadian postal codes: A1A1A1
        if (/^[A-Z]\d[A-Z]\d[A-Z]\d$/i.test(pincode)) {
            return 'CA';
        }
        
        // UK postal codes: Various formats
        if (/^[A-Z]{1,2}\d[A-Z\d]?\s?\d[A-Z]{2}$/i.test(pincode)) {
            return 'GB';
        }
        
        // Default to India for unknown patterns
        return 'IN';
    }
    
    /**
     * Get fallback countries based on pincode
     */
    getFallbackCountries(pincode) {
        const detected = this.detectCountryFromPincode(pincode);
        const allCountries = ['US', 'IN', 'GB', 'CA', 'AU', 'DE', 'FR'];
        
        // Return other countries excluding the already detected one
        return allCountries.filter(country => country !== detected);
    }
    
    /**
     * Show location error message
     */
    showLocationError(locationInfo, locationDisplay, message) {
        if (locationInfo) {
            locationInfo.innerHTML = `
                <div class="alert alert-warning alert-sm">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    ${message}
                </div>
            `;
        }
    }
    async tryPincodeWithFallbacks(pincode, countries, locationInfo, locationDisplay) {
        for (const countryCode of countries) {
            try {
                console.log(`🔍 Trying pincode ${pincode} with country ${countryCode}`);
                const location = await this.getPincodeDetails(pincode, countryCode);
                console.log(`✅ Success with country ${countryCode}:`, location);
                this.setLocation(location);
                this.showLocationInfo(locationInfo, locationDisplay, location);
                return; // Success, stop trying
            } catch (error) {
                console.log(`❌ Failed with country ${countryCode}:`, error.message);
                continue; // Try next country
            }
        }
        console.warn('⚠️ All fallback countries failed for pincode:', pincode);
    }

    /**
     * Get pincode details with country support
     */
    async getPincodeDetails(pincode, countryCode = 'IN') {
        try {
            const response = await fetch(`/api/pincode-details?pincode=${pincode}&country_code=${countryCode}`);
            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.error || 'Invalid pincode');
            }
        } catch (error) {
            console.error('Error getting pincode details:', error);
            throw error;
        }
    }
    
    /**
     * Set current location manually
     */
    setLocation(locationData) {
        const oldLocation = this.currentLocation;
        // Normalize location data before setting
        this.currentLocation = this.normalizeLocationData(locationData);
        this.trigger('onLocationChanged', { old: oldLocation, new: this.currentLocation });
    }
    
    /**
     * Get current location
     */
    getCurrentLocation() {
        return this.currentLocation;
    }
    
    /**
     * Check if location is available
     */
    hasLocation() {
        return this.currentLocation !== null;
    }
    
    /**
     * Clear current location
     */
    clearLocation() {
        this.currentLocation = null;
    }
    
    /**
     * Auto-fill form fields with location data
     */
    fillForm(formSelector, fieldMapping = {}) {
        if (!this.currentLocation) {
            console.warn('No location data available to fill form');
            return;
        }
        
        const form = document.querySelector(formSelector);
        if (!form) {
            console.warn(`Form not found: ${formSelector}`);
            return;
        }
        
        const defaultMapping = {
            area: 'area',
            city: 'city',
            state: 'state',
            pincode: 'pincode',
            country: 'country',
            address: 'formatted_address',
            latitude: 'latitude',
            longitude: 'longitude'
        };
        
        const mapping = { ...defaultMapping, ...fieldMapping };
        
        Object.keys(mapping).forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"], #${fieldName}`);
            if (field && this.currentLocation[mapping[fieldName]]) {
                field.value = this.currentLocation[mapping[fieldName]];
                
                // Trigger change event
                field.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }
    
    /**
     * Create location picker widget
     */
    createLocationPicker(container, options = {}) {
        const config = {
            showDetectButton: true,
            showSearchBox: true,
            showPincodeInput: true,
            placeholder: 'Search for area, city, or landmark...',
            pincodePlaceholder: 'Enter Pincode',
            detectButtonText: 'Detect My Location',
            ...options
        };
        
        const containerEl = typeof container === 'string' ? document.querySelector(container) : container;
        
        if (!containerEl) {
            console.error('Container not found for location picker');
            return;
        }
        
        const html = `
            <div class="location-picker">
                ${config.showDetectButton ? `
                    <div class="location-detect mb-3">
                        <button type="button" class="btn btn-primary location-detect-btn">
                            <i class="bi bi-geo-alt me-2"></i>
                            ${config.detectButtonText}
                        </button>
                        <div class="location-loading" style="display: none;">
                            <div class="spinner-border spinner-border-sm me-2"></div>
                            Detecting location...
                        </div>
                    </div>
                ` : ''}
                
                <div class="row">
                    ${config.showSearchBox ? `
                        <div class="col-md-8 mb-3">
                            <div class="location-search-container position-relative">
                                <input type="text" class="form-control location-search-input" 
                                       placeholder="${config.placeholder}" autocomplete="off">
                                <div class="location-search-results position-absolute w-100 bg-white border rounded shadow-sm" 
                                     style="display: none; z-index: 1000; max-height: 300px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    ` : ''}
                    
                    ${config.showPincodeInput ? `
                        <div class="col-md-4 mb-3">
                            <input type="text" class="form-control location-pincode-input" 
                                   placeholder="${config.pincodePlaceholder}" maxlength="10" 
                                   title="Enter postal/ZIP code (5-10 characters)">
                        </div>
                    ` : ''}
                </div>
                
                <div class="location-info" style="display: none;">
                    <div class="alert alert-success">
                        <strong>Location Detected:</strong>
                        <span class="location-display"></span>
                    </div>
                </div>
            </div>
        `;
        
        containerEl.innerHTML = html;
        
        // Bind events
        this.bindLocationPickerEvents(containerEl);
        
        return containerEl;
    }
    
    /**
     * Bind events for location picker
     */
    bindLocationPickerEvents(container) {
        const detectBtn = container.querySelector('.location-detect-btn');
        const loading = container.querySelector('.location-loading');
        const searchInput = container.querySelector('.location-search-input');
        const searchResults = container.querySelector('.location-search-results');
        const pincodeInput = container.querySelector('.location-pincode-input');
        const locationInfo = container.querySelector('.location-info');
        const locationDisplay = container.querySelector('.location-display');
        
        let searchTimeout;
        
        // Detect location button
        if (detectBtn) {
            detectBtn.addEventListener('click', () => {
                detectBtn.style.display = 'none';
                loading.style.display = 'block';
                
                this.detectLocation()
                    .then((location) => {
                        this.showLocationInfo(locationInfo, locationDisplay, location);
                    })
                    .catch((error) => {
                        console.error('Location detection failed:', error);
                        alert('Failed to detect location. Please try manual search.');
                    })
                    .finally(() => {
                        detectBtn.style.display = 'block';
                        loading.style.display = 'none';
                    });
            });
        }
        
        // Search input
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.trim();
                
                clearTimeout(searchTimeout);
                
                if (query.length >= 3) {
                    searchTimeout = setTimeout(() => {
                        this.searchLocations(query)
                            .then((results) => {
                                this.showSearchResults(searchResults, results, locationInfo, locationDisplay);
                            })
                            .catch((error) => {
                                console.error('Search failed:', error);
                                searchResults.style.display = 'none';
                            });
                    }, 500);
                } else {
                    searchResults.style.display = 'none';
                }
            });
            
            // Hide results when clicking outside
            document.addEventListener('click', (e) => {
                if (!container.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });
        }
        
        // Pincode input
        if (pincodeInput) {
            // Clear any existing timeout when creating new listener
            if (this.pincodeTimeout) {
                clearTimeout(this.pincodeTimeout);
            }
            
            pincodeInput.addEventListener('input', (e) => {
                // Remove non-alphanumeric for international support
                e.target.value = e.target.value.replace(/[^0-9A-Za-z]/g, '');
                
                const pincode = e.target.value.trim();
                console.log('📮 Pincode input:', {
                    value: pincode,
                    length: pincode.length,
                    shouldTrigger: this.isValidPincodeForLookup(pincode)
                });
                
                // Clear previous timeout
                if (this.pincodeTimeout) {
                    clearTimeout(this.pincodeTimeout);
                }
                
                // Validate pincode before making API call
                if (this.isValidPincodeForLookup(pincode)) {
                    console.log('⏱️ Scheduling pincode lookup with 800ms delay...');
                    
                    // Add debouncing to prevent excessive API calls
                    this.pincodeTimeout = setTimeout(() => {
                        console.log('🚀 Executing pincode lookup for:', pincode);
                        this.handlePincodeChange(pincode, locationInfo, locationDisplay);
                    }, 800); // 800ms delay to reduce API calls while typing
                } else {
                    console.log('❌ Pincode not valid for lookup:', {
                        value: pincode,
                        length: pincode.length,
                        reason: this.getPincodeValidationReason(pincode)
                    });
                }
            });
        }
    }
    
    /**
     * Show search results
     */
    showSearchResults(container, results, locationInfo, locationDisplay) {
        if (!results || results.length === 0) {
            container.style.display = 'none';
            return;
        }
        
        container.innerHTML = results.map(result => `
            <div class="location-search-result p-3 border-bottom cursor-pointer" data-result='${JSON.stringify(result)}'>
                <div class="fw-bold">${result.display_name}</div>
                <small class="text-muted">
                    ${result.city ? result.city + ', ' : ''}
                    ${result.state ? result.state + ', ' : ''}
                    ${result.country}
                    ${result.pincode ? ' - ' + result.pincode : ''}
                </small>
            </div>
        `).join('');
        
        container.style.display = 'block';
        
        // Bind click events
        container.querySelectorAll('.location-search-result').forEach(item => {
            item.addEventListener('click', () => {
                const result = JSON.parse(item.dataset.result);
                
                if (result.latitude && result.longitude) {
                    this.getLocationDetails(result.latitude, result.longitude)
                        .then((location) => {
                            this.setLocation(location);
                            this.showLocationInfo(locationInfo, locationDisplay, location);
                        })
                        .catch((error) => {
                            console.error('Failed to get detailed location:', error);
                            this.setLocation(result);
                            this.showLocationInfo(locationInfo, locationDisplay, result);
                        });
                } else {
                    this.setLocation(result);
                    this.showLocationInfo(locationInfo, locationDisplay, result);
                }
                
                container.style.display = 'none';
            });
        });
    }
    
    /**
     * Show location information
     */
    showLocationInfo(container, display, location) {
        if (display) {
            display.textContent = location.formatted_address || 
                `${location.area || ''} ${location.city || ''} ${location.state || ''} ${location.pincode || ''}`.trim();
        }
        
        if (container) {
            container.style.display = 'block';
        }
    }
}

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = GeolocationManager;
}

// Global instance for easy access
window.GeolocationManager = GeolocationManager;