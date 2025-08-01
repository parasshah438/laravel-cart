<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ $product->name }} - Product Details</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/css/glide.core.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/css/glide.theme.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/glide.min.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>
<div class="container py-4">
    {{-- Page title --}}
    <h1 class="mb-4 h3">{{ $product->name }}</h1>
    <div class="row g-4">
        {{-- Left: Product Image & Customization Preview --}}
        <div class="col-lg-6">
            <div class="product-preview-container">
                <!-- Main Product Image with Canvas Overlay -->
                <div class="position-relative border rounded p-3 text-center bg-white shadow-sm">
                    <img id="mainProductImage" 
                         src="{{ $product->image }}" 
                         class="img-fluid" 
                         alt="{{ $product->name }}"
                         style="max-width: 100%; height: auto;">
                    
                   
                    <!-- Customization Canvas Overlay -->
                    <canvas id="customizationCanvas" 
                            class="position-absolute top-0 start-0" 
                            style="pointer-events: none; z-index: 10; margin: 1rem;">
                    </canvas>
                
                </div>



           <div class="customization-actions mt-3">
    <div class="row g-2">
        <div class="col-4">
            <button type="button" class="btn btn-outline-success btn-sm w-100" onclick="downloadCustomizedImage()">
                <i class="fas fa-download"></i> Design Only
            </button>
        </div>
        <div class="col-4">
            <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="downloadScreenshot()">
                <i class="fas fa-camera"></i> Screenshot
            </button>
        </div>
        <div class="col-4">
            <button type="button" class="btn btn-outline-info btn-sm w-100" onclick="uploadCustomizedImageToServer()">
                <i class="fas fa-cloud-upload"></i> Save
            </button>
        </div>
    </div>
