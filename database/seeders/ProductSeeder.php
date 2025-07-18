<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductStock;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        //Clear old data
        Product::truncate();
        ProductStock::truncate();

        //Products with basic info
        $products = [
            [
                'name' => 'Red T-Shirt',
                'price' => 499,
                'image' => 'https://placehold.co/400?text=Red+TeeShirt',
            ],
            [
                'name' => 'Blue Jeans',
                'price' => 1199,
                'image' => 'https://placehold.co/400?text=Blue+Jeans',
            ],
            [
                'name' => 'Sneakers',
                'price' => 2199,
                'image' => 'https://placehold.co/400?text=Sneakers',
            ],
            [
                'name' => 'Leather Jacket',
                'price' => 4999,
                'image' => 'https://placehold.co/400?text=Leather+Jacket',
            ],
            [
                'name' => 'Wool Sweater',
                'price' => 1599,
                'image' => 'https://placehold.co/400?text=Wool+Sweater',
            ],
            [
                'name' => 'Running Shoes',
                'price' => 2499,
                'image' => 'https://placehold.co/400?text=Running+Shoes',
            ],
            [
                'name' => 'Casual Shirt',
                'price' => 899,
                'image' => 'https://placehold.co/400?text=Casual+Shirt',
            ],
            [
                'name' => 'Formal Pants',
                'price' => 1299,
                'image' => 'https://placehold.co/400?text=Formal+Pants',
            ],
            [
                'name' => 'Winter Coat',
                'price' => 5999,
                'image' => 'https://placehold.co/400?text=Winter+Coat',
            ],
            [
                'name' => 'Sports Watch',
                'price' => 3499,
                'image' => 'https://placehold.co/400?text=Sports+Watch',
            ],
            [
                'name' => 'Sunglasses',
                'price' => 1299,
                'image' => 'https://placehold.co/400?text=Sunglasses',
            ],
            [
                'name' => 'Backpack',
                'price' => 899,
                'image' => 'https://placehold.co/400?text=Backpack',
            ],
            [
                'name' => 'Baseball Cap',
                'price' => 499,
                'image' => 'https://placehold.co/400?text=Baseball+Cap',
            ],
            [
                'name' => 'Flip Flops',
                'price' => 299,
                'image' => 'https://placehold.co/400?text=Flip+Flops',
            ],
            [
                'name' => 'Hiking Boots',
                'price' => 2999,
                'image' => 'https://placehold.co/400?text=Hiking+Boots',
            ],
            [
                'name' => 'Graphic Hoodie',
                'price' => 1799,
                'image' => 'https://placehold.co/400?text=Graphic+Hoodie',
            ],
            [
                'name' => 'Denim Jacket',
                'price' => 2499,
                'image' => 'https://placehold.co/400?text=Denim+Jacket',
            ],
            [
                'name' => 'Cargo Shorts',
                'price' => 899,
                'image' => 'https://placehold.co/400?text=Cargo+Shorts',
            ],
            [
                'name' => 'Wristband',
                'price' => 199,
                'image' => 'https://placehold.co/400?text=Wristband',
            ],
            [
                'name' => 'Beanie',
                'price' => 399,
                'image' => 'https://placehold.co/400?text=Beanie',
            ],
            [
                'name' => 'Running Shorts',
                'price' => 699,
                'image' => 'https://placehold.co/400?text=Running+Shorts',
            ],
            [
                'name' => 'Yoga Pants',
                'price' => 1299,
                'image' => 'https://placehold.co/400?text=Yoga+Pants',
            ],
            [
                'name' => 'Cycling Jersey',
                'price' => 1599,
                'image' => 'https://placehold.co/400?text=Cycling+Jersey',
            ],
            [
                'name' => 'Golf Polo',
                'price' => 899,
                'image' => 'https://placehold.co/400?text=Golf+Polo',
            ],
            [
                'name' => 'Swim Trunks',
                'price' => 699,
                'image' => 'https://placehold.co/400?text=Swim+Trunks',
            ],
            [   'name' => 'Tennis Racket',
                'price' => 3499,
                'image' => 'https://placehold.co/400?text=Tennis+Racket',
            ],
            [   'name' => 'Basketball',
                'price' => 999,
                'image' => 'https://placehold.co/400?text=Basketball',
            ],
            [
                'name' => 'Soccer Ball',
                'price' => 899,
                'image' => 'https://placehold.co/400?text=Soccer+Ball',
            ],
            [
                'name' => 'Volleyball',
                'price' => 799,
                'image' => 'https://placehold.co/400?text=Volleyball',
            ],
            [
                'name' => 'Badminton Racket',
                'price' => 1299,
                'image' => 'https://placehold.co/400?text=Badminton+Racket',
            ],
            [   'name' => 'Table Tennis Paddle',
                'price' => 499,
                'image' => 'https://placehold.co/400?text=Table+Tennis+Paddle',
            ],
            [
                'name' => 'Golf Balls',
                'price' => 399,
                'image' => 'https://placehold.co/400?text=Golf+Balls',
            ],
            [
                'name' => 'Fishing Rod',
                'price' => 2499,
                'image' => 'https://placehold.co/400?text=Fishing+Rod',
            ],
            [
                'name' => 'Camping Tent',
                'price' => 4999,
                'image' => 'https://placehold.co/400?text=Camping+Tent',
            ],
            [
                'name' => 'Hiking Backpack',
                'price' => 1999,
                'image' => 'https://placehold.co/400?text=Hiking+Backpack',
            ], 
            [
                'name' => 'Portable Grill',
                'price' => 3499,
                'image' => 'https://placehold.co/400?text=Portable+Grill',
            ],
            [
                'name' => 'Camping Chair',
                'price' => 799,
                'image' => 'https://placehold.co/400?text=Camping+Chair',
            ],
            [
                'name' => 'Outdoor Blanket',
                'price' => 499,
                'image' => 'https://placehold.co/400?text=Outdoor+Blanket',
            ],
            [
                'name' => 'Fishing Tackle Box',
                'price' => 899,
                'image' => 'https://placehold.co/400?text=Fishing+Tackle+Box',
            ],
            [
                'name' => 'Hiking Poles',
                'price' => 1299,
                'image' => 'https://placehold.co/400?text=Hiking+Poles',
            ],
            [
                'name' => 'Portable Power Bank',
                'price' => 599,
                'image' => 'https://placehold.co/400?text=Portable+Power+Bank',
            ],
            [    'name' => 'Travel Pillow',
                'price' => 399,
                'image' => 'https://placehold.co/400?text=Travel+Pillow',
            ],
            [
                'name' => 'Water Bottle',
                'price' => 299,
                'image' => 'https://placehold.co/400?text=Water+Bottle',
            ],
            [
                'name' => 'Insulated Cooler',
                'price' => 1999,
                'image' => 'https://placehold.co/400?text=Insulated+Cooler',
            ],
            [
                'name' => 'Multi-tool',
                'price' => 899,
                'image' => 'https://placehold.co/400?text=Multi-tool',
            ],
            [
                'name' => 'First Aid Kit',
                'price' => 799,
                'image' => 'https://placehold.co/400?text=First+Aid+Kit',
            ],
            [
                'name' => 'Portable Speaker',
                'price' => 1499,
                'image' => 'https://placehold.co/400?text=Portable+Speaker',
            ],
            [
                'name' => 'Bluetooth Headphones',
                'price' => 2499,
                'image' => 'https://placehold.co/400?text=Bluetooth+Headphones',
            ],
            [
                'name' => 'Smartphone Case',
                'price' => 499,
                'image' => 'https://placehold.co/400?text=Smartphone+Case',
            ],
            [
                'name' => 'Laptop Sleeve',
                'price' => 899,
                'image' => 'https://placehold.co/400?text=Laptop+Sleeve',
            ],
            [
                'name' => 'Wireless Charger',
                'price' => 1299,
                'image' => 'https://placehold.co/400?text=Wireless+Charger',
            ],
            [
                'name' => 'USB Flash Drive',
                'price' => 399,
                'image' => 'https://placehold.co/400?text=USB+Flash+Drive',
            ],
            [
                'name' => 'External Hard Drive',
                'price' => 1999,
                'image' => 'https://placehold.co/400?text=External+Hard+Drive',
            ],
            [
                'name' => 'Smartwatch',
                'price' => 3499,
                'image' => 'https://placehold.co/400?text=Smartwatch',
            ],
            [
                'name' => 'Fitness Tracker',
                'price' => 1999,
                'image' => 'https://placehold.co/400?text=Fitness+Tracker',
            ],
            [
                'name' => 'E-Reader',
                'price' => 2999,
                'image' => 'https://placehold.co/400?text=E-Reader',
            ],
            [
                'name' => 'Digital Camera',
                'price' => 5999,
                'image' => 'https://placehold.co/400?text=Digital+Camera',
            ],
            [
                'name' => 'Action Camera',
                'price' => 3999,
                'image' => 'https://placehold.co/400?text=Action+Camera',
            ],
            [
                'name' => 'Drone',
                'price' => 7999,
                'image' => 'https://placehold.co/400?text=Drone',
            ],
            [
                'name' => 'VR Headset',
                'price' => 4999,
                'image' => 'https://placehold.co/400?text=VR+Headset',
            ],
            [
                'name' => 'Gaming Console',
                'price' => 29999,
                'image' => 'https://placehold.co/400?text=Gaming+Console',
            ],
            [
                'name' => 'Wireless Mouse',
                'price' => 799,
                'image' => 'https://placehold.co/400?text=Wireless+Mouse',
            ],
            [
                'name' => 'Mechanical Keyboard',
                'price' => 1999,
                'image' => 'https://placehold.co/400?text=Mechanical+Keyboard',
            ],
            [
                'name' => 'Monitor Stand',
                'price' => 899,
                'image' => 'https://placehold.co/400?text=Monitor+Stand',
            ],
            [
                'name' => 'Desk Organizer',
                'price' => 499,
                'image' => 'https://placehold.co/400?text=Desk+Organizer',
            ],
            [
                'name' => 'Cable Management Sleeve',
                'price' => 299,
                'image' => 'https://placehold.co/400?text=Cable+Management+Sleeve',
            ],
            [
                'name' => 'Laptop Stand',
                'price' => 899,
                'image' => 'https://placehold.co/400?text=Laptop+Stand',
            ],
            [
                'name' => 'Portable Monitor',
                'price' => 4999,
                'image' => 'https://placehold.co/400?text=Portable+Monitor',
            ],
            [
                'name' => 'Webcam',
                'price' => 1299, 
                'image' => 'https://placehold.co/400?text=Webcam',
            ],       
            [
                'name' => 'Microphone',
                'price' => 1999,
                'image' => 'https://placehold.co/400?text=Microphone',
            ],
            [
                'name' => 'Graphics Tablet',
                'price' => 2499,
                'image' => 'https://placehold.co/400?text=Graphics+Tablet',
            ],
            [
                'name' => 'Drawing Pen',
                'price' => 499,
                'image' => 'https://placehold.co/400?text=Drawing+Pen',
            ],
            [
                'name' => '3D Printer',
                'price' => 14999,
                'image' => 'https://placehold.co/400?text=3D+Printer',
            ], 
            [
                'name' => 'Smart Home Hub',
                'price' => 2499,
                'image' => 'https://placehold.co/400?text=Smart+Home+Hub',
            ],
            [
                'name' => 'Smart Light Bulb',
                'price' => 399,
                'image' => 'https://placehold.co/400?text=Smart+Light+Bulb',
            ],
            [
                'name' => 'Smart Thermostat',
                'price' => 4999,
                'image' => 'https://placehold.co/400?text=Smart+Thermostat',
            ],
            [
                'name' => 'Smart Lock',
                'price' => 2999,
                'image' => 'https://placehold.co/400?text=Smart+Lock',
            ],
            [
                'name' => 'Smart Security Camera',
                'price' => 3499,
                'image' => 'https://placehold.co/400?text=Smart+Security+Camera',
            ],  
            [
                'name' => 'Smart Smoke Detector',
                'price' => 1999,
                'image' => 'https://placehold.co/400?text=Smart+Smoke+Detector',
            ],
            [
                'name' => 'Smart Plug',
                'price' => 499,
                'image' => 'https://placehold.co/400?text=Smart+Plug',
            ],
            [
                'name' => 'Smart Air Purifier',
                'price' => 3999,
                'image' => 'https://placehold.co/400?text=Smart+Air+Purifier',
            ],
            [
                'name' => 'Smart Coffee Maker',
                'price' => 2499,
                'image' => 'https://placehold.co/400?text=Smart+Coffee+Maker',
            ],
            [
                'name' => 'Smart Refrigerator',
                'price' => 59999,
                'image' => 'https://placehold.co/400?text=Smart+Refrigerator',
            ],
            [
                'name' => 'Smart Washing Machine',
                'price' => 39999, 
                'image' => 'https://placehold.co/400?text=Smart+Washing+Machine',
            ],
            [
                'name' => 'Smart Oven',
                'price' => 49999,
                'image' => 'https://placehold.co/400?text=Smart+Oven',
            ],
            [
                'name' => 'Smart Dishwasher',
                'price' => 44999,                       
                'image' => 'https://placehold.co/400?text=Smart+Dishwasher',
            ],
            [
                'name' => 'Smart Vacuum Cleaner',
                'price' => 29999,           
                'image' => 'https://placehold.co/400?text=Smart+Vacuum+Cleaner',
            ],
            [
                'name' => 'Smart Air Conditioner',
                'price' => 59999,           
                'image' => 'https://placehold.co/400?text=Smart+Air+Conditioner',
            ],
            [
                'name' => 'Smart Heater',
                'price' => 24999,
                'image' => 'https://placehold.co/400?text=Smart+Heater',
            ],
            [
                'name' => 'Smart Humidifier',
                'price' => 19999,
                'image' => 'https://placehold.co/400?text=Smart+Humidifier',
            ],
            [
                'name' => 'Smart Dehumidifier',
                'price' => 24999,
                'image' => 'https://placehold.co/400?text=Smart+Dehumidifier',
            ],
            [
                'name' => 'Smart Air Quality Monitor',
                'price' => 2999,
                'image' => 'https://placehold.co/400?text=Smart+Air+Quality+Monitor',
            ],
            [
                'name' => 'Smart Water Leak Detector',
                'price' => 1999,
                'image' => 'https://placehold.co/400?text=Smart+Water+Leak+Detector',
            ],
            [
                'name' => 'Smart Garage Door Opener',
                'price' => 2999,
                'image' => 'https://placehold.co/400?text=Smart+Garage+Door+Opener',
            ],
            [
                'name' => 'Smart Irrigation System',
                'price' => 3999,
                'image' => 'https://placehold.co/400?text=Smart+Irrigation+System',
            ],
            [
                'name' => 'Smart Pet Feeder',
                'price' => 2499,
                'image' => 'https://placehold.co/400?text=Smart+Pet+Feeder',
            ],
            [
                'name' => 'Smart Pet Camera',
                'price' => 3499,
                'image' => 'https://placehold.co/400?text=Smart+Pet+Camera',
            ],
            [
                'name' => 'Smart Pet Door',
                'price' => 3999,
                'image' => 'https://placehold.co/400?text=Smart+Pet+Door',
            ],
            [
                'name' => 'Smart Pet Tracker',          
                'price' => 1999,
                'image' => 'https://placehold.co/400?text=Smart+Pet+Tracker',
            ],
            [
                'name' => 'Smart Plant Monitor',
                'price' => 1499,
                'image' => 'https://placehold.co/400?text=Smart+Plant+Monitor',
            ],
        ];

        foreach ($products as $index => $data) {
            //Insert product
            $product = Product::create($data);

            //Insert default stock for that product (no variant)
            ProductStock::create([
                'product_id' => $product->id,
                'sku' => 'SKU-' . strtoupper(str_replace(' ', '-', $product->name)) . '-' . ($index + 1),
                'variant' => null,
                'price' => $product->price,
                'qty' => rand(3,10),
                'image' => $product->image,
                'status' => 'active',
            ]);
        }
    }
}
