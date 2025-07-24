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
<div class="container py-4">
    {{-- Page title --}}
    <h1 class="mb-4 h3">{{ $product->name }}</h1>

    <div class="row g-4">
        {{-- Left: Product Image --}}
        <div class="col-md-6">
            <div class="border rounded p-3 text-center bg-white shadow-sm">
                <img src="{{ $product->image }}" class="img-fluid" alt="{{ $product->name }}">
            </div>
        </div>

        {{-- Right: Product Info --}}
        <div class="col-md-6">
            <div class="bg-white p-4 rounded shadow-sm">
                {{-- Price --}}
                <h4 class="text-success fw-bold">₹{{ number_format($product->price, 2) }}</h4>

                {{-- Description --}}
                <p class="text-muted mt-3">{{ $product->description }}</p>

                {{-- Buttons --}}
                <div class="d-grid gap-2 mt-4">
                    <form action="{{ route('cart.ajaxAdd', $product->id) }}" method="POST">
                        @csrf
                        <button class="btn btn-primary btn-lg w-100" type="submit">Add to Cart</button>
                    </form>

                    <a href="" class="btn btn-warning btn-lg w-100">Buy Now</a>

                    <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">
                           
                            <i class="bi bi-heart"></i> {{ auth()->check() && $wishlistProductIds->contains($product->id) ? 'Remove from Wishlist' : 'Add to Wishlist' }}
                        </button>
                    </form>

                    <a href="#inquiryModal" class="btn btn-outline-secondary w-100" data-bs-toggle="modal">
                        Contact for Inquiry
                    </a>
                </div>

                {{-- Rating (placeholder for future use) --}}
                <div class="mt-4">
                    <strong>Rating:</strong>
                    ★★★★☆ (Coming Soon)
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
                    <h5 class="modal-title" id="inquiryModalLabel">Product Inquiry</h5>
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
                        <textarea name="message" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Send Inquiry</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Similar Products --}}
    @if($similarProducts->count())
    <div class="mt-5">
        <h5>Similar Products</h5>
        <div class="glide" id="similarProductsSlider">
            <div class="glide__track" data-glide-el="track">
                <ul class="glide__slides">
                    @foreach($similarProducts as $simProduct)
                        <li class="glide__slide">
                            <a href="{{ route('product.show', $simProduct->id) }}" class="text-decoration-none text-dark">
                                <div class="card h-100 shadow-sm">
                                    <img src="{{ $simProduct->image }}" class="card-img-top" alt="{{ $simProduct->name }}">
                                    <div class="card-body p-2">
                                        <h6 class="card-title text-truncate">{{ $simProduct->name }}</h6>
                                        <p class="text-success mb-0">₹{{ number_format($simProduct->price, 2) }}</p>
                                    </div>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="glide__arrows" data-glide-el="controls">
                <button class="glide__arrow glide__arrow--left" data-glide-dir="<">&lt;</button>
                <button class="glide__arrow glide__arrow--right" data-glide-dir=">">&gt;</button>
            </div>
        </div>
    </div>
    @endif
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    new Glide('#similarProductsSlider', {
        type: 'carousel',
        perView: 5,
        gap: 16,
        autoplay: 3000,
        keyboard: true,
        breakpoints: {
            992: { perView: 3 },
            768: { perView: 2 },
            576: { perView: 1 }
        }
    }).mount();
</script>
</body>
</html>