</div>
            <input type="hidden" id="customizationImageUrl" name="customization_image_url">

               
                <!-- Customization Area Selector -->
                <div class="customization-areas mt-3">
                    <h6><i class="fas fa-crosshairs text-primary"></i> Select Customization Area:</h6>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="customization_area" id="front_chest" value="front_chest" checked>
                        <label class="btn btn-outline-primary" for="front_chest">
                            <i class="fas fa-tshirt"></i> Front Chest
                        </label>
                        
                        <input type="radio" class="btn-check" name="customization_area" id="back_center" value="back_center">
                        <label class="btn btn-outline-primary" for="back_center">
                            <i class="fas fa-tshirt fa-flip-horizontal"></i> Back Center
                        </label>
                    </div>
                </div>
                
            </div>
        </div>

        {{-- Right: Product Info & Customization Options --}}
        <div class="col-lg-6">
            <div class="bg-white p-4 rounded shadow-sm">
                {{-- Price Display --}}
                <div class="price-section mb-4">
                    <h4 class="text-success fw-bold mb-1">
                        ₹<span id="basePrice">{{ number_format($product->price, 2) }}</span>
                        <span id="customizationPrice" class="text-warning small"></span>
                    </h4>
                    <p class="text-muted small mb-0">
                        Base Price
                       
                        <span class="badge bg-info ms-2">
                            <i class="fas fa-palette"></i> Customizable
                        </span>
                    
                    </p>
                </div>

                {{-- Description --}}
                <p class="text-muted mt-3">{{ $product->description }}</p>

               
                {{-- Customization Panel --}}
                <div class="customization-panel mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-palette text-primary"></i> Customize Your T-Shirt
                        </h5>
                        <button type="button" 
                                class="btn btn-sm btn-outline-secondary" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#customizationOptions">
                            <i class="fas fa-cog"></i> Options
                        </button>
                    </div>
                    
                    <div class="collapse show" id="customizationOptions">
                        {{-- Text Customization --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-font text-primary"></i> Add Custom Text
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Your Text:</label>
                                        <input type="text" 
                                               id="customText" 
                                               class="form-control" 
                                               placeholder="Enter your custom text..."
                                               maxlength="30">
                                        <div class="form-text">Maximum 30 characters</div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Font Style:</label>
                                        <select id="fontFamily" class="form-select">
                                            <option value="Arial, sans-serif" data-price="0">Arial (Free)</option>
                                            <option value="Times New Roman, serif" data-price="50">Times New Roman (+₹50)</option>
                                            <option value="Comic Sans MS, cursive" data-price="75">Comic Sans (+₹75)</option>
                                            <option value="Impact, sans-serif" data-price="100">Impact (+₹100)</option>
                                            <option value="Brush Script MT, cursive" data-price="150">Brush Script (+₹150)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Font Size:</label>
                                        <select id="fontSize" class="form-select">
                                            <option value="16">16px</option>
                                            <option value="20" selected>20px</option>
                                            <option value="24">24px</option>
                                            <option value="28">28px</option>
                                            <option value="32">32px</option>
                                            <option value="36">36px</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label class="form-label">Text Color:</label>
                                        <div class="color-picker-container mb-2">
                                            <div class="color-option selected" data-color="#000000" style="background-color: #000000;"></div>
                                            <div class="color-option" data-color="#FFFFFF" style="background-color: #FFFFFF; border: 2px solid #ddd;"></div>
                                            <div class="color-option" data-color="#FF0000" style="background-color: #FF0000;"></div>
                                            <div class="color-option" data-color="#00FF00" style="background-color: #00FF00;"></div>
                                            <div class="color-option" data-color="#0000FF" style="background-color: #0000FF;"></div>
                                            <div class="color-option" data-color="#FFFF00" style="background-color: #FFFF00;"></div>
                                            <div class="color-option" data-color="#FF00FF" style="background-color: #FF00FF;"></div>
                                            <div class="color-option" data-color="#00FFFF" style="background-color: #00FFFF;"></div>
                                        </div>
                                        <input type="color" id="customColor" class="form-control form-control-color" value="#000000">
                                    </div>
                                </div>

                                <button type="button" class="btn btn-primary btn-sm mt-3" onclick="addTextToCanvas()">
                                    <i class="fas fa-plus"></i> Add Text to Design
                                </button>
                            </div>
                        </div>

                        {{-- Image/Logo Customization --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-image text-success"></i> Add Logo/Image
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    
                                  

<div class="col-4 col-md-3">
    <div class="logo-option card text-center p-2" 
         data-logo-url="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48bGluZWFyR3JhZGllbnQgaWQ9ImEiIHgxPSIwJSIgeTE9IjAlIiB4Mj0iMTAwJSIgeTI9IjEwMCUiPjxzdG9wIG9mZnNldD0iMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiNGRjY5QjQ7c3RvcC1vcGFjaXR5OjEiLz48c3RvcCBvZmZzZXQ9IjEwMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiNGRjE0OTM7c3RvcC1vcGFjaXR5OjEiLz48L2xpbmVhckdyYWRpZW50PjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0idXJsKCNhKSIvPjx0ZXh0IHg9IjUwIiB5PSI1NSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjMwIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSI+4p2k77iPPC90ZXh0Pjwvc3ZnPg=="
         data-logo-name="Love Heart"
         data-price="100">
        <i class="fas fa-heart fa-2x text-danger mb-1"></i>
        <small class="d-block">Love Heart</small>
        <small class="text-success">+₹100</small>
    </div>
</div>

<div class="col-4 col-md-3">
    <div class="logo-option card text-center p-2"
         data-logo-url="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48bGluZWFyR3JhZGllbnQgaWQ9ImEiIHgxPSIwJSIgeTE9IjAlIiB4Mj0iMTAwJSIgeTI9IjEwMCUiPjxzdG9wIG9mZnNldD0iMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiNGRkQ3MDA7c3RvcC1vcGFjaXR5OjEiLz48c3RvcCBvZmZzZXQ9IjEwMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiNGRkE1MDA7c3RvcC1vcGFjaXR5OjEiLz48L2xpbmVhckdyYWRpZW50PjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0idXJsKCNhKSIvPjx0ZXh0IHg9IjUwIiB5PSI1NSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjMwIiBmaWxsPSJibGFjayIgdGV4dC1hbmNob3I9Im1pZGRsZSI+4q2Q77iPPC90ZXh0Pjwvc3ZnPg=="
         data-logo-name="Star"
         data-price="75">
        <i class="fas fa-star fa-2x text-warning mb-1"></i>
        <small class="d-block">Star</small>
        <small class="text-success">+₹75</small>
    </div>
</div>

<div class="col-4 col-md-3">
    <div class="logo-option card text-center p-2"
         data-logo-url="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48bGluZWFyR3JhZGllbnQgaWQ9ImEiIHgxPSIwJSIgeTE9IjAlIiB4Mj0iMTAwJSIgeTI9IjEwMCUiPjxzdG9wIG9mZnNldD0iMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiM5MzcwREI7c3RvcC1vcGFjaXR5OjEiLz48c3RvcCBvZmZzZXQ9IjEwMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiM2ODY2RkY7c3RvcC1vcGFjaXR5OjEiLz48L2xpbmVhckdyYWRpZW50PjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0idXJsKCNhKSIvPjx0ZXh0IHg9IjUwIiB5PSI1NSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjMwIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSI+4pqh77iPPC90ZXh0Pjwvc3ZnPg=="
         data-logo-name="Lightning"
         data-price="125">
        <i class="fas fa-bolt fa-2x text-primary mb-1"></i>
        <small class="d-block">Lightning</small>
        <small class="text-success">+₹125</small>
    </div>
</div>

<div class="col-4 col-md-3">
    <div class="logo-option card text-center p-2"
         data-logo-url="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48bGluZWFyR3JhZGllbnQgaWQ9ImEiIHgxPSIwJSIgeTE9IjAlIiB4Mj0iMTAwJSIgeTI9IjEwMCUiPjxzdG9wIG9mZnNldD0iMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiMzMkNEMzI7c3RvcC1vcGFjaXR5OjEiLz48c3RvcCBvZmZzZXQ9IjEwMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiMyOEE3NDU7c3RvcC1vcGFjaXR5OjEiLz48L2xpbmVhckdyYWRpZW50PjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0idXJsKCNhKSIvPjx0ZXh0IHg9IjUwIiB5PSI1NSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjMwIiBmaWxsPSJibGFjayIgdGV4dC1hbmNob3I9Im1pZGRsZSI+8J+mhTwvdGV4dD48L3N2Zz4="
         data-logo-name="Eagle"
         data-price="200">
        <i class="fas fa-crow fa-2x text-dark mb-1"></i>
        <small class="d-block">Eagle</small>
        <small class="text-success">+₹200</small>
    </div>
</div>
                                    <div class="col-4 col-md-3">
                                        <div class="logo-option card text-center p-2"
                                             data-logo-url="upload"
                                             data-logo-name="Custom Upload"
                                             data-price="300">
                                            <i class="fas fa-upload fa-2x text-secondary mb-1"></i>
                                            <small class="d-block">Upload</small>
                                            <small class="text-success">+₹300</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Custom Upload Area -->
                                <div class="mt-3" id="uploadArea" style="display: none;">
                                    <label class="form-label">
                                        <i class="fas fa-cloud-upload-alt"></i> Upload Your Image:
                                    </label>
                                    <input type="file" id="customImageUpload" class="form-control" accept="image/*">
                                    <div class="form-text">PNG, JPG up to 2MB. For best results use 200x200px images.</div>
                                </div>
                            </div>
                        </div>

                        {{-- Customization Summary --}}
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>
                                    <i class="fas fa-list-check text-info"></i> Customization Summary:
                                </h6>
                                <div id="customizationSummary">
                                    <p class="text-muted mb-0">No customizations added yet</p>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Total Price:</strong>
                                    <h5 class="text-primary mb-0">₹<span id="totalPrice">{{ number_format($product->price, 2) }}</span></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                

                {{-- Action Buttons --}}
                <div class="d-grid gap-2 mt-4">
                   
                    <!-- Customized Add to Cart Form -->
                    <form id="customizedCartForm" method="POST" action="">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="customization_data" id="customizationData">
                        <div class="row g-2 align-items-end mb-3">
                            <div class="col-auto">
                                <label class="form-label small">Quantity:</label>
                                <input type="number" name="quantity" value="1" min="1" max="10" class="form-control" style="width: 80px;">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-shopping-cart"></i> Add Customized Product to Cart
                        </button>
                    </form>
                   

                    <a href="" class="btn btn-warning btn-lg w-100">
                        <i class="fas fa-bolt"></i> Buy Now
                    </a>

                    <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-heart"></i> 
                            {{ auth()->check() && $wishlistProductIds->contains($product->id) ? 'Remove from Wishlist' : 'Add to Wishlist' }}
                        </button>
                    </form>

                    <a href="#inquiryModal" class="btn btn-outline-secondary w-100" data-bs-toggle="modal">
                        <i class="fas fa-envelope"></i> Contact for Inquiry
                    </a>
                </div>

                {{-- Rating --}}
                <div class="mt-4">
                    <strong>Rating:</strong>
                    <span class="text-warning">★★★★☆</span> (Coming Soon)
                </div>
            </div>
        </div>
    </div>

    {{-- Product Inquiry Modal --}}
    <div class="modal fade" id="inquiryModal" tabindex="-1" aria-labelledby="inquiryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" action="" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="inquiryModalLabel">
                        <i class="fas fa-envelope text-primary"></i> Product Inquiry
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Your Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Your Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="4" required placeholder="Ask about this product..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Inquiry
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Similar Products --}}
    @if($similarProducts->count())
    <div class="mt-5">
        <h5><i class="fas fa-thumbs-up text-primary"></i> Similar Products</h5>
        <div class="glide" id="similarProductsSlider">
            <div class="glide__track" data-glide-el="track">
                <ul class="glide__slides">
                    @foreach($similarProducts as $simProduct)
                        <li class="glide__slide">
                            <a href="{{ route('product.show', $simProduct->id) }}" class="text-decoration-none text-dark">
                                <div class="card h-100 shadow-sm hover-lift">
                                    <img src="{{ $simProduct->image }}" class="card-img-top" alt="{{ $simProduct->name }}" style="height: 200px; object-fit: cover;">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-truncate">{{ $simProduct->name }}</h6>
                                        <p class="text-success mb-0 fw-bold">₹{{ number_format($simProduct->price, 2) }}</p>
                                        @if($simProduct->is_customizable ?? false)
                                        <span class="badge bg-info mt-1">
                                            <i class="fas fa-palette"></i> Customizable
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="glide__arrows" data-glide-el="controls">
                <button class="glide__arrow glide__arrow--left" data-glide-dir="<">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="glide__arrow glide__arrow--right" data-glide-dir=">">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
/* Product Preview Styles */
.product-preview-container {
    max-width: 500px;
    margin: 0 auto;
}

#customizationCanvas {
    max-width: calc(100% - 2rem);
    height: auto;
}

/* Color Picker Styles */
.color-picker-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.color-option {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    border: 2px solid #ddd;
    transition: all 0.3s ease;
    display: inline-block;
}

.color-option:hover {
    transform: scale(1.1);
    border-color: #007bff;
}

.color-option.selected {
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.25);
}

