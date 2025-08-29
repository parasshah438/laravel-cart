<?php

namespace App\Http\Controllers;

use App\Services\SEOService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    private $seoService;

    public function __construct(SEOService $seoService)
    {
        $this->seoService = $seoService;
    }

    /**
     * Generate and return XML sitemap
     */
    public function index()
    {
        $sitemap = $this->seoService->generateSitemap();

        return response($sitemap, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600', // 1 hour
        ]);
    }

    /**
     * Generate robots.txt
     */
    public function robots()
    {
        $robots = "User-agent: *\n";
        $robots .= "Allow: /\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /api/\n";
        $robots .= "Disallow: /cart/\n";
        $robots .= "Disallow: /checkout/\n";
        $robots .= "Disallow: /profile/\n";
        $robots .= "Disallow: /search?*\n";
        $robots .= "\n";
        $robots .= "Sitemap: " . url('/sitemap.xml') . "\n";

        return response($robots, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'public, max-age=86400', // 24 hours
        ]);
    }
}