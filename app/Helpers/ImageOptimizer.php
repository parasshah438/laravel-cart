<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageOptimizer
{
    /**
     * Optimize and convert image to WebP
     */
    public static function optimizeImage($imagePath, $quality = 80, $maxWidth = 1200)
    {
        try {
            // Get the original image
            $image = Image::make($imagePath);
            
            // Resize if too large
            if ($image->width() > $maxWidth) {
                $image->resize($maxWidth, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // Get file info
            $pathInfo = pathinfo($imagePath);
            $filename = $pathInfo['filename'];
            $directory = $pathInfo['dirname'];
            
            // Create WebP version
            $webpPath = $directory . '/' . $filename . '.webp';
            $image->encode('webp', $quality)->save($webpPath);
            
            // Create optimized JPEG fallback
            $jpegPath = $directory . '/' . $filename . '_optimized.jpg';
            $image->encode('jpg', $quality)->save($jpegPath);
            
            return [
                'webp' => $webpPath,
                'jpeg' => $jpegPath,
                'original' => $imagePath
            ];
            
        } catch (\Exception $e) {
            \Log::error('Image optimization failed: ' . $e->getMessage());
            return ['original' => $imagePath];
        }
    }
    
    /**
     * Generate responsive image HTML
     */
    public static function generateResponsiveImage($imagePath, $alt = '', $class = '', $sizes = [])
    {
        $defaultSizes = [
            'sm' => 576,
            'md' => 768,
            'lg' => 992,
            'xl' => 1200
        ];
        
        $sizes = array_merge($defaultSizes, $sizes);
        $pathInfo = pathinfo($imagePath);
        $filename = $pathInfo['filename'];
        $directory = $pathInfo['dirname'];
        
        // Generate srcset
        $srcset = [];
        foreach ($sizes as $breakpoint => $width) {
            $resizedPath = $directory . '/' . $filename . '_' . $width . '.webp';
            if (file_exists(public_path($resizedPath))) {
                $srcset[] = $resizedPath . ' ' . $width . 'w';
            }
        }
        
        $html = '<picture>';
        
        // WebP source
        if (!empty($srcset)) {
            $html .= '<source type="image/webp" srcset="' . implode(', ', $srcset) . '">';
        }
        
        // Fallback image
        $html .= '<img src="' . $imagePath . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . '" loading="lazy" decoding="async">';
        $html .= '</picture>';
        
        return $html;
    }
    
    /**
     * Generate image with lazy loading
     */
    public static function lazyImage($src, $alt = '', $class = '', $placeholder = null)
    {
        $placeholder = $placeholder ?: 'data:image/svg+xml;base64,' . base64_encode(
            '<svg width="400" height="300" xmlns="http://www.w3.org/2000/svg"><rect width="100%" height="100%" fill="#f8f9fa"/><text x="50%" y="50%" text-anchor="middle" dy=".3em" fill="#6c757d">Loading...</text></svg>'
        );
        
        return '<img src="' . $placeholder . '" data-src="' . $src . '" alt="' . htmlspecialchars($alt) . '" class="lazy ' . $class . '" loading="lazy" decoding="async">';
    }
}