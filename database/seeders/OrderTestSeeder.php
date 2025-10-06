<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;
use App\Models\UserAddress;

class OrderTestSeeder extends Seeder
{
    public function run()
    {
        // Get the first user (assuming you have a user created)
        $user = User::first();
        
        if (!$user) {
            $this->command->error('No users found. Please create a user first.');
            return;
        }

        // Get or create an address for the user
        $address = UserAddress::firstOrCreate([
            'user_id' => $user->id,
        ], [
            'full_name' => $user->name,
            'phone' => '1234567890',
            'address_line_1' => '123 Test Street',
            'address_line_2' => 'Apartment 4B',
            'city_id' => 1, // Assuming city ID 1 exists
            'state_id' => 1, // Assuming state ID 1 exists
            'country_id' => 1, // Assuming country ID 1 exists
            'postal_code' => '12345',
            'address_type' => 'home',
            'is_default' => true,
        ]);

        // Get some products (assuming you have products)
        $products = Product::take(5)->get();
        
        if ($products->isEmpty()) {
            $this->command->error('No products found. Please seed products first.');
            return;
        }

        // Create test orders with different statuses
        $statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        
        foreach ($statuses as $index => $status) {
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'order_number' => 'ORD' . strtoupper(uniqid()),
                'total' => 1500 + ($index * 500),
                'discount' => $index * 100,
                'grand_total' => 1500 + ($index * 400),
                'status' => $status,
                'payment_method' => 'cod',
                'payment_status' => $status === 'delivered' ? 'paid' : 'pending',
                'created_at' => now()->subDays($index * 2),
                'updated_at' => now()->subDays($index),
            ]);

            // Add items to each order
            foreach ($products->take(rand(1, 3)) as $product) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => rand(1, 3),
                    'total' => $product->price * rand(1, 3),
                ]);
            }

            $this->command->info("Created {$status} order: {$order->order_number}");
        }

        $this->command->info('Test orders created successfully!');
    }
}