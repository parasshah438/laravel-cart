<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Location Demo - BigBasket Style</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .location-card {
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .location-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }
        .location-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin: 0 auto 20px;
        }
        .btn-location {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-location:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .location-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        .search-container {
            position: relative;
        }
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        .search-result-item {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }
        .search-result-item:hover {
            background-color: #f8f9fa;
        }
        .search-result-item:last-child {
            border-bottom: none;
        }
        .loading-spinner {
            display: none;
        }
        .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-top: 10px;
        }
        .success-message {
            color: #28a745;
            font-size: 14px;
            margin-top: 10px;
        }
        .location-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <h1 class="display-4 fw-bold text-primary mb-3">
                        <i class="bi bi-geo-alt-fill"></i> Location Demo
                    </h1>
                    <p class="lead text-muted">Experience BigBasket-style location detection and auto-fill functionality</p>
                </div>

                <!-- Location Detection Card -->
                <div class="card location-card mb-4">
                    <div class="card-body p-4">
                        <div class="location-icon">
                            <i class="bi bi-crosshair"></i>
                        </div>
                        <h3 class="text-center mb-3">Detect Your Location</h3>
                        <p class="text-center text-muted mb-4">
                            Allow location access to automatically fill your area and pincode details
                        </p>
                        <div class="text-center">
                            <button class="btn btn-location" id="detectLocationBtn">
                                <i class="bi bi-geo-alt me-2"></i>
                                Detect My Location
                            </button>
                            <div class="loading-spinner mt-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Getting your location...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manual Location Entry -->
                <div class="card location-card mb-4">
                    <div class="card-body p-4">
                        <h4 class="mb-3">
                            <i class="bi bi-search me-2"></i>
                            Or Search Your Location
                        </h4>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <div class="search-container">
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="locationSearch" 
                                           placeholder="Search for area, city, or landmark..."
                                           autocomplete="off">
                                    <div class="search-results" id="searchResults"></div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="pincodeInput" 
                                       placeholder="Enter Pincode"
                                       maxlength="6"
                                       pattern="[0-9]{6}">
                            </div>
                        </div>
                        
                        <button class="btn btn-outline-primary" id="searchLocationBtn">
                            <i class="bi bi-search me-2"></i>
                            Search Location
                        </button>
                    </div>
                </div>

                <!-- Location Information Display -->
                <div class="card location-card" id="locationInfoCard" style="display: none;">
                    <div class="card-body p-4">
                        <h4 class="mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Your Location Details
                        </h4>
                        <div id="locationInfo" class="location-info">
                            <!-- Location details will be populated here -->
                        </div>
                        
                        <!-- Sample Form with Auto-filled Data -->
                        <div class="mt-4">
                            <h5 class="mb-3">Sample Delivery Form</h5>
                            <form id="deliveryForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="area" class="form-label">Area/Locality</label>
                                        <input type="text" class="form-control" id="area" name="area" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="state" class="form-label">State</label>
                                        <input type="text" class="form-control" id="state" name="state" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="pincode" class="form-label">Pincode</label>
                                        <input type="text" class="form-control" id="pincode" name="pincode" readonly>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="fullAddress" class="form-label">Full Address</label>
                                        <textarea class="form-control" id="fullAddress" name="fullAddress" rows="3" readonly></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Error/Success Messages -->
                <div id="messageContainer"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // CSRF Token setup
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // DOM Elements
        const detectLocationBtn = document.getElementById('detectLocationBtn');
        const locationSearch = document.getElementById('locationSearch');
        const pincodeInput = document.getElementById('pincodeInput');
        const searchLocationBtn = document.getElementById('searchLocationBtn');
        const searchResults = document.getElementById('searchResults');
        const locationInfoCard = document.getElementById('locationInfoCard');
        const locationInfo = document.getElementById('locationInfo');
        const messageContainer = document.getElementById('messageContainer');
        const loadingSpinner = document.querySelector('.loading-spinner');
        
        // Form fields
        const areaField = document.getElementById('area');
        const cityField = document.getElementById('city');
        const stateField = document.getElementById('state');
        const pincodeField = document.getElementById('pincode');
        const fullAddressField = document.getElementById('fullAddress');
        
        let searchTimeout;
        
        // Detect location button click
        detectLocationBtn.addEventListener('click', function() {
            if (navigator.geolocation) {
                showLoading(true);
                showMessage('', '');
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;
                        
                        getLocationDetails(latitude, longitude);
                    },
                    function(error) {
                        showLoading(false);
                        let errorMessage = 'Unable to get your location. ';
                        
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage += 'Please allow location access and try again.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage += 'Location information is unavailable.';
                                break;
                            case error.TIMEOUT:
                                errorMessage += 'Location request timed out.';
                                break;
                            default:
                                errorMessage += 'An unknown error occurred.';
                                break;
                        }
                        
                        showMessage(errorMessage, 'error');
                        
                        // Fallback to IP-based location
                        getLocationFromIP();
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 300000
                    }
                );
            } else {
                showMessage('Geolocation is not supported by this browser. Trying IP-based location...', 'error');
                getLocationFromIP();
            }
        });
        
        // Location search functionality
        locationSearch.addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length >= 3) {
                searchTimeout = setTimeout(() => {
                    searchLocations(query);
                }, 500);
            } else {
                hideSearchResults();
            }
        });
        
        // Pincode input functionality
        pincodeInput.addEventListener('input', function() {
            const pincode = this.value.trim();
            
            // Only allow numbers
            this.value = this.value.replace(/[^0-9]/g, '');
            
            if (pincode.length === 6) {
                getPincodeDetails(pincode);
            }
        });
        
        // Search location button
        searchLocationBtn.addEventListener('click', function() {
            const query = locationSearch.value.trim();
            const pincode = pincodeInput.value.trim();
            
            if (query.length >= 3) {
                searchLocations(query);
            } else if (pincode.length === 6) {
                getPincodeDetails(pincode);
            } else {
                showMessage('Please enter at least 3 characters for location search or a valid 6-digit pincode.', 'error');
            }
        });
        
        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-container')) {
                hideSearchResults();
            }
        });
        
        // Get location details from coordinates
        function getLocationDetails(latitude, longitude) {
            fetch('/api/location-details', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    latitude: latitude,
                    longitude: longitude
                })
            })
            .then(response => response.json())
            .then(data => {
                showLoading(false);
                
                if (data.success) {
                    displayLocationInfo(data.data);
                    fillForm(data.data);
                    showMessage('Location detected successfully!', 'success');
                } else {
                    showMessage(data.error || 'Failed to get location details', 'error');
                }
            })
            .catch(error => {
                showLoading(false);
                console.error('Error:', error);
                showMessage('Failed to get location details', 'error');
            });
        }
        
        // Get location from IP address
        function getLocationFromIP() {
            fetch('/api/location-from-ip')
            .then(response => response.json())
            .then(data => {
                showLoading(false);
                
                if (data.success) {
                    displayLocationInfo(data.data);
                    fillForm(data.data);
                    showMessage('Location detected from IP address!', 'success');
                } else {
                    showMessage(data.error || 'Failed to get location from IP', 'error');
                }
            })
            .catch(error => {
                showLoading(false);
                console.error('Error:', error);
                showMessage('Failed to get location from IP', 'error');
            });
        }
        
        // Search locations
        function searchLocations(query) {
            fetch(`/api/search-locations?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    displaySearchResults(data.data);
                } else {
                    hideSearchResults();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                hideSearchResults();
            });
        }
        
        // Get pincode details
        function getPincodeDetails(pincode) {
            fetch(`/api/pincode-details?pincode=${pincode}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayLocationInfo(data.data);
                    fillForm(data.data);
                    showMessage('Pincode details loaded successfully!', 'success');
                } else {
                    showMessage(data.error || 'Invalid pincode', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Failed to get pincode details', 'error');
            });
        }
        
        // Display search results
        function displaySearchResults(results) {
            searchResults.innerHTML = '';
            
            results.forEach(result => {
                const item = document.createElement('div');
                item.className = 'search-result-item';
                item.innerHTML = `
                    <div class="fw-bold">${result.display_name}</div>
                    <small class="text-muted">
                        ${result.city ? result.city + ', ' : ''}
                        ${result.state ? result.state + ', ' : ''}
                        ${result.country}
                        ${result.pincode ? ' - ' + result.pincode : ''}
                    </small>
                `;
                
                item.addEventListener('click', function() {
                    locationSearch.value = result.display_name;
                    hideSearchResults();
                    
                    // Get detailed location info
                    if (result.latitude && result.longitude) {
                        getLocationDetails(result.latitude, result.longitude);
                    } else {
                        displayLocationInfo(result);
                        fillForm(result);
                    }
                });
                
                searchResults.appendChild(item);
            });
            
            searchResults.style.display = 'block';
        }
        
        // Hide search results
        function hideSearchResults() {
            searchResults.style.display = 'none';
        }
        
        // Display location information
        function displayLocationInfo(locationData) {
            const info = `
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong><i class="bi bi-geo-alt me-2"></i>Area:</strong>
                        <span class="location-badge ms-2">${locationData.area || 'N/A'}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="bi bi-building me-2"></i>City:</strong>
                        <span class="location-badge ms-2">${locationData.city || 'N/A'}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="bi bi-map me-2"></i>State:</strong>
                        <span class="location-badge ms-2">${locationData.state || 'N/A'}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="bi bi-mailbox me-2"></i>Pincode:</strong>
                        <span class="location-badge ms-2">${locationData.pincode || 'N/A'}</span>
                    </div>
                    <div class="col-12 mb-3">
                        <strong><i class="bi bi-house me-2"></i>Full Address:</strong>
                        <p class="mt-2 p-3 bg-white rounded border">${locationData.formatted_address || 'N/A'}</p>
                    </div>
                    ${locationData.latitude && locationData.longitude ? `
                    <div class="col-12">
                        <strong><i class="bi bi-crosshair me-2"></i>Coordinates:</strong>
                        <span class="text-muted ms-2">${locationData.latitude.toFixed(6)}, ${locationData.longitude.toFixed(6)}</span>
                    </div>
                    ` : ''}
                </div>
            `;
            
            locationInfo.innerHTML = info;
            locationInfoCard.style.display = 'block';
            
            // Scroll to the location info card
            locationInfoCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        
        // Fill form with location data
        function fillForm(locationData) {
            areaField.value = locationData.area || '';
            cityField.value = locationData.city || '';
            stateField.value = locationData.state || '';
            pincodeField.value = locationData.pincode || '';
            fullAddressField.value = locationData.formatted_address || '';
        }
        
        // Show loading state
        function showLoading(show) {
            if (show) {
                detectLocationBtn.style.display = 'none';
                loadingSpinner.style.display = 'block';
            } else {
                detectLocationBtn.style.display = 'inline-block';
                loadingSpinner.style.display = 'none';
            }
        }
        
        // Show message
        function showMessage(message, type) {
            if (!message) {
                messageContainer.innerHTML = '';
                return;
            }
            
            const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
            const icon = type === 'error' ? 'bi-exclamation-triangle' : 'bi-check-circle';
            
            messageContainer.innerHTML = `
                <div class="alert ${alertClass} alert-dismissible fade show mt-3" role="alert">
                    <i class="bi ${icon} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }
        
        // Auto-detect location on page load (optional)
        window.addEventListener('load', function() {
            // Uncomment the line below to auto-detect location on page load
            // detectLocationBtn.click();
        });
    </script>
</body>
</html>