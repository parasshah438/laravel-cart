<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Illuminate\Http\UploadedFile;

class ImageOptimizer
{
    /**
     * Get Image Manager instance
     */
    private static function getImageManager()
    {
        return new ImageManager(new Driver());
    }
    /**
     * Optimize uploaded image using Spatie Image Optimizer
     */
    public static function optimizeUploadedImage(UploadedFile $file, string $directory = 'uploads', array $options = [])
    {
        // Memory management for large files
        $originalMemoryLimit = ini_get('memory_limit');
        $fileSize = $file->getSize();
        
        // Increase memory limit for files larger than 2MB
        if ($fileSize > 2 * 1024 * 1024) {
            ini_set('memory_limit', '512M');
            set_time_limit(120);
        }
        
        $options = array_merge([
            'quality' => 85,
            'maxWidth' => 1200,
            'maxHeight' => 1200,
            'generateWebP' => true,
            'generateThumbnails' => true,
            'thumbnailSizes' => [150, 300, 600],
        ], $options);

        try {
            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = uniqid() . '_' . time();
            $originalPath = $directory . '/' . $filename . '.' . $extension;
            
            // Store the original file temporarily
            $tempPath = $file->storeAs($directory, $filename . '.' . $extension, 'public');
            $fullPath = storage_path('app/public/' . $tempPath);
            
            // Optimize the uploaded file using Spatie Image Optimizer
            $optimizerChain = OptimizerChainFactory::create();
            
            // Check if the file exists before optimizing
            if (!file_exists($fullPath)) {
                throw new \Exception('Uploaded file not found at: ' . $fullPath);
            }
            
            $optimizerChain->optimize($fullPath);
            
            // Further process with Intervention Image for resizing and format conversion
            $manager = self::getImageManager();
            $image = $manager->read($fullPath);
            
            // Resize if too large
            if ($image->width() > $options['maxWidth'] || $image->height() > $options['maxHeight']) {
                $image->scaleDown($options['maxWidth'], $options['maxHeight']);
                $image->toJpeg($options['quality'])->save($fullPath);
                
                // Re-optimize after resizing
                $optimizerChain->optimize($fullPath);
            }
            
            $results = [
                'original' => $tempPath,
                'optimized' => $tempPath,
            ];
            
            // Generate WebP version if requested
            if ($options['generateWebP']) {
                $webpPath = $directory . '/' . $filename . '.webp';
                $webpFullPath = storage_path('app/public/' . $webpPath);
                $image->toWebp($options['quality'])->save($webpFullPath);
                $optimizerChain->optimize($webpFullPath);
                $results['webp'] = $webpPath;
            }
            
            // Generate thumbnails if requested
            if ($options['generateThumbnails']) {
                $results['thumbnails'] = [];
                foreach ($options['thumbnailSizes'] as $size) {
                    $thumbPath = $directory . '/' . $filename . '_' . $size . '.' . $extension;
                    $thumbFullPath = storage_path('app/public/' . $thumbPath);
                    
                    $thumbImage = $manager->read($fullPath);
                    $thumbImage->scaleDown($size, $size);
                    $thumbImage->toJpeg($options['quality'])->save($thumbFullPath);
                    $optimizerChain->optimize($thumbFullPath);
                    
                    $results['thumbnails'][$size] = $thumbPath;
                }
            }
            
            // Restore original memory limit
            ini_set('memory_limit', $originalMemoryLimit);
            
            return $results;
            
        } catch (\Exception $e) {
            // Restore original memory limit
            ini_set('memory_limit', $originalMemoryLimit);
            
            \Log::error('Image optimization failed: ' . $e->getMessage());
            // Fallback: store without optimization
            $path = $file->store($directory, 'public');
            return ['original' => $path, 'optimized' => $path];
        }
    }

    /**
     * Optimize existing image file
     */
    public static function optimizeExistingImage(string $filePath, array $options = [])
    {
        $options = array_merge([
            'quality' => 85,
            'maxWidth' => 1200,
            'maxHeight' => 1200,
        ], $options);

        try {
            $fullPath = storage_path('app/public/' . $filePath);
            
            if (!file_exists($fullPath)) {
                \Log::error('Image file not found: ' . $fullPath);
                return false;
            }
            
            // Create backup
            $backupPath = $fullPath . '.backup';
            copy($fullPath, $backupPath);
            
            // Optimize with Spatie Image Optimizer
            $optimizerChain = OptimizerChainFactory::create();
            
            // Verify file exists
            if (!file_exists($fullPath)) {
                \Log::error('Image file not found for optimization: ' . $fullPath);
                return false;
            }
            
            $optimizerChain->optimize($fullPath);
            
            // Further optimization with Intervention Image
            $manager = self::getImageManager();
            $image = $manager->read($fullPath);
            
            // Resize if too large
            if ($image->width() > $options['maxWidth'] || $image->height() > $options['maxHeight']) {
                $image->scaleDown($options['maxWidth'], $options['maxHeight']);
                $image->toJpeg($options['quality'])->save($fullPath);
                
                // Re-optimize after resizing
                $optimizerChain->optimize($fullPath);
            }
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Image optimization failed for existing file: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Optimize and convert image to WebP
     */
    public static function optimizeImage($imagePath, $quality = 80, $maxWidth = 1200)
    {
        try {
            // Get the original image
            $manager = self::getImageManager();
            $image = $manager->read($imagePath);
            
            // Resize if too large
            if ($image->width() > $maxWidth) {
                $image->scaleDown($maxWidth);
            }
            
            // Get file info
            $pathInfo = pathinfo($imagePath);
            $filename = $pathInfo['filename'];
            $directory = $pathInfo['dirname'];
            
            // Save optimized original format
            $image->toJpeg($quality)->save($imagePath);
            
            // Optimize with Spatie Image Optimizer
            $optimizerChain = OptimizerChainFactory::create();
            
            // Verify file exists
            if (!file_exists($imagePath)) {
                \Log::error('Image file not found for optimization: ' . $imagePath);
                return ['original' => $imagePath];
            }
            
            $optimizerChain->optimize($imagePath);
            
            // Create WebP version
            $webpPath = $directory . '/' . $filename . '.webp';
            $image->toWebp($quality)->save($webpPath);
            $optimizerChain->optimize($webpPath);
            
            // Create optimized JPEG fallback
            $jpegPath = $directory . '/' . $filename . '_optimized.jpg';
            $image->toJpeg($quality)->save($jpegPath);
            $optimizerChain->optimize($jpegPath);
            
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