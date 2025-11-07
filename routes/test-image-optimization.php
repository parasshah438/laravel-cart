<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Image Optimization Test Route
|--------------------------------------------------------------------------
|
| This route is for testing the spatie/image-optimizer implementation
| Remove this route in production
|
*/

Route::get('/test-image-optimization', function () {
    
    // Check if spatie/image-optimizer is installed
    $optimizerExists = class_exists(\Spatie\ImageOptimizer\OptimizerChainFactory::class);
    
    // Check installed optimizers and their binaries
    $installedOptimizers = [];
    $availableOptimizers = [];
    
    if ($optimizerExists) {
        try {
            $optimizerChain = \Spatie\ImageOptimizer\OptimizerChainFactory::create();
            $optimizers = $optimizerChain->getOptimizers();
            
            foreach ($optimizers as $optimizer) {
                $className = get_class($optimizer);
                $binaryName = 'N/A';
                $canHandle = 'Unknown';
                $binaryExists = false;
                
                // Get binary name if method exists
                if (method_exists($optimizer, 'binaryName')) {
                    $binaryName = $optimizer->binaryName();
                    
                    // Check if binary exists in PATH (simple check)
                    $checkCommand = PHP_OS_FAMILY === 'Windows' ? "where {$binaryName} 2>nul" : "which {$binaryName} 2>/dev/null";
                    $output = shell_exec($checkCommand);
                    $binaryExists = !empty(trim($output));
                }
                
                // Test canHandle with proper Image object for newer versions
                try {
                    if (class_exists('\Spatie\ImageOptimizer\Image')) {
                        // Create a minimal test image if it doesn't exist
                        $testImagePath = storage_path('app/test_image_optimizer.jpg');
                        
                        if (!file_exists($testImagePath)) {
                            try {
                                // Create a simple test image using GD directly as fallback
                                $width = 100;
                                $height = 100;
                                $image = imagecreatetruecolor($width, $height);
                                $white = imagecolorallocate($image, 255, 255, 255);
                                imagefill($image, 0, 0, $white);
                                
                                // Save as JPEG
                                imagejpeg($image, $testImagePath, 80);
                                imagedestroy($image);
                                
                                // If that fails, try Intervention Image v3
                                if (!file_exists($testImagePath)) {
                                    $manager = new \Intervention\Image\ImageManager(
                                        new \Intervention\Image\Drivers\Gd\Driver()
                                    );
                                    $testImage = $manager->create(100, 100)->fill('ffffff');
                                    $testImage->toJpeg(80)->save($testImagePath);
                                }
                            } catch (\Exception $e) {
                                // Fallback to favicon or any existing image
                                $testImagePath = public_path('favicon.ico');
                                if (!file_exists($testImagePath)) {
                                    $testImagePath = null;
                                }
                            }
                        }
                        
                        if ($testImagePath && file_exists($testImagePath)) {
                            $testImage = new \Spatie\ImageOptimizer\Image($testImagePath);
                            $canHandle = $optimizer->canHandle($testImage) ? 'Yes' : 'No';
                        } else {
                            $canHandle = 'No test image available';
                        }
                    }
                } catch (\Exception $e) {
                    $canHandle = 'Test failed';
                }
                
                $availableOptimizers[] = [
                    'class' => $className,
                    'can_handle' => $canHandle,
                    'binary_available' => $binaryName,
                    'binary_exists' => $binaryExists,
                    'status' => $binaryExists && $canHandle === 'Yes' ? 'Working' : 'Limited'
                ];
            }
        } catch (\Exception $e) {
            $availableOptimizers = ['error' => $e->getMessage()];
        }
    }
    
    // Test ImageOptimizer helper
    $helperExists = class_exists(\App\Helpers\ImageOptimizer::class);
    
    // Test sample directories
    $directories = [
        'reviews' => storage_path('app/public/reviews'),
        'products' => storage_path('app/public/products'),
        'categories' => storage_path('app/public/categories'),
        'customizations' => storage_path('app/public/customizations'),
        'test-uploads' => storage_path('app/public/test-uploads'),
    ];
    
    $directoryStatus = [];
    foreach ($directories as $name => $path) {
        $directoryStatus[$name] = [
            'path' => $path,
            'exists' => is_dir($path),
            'writable' => is_writable($path),
            'files_count' => is_dir($path) ? count(glob($path . '/*')) : 0
        ];
    }
    
    // Get PHP upload settings
    $uploadSettings = [
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_execution_time' => ini_get('max_execution_time'),
        'memory_limit' => ini_get('memory_limit'),
        'file_uploads' => ini_get('file_uploads') ? 'Enabled' : 'Disabled',
        'max_file_uploads' => ini_get('max_file_uploads'),
    ];
    
    return view('test-image-optimization', [
        'optimizer_exists' => $optimizerExists,
        'available_optimizers' => $availableOptimizers,
        'helper_exists' => $helperExists,
        'directory_status' => $directoryStatus,
        'upload_settings' => $uploadSettings,
        'intervention_image_version' => class_exists(\Intervention\Image\ImageManager::class) ? 'Available (v3.x)' : 'Not Available',
    ]);
    
})->name('test.image.optimization');