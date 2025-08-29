<?php

namespace App\Http\Controllers\Traits;

use App\Services\SEOService;
use Illuminate\Support\Facades\View;

trait SEOTrait
{
    protected $seoService;

    /**
     * Initialize SEO service
     */
    public function initializeSEO()
    {
        $this->seoService = app(SEOService::class);
    }

    /**
     * Set SEO data for view
     */
    protected function setSEO($page, $data = [], $breadcrumbs = null)
    {
        if (!$this->seoService) {
            $this->initializeSEO();
        }

        $seoData = $this->seoService->generateMetaTags($page, $data);
        
        // Share SEO data with all views
        View::share('seo', $seoData);
        
        // Share breadcrumbs if provided
        if ($breadcrumbs) {
            $breadcrumbSchema = $this->seoService->generateBreadcrumbs($breadcrumbs);
            View::share('breadcrumbs', $breadcrumbSchema);
            View::share('showBreadcrumbs', true);
        }

        return $seoData;
    }

    /**
     * Generate SEO-friendly URL slug
     */
    protected function generateSlug($title, $model = null, $id = null)
    {
        $slug = \Str::slug($title);
        
        if ($model && class_exists($model)) {
            $count = 1;
            $originalSlug = $slug;
            
            while (true) {
                $query = $model::where('slug', $slug);
                
                if ($id) {
                    $query->where('id', '!=', $id);
                }
                
                if (!$query->exists()) {
                    break;
                }
                
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
        }
        
        return $slug;
    }

    /**
     * Optimize images for SEO
     */
    protected function optimizeImageForSEO($imagePath, $alt = '', $title = '')
    {
        if (!$this->seoService) {
            $this->initializeSEO();
        }

        return $this->seoService->optimizeImage($imagePath, $alt, $title);
    }

    /**
     * Generate product breadcrumbs
     */
    protected function generateProductBreadcrumbs($product)
    {
        $breadcrumbs = [
            ['name' => 'Home', 'url' => route('front.index')]
        ];

        if ($product->category) {
            $breadcrumbs[] = [
                'name' => $product->category->name,
                'url' => route('categories.show', $product->category->slug)
            ];
        }

        $breadcrumbs[] = [
            'name' => $product->name,
            'url' => route('products.show', $product->slug)
        ];

        return $breadcrumbs;
    }

    /**
     * Generate category breadcrumbs
     */
    protected function generateCategoryBreadcrumbs($category)
    {
        $breadcrumbs = [
            ['name' => 'Home', 'url' => route('front.index')],
            ['name' => 'Categories', 'url' => route('categories.index')]
        ];

        if ($category->parent) {
            $breadcrumbs[] = [
                'name' => $category->parent->name,
                'url' => route('categories.show', $category->parent->slug)
            ];
        }

        $breadcrumbs[] = [
            'name' => $category->name,
            'url' => route('categories.show', $category->slug)
        ];

        return $breadcrumbs;
    }

    /**
     * Add structured data for products
     */
    protected function addProductStructuredData($products)
    {
        if (!is_iterable($products)) {
            $products = [$products];
        }

        $structuredData = [];
        
        foreach ($products as $product) {
            $structuredData[] = [
                "@type" => "Product",
                "name" => $product->name,
                "description" => strip_tags($product->description),
                "image" => $product->image_url,
                "sku" => $product->sku ?? $product->id,
                "offers" => [
                    "@type" => "Offer",
                    "url" => route('products.show', $product->slug),
                    "priceCurrency" => "USD",
                    "price" => $product->price,
                    "availability" => $product->stock > 0 ? "https://schema.org/InStock" : "https://schema.org/OutOfStock"
                ]
            ];
        }

        if (count($structuredData) === 1) {
            return $structuredData[0];
        }

        return [
            "@context" => "https://schema.org/",
            "@type" => "ItemList",
            "itemListElement" => array_map(function($item, $index) {
                return [
                    "@type" => "ListItem",
                    "position" => $index + 1,
                    "item" => $item
                ];
            }, $structuredData, array_keys($structuredData))
        ];
    }

    /**
     * Set page cache headers
     */
    protected function setCacheHeaders($minutes = 60)
    {
        $response = response();
        $response->header('Cache-Control', 'public, max-age=' . ($minutes * 60));
        $response->header('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + ($minutes * 60)));
        
        return $response;
    }

    /**
     * Generate meta robots tag
     */
    protected function setRobotsTag($index = true, $follow = true, $additional = [])
    {
        $robots = [];
        
        $robots[] = $index ? 'index' : 'noindex';
        $robots[] = $follow ? 'follow' : 'nofollow';
        
        if (!empty($additional)) {
            $robots = array_merge($robots, $additional);
        }

        View::share('robots', implode(', ', $robots));
    }
}