/* Logo Option Styles */
.logo-option {
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.logo-option:hover {
    border-color: #007bff;
    transform: scale(1.05);
}

.logo-option.selected {
    border-color: #007bff;
    background-color: #f8f9ff;
}

/* Card Hover Effects */
.hover-lift:hover {
    transform: translateY(-5px);
    transition: all 0.3s ease;
}

/* Customization Panel */
.customization-panel .card {
    border: 1px solid #e9ecef;
}

.customization-panel .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

/* Glide Arrow Styles */
.glide__arrow {
    background: rgba(0,0,0,0.5);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
}

.glide__arrow:hover {
    background: rgba(0,0,0,0.8);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .customization-panel {
        margin-top: 2rem;
    }
    
    .color-option {
        width: 25px;
        height: 25px;
    }
    
    #customizationCanvas {
        margin: 0.5rem;
    }
}

/* Loading animation */
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Canvas and customization variables
let canvas, ctx;
let customizations = [];
let currentArea = 'front_chest';
let basePrice = {{ $product->price }};
let isCustomizable = true;

// Dragging variables
let isDragging = false;
let dragTarget = null;
let mousePos = { x: 0, y: 0 };
let dragOffset = { x: 0, y: 0 };

// Customization areas coordinates
let customizationAreas = {
    front_chest: { x: 150, y: 120, width: 200, height: 150 },
    back_center: { x: 150, y: 100, width: 220, height: 180 }
};

