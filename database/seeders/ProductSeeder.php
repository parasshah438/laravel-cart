<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{

    public function run(): void
    {
        Product::truncate();
        Product::insert([
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
        ]);
    }
}
