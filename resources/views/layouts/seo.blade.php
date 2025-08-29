<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- SEO Meta Tags --}}
    <title>{{ $seo['title'] ?? config('app.name') }}</title>
    <meta name="description" content="{{ $seo['description'] ?? 'Default description' }}">
    <meta name="keywords" content="{{ $seo['keywords'] ?? '' }}">
    <meta name="author" content="{{ config('app.name') }}">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    
    {{-- Canonical URL --}}
    <link rel="canonical" href="{{ $seo['canonical'] ?? url()->current() }}">
    
    {{-- Open Graph Meta Tags --}}
    <meta property="og:locale" content="{{ app()->getLocale() }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $seo['og_title'] ?? $seo['title'] ?? config('app.name') }}">
    <meta property="og:description" content="{{ $seo['og_description'] ?? $seo['description'] ?? 'Default description' }}">
    <meta property="og:url" content="{{ $seo['og_url'] ?? url()->current() }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:image" content="{{ $seo['og_image'] ?? asset('images/default-og.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:type" content="image/jpeg">
    
    {{-- Twitter Card Meta Tags --}}
    <meta name="twitter:card" content="{{ $seo['twitter_card'] ?? 'summary_large_image' }}">
    <meta name="twitter:site" content="@{{ config('app.twitter_handle', 'yourhandle') }}">
    <meta name="twitter:title" content="{{ $seo['og_title'] ?? $seo['title'] ?? config('app.name') }}">
    <meta name="twitter:description" content="{{ $seo['og_description'] ?? $seo['description'] ?? 'Default description' }}">
    <meta name="twitter:image" content="{{ $seo['og_image'] ?? asset('images/default-og.jpg') }}">
    
    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    
    {{-- Preconnect to external domains --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    
    {{-- DNS Prefetch --}}
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="//www.google-analytics.com">
    
    {{-- Preload critical resources --}}
    <link rel="preload" href="{{ asset('css/app.css') }}" as="style">
    <link rel="preload" href="{{ asset('js/app.js') }}" as="script">
    
    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    {{-- Custom CSS --}}
    @vite(['resources/css/app.scss'])
    
    {{-- Additional CSS --}}
    @stack('styles')
    
    {{-- Schema.org Structured Data --}}
    @if(isset($seo['schema']))
    <script type="application/ld+json">
        {!! $seo['schema'] !!}
    </script>
    @endif
    
    {{-- Breadcrumbs Schema --}}
    @if(isset($breadcrumbs))
    <script type="application/ld+json">
        {!! $breadcrumbs !!}
    </script>
    @endif
    
    {{-- Google Analytics --}}
    @if(config('services.google_analytics.tracking_id'))
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google_analytics.tracking_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ config('services.google_analytics.tracking_id') }}');
    </script>
    @endif
    
    {{-- Google Search Console --}}
    @if(config('services.google_search_console.verification'))
    <meta name="google-site-verification" content="{{ config('services.google_search_console.verification') }}">
    @endif
    
    {{-- Additional head content --}}
    @stack('head')
</head>

<body class="@yield('body-class')">
    {{-- Skip to main content for accessibility --}}
    <a class="visually-hidden-focusable" href="#main-content">Skip to main content</a>
    
    {{-- Header --}}
    @include('partials.header')
    
    {{-- Breadcrumbs --}}
    @if(isset($showBreadcrumbs) && $showBreadcrumbs)
        @include('partials.breadcrumbs')
    @endif
    
    {{-- Main Content --}}
    <main id="main-content" role="main">
        @yield('content')
    </main>
    
    {{-- Footer --}}
    @include('partials.footer')
    
    {{-- Bootstrap 5 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    {{-- Custom JS --}}
    @vite(['resources/js/app.js'])
    
    {{-- Additional Scripts --}}
    @stack('scripts')
    
    {{-- Performance monitoring --}}
    <script>
        // Page load performance
        window.addEventListener('load', function() {
            setTimeout(function() {
                const perfData = performance.getEntriesByType('navigation')[0];
                if (perfData && perfData.loadEventEnd > 0) {
                    const loadTime = perfData.loadEventEnd - perfData.fetchStart;
                    console.log('Page load time:', Math.round(loadTime), 'ms');
                    
                    // Send to analytics if needed
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'page_load_time', {
                            'event_category': 'Performance',
                            'event_label': window.location.pathname,
                            'value': Math.round(loadTime)
                        });
                    }
                }
            }, 0);
        });
        
        // Core Web Vitals
        if ('web-vital' in window) {
            import('https://unpkg.com/web-vitals@3/dist/web-vitals.js').then(({getCLS, getFID, getFCP, getLCP, getTTFB}) => {
                getCLS(console.log);
                getFID(console.log);
                getFCP(console.log);
                getLCP(console.log);
                getTTFB(console.log);
            });
        }
    </script>
    
    {{-- Service Worker for PWA --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('SW registered: ', registration);
                    })
                    .catch(function(registrationError) {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }
    </script>
</body>
</html>