document.addEventListener('DOMContentLoaded', function() {
    if (isCustomizable) {
        initializeCanvas();
        setupCanvasDragging();
        setupEventListeners();
    }
    
    // Initialize similar products slider
    initializeSimilarProductsSlider();
    
    // Setup regular add to cart AJAX
    setupRegularCartAjax();
});

function initializeCanvas() {
    canvas = document.getElementById('customizationCanvas');
    if (!canvas) return;
    
    ctx = canvas.getContext('2d');
    
    const img = document.getElementById('mainProductImage');
    img.onload = function() {
        canvas.width = img.clientWidth - 32; // Account for padding
        canvas.height = img.clientHeight - 32;
        redrawCanvas();
    };
    
    // Set initial canvas size
    canvas.width = img.clientWidth - 32 || 468;
    canvas.height = img.clientHeight - 32 || 568;
}

function setupCanvasDragging() {
    // Enable pointer events on canvas
    canvas.style.pointerEvents = 'auto';
    canvas.style.cursor = 'default';
    
    // Mouse down event
    canvas.addEventListener('mousedown', function(e) {
        const rect = canvas.getBoundingClientRect();
        mousePos.x = e.clientX - rect.left;
        mousePos.y = e.clientY - rect.top;
        
        // Find which customization item was clicked
        dragTarget = findClickedItem(mousePos.x, mousePos.y);
        
        if (dragTarget) {
            isDragging = true;
            canvas.style.cursor = 'grabbing';
            
            // Calculate offset from mouse to item center
            dragOffset.x = mousePos.x - dragTarget.x;
            dragOffset.y = mousePos.y - dragTarget.y;
            
            showToast(`🎯 Dragging ${dragTarget.type === 'text' ? dragTarget.text : dragTarget.name}`, true);
        }
    });
    
    // Mouse move event
    canvas.addEventListener('mousemove', function(e) {
        const rect = canvas.getBoundingClientRect();
        mousePos.x = e.clientX - rect.left;
        mousePos.y = e.clientY - rect.top;
        
        if (isDragging && dragTarget) {
            // Update position
            dragTarget.x = mousePos.x - dragOffset.x;
            dragTarget.y = mousePos.y - dragOffset.y;
            
            // Keep within canvas bounds
            dragTarget.x = Math.max(30, Math.min(canvas.width - 30, dragTarget.x));
            dragTarget.y = Math.max(30, Math.min(canvas.height - 30, dragTarget.y));
            
            // Redraw canvas
            redrawCanvas();
            updateSummary();
            
        } else {
            // Change cursor when hovering over draggable items
            const hoverItem = findClickedItem(mousePos.x, mousePos.y);
            canvas.style.cursor = hoverItem ? 'grab' : 'default';
        }
    });
    
    // Mouse up event
    canvas.addEventListener('mouseup', function(e) {
        if (isDragging) {
            isDragging = false;
            dragTarget = null;
            canvas.style.cursor = 'default';
            showToast('📍 Position updated!', true);
        }
    });
    
    // Mouse leave event
    canvas.addEventListener('mouseleave', function(e) {
        if (isDragging) {
            isDragging = false;
            dragTarget = null;
            canvas.style.cursor = 'default';
        }
    });
    
    // Touch events for mobile
    canvas.addEventListener('touchstart', handleTouchStart, { passive: false });
    canvas.addEventListener('touchmove', handleTouchMove, { passive: false });
    canvas.addEventListener('touchend', handleTouchEnd, { passive: false });
}

// Touch event handlers for mobile
function handleTouchStart(e) {
    e.preventDefault();
    const touch = e.touches[0];
    const rect = canvas.getBoundingClientRect();
    mousePos.x = touch.clientX - rect.left;
    mousePos.y = touch.clientY - rect.top;
    
    dragTarget = findClickedItem(mousePos.x, mousePos.y);
    
    if (dragTarget) {
        isDragging = true;
        dragOffset.x = mousePos.x - dragTarget.x;
        dragOffset.y = mousePos.y - dragTarget.y;
        showToast(`📱 Touch dragging ${dragTarget.type === 'text' ? dragTarget.text : dragTarget.name}`, true);
    }
}

