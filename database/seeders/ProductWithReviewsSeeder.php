<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Review;

class ProductWithReviewsSeeder extends Seeder
{
    public function run()
    {
        // Create some sample categories if they don't exist
        $category = Category::firstOrCreate([
            'name' => 'Clothing',
            'slug' => 'clothing'
        ]);

        // Create some sample products if they don't exist
        $products = [
            [
                'name' => 'Premium Cotton T-Shirt',
                'slug' => 'premium-cotton-t-shirt',
                'description' => 'High-quality cotton t-shirt with comfortable fit and premium fabric.',
                'price' => 29.99,
                'category_id' => $category->id,
                'status' => 'active'
            ],
            [
                'name' => 'Denim Jeans Classic Fit',
                'slug' => 'denim-jeans-classic-fit',
                'description' => 'Classic fit denim jeans made from premium denim fabric.',
                'price' => 79.99,
                'category_id' => $category->id,
                'status' => 'active'
            ],
            [
                'name' => 'Wireless Bluetooth Headphones',
                'slug' => 'wireless-bluetooth-headphones',
                'description' => 'High-quality wireless headphones with noise cancellation.',
                'price' => 149.99,
                'category_id' => $category->id,
                'status' => 'active'
            ]
        ];

        foreach ($products as $productData) {
            $product = Product::firstOrCreate(
                ['slug' => $productData['slug']],
                $productData
            );

            echo "Created/Found product: {$product->name} (ID: {$product->id})\n";
        }

        // Create a test user if it doesn't exist
        $user = User::firstOrCreate([
            'email' => 'reviewer@test.com'
        ], [
            'name' => 'Test Reviewer',
            'password' => bcrypt('password'),
            'email_verified_at' => now()
        ]);

        echo "Product reviews can be viewed at:\n";
        foreach (Product::all() as $product) {
            echo "- http://127.0.0.1:8003/product/{$product->id}/reviews ({$product->name})\n";
        }
    }
}
