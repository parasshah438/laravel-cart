<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Support Ticket - Laravel Cart</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #4CAF50 0%, #8BC34A 50%, #CDDC39 100%);
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(76, 175, 80, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(139, 195, 74, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(205, 220, 57, 0.2) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .form-card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: linear-gradient(45deg, #4CAF50, #66BB6A);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #388E3C, #4CAF50);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
        }

        .form-control, .form-select {
            border-radius: 15px;
            border: 1px solid #ddd;
            padding: 12px 20px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }

        .breadcrumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            padding: 10px 20px;
        }

        .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: white;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .priority-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .priority-low { background-color: #6c757d; }
        .priority-normal { background-color: #0d6efd; }
        .priority-high { background-color: #fd7e14; }
        .priority-urgent { background-color: #dc3545; }

        .info-card {
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.2);
            border-radius: 15px;
            padding: 20px;
        }

        .character-count {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .file-upload-area {
            border: 2px dashed #ddd;
            border-radius: 15px;
            padding: 40px 20px;
            text-align: center;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .file-upload-area:hover {
            border-color: #4CAF50;
            background: rgba(76, 175, 80, 0.05);
        }

        .file-upload-area.dragover {
            border-color: #4CAF50;
            background: rgba(76, 175, 80, 0.1);
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('support.index') }}">
                        <i class="fas fa-home me-1"></i> Support Center
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('support.tickets') }}">My Tickets</a>
                </li>
                <li class="breadcrumb-item active">Create Ticket</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold text-white mb-3">Create Support Ticket</h1>
            <p class="lead text-white-75">Tell us about your issue and we'll get back to you as soon as possible</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Help Info -->
                <div class="info-card mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="fw-semibold text-success mb-2">
                                <i class="fas fa-lightbulb me-2"></i>
                                Before creating a ticket
                            </h6>
                            <p class="mb-0 text-muted">
                                Check our <a href="{{ route('help') }}" class="text-success text-decoration-none">Help Center</a> 
                                for quick answers, or try our <a href="{{ route('support.chat') }}" class="text-success text-decoration-none">Live Chat</a> 
                                for immediate assistance.
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('help') }}" class="btn btn-outline-success btn-sm me-2">
                                <i class="fas fa-question-circle me-1"></i> Help Center
                            </a>
                            <a href="{{ route('support.chat') }}" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-comments me-1"></i> Live Chat
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Create Ticket Form -->
                <div class="card form-card">
                    <div class="card-body p-5">
                        <form action="{{ route('support.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <!-- Subject -->
                            <div class="mb-4">
                                <label for="subject" class="form-label">
                                    <i class="fas fa-tag me-2 text-primary"></i>Subject *
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control @error('subject') is-invalid @enderror" 
                                    id="subject" 
                                    name="subject" 
                                    value="{{ old('subject') }}" 
                                    placeholder="Brief description of your issue"
                                    required
                                    maxlength="255"
                                >
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Category and Priority Row -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="category" class="form-label">
                                        <i class="fas fa-folder me-2 text-primary"></i>Category *
                                    </label>
                                    <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                        <option value="">Select a category</option>
                                        <option value="order" {{ old('category') === 'order' ? 'selected' : '' }}>Order Support</option>
                                        <option value="product" {{ old('category') === 'product' ? 'selected' : '' }}>Product Question</option>
                                        <option value="account" {{ old('category') === 'account' ? 'selected' : '' }}>Account Support</option>
                                        <option value="technical" {{ old('category') === 'technical' ? 'selected' : '' }}>Technical Issue</option>
                                        <option value="billing" {{ old('category') === 'billing' ? 'selected' : '' }}>Billing</option>
                                        <option value="general" {{ old('category') === 'general' ? 'selected' : '' }}>General</option>
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="priority" class="form-label">
                                        <i class="fas fa-exclamation-triangle me-2 text-primary"></i>Priority *
                                    </label>
                                    <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                        <option value="">Select priority</option>
                                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>
                                            <span class="priority-indicator priority-low"></span>Low - General inquiry
                                        </option>
                                        <option value="normal" {{ old('priority') === 'normal' ? 'selected' : '' }}>
                                            <span class="priority-indicator priority-normal"></span>Normal - Standard request
                                        </option>
                                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>
                                            <span class="priority-indicator priority-high"></span>High - Important issue
                                        </option>
                                        <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>
                                            <span class="priority-indicator priority-urgent"></span>Urgent - Critical issue
                                        </option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Order ID and Product ID Row -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="order_id" class="form-label">
                                        <i class="fas fa-shopping-cart me-2 text-primary"></i>Order Number
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control @error('order_id') is-invalid @enderror" 
                                        id="order_id" 
                                        name="order_id" 
                                        value="{{ old('order_id') }}" 
                                        placeholder="e.g., #12345 (if related to an order)"
                                    >
                                    @error('order_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="product_id" class="form-label">
                                        <i class="fas fa-box me-2 text-primary"></i>Product ID
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control @error('product_id') is-invalid @enderror" 
                                        id="product_id" 
                                        name="product_id" 
                                        value="{{ old('product_id') }}" 
                                        placeholder="Product ID (if related to a product)"
                                    >
                                    @error('product_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-4">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left me-2 text-primary"></i>Description *
                                </label>
                                <textarea 
                                    class="form-control @error('description') is-invalid @enderror" 
                                    id="description" 
                                    name="description" 
                                    rows="6" 
                                    placeholder="Please provide detailed information about your issue. Include steps to reproduce the problem if applicable."
                                    required
                                    maxlength="5000"
                                >{{ old('description') }}</textarea>
                                <div class="d-flex justify-content-between mt-2">
                                    <div class="character-count">
                                        <span id="char-count">0</span> / 5000 characters
                                    </div>
                                    @error('description')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- File Attachments -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-paperclip me-2 text-primary"></i>Attachments
                                </label>
                                <div class="file-upload-area" id="fileUploadArea">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Drop files here or click to browse</h6>
                                    <p class="text-muted small mb-0">
                                        Supported formats: JPG, PNG, PDF, DOC, DOCX (Max: 10MB per file)
                                    </p>
                                    <input 
                                        type="file" 
                                        class="form-control d-none" 
                                        id="attachments" 
                                        name="attachments[]" 
                                        multiple 
                                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                    >
                                </div>
                                <div id="selectedFiles" class="mt-3"></div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-between pt-4 border-top">
                                <a href="{{ route('support.tickets') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Tickets
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Create Ticket
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Character counter for description
        document.getElementById('description').addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('char-count').textContent = count;
            
            if (count > 4500) {
                document.getElementById('char-count').style.color = '#dc3545';
            } else if (count > 4000) {
                document.getElementById('char-count').style.color = '#fd7e14';
            } else {
                document.getElementById('char-count').style.color = '#6c757d';
            }
        });

        // File upload handling
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileInput = document.getElementById('attachments');
        const selectedFilesDiv = document.getElementById('selectedFiles');

        fileUploadArea.addEventListener('click', () => fileInput.click());

        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('dragover');
        });

        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('dragover');
        });

        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
            fileInput.files = e.dataTransfer.files;
            displaySelectedFiles();
        });

        fileInput.addEventListener('change', displaySelectedFiles);

        function displaySelectedFiles() {
            const files = Array.from(fileInput.files);
            selectedFilesDiv.innerHTML = '';

            if (files.length > 0) {
                files.forEach((file, index) => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'alert alert-success d-flex justify-content-between align-items-center';
                    fileItem.innerHTML = `
                        <div>
                            <i class="fas fa-file me-2"></i>
                            <strong>${file.name}</strong> 
                            <small class="text-muted">(${(file.size / 1024 / 1024).toFixed(2)} MB)</small>
                        </div>
                        <button type="button" class="btn-close" onclick="removeFile(${index})"></button>
                    `;
                    selectedFilesDiv.appendChild(fileItem);
                });
            }
        }

        function removeFile(index) {
            const dt = new DataTransfer();
            const files = Array.from(fileInput.files);
            
            files.forEach((file, i) => {
                if (i !== index) dt.items.add(file);
            });
            
            fileInput.files = dt.files;
            displaySelectedFiles();
        }

        // Update character count on page load
        document.addEventListener('DOMContentLoaded', function() {
            const description = document.getElementById('description');
            if (description.value) {
                document.getElementById('char-count').textContent = description.value.length;
            }
        });
    </script>
</body>
</html>