function handleTouchMove(e) {
    e.preventDefault();
    if (!isDragging || !dragTarget) return;
    
    const touch = e.touches[0];
    const rect = canvas.getBoundingClientRect();
    mousePos.x = touch.clientX - rect.left;
    mousePos.y = touch.clientY - rect.top;
    
    dragTarget.x = mousePos.x - dragOffset.x;
    dragTarget.y = mousePos.y - dragOffset.y;
    
    // Keep within bounds
    dragTarget.x = Math.max(30, Math.min(canvas.width - 30, dragTarget.x));
    dragTarget.y = Math.max(30, Math.min(canvas.height - 30, dragTarget.y));
    
    redrawCanvas();
    updateSummary();
}

function handleTouchEnd(e) {
    e.preventDefault();
    isDragging = false;
    dragTarget = null;
    showToast('📍 Touch position updated!', true);
}

// Find which customization item was clicked
function findClickedItem(x, y) {
    const currentCustomizations = customizations.filter(c => c.area === currentArea);
    
    // Check in reverse order (last drawn = top layer)
    for (let i = currentCustomizations.length - 1; i >= 0; i--) {
        const item = currentCustomizations[i];
        
        if (item.type === 'text') {
            // Check if click is within text bounds
            ctx.font = `${item.size}px ${item.font}`;
            const textWidth = ctx.measureText(item.text).width;
            const textHeight = item.size;
            
            if (x >= item.x - textWidth/2 - 10 && x <= item.x + textWidth/2 + 10 &&
                y >= item.y - textHeight/2 - 10 && y <= item.y + textHeight/2 + 10) {
                return item;
            }
        } else if (item.type === 'image') {
            // Check if click is within image bounds
            if (x >= item.x - item.width/2 - 5 && x <= item.x + item.width/2 + 5 &&
                y >= item.y - item.height/2 - 5 && y <= item.y + item.height/2 + 5) {
                return item;
            }
        }
    }
    
    return null;
}

function setupEventListeners() {
    // Area selection
    document.querySelectorAll('input[name="customization_area"]').forEach(radio => {
        radio.addEventListener('change', function() {
            currentArea = this.value;
            redrawCanvas();
        });
    });

    // Text input changes
    document.getElementById('customText')?.addEventListener('input', updateTextPreview);
    document.getElementById('fontFamily')?.addEventListener('change', updateTextPreview);
    document.getElementById('fontSize')?.addEventListener('change', updateTextPreview);
    document.getElementById('customColor')?.addEventListener('change', updateTextPreview);

    // Color picker clicks
    document.querySelectorAll('.color-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.color-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            document.getElementById('customColor').value = this.dataset.color;
            updateTextPreview();
        });
    });

    // Logo selection
    document.querySelectorAll('.logo-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.logo-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            
            if (this.dataset.logoUrl === 'upload') {
                document.getElementById('uploadArea').style.display = 'block';
            } else {
                document.getElementById('uploadArea').style.display = 'none';
                addImageToCanvas(this.dataset.logoUrl, this.dataset.logoName, parseFloat(this.dataset.price));
            }
        });
    });

    // Custom image upload
    document.getElementById('customImageUpload')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (!file.type.startsWith('image/')) {
                showToast('Please select an image file', false);
                return;
            }
            
            if (file.size > 2 * 1024 * 1024) {
                showToast('File size should be less than 2MB', false);
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const selectedLogo = document.querySelector('.logo-option.selected');
                const price = selectedLogo ? parseFloat(selectedLogo.dataset.price) : 300;
                addImageToCanvas(e.target.result, 'Custom Upload', price);
            };
            reader.readAsDataURL(file);
        }
    });

    // Form submission
    document.getElementById('customizedCartForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        document.getElementById('customizationData').value = JSON.stringify(customizations);
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="loading-spinner"></span> Adding to Cart...';
        
        fetch('{{ route("cart.ajaxAdd", $product->id) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || data.status) {
                showToast(data.message || 'Customized product added to cart! 🎨', true);
            } else {
                showToast(data.message || 'Error adding product to cart', false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error adding product to cart', false);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Add Customized Product to Cart';
        });
    });
}

function updateTextPreview() {
    const text = document.getElementById('customText')?.value;
    if (!text) return;

    const font = document.getElementById('fontFamily')?.value || 'Arial, sans-serif';
    const size = document.getElementById('fontSize')?.value || '20';
    const color = document.getElementById('customColor')?.value || '#000000';

    const existingIndex = customizations.findIndex(c => c.type === 'text' && c.area === currentArea);
    
    // Keep existing position if item already exists
    let x = customizationAreas[currentArea]?.x || 150;
    let y = customizationAreas[currentArea]?.y || 120;
    
    if (existingIndex >= 0) {
        x = customizations[existingIndex].x;
        y = customizations[existingIndex].y;
    }
    
    const textCustomization = {
        type: 'text',
        area: currentArea,
        text: text,
        font: font,
        size: parseInt(size),
        color: color,
        x: x,
        y: y,
        price: getTextPrice()
    };

    if (existingIndex >= 0) {
        customizations[existingIndex] = textCustomization;
    } else {
        customizations.push(textCustomization);
    }

    redrawCanvas();
    updateSummary();
}

