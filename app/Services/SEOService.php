<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SEOService
{
    /**
     * Generate SEO meta tags for pages
     */
    public function generateMetaTags($page, $data = [])
    {
        $seoData = $this->getSEOData($page, $data);
        
        return [
            'title' => $seoData['title'],
            'description' => $seoData['description'],
            'keywords' => $seoData['keywords'],
            'canonical' => $seoData['canonical'],
            'og_title' => $seoData['og_title'],
            'og_description' => $seoData['og_description'],
            'og_image' => $seoData['og_image'],
            'og_url' => $seoData['og_url'],
            'twitter_card' => $seoData['twitter_card'],
            'schema' => $this->generateSchemaMarkup($page, $data)
        ];
    }

    /**
     * Get SEO data based on page type
     */
    private function getSEOData($page, $data)
    {
        switch ($page) {
            case 'product':
                return $this->getProductSEO($data);
            case 'category':
                return $this->getCategorySEO($data);
            case 'home':
                return $this->getHomeSEO();
            case 'cart':
                return $this->getCartSEO();
            case 'checkout':
                return $this->getCheckoutSEO();
            default:
                return $this->getDefaultSEO($page, $data);
        }
    }

    /**
     * Product page SEO
     */
    private function getProductSEO($product)
    {
        $title = $product->name . ' - ' . config('app.name');
        $description = Str::limit(strip_tags($product->description), 155);
        $keywords = $this->generateKeywords($product->name, $product->category->name ?? '');
        
        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => route('products.show', $product->slug),
            'og_title' => $product->name,
            'og_description' => $description,
            'og_image' => $product->image_url ?? asset('images/default-product.jpg'),
            'og_url' => route('products.show', $product->slug),
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Category page SEO
     */
    private function getCategorySEO($category)
    {
        $title = $category->name . ' - Shop ' . config('app.name');
        $description = $category->description ?? "Shop the best {$category->name} products at " . config('app.name') . ". Free shipping on orders over $50.";
        
        return [
            'title' => $title,
            'description' => Str::limit($description, 155),
            'keywords' => $this->generateKeywords($category->name, 'shop', 'buy'),
            'canonical' => route('categories.show', $category->slug),
            'og_title' => $category->name,
            'og_description' => Str::limit($description, 155),
            'og_image' => $category->image_url ?? asset('images/default-category.jpg'),
            'og_url' => route('categories.show', $category->slug),
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Home page SEO
     */
    private function getHomeSEO()
    {
        return [
            'title' => config('app.name') . ' - Premium Online Shopping Experience',
            'description' => 'Discover amazing products at ' . config('app.name') . '. Fast shipping, easy returns, and excellent customer service. Shop now!',
            'keywords' => 'online shopping, ecommerce, products, fast shipping, ' . strtolower(config('app.name')),
            'canonical' => route('front.index'),
            'og_title' => config('app.name') . ' - Premium Online Shopping',
            'og_description' => 'Discover amazing products with fast shipping and easy returns.',
            'og_image' => asset('images/og-home.jpg'),
            'og_url' => route('front.index'),
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Cart page SEO
     */
    private function getCartSEO()
    {
        return [
            'title' => 'Shopping Cart - ' . config('app.name'),
            'description' => 'Review your items and proceed to checkout. Secure payment and fast shipping guaranteed.',
            'keywords' => 'shopping cart, checkout, secure payment',
            'canonical' => route('cart.view'),
            'og_title' => 'Shopping Cart',
            'og_description' => 'Review your items and proceed to checkout.',
            'og_image' => asset('images/cart-og.jpg'),
            'og_url' => route('cart.view'),
            'twitter_card' => 'summary'
        ];
    }

    /**
     * Checkout page SEO
     */
    private function getCheckoutSEO()
    {
        return [
            'title' => 'Secure Checkout - ' . config('app.name'),
            'description' => 'Complete your purchase with our secure checkout process. Multiple payment options available.',
            'keywords' => 'secure checkout, payment, order',
            'canonical' => route('checkout.index'),
            'og_title' => 'Secure Checkout',
            'og_description' => 'Complete your purchase securely.',
            'og_image' => asset('images/checkout-og.jpg'),
            'og_url' => route('checkout.index'),
            'twitter_card' => 'summary'
        ];
    }

    /**
     * Default SEO for other pages
     */
    private function getDefaultSEO($page, $data)
    {
        $title = ucfirst(str_replace('-', ' ', $page)) . ' - ' . config('app.name');
        
        return [
            'title' => $title,
            'description' => 'Explore ' . config('app.name') . ' for the best online shopping experience.',
            'keywords' => strtolower($page) . ', ' . strtolower(config('app.name')),
            'canonical' => url()->current(),
            'og_title' => $title,
            'og_description' => 'Explore ' . config('app.name') . ' for the best online shopping experience.',
            'og_image' => asset('images/default-og.jpg'),
            'og_url' => url()->current(),
            'twitter_card' => 'summary'
        ];
    }

    /**
     * Generate keywords from text
     */
    private function generateKeywords(...$texts)
    {
        $keywords = [];
        foreach ($texts as $text) {
            $words = explode(' ', strtolower($text));
            $keywords = array_merge($keywords, $words);
        }
        
        // Remove common words and duplicates
        $stopWords = ['the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'a', 'an'];
        $keywords = array_diff(array_unique($keywords), $stopWords);
        
        return implode(', ', array_slice($keywords, 0, 10));
    }

    /**
     * Generate Schema.org markup
     */
    public function generateSchemaMarkup($page, $data)
    {
        switch ($page) {
            case 'product':
                return $this->getProductSchema($data);
            case 'category':
                return $this->getCategorySchema($data);
            case 'home':
                return $this->getOrganizationSchema();
            default:
                return $this->getWebsiteSchema();
        }
    }

    /**
     * Product Schema markup
     */
    private function getProductSchema($product)
    {
        $schema = [
            "@context" => "https://schema.org/",
            "@type" => "Product",
            "name" => $product->name,
            "description" => strip_tags($product->description),
            "image" => $product->image_url ?? asset('images/default-product.jpg'),
            "sku" => $product->sku ?? $product->id,
            "brand" => [
                "@type" => "Brand",
                "name" => $product->brand ?? config('app.name')
            ],
            "offers" => [
                "@type" => "Offer",
                "url" => route('products.show', $product->slug),
                "priceCurrency" => "USD",
                "price" => $product->price,
                "availability" => $product->stock > 0 ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
                "seller" => [
                    "@type" => "Organization",
                    "name" => config('app.name')
                ]
            ]
        ];

        // Add reviews if available
        if ($product->reviews && $product->reviews->count() > 0) {
            $schema["aggregateRating"] = [
                "@type" => "AggregateRating",
                "ratingValue" => $product->reviews->avg('rating'),
                "reviewCount" => $product->reviews->count()
            ];
        }

        return json_encode($schema, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Category Schema markup
     */
    private function getCategorySchema($category)
    {
        $schema = [
            "@context" => "https://schema.org/",
            "@type" => "CollectionPage",
            "name" => $category->name,
            "description" => $category->description ?? "Shop {$category->name} products",
            "url" => route('categories.show', $category->slug)
        ];

        return json_encode($schema, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Organization Schema markup
     */
    private function getOrganizationSchema()
    {
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "Organization",
            "name" => config('app.name'),
            "url" => config('app.url'),
            "logo" => asset('images/logo.png'),
            "contactPoint" => [
                "@type" => "ContactPoint",
                "telephone" => "+1-234-567-8900",
                "contactType" => "customer service",
                "availableLanguage" => "English"
            ],
            "sameAs" => [
                "https://www.facebook.com/yourpage",
                "https://www.twitter.com/yourhandle",
                "https://www.instagram.com/yourhandle"
            ]
        ];

        return json_encode($schema, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Website Schema markup
     */
    private function getWebsiteSchema()
    {
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "WebSite",
            "name" => config('app.name'),
            "url" => config('app.url'),
            "potentialAction" => [
                "@type" => "SearchAction",
                "target" => [
                    "@type" => "EntryPoint",
                    "urlTemplate" => route('products.search') . "?q={search_term_string}"
                ],
                "query-input" => "required name=search_term_string"
            ]
        ];

        return json_encode($schema, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Generate XML Sitemap
     */
    public function generateSitemap()
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Home page
        $sitemap .= $this->addSitemapUrl(route('front.index'), now(), 'daily', '1.0');

        // Categories
        $categories = \App\Models\Category::all();
        foreach ($categories as $category) {
            $sitemap .= $this->addSitemapUrl(
                route('categories.show', $category->slug),
                $category->updated_at,
                'weekly',
                '0.8'
            );
        }

        // Products
        $products = \App\Models\Product::where('status', 'active')->get();
        foreach ($products as $product) {
            $sitemap .= $this->addSitemapUrl(
                route('products.show', $product->slug),
                $product->updated_at,
                'weekly',
                '0.9'
            );
        }

        // Static pages
        $staticPages = [
            ['url' => '/about', 'priority' => '0.5'],
            ['url' => '/contact', 'priority' => '0.5'],
            ['url' => '/privacy-policy', 'priority' => '0.3'],
            ['url' => '/terms-of-service', 'priority' => '0.3'],
        ];

        foreach ($staticPages as $page) {
            $sitemap .= $this->addSitemapUrl(
                url($page['url']),
                now(),
                'monthly',
                $page['priority']
            );
        }

        $sitemap .= '</urlset>';

        // Save sitemap
        Storage::disk('public')->put('sitemap.xml', $sitemap);

        return $sitemap;
    }

    /**
     * Add URL to sitemap
     */
    private function addSitemapUrl($url, $lastmod, $changefreq, $priority)
    {
        return "  <url>\n" .
               "    <loc>" . htmlspecialchars($url) . "</loc>\n" .
               "    <lastmod>" . $lastmod->format('Y-m-d\TH:i:s\Z') . "</lastmod>\n" .
               "    <changefreq>{$changefreq}</changefreq>\n" .
               "    <priority>{$priority}</priority>\n" .
               "  </url>\n";
    }

    /**
     * Optimize images for SEO
     */
    public function optimizeImage($imagePath, $alt = '', $title = '')
    {
        return [
            'src' => $imagePath,
            'alt' => $alt ?: 'Image from ' . config('app.name'),
            'title' => $title ?: $alt,
            'loading' => 'lazy',
            'decoding' => 'async'
        ];
    }

    /**
     * Generate breadcrumbs
     */
    public function generateBreadcrumbs($items)
    {
        $breadcrumbs = [];
        $position = 1;

        foreach ($items as $item) {
            $breadcrumbs[] = [
                "@type" => "ListItem",
                "position" => $position++,
                "name" => $item['name'],
                "item" => $item['url'] ?? null
            ];
        }

        $schema = [
            "@context" => "https://schema.org",
            "@type" => "BreadcrumbList",
            "itemListElement" => $breadcrumbs
        ];

        return json_encode($schema, JSON_UNESCAPED_SLASHES);
    }
}