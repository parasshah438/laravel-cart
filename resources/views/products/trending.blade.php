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

    <style>
        .glide__slide .card {
            height: 100%;
        }
        
        .glide__slide .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .glide__slide .card-img-top {
                height: 150px;
            }
            
            .card-body {
                padding: 0.5rem !important;
            }
            
            .card-title {
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 576px) {
            .glide__slide .card-img-top {
                height: 180px;
            }
            
            .container {
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>
@if($trendingProducts->count())
<div class="container mt-4">
    <h5>Trending Products</h5>
    <div class="glide"> 
        <div class="glide__track" data-glide-el="track">
            <ul class="glide__slides">
                @foreach($trendingProducts as $product)
                    <li class="glide__slide">
                        <div class="card">
                            <a href="{{ route('product.show', $product->slug) }}">
                                <img src="{{ $product->image }}" class="card-img-top" alt="{{ $product->name }}">
                            </a>
                            <div class="card-body p-2">
                                <h6 class="card-title mb-1">{{ Str::limit($product->name, 40) }}</h6>
                                <p class="card-text text-muted mb-0">₹{{ $product->price }}</p>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
        @if($trendingProducts->count() > 5)
        <!-- Optional arrows -->
        <div class="glide__arrows" data-glide-el="controls">
            <button class="glide__arrow glide__arrow--left" data-glide-dir="<">‹</button>
            <button class="glide__arrow glide__arrow--right" data-glide-dir=">">›</button>
        </div>
        @endif
    </div>
</div>
@else
<div class="container mt-4">
    <div class="alert alert-info" role="alert">
        No trending products available at the moment.
    </div>
</div>
@endif

<!-- Glide.js JS -->
<script src="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/glide.min.js"></script>

<script>
    new Glide('.glide', {
        type: 'carousel',
        perView: 5,
        focusAt: 'center',
        autoplay: 3000,
        animationDuration: 800,
        animationTimingFunc: 'ease-in-out',
        gap: 20,
        keyboard: true,
        touchRatio: 0.5,
        dragThreshold: 120,
        swipeThreshold: 80,
        breakpoints: {
            1200: { perView: 4, gap: 15 },
            992:  { perView: 3, gap: 15 },
            768:  { perView: 2, gap: 10 },
            576:  { perView: 1, gap: 10 },
            480:  { perView: 1, gap: 5 }
        }
    }).mount();
</script>

</body>
</html>