function addTextToCanvas() {
    const text = document.getElementById('customText')?.value;
    if (!text) {
        showToast('Please enter some text first', false);
        return;
    }
    
    updateTextPreview();
    showToast('✨ Text added! Click and drag on the image to reposition', true);
}

function addImageToCanvas(imageUrl, imageName, price) {
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = function() {
        const existingIndex = customizations.findIndex(c => c.type === 'image' && c.area === currentArea);
        
        // Keep existing position if item already exists
        let x = customizationAreas[currentArea]?.x || 150;
        let y = customizationAreas[currentArea]?.y || 120;
        
        if (existingIndex >= 0) {
            x = customizations[existingIndex].x;
            y = customizations[existingIndex].y;
        }
        
        const imageCustomization = {
            type: 'image',
            area: currentArea,
            url: imageUrl,
            name: imageName,
            x: x,
            y: y,
            width: 80,
            height: 80,
            price: price
        };

        if (existingIndex >= 0) {
            customizations[existingIndex] = imageCustomization;
        } else {
            customizations.push(imageCustomization);
        }

        redrawCanvas();
        updateSummary();
        showToast(`🎨 ${imageName} added! Click and drag on the image to reposition`, true);
    };
    img.src = imageUrl;
}

// Replace your redrawCanvas function with this clean version:


function redrawCanvas() {
    if (!ctx) return;
    
    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Get customizations for current area
    const currentCustomizations = customizations.filter(c => c.area === currentArea);
    
    // Draw each customization cleanly
    currentCustomizations.forEach(customization => {
        if (customization.type === 'text') {
            // Draw text only
            ctx.font = `${customization.size}px ${customization.font}`;
            ctx.fillStyle = customization.color;
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(customization.text, customization.x, customization.y);
            
        } else if (customization.type === 'image') {
            // Draw image only
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = function() {
                ctx.drawImage(
                    img, 
                    customization.x - customization.width/2, 
                    customization.y - customization.height/2, 
                    customization.width, 
                    customization.height
                );
            };
            img.src = customization.url;
        }
    });
}

function getTextPrice() {
    const fontSelect = document.getElementById('fontFamily');
    const selectedOption = fontSelect?.options[fontSelect.selectedIndex];
    return parseFloat(selectedOption?.dataset.price || 0);
}

function updateSummary() {
    const summaryDiv = document.getElementById('customizationSummary');
    const totalPriceSpan = document.getElementById('totalPrice');
    const customizationPriceSpan = document.getElementById('customizationPrice');
    
    if (customizations.length === 0) {
        summaryDiv.innerHTML = '<p class="text-muted mb-0">No customizations added yet</p>';
        totalPriceSpan.textContent = basePrice.toFixed(2);
        customizationPriceSpan.textContent = '';
        return;
    }

    let html = '<ul class="list-unstyled mb-0">';
    let totalCustomizationPrice = 0;

    customizations.forEach(c => {
        totalCustomizationPrice += c.price;
        if (c.type === 'text') {
            html += `<li class="mb-2">
                <i class="fas fa-font text-primary"></i> 
                Text: "<strong>${c.text}</strong>" on ${c.area.replace('_', ' ')} 
                <small class="text-muted">(${Math.round(c.x)}, ${Math.round(c.y)})</small>
                <span class="badge bg-success">+₹${c.price}</span>
                <small class="text-info d-block">🖱️ Click and drag on canvas to reposition</small>
            </li>`;
        } else if (c.type === 'image') {
            html += `<li class="mb-2">
                <i class="fas fa-image text-success"></i> 
                ${c.name} on ${c.area.replace('_', ' ')} 
                <small class="text-muted">(${Math.round(c.x)}, ${Math.round(c.y)})</small>
                <span class="badge bg-success">+₹${c.price}</span>
                <small class="text-info d-block">🖱️ Click and drag on canvas to reposition</small>
            </li>`;
        }
    });

    html += '</ul>';
    summaryDiv.innerHTML = html;
    
    const totalPrice = basePrice + totalCustomizationPrice;
    totalPriceSpan.textContent = totalPrice.toFixed(2);
    customizationPriceSpan.textContent = totalCustomizationPrice > 0 ? ` + ₹${totalCustomizationPrice.toFixed(2)} customization` : '';
}

function setupRegularCartAjax() {
    $(document).on('submit', '.add-to-cart-form', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).html('<span class="loading-spinner"></span> Adding...');
        
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            success: function(response) {
                showToast(response.message || 'Product added to cart!', true);
                
                if (typeof showGiftProductsModal === 'function') {
                    showGiftProductsModal(response.product_id);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.values(errors).forEach(e => showToast(e[0], false));
                } else {
                    showToast('Error adding product to cart', false);
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-shopping-cart"></i> Add to Cart');
            }
        });
    });
}

function initializeSimilarProductsSlider() {
    if (document.getElementById('similarProductsSlider')) {
        new Glide('#similarProductsSlider', {
            type: 'carousel',
            perView: 4,
            gap: 16,
            autoplay: 3000,
            keyboard: true,
            breakpoints: {
                992: { perView: 3 },
                768: { perView: 2 },
                576: { perView: 1 }
            }
        }).mount();
    }
}

