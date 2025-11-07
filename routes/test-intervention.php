<?php

/*
|--------------------------------------------------------------------------
| Quick Intervention Image v3 Test
|--------------------------------------------------------------------------
|
| This route tests if Intervention Image v3 is working correctly
|
*/

Route::get('/test-intervention-image', function () {
    $results = [];
    
    try {
        // Test 1: Check if classes exist
        $results['classes_exist'] = [
            'ImageManager' => class_exists(\Intervention\Image\ImageManager::class),
            'GdDriver' => class_exists(\Intervention\Image\Drivers\Gd\Driver::class),
            'Image' => class_exists(\Intervention\Image\Image::class),
        ];
        
        // Test 2: Try to create ImageManager
        try {
            $manager = new \Intervention\Image\ImageManager(
                new \Intervention\Image\Drivers\Gd\Driver()
            );
            $results['manager_created'] = true;
            $results['manager_class'] = get_class($manager);
        } catch (\Exception $e) {
            $results['manager_created'] = false;
            $results['manager_error'] = $e->getMessage();
        }
        
        // Test 3: Try to create a simple image
        try {
            $manager = new \Intervention\Image\ImageManager(
                new \Intervention\Image\Drivers\Gd\Driver()
            );
            $image = $manager->create(100, 100)->fill('ff0000');
            $results['image_created'] = true;
            $results['image_width'] = $image->width();
            $results['image_height'] = $image->height();
        } catch (\Exception $e) {
            $results['image_created'] = false;
            $results['image_error'] = $e->getMessage();
        }
        
        // Test 4: Try to save an image
        try {
            $manager = new \Intervention\Image\ImageManager(
                new \Intervention\Image\Drivers\Gd\Driver()
            );
            $testPath = storage_path('app/intervention_test.jpg');
            $image = $manager->create(100, 100)->fill('00ff00');
            $image->toJpeg(80)->save($testPath);
            
            $results['image_saved'] = file_exists($testPath);
            $results['saved_file_size'] = file_exists($testPath) ? filesize($testPath) : 0;
            
            // Clean up
            if (file_exists($testPath)) {
                unlink($testPath);
            }
        } catch (\Exception $e) {
            $results['image_saved'] = false;
            $results['save_error'] = $e->getMessage();
        }
        
    } catch (\Exception $e) {
        $results['general_error'] = $e->getMessage();
    }
    
    return response()->json($results, 200, [], JSON_PRETTY_PRINT);
    
})->name('test.intervention.image');