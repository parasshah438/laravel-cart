<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Optimization Test - Laravel Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">
                            <i class="fas fa-image me-2"></i>
                            Image Optimization Test Results
                        </h2>
                    </div>
                    <div class="card-body">
                        
                        <!-- Spatie Image Optimizer Status -->
                        <div class="mb-4">
                            <h4>
                                <i class="fas fa-compress-alt me-2"></i>
                                Spatie Image Optimizer Status
                            </h4>
                            <div class="alert {{ $optimizer_exists ? 'alert-success' : 'alert-danger' }}">
                                @if($optimizer_exists)
                                    <i class="fas fa-check-circle me-2"></i>
                                    Spatie Image Optimizer is installed and available!
                                @else
                                    <i class="fas fa-times-circle me-2"></i>
                                    Spatie Image Optimizer is NOT installed!
                                @endif
                            </div>
                        </div>

                        <!-- Available Optimizers -->
                        @if($optimizer_exists && !isset($available_optimizers['error']))
                        <div class="mb-4">
                            <h5><i class="fas fa-cogs me-2"></i>Available Optimizers</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Optimizer Class</th>
                                            <th>Can Handle Images</th>
                                            <th>Binary Name</th>
                                            <th>Binary Available</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($available_optimizers as $optimizer)
                                        <tr>
                                            <td><code>{{ $optimizer['class'] }}</code></td>
                                            <td>
                                                @if($optimizer['can_handle'] === 'Yes')
                                                    <span class="badge bg-success">{{ $optimizer['can_handle'] }}</span>
                                                @elseif($optimizer['can_handle'] === 'No')
                                                    <span class="badge bg-warning">{{ $optimizer['can_handle'] }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $optimizer['can_handle'] }}</span>
                                                @endif
                                            </td>
                                            <td><code>{{ $optimizer['binary_available'] }}</code></td>
                                            <td>
                                                @if(isset($optimizer['binary_exists']) && $optimizer['binary_exists'])
                                                    <span class="badge bg-success"><i class="fas fa-check"></i> Found</span>
                                                @else
                                                    <span class="badge bg-danger"><i class="fas fa-times"></i> Missing</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($optimizer['status']))
                                                    @if($optimizer['status'] === 'Working')
                                                        <span class="badge bg-success">{{ $optimizer['status'] }}</span>
                                                    @else
                                                        <span class="badge bg-warning">{{ $optimizer['status'] }}</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary">Unknown</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @elseif(isset($available_optimizers['error']))
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error loading optimizers: {{ $available_optimizers['error'] }}
                        </div>
                        @endif

                        <!-- Optimizer Information -->
                        @if($optimizer_exists && !isset($available_optimizers['error']))
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>About Image Optimization</h6>
                            <p><strong>Spatie Image Optimizer</strong> works even without external binaries installed, but having the optimization binaries (like jpegoptim, pngquant, etc.) installed on your server will provide better compression results.</p>
                            <p><strong>Missing binaries are normal</strong> and the package will still optimize images using fallback methods.</p>
                        </div>
                        @endif

                        <!-- ImageOptimizer Helper Status -->
                        <div class="mb-4">
                            <h4>
                                <i class="fas fa-tools me-2"></i>
                                ImageOptimizer Helper Status
                            </h4>
                            <div class="alert {{ $helper_exists ? 'alert-success' : 'alert-danger' }}">
                                @if($helper_exists)
                                    <i class="fas fa-check-circle me-2"></i>
                                    Custom ImageOptimizer helper is available!
                                @else
                                    <i class="fas fa-times-circle me-2"></i>
                                    Custom ImageOptimizer helper is NOT available!
                                @endif
                            </div>
                        </div>

                        <!-- Intervention Image Status -->
                        <div class="mb-4">
                            <h4>
                                <i class="fas fa-image me-2"></i>
                                Intervention Image Status
                            </h4>
                            <div class="alert {{ $intervention_image_version !== 'Not Available' ? 'alert-success' : 'alert-danger' }}">
                                @if($intervention_image_version !== 'Not Available')
                                    <i class="fas fa-check-circle me-2"></i>
                                    Intervention Image is available! ({{ $intervention_image_version }})
                                @else
                                    <i class="fas fa-times-circle me-2"></i>
                                    Intervention Image is NOT available!
                                @endif
                            </div>
                        </div>

                        <!-- Directory Status -->
                        <div class="mb-4">
                            <h4>
                                <i class="fas fa-folder me-2"></i>
                                Storage Directory Status
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Directory</th>
                                            <th>Path</th>
                                            <th>Exists</th>
                                            <th>Writable</th>
                                            <th>Files Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($directory_status as $name => $status)
                                        <tr>
                                            <td><strong>{{ ucfirst($name) }}</strong></td>
                                            <td><code>{{ $status['path'] }}</code></td>
                                            <td>
                                                @if($status['exists'])
                                                    <span class="badge bg-success">Yes</span>
                                                @else
                                                    <span class="badge bg-danger">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($status['writable'])
                                                    <span class="badge bg-success">Yes</span>
                                                @else
                                                    <span class="badge bg-danger">No</span>
                                                @endif
                                            </td>
                                            <td><span class="badge bg-info">{{ $status['files_count'] }}</span></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- PHP Upload Settings -->
                        <div class="mb-4">
                            <h4>
                                <i class="fas fa-server me-2"></i>
                                PHP Upload Configuration
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Setting</th>
                                            <th>Value</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($upload_settings as $setting => $value)
                                        <tr>
                                            <td><strong>{{ str_replace('_', ' ', ucfirst($setting)) }}</strong></td>
                                            <td><code>{{ $value }}</code></td>
                                            <td>
                                                @if($setting === 'file_uploads')
                                                    @if($value === 'Enabled')
                                                        <span class="badge bg-success">OK</span>
                                                    @else
                                                        <span class="badge bg-danger">Disabled</span>
                                                    @endif
                                                @elseif(in_array($setting, ['upload_max_filesize', 'post_max_size']))
                                                    @php
                                                        $sizeInMB = preg_replace('/[^0-9]/', '', $value);
                                                        $sizeInMB = (int)$sizeInMB;
                                                        if(stripos($value, 'G') !== false) $sizeInMB *= 1024;
                                                    @endphp
                                                    @if($sizeInMB >= 10)
                                                        <span class="badge bg-success">OK</span>
                                                    @else
                                                        <span class="badge bg-warning">Low</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-info">Info</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Test Image Upload -->
                        <div class="mb-4">
                            <h4>
                                <i class="fas fa-upload me-2"></i>
                                Test Image Upload & Optimization
                            </h4>
                            <form id="testUploadForm" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="testImage" class="form-label">Select an image to test optimization:</label>
                                    <input type="file" class="form-control" id="testImage" name="test_image" accept="image/*" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-magic me-2"></i>
                                    Test Optimization
                                </button>
                            </form>
                            <div id="uploadResult" class="mt-3"></div>
                        </div>

                        <!-- Implementation Status -->
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle me-2"></i>Implementation Status</h5>
                            <ul class="mb-0">
                                <li><strong>ReviewController:</strong> ✅ Updated to use optimized image uploads</li>
                                <li><strong>CartController:</strong> ✅ Updated for customization image uploads</li>
                                <li><strong>ProductController:</strong> ✅ Added admin methods with optimization</li>
                                <li><strong>CategoryController:</strong> ✅ Created with optimization support</li>
                                <li><strong>Admin Routes:</strong> ✅ Added for product and category management</li>
                            </ul>
                        </div>

                        <!-- Navigation -->
                        <div class="mt-4">
                            <a href="{{ url('/') }}" class="btn btn-secondary">
                                <i class="fas fa-home me-2"></i>
                                Back to Home
                            </a>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Admin Dashboard
                            </a>
                            <a href="{{ url('/test-intervention-image') }}" class="btn btn-info">
                                <i class="fas fa-image me-2"></i>
                                Test Intervention Image
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('testUploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            const fileInput = document.getElementById('testImage');
            const resultDiv = document.getElementById('uploadResult');
            
            if (!fileInput.files[0]) {
                resultDiv.innerHTML = '<div class="alert alert-warning">Please select an image file.</div>';
                return;
            }
            
            const file = fileInput.files[0];
            const maxSize = 10 * 1024 * 1024; // 10MB
            
            if (file.size > maxSize) {
                resultDiv.innerHTML = '<div class="alert alert-danger">File size too large. Maximum allowed size is 10MB.</div>';
                return;
            }
            
            formData.append('test_image', file);
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            
            resultDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin me-2"></i>Testing optimization... This may take a moment for large files.</div>';
            
            fetch('/test-image-upload', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned HTML instead of JSON. Check server logs for errors.');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <h6><i class="fas fa-check-circle me-2"></i>Optimization Test Successful!</h6>
                            <p><strong>Original Size:</strong> ${data.original_size}</p>
                            <p><strong>Optimized Size:</strong> ${data.optimized_size}</p>
                            <p><strong>Compression Ratio:</strong> ${data.compression_ratio}%</p>
                            <p><strong>Files Generated:</strong></p>
                            <ul>
                                ${data.files.map(file => `<li><code>${file}</code></li>`).join('')}
                            </ul>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle me-2"></i>
                        <strong>Error:</strong> ${error.message}
                        <br><small>Check the browser console and server logs for more details.</small>
                    </div>
                `;
            });
        });
    </script>
</body>
</html>