function downloadCustomizedImage() {
    if (!canvas || customizations.length === 0) {
        showToast('❌ No customizations to download', false);
        return;
    }
    
    try {
        // Create a clean canvas without any external images
        const downloadCanvas = document.createElement('canvas');
        const downloadCtx = downloadCanvas.getContext('2d');
        
        // Set high resolution
        const scale = 2;
        downloadCanvas.width = canvas.width * scale;
        downloadCanvas.height = canvas.height * scale;
        downloadCtx.scale(scale, scale);
        
        // Create a clean background (instead of using external image)
        downloadCtx.fillStyle = '#f8f9fa'; // Light gray background
        downloadCtx.fillRect(0, 0, canvas.width, canvas.height);
        
        // Add a t-shirt outline (drawn programmatically)
        drawTShirtOutline(downloadCtx, canvas.width, canvas.height);
        
        // Draw all customizations
        const allCustomizations = customizations.filter(c => c.area === currentArea);
        
        // Draw text customizations (these are safe)
        allCustomizations.forEach(customization => {
            if (customization.type === 'text') {
                downloadCtx.font = `${customization.size}px ${customization.font}`;
                downloadCtx.fillStyle = customization.color;
                downloadCtx.textAlign = 'center';
                downloadCtx.textBaseline = 'middle';
                downloadCtx.fillText(customization.text, customization.x, customization.y);
            }
        });
        
        // Handle images - only process data URLs (safe ones)
        let pendingImages = 0;
        const imageCustomizations = allCustomizations.filter(c => c.type === 'image');
        
        if (imageCustomizations.length === 0) {
            // No images to process, download immediately
            triggerDownloadSafe(downloadCanvas);
        } else {
            imageCustomizations.forEach(customization => {
                if (customization.url.startsWith('data:')) {
                    // Safe data URL
                    pendingImages++;
                    const img = new Image();
                    img.onload = function() {
                        downloadCtx.drawImage(
                            img,
                            customization.x - customization.width/2,
                            customization.y - customization.height/2,
                            customization.width,
                            customization.height
                        );
                        
                        pendingImages--;
                        if (pendingImages === 0) {
                            triggerDownloadSafe(downloadCanvas);
                        }
                    };
                    img.src = customization.url;
                } else {
                    // External URL - draw a placeholder instead
                    drawImagePlaceholder(downloadCtx, customization);
                }
            });
            
            if (pendingImages === 0) {
                triggerDownloadSafe(downloadCanvas);
            }
        }
        
    } catch (error) {
        console.error('Download error:', error);
        // Fallback: Use Canvas2Image library
        fallbackDownload();
    }
}


function downloadCustomizedImagebk() {
    if (!canvas || customizations.length === 0) {
        showToast('❌ No customizations to download', false);
        return;
    }
    
    // Create a higher resolution canvas for download
    const downloadCanvas = document.createElement('canvas');
    const downloadCtx = downloadCanvas.getContext('2d');
    
    // Set high resolution (2x for better quality)
    const scale = 2;
    downloadCanvas.width = canvas.width * scale;
    downloadCanvas.height = canvas.height * scale;
    downloadCtx.scale(scale, scale);
    
    // Draw the base t-shirt image first
    const baseImg = document.getElementById('mainProductImage');
    downloadCtx.drawImage(baseImg, 0, 0, canvas.width, canvas.height);
    
    // Draw all customizations on top
    const allCustomizations = customizations.filter(c => c.area === currentArea);
    
    allCustomizations.forEach(customization => {
        if (customization.type === 'text') {
            // Draw text
            downloadCtx.font = `${customization.size}px ${customization.font}`;
            downloadCtx.fillStyle = customization.color;
            downloadCtx.textAlign = 'center';
            downloadCtx.textBaseline = 'middle';
            downloadCtx.fillText(customization.text, customization.x, customization.y);
            
        } else if (customization.type === 'image') {
            // Draw image
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = function() {
                downloadCtx.drawImage(
                    img,
                    customization.x - customization.width/2,
                    customization.y - customization.height/2,
                    customization.width,
                    customization.height
                );
                
                // Trigger download after last image is loaded
                if (customization === allCustomizations[allCustomizations.length - 1]) {
                    triggerDownload(downloadCanvas);
                }
            };
            img.src = customization.url;
        }
    });
    
    // If no images, download immediately
    if (!allCustomizations.some(c => c.type === 'image')) {
        triggerDownload(downloadCanvas);
    }
}

function triggerDownload(canvas) {
    // Convert canvas to blob
    canvas.toBlob(function(blob) {
        // Create download link
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.download = `customized-tshirt-${Date.now()}.png`;
        link.href = url;
        
        // Trigger download
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Clean up
        URL.revokeObjectURL(url);
        
        showToast('✅ Customized design downloaded!', true);
    }, 'image/png', 1.0);
}

