<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SEO Routes
|--------------------------------------------------------------------------
|
| Here are the routes for SEO-related functionality like sitemaps,
| robots.txt, and other search engine optimization features.
|
*/

// Sitemap routes
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// SEO-friendly product routes
Route::get('/products', [App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
Route::get('/products/search', [App\Http\Controllers\ProductController::class, 'search'])->name('products.search');
Route::get('/product/{slug}', [App\Http\Controllers\ProductController::class, 'show'])->name('products.show');

// SEO-friendly category routes
Route::get('/categories', [App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
Route::get('/category/{slug}', [App\Http\Controllers\CategoryController::class, 'show'])->name('categories.show');

// Manifest for PWA
Route::get('/site.webmanifest', function () {
    return response()->json([
        'name' => config('app.name'),
        'short_name' => config('app.name'),
        'description' => 'Premium online shopping experience',
        'start_url' => '/',
        'display' => 'standalone',
        'background_color' => '#ffffff',
        'theme_color' => '#667eea',
        'icons' => [
            [
                'src' => '/images/icon-192x192.png',
                'sizes' => '192x192',
                'type' => 'image/png'
            ],
            [
                'src' => '/images/icon-512x512.png',
                'sizes' => '512x512',
                'type' => 'image/png'
            ]
        ]
    ]);
})->name('manifest');