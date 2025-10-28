<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Location Integration Example</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .cursor-pointer { cursor: pointer; }
        .location-search-result:hover { background-color: #f8f9fa; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-truck me-2"></i>
                            Delivery Address Form
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Location Picker Widget -->
                        <div class="mb-4">
                            <h5 class="mb-3">Select Your Location</h5>
                            <div id="locationPicker"></div>
                        </div>
                        
                        <hr>
                        
                        <!-- Address Form -->
                        <form id="addressForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="area" class="form-label">Area/Locality *</label>
                                    <input type="text" class="form-control" id="area" name="area" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City *</label>
                                    <input type="text" class="form-control" id="city" name="city" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="state" class="form-label">State *</label>
                                    <input type="text" class="form-control" id="state" name="state" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="country" class="form-label">Country *</label>
                                    <input type="text" class="form-control" id="country" name="country" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="pincode" class="form-label">Pincode *</label>
                                    <input type="text" class="form-control" id="pincode" name="pincode" required 
                                           maxlength="10" placeholder="Enter postal/ZIP code" 
                                           title="Enter postal/ZIP code (5-10 characters)">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="address" class="form-label">Complete Address *</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" required 
                                              placeholder="House/Flat No., Building Name, Street Name"></textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="landmark" class="form-label">Landmark (Optional)</label>
                                    <input type="text" class="form-control" id="landmark" name="landmark" 
                                           placeholder="Near by landmark for easy identification">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Address Type</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="address_type" id="home" value="home" checked>
                                            <label class="form-check-label" for="home">
                                                <i class="bi bi-house me-1"></i> Home
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="address_type" id="office" value="office">
                                            <label class="form-check-label" for="office">
                                                <i class="bi bi-building me-1"></i> Office
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="address_type" id="other" value="other">
                                            <label class="form-check-label" for="other">
                                                <i class="bi bi-geo-alt me-1"></i> Other
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>
                                    Save Address
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="clearForm">
                                    <i class="bi bi-arrow-clockwise me-2"></i>
                                    Clear Form
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Current Location Display -->
                <div class="card mt-4" id="currentLocationCard" style="display: none;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-geo-alt-fill me-2"></i>
                            Current Location
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="currentLocationDisplay"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/geolocation.js') }}"></script>
    <script>
        // Initialize Geolocation Manager
        const geoManager = new GeolocationManager({
            autoDetect: false, // Don't auto-detect on page load
            fallbackToIP: true
        });
        
        // Create location picker widget
        const locationPicker = geoManager.createLocationPicker('#locationPicker', {
            showDetectButton: true,
            showSearchBox: true,
            showPincodeInput: true,
            detectButtonText: 'Use My Current Location'
        });
        
        // Listen for location detection events
        geoManager.on('onLocationDetected', function(location) {
            console.log('Location detected:', location);
            
            // Auto-fill the form
            fillAddressForm(location);
            
            // Show current location
            showCurrentLocation(location);
            
            // Show success message
            showMessage('Location detected successfully!', 'success');
        });
        
        geoManager.on('onLocationError', function(error) {
            console.error('Location error:', error);
            showMessage('Failed to detect location. Please enter manually.', 'error');
        });
        
        geoManager.on('onLocationChanged', function(data) {
            console.log('Location changed:', data);
            fillAddressForm(data.new);
            showCurrentLocation(data.new);
        });
        
        // Fill address form with location data
        function fillAddressForm(location) {
            document.getElementById('area').value = location.area || '';
            document.getElementById('city').value = location.city || '';
            document.getElementById('state').value = location.state || '';
            document.getElementById('country').value = location.country || '';
            document.getElementById('pincode').value = location.pincode || '';
            
            // If address field is empty, use formatted address
            const addressField = document.getElementById('address');
            if (!addressField.value.trim() && location.formatted_address) {
                addressField.value = location.formatted_address;
            }
        }
        
        // Show current location details
        function showCurrentLocation(location) {
            const card = document.getElementById('currentLocationCard');
            const display = document.getElementById('currentLocationDisplay');
            
            const html = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>Area:</strong> ${location.area || 'N/A'}<br>
                        <strong>City:</strong> ${location.city || 'N/A'}<br>
                        <strong>State:</strong> ${location.state || 'N/A'}
                    </div>
                    <div class="col-md-6">
                        <strong>Pincode:</strong> ${location.pincode || 'N/A'}<br>
                        <strong>Country:</strong> ${location.country || 'N/A'}
                        ${location.latitude && location.longitude ? `<br><strong>Coordinates:</strong> ${location.latitude.toFixed(4)}, ${location.longitude.toFixed(4)}` : ''}
                    </div>
                    <div class="col-12 mt-2">
                        <strong>Full Address:</strong><br>
                        <span class="text-muted">${location.formatted_address || 'N/A'}</span>
                    </div>
                </div>
            `;
            
            display.innerHTML = html;
            card.style.display = 'block';
        }
        
        // Show message
        function showMessage(message, type) {
            // Remove existing messages
            const existingMessages = document.querySelectorAll('.alert-message');
            existingMessages.forEach(msg => msg.remove());
            
            const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
            const icon = type === 'error' ? 'bi-exclamation-triangle' : 'bi-check-circle';
            
            const messageHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show alert-message mt-3" role="alert">
                    <i class="bi ${icon} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            document.querySelector('.container').insertAdjacentHTML('beforeend', messageHtml);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                const alert = document.querySelector('.alert-message');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }
        
        // Form submission
        document.getElementById('addressForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            console.log('Form submitted with data:', data);
            
            // Add current location data if available
            const currentLocation = geoManager.getCurrentLocation();
            if (currentLocation) {
                data.latitude = currentLocation.latitude;
                data.longitude = currentLocation.longitude;
            }
            
            // Here you would typically send the data to your server
            showMessage('Address saved successfully!', 'success');
        });
        
        // Clear form
        document.getElementById('clearForm').addEventListener('click', function() {
            document.getElementById('addressForm').reset();
            document.getElementById('currentLocationCard').style.display = 'none';
            geoManager.clearLocation();
            showMessage('Form cleared', 'success');
        });
        
        // Auto-detect location on page load (optional)
        // Uncomment the line below to enable auto-detection
        // geoManager.detectLocation();
    </script>
</body>
</html>