function uploadCustomizedImageToServer() {
    if (!canvas || customizations.length === 0) {
        showToast('❌ No customizations to upload', false);
        return;
    }
    
    // Convert canvas to blob
    canvas.toBlob(function(blob) {
        const formData = new FormData();
        formData.append('customized_image', blob, `customized-${Date.now()}.png`);
        formData.append('product_id', {{ $product->id }});
        formData.append('customizations', JSON.stringify(customizations));
        
        // Upload to server
        fetch('{{ route("customization.saveImage") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('✅ Design saved to order!', true);
                
                // Store image URL for order
                document.getElementById('customizationImageUrl').value = data.image_url;
            } else {
                showToast('❌ Failed to save design', false);
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            showToast('❌ Upload failed', false);
        });
    }, 'image/png', 0.9);
}

function captureDesignScreenshot() {
    const designContainer = document.querySelector('.product-preview-container');
    
    html2canvas(designContainer, {
        backgroundColor: '#ffffff',
        scale: 2,
        logging: false,
        useCORS: true,
        allowTaint: true
    }).then(function(screenshotCanvas) {
        // Download the screenshot
        screenshotCanvas.toBlob(function(blob) {
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.download = `tshirt-design-${Date.now()}.png`;
            link.href = url;
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            URL.revokeObjectURL(url);
            showToast('✅ Design screenshot downloaded!', true);
        });
    }).catch(function(error) {
        console.error('Screenshot error:', error);
        showToast('❌ Screenshot failed. Please use browser screenshot (Ctrl+Shift+S)', false);
    });
}

function fallbackDownload() {
    try {
        // Try Canvas2Image as fallback
        if (typeof Canvas2Image !== 'undefined') {
            Canvas2Image.saveAsPNG(canvas, canvas.width, canvas.height, `customized-tshirt-${Date.now()}`);
            showToast('✅ Design downloaded (fallback method)!', true);
        } else {
            // Last resort: screenshot instruction
            showToast('❌ Cannot download due to security restrictions. Please take a screenshot instead.', false);
        }
    } catch (error) {
        showToast('❌ Download failed. Please take a screenshot of your design.', false);
    }
}

function triggerDownloadSafe(canvas) {
    try {
        // Convert canvas to blob
        canvas.toBlob(function(blob) {
            if (blob) {
                // Create download link
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.download = `customized-tshirt-${Date.now()}.png`;
                link.href = url;
                
                // Trigger download
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Clean up
                URL.revokeObjectURL(url);
                
                showToast('✅ Customized design downloaded!', true);
            } else {
                fallbackDownload();
            }
        }, 'image/png', 1.0);
    } catch (error) {
        console.error('Download error:', error);
        fallbackDownload();
    }
}


function drawImagePlaceholder(ctx, customization) {
    // Draw a placeholder for external images
    const x = customization.x - customization.width/2;
    const y = customization.y - customization.height/2;
    
    // Background
    ctx.fillStyle = '#e9ecef';
    ctx.fillRect(x, y, customization.width, customization.height);
    
    // Border
    ctx.strokeStyle = '#adb5bd';
    ctx.lineWidth = 1;
    ctx.strokeRect(x, y, customization.width, customization.height);
    
    // Icon/Text
    ctx.fillStyle = '#6c757d';
    ctx.font = '12px Arial';
    ctx.textAlign = 'center';
    ctx.fillText('🖼️', customization.x, customization.y - 5);
    ctx.fillText(customization.name, customization.x, customization.y + 10);
}

function drawTShirtOutline(ctx, width, height) {
    // Draw a simple t-shirt outline
    ctx.strokeStyle = '#dee2e6';
    ctx.lineWidth = 2;
    ctx.setLineDash([5, 5]);
    
    // T-shirt shape (simplified)
    const centerX = width / 2;
    const centerY = height / 2;
    const shirtWidth = Math.min(width * 0.6, 200);
    const shirtHeight = Math.min(height * 0.7, 250);
    
    // Main body
    ctx.strokeRect(
        centerX - shirtWidth/2, 
        centerY - shirtHeight/2 + 20, 
        shirtWidth, 
        shirtHeight - 20
    );
    
    // Sleeves
    ctx.strokeRect(centerX - shirtWidth/2 - 30, centerY - shirtHeight/2 + 20, 30, 60);
    ctx.strokeRect(centerX + shirtWidth/2, centerY - shirtHeight/2 + 20, 30, 60);
    
    // Neck
    ctx.beginPath();
    ctx.arc(centerX, centerY - shirtHeight/2 + 20, 20, 0, Math.PI, false);
    ctx.stroke();
    
    ctx.setLineDash([]);
    
    // Add text label
    ctx.fillStyle = '#6c757d';
    ctx.font = '14px Arial';
    ctx.textAlign = 'center';
    ctx.fillText('Custom T-Shirt Design', centerX, height - 20);
}


function downloadScreenshot() {
    if (!canvas || customizations.length === 0) {
        showToast('❌ No customizations to download', false);
        return;
    }
    
    // Use html2canvas library instead
    showToast('📸 Preparing screenshot... Please wait', true);
    
    // Import html2canvas dynamically
    if (!window.html2canvas) {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
        script.onload = function() {
            captureDesignScreenshot();
        };
        document.head.appendChild(script);
    } else {
        captureDesignScreenshot();
    }
}


function showToast(message, isSuccess = true) {
    Toastify({
        text: message,
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: isSuccess ? "#28a745" : "#dc3545",
        stopOnFocus: true,
        onClick: function(){}
    }).showToast();
}
</script>
</body>
</html>