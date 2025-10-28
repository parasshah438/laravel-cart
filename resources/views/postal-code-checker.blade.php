<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Postal Code Checker</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .result-card {
            background: rgba(255, 255, 255, 0.9);
            border-left: 4px solid #28a745;
            animation: slideInUp 0.5s ease-out;
        }
        
        .error-card {
            background: rgba(255, 255, 255, 0.9);
            border-left: 4px solid #dc3545;
            animation: slideInUp 0.5s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .loading-spinner {
            display: none;
        }
        
        .place-item {
            background: rgba(40, 167, 69, 0.1);
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .place-item:hover {
            background: rgba(40, 167, 69, 0.2);
            transform: translateY(-2px);
        }
        
        .country-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <!-- Header -->
                <div class="text-center mb-5">
                    <h1 class="display-4 text-white mb-3">
                        <i class="bi bi-geo-alt-fill"></i>
                        Postal Code Checker
                    </h1>
                    <p class="lead text-white-50">Validate postal codes from 60+ countries worldwide</p>
                </div>
                
                <!-- Main Card -->
                <div class="card rounded-4">
                    <div class="card-body p-4">
                        <form id="postalCodeForm">
                            <div class="row g-3">
                                <!-- Country Selection -->
                                <div class="col-12">
                                    <label for="country" class="form-label fw-bold">
                                        <i class="bi bi-flag-fill text-primary"></i>
                                        Country
                                    </label>
                                    <select class="form-select form-select-lg country-select" id="country" name="country" required>
                                        <option value="">Select a country...</option>
                                        <option value="US">🇺🇸 United States</option>
                                        <option value="GB">🇬🇧 United Kingdom</option>
                                        <option value="CA">🇨🇦 Canada</option>
                                        <option value="DE">🇩🇪 Germany</option>
                                        <option value="FR">🇫🇷 France</option>
                                        <option value="IT">🇮🇹 Italy</option>
                                        <option value="ES">🇪🇸 Spain</option>
                                        <option value="NL">🇳🇱 Netherlands</option>
                                        <option value="BE">🇧🇪 Belgium</option>
                                        <option value="AT">🇦🇹 Austria</option>
                                        <option value="CH">🇨🇭 Switzerland</option>
                                        <option value="DK">🇩🇰 Denmark</option>
                                        <option value="SE">🇸🇪 Sweden</option>
                                        <option value="NO">🇳🇴 Norway</option>
                                        <option value="FI">🇫🇮 Finland</option>
                                        <option value="PL">🇵🇱 Poland</option>
                                        <option value="CZ">🇨🇿 Czech Republic</option>
                                        <option value="SK">🇸🇰 Slovakia</option>
                                        <option value="HU">🇭🇺 Hungary</option>
                                        <option value="PT">🇵🇹 Portugal</option>
                                        <option value="GR">🇬🇷 Greece</option>
                                        <option value="MX">🇲🇽 Mexico</option>
                                        <option value="BR">🇧🇷 Brazil</option>
                                        <option value="AR">🇦🇷 Argentina</option>
                                        <option value="AU">🇦🇺 Australia</option>
                                        <option value="NZ">🇳🇿 New Zealand</option>
                                        <option value="JP">🇯🇵 Japan</option>
                                        <option value="KR">🇰🇷 South Korea</option>
                                        <option value="IN">🇮🇳 India</option>
                                        <option value="ZA">🇿🇦 South Africa</option>
                                        <option value="RU">🇷🇺 Russia</option>
                                        <option value="TR">🇹🇷 Turkey</option>
                                    </select>
                                </div>
                                
                                <!-- Postal Code Input -->
                                <div class="col-12">
                                    <label for="postal_code" class="form-label fw-bold">
                                        <i class="bi bi-mailbox text-warning"></i>
                                        Postal Code
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="postal_code" 
                                           name="postal_code" 
                                           placeholder="Enter postal code (e.g., 90210, SW1A 1AA, 10115)"
                                           required>
                                    <div class="form-text">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i>
                                            Examples: US (90210), UK (SW1A 1AA), Germany (10115)
                                        </small>
                                    </div>
                                </div>
                                
                                <!-- Submit Button -->
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg w-100 mt-3">
                                        <span class="button-text">
                                            <i class="bi bi-search"></i>
                                            Validate Postal Code
                                        </span>
                                        <span class="loading-spinner">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Validating...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Results Section -->
                <div id="resultsSection" class="mt-4" style="display: none;">
                    <!-- Success Result -->
                    <div id="successResult" class="card result-card" style="display: none;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-check-circle-fill text-success fs-3 me-3"></i>
                                <div>
                                    <h5 class="card-title mb-1 text-success">Valid Postal Code!</h5>
                                    <p class="card-text text-muted mb-0">Postal code found in our database</p>
                                </div>
                            </div>
                            
                            <div id="postalCodeInfo"></div>
                            
                            <div id="placesInfo" class="mt-3"></div>
                        </div>
                    </div>
                    
                    <!-- Error Result -->
                    <div id="errorResult" class="card error-card" style="display: none;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-x-circle-fill text-danger fs-3 me-3"></i>
                                <div>
                                    <h5 class="card-title mb-1 text-danger">Invalid Postal Code</h5>
                                    <p class="card-text mb-0" id="errorMessage">The postal code you entered is not valid or not supported.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- API Info -->
                <div class="mt-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-info-circle text-info"></i>
                                API Information
                            </h6>
                            <p class="card-text small text-muted mb-0">
                                Powered by <strong>Zippopotam.us</strong> - Free postal code API covering 60+ countries worldwide.
                                No API key required.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Set up CSRF token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        document.getElementById('postalCodeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const country = document.getElementById('country').value;
            const postalCode = document.getElementById('postal_code').value;
            const button = document.querySelector('button[type="submit"]');
            const buttonText = button.querySelector('.button-text');
            const loadingSpinner = button.querySelector('.loading-spinner');
            const resultsSection = document.getElementById('resultsSection');
            const successResult = document.getElementById('successResult');
            const errorResult = document.getElementById('errorResult');
            
            // Validation
            if (!country || !postalCode) {
                alert('Please select a country and enter a postal code.');
                return;
            }
            
            // Show loading state
            button.disabled = true;
            buttonText.style.display = 'none';
            loadingSpinner.style.display = 'inline-block';
            
            // Hide previous results
            resultsSection.style.display = 'none';
            successResult.style.display = 'none';
            errorResult.style.display = 'none';
            
            // Make AJAX request
            fetch('/api/validate-postal-code', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    country: country,
                    postal_code: postalCode
                })
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading state
                button.disabled = false;
                buttonText.style.display = 'inline-block';
                loadingSpinner.style.display = 'none';
                
                // Show results section
                resultsSection.style.display = 'block';
                
                if (data.success && data.valid) {
                    // Show success result
                    successResult.style.display = 'block';
                    
                    // Display postal code info
                    const infoHtml = `
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <strong>Country:</strong> ${data.data.country}
                            </div>
                            <div class="col-sm-6">
                                <strong>Postal Code:</strong> ${data.data.post_code}
                            </div>
                        </div>
                    `;
                    document.getElementById('postalCodeInfo').innerHTML = infoHtml;
                    
                    // Display places
                    if (data.data.places && data.data.places.length > 0) {
                        let placesHtml = '<h6 class="mt-3 mb-2"><i class="bi bi-geo-alt text-primary"></i> Locations:</h6>';
                        data.data.places.forEach(place => {
                            placesHtml += `
                                <div class="place-item p-3 mb-2">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <strong>${place['place name']}</strong>
                                        </div>
                                        <div class="col-md-6 text-md-end">
                                            <small class="text-muted">${place.state} ${place['state abbreviation'] ? '(' + place['state abbreviation'] + ')' : ''}</small>
                                        </div>
                                        ${place.latitude && place.longitude ? `
                                        <div class="col-12">
                                            <small class="text-muted">
                                                <i class="bi bi-geo"></i>
                                                ${place.latitude}, ${place.longitude}
                                            </small>
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            `;
                        });
                        document.getElementById('placesInfo').innerHTML = placesHtml;
                    }
                } else {
                    // Show error result
                    errorResult.style.display = 'block';
                    document.getElementById('errorMessage').textContent = data.message;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Hide loading state
                button.disabled = false;
                buttonText.style.display = 'inline-block';
                loadingSpinner.style.display = 'none';
                
                // Show error result
                resultsSection.style.display = 'block';
                errorResult.style.display = 'block';
                document.getElementById('errorMessage').textContent = 'An error occurred while validating the postal code. Please try again.';
            });
        });
        
        // Auto-focus postal code input when country is selected
        document.getElementById('country').addEventListener('change', function() {
            if (this.value) {
                document.getElementById('postal_code').focus();
            }
        });
        
        // Clear results when input changes
        document.getElementById('postal_code').addEventListener('input', function() {
            document.getElementById('resultsSection').style.display = 'none';
        });
        
        document.getElementById('country').addEventListener('change', function() {
            document.getElementById('resultsSection').style.display = 'none';
        });
    </script>
</body>
</html>