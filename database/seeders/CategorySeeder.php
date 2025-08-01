<?php
// filepath: c:\wamp64\www\test\12\laravel-cart\database\seeders\CategorySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing categories
        DB::table('categories')->truncate();

        $categories = [
            // 1. FASHION & CLOTHING
            [
                'name' => 'Fashion',
                'slug' => 'fashion',
                'parent_id' => null,
                'status' => true,
                'icon' => 'fas fa-tshirt',
                'image' => 'categories/fashion.jpg',
                'sort_order' => 1,
                'meta_title' => 'Fashion & Clothing - Latest Trends',
                'meta_description' => 'Shop latest fashion trends for men, women and kids. Clothing, footwear, accessories and more.',
                'meta_keywords' => 'fashion, clothing, apparel, trendy clothes',
                'children' => [
                    [
                        'name' => 'Men',
                        'slug' => 'men-fashion',
                        'icon' => 'fas fa-male',
                        'image' => 'categories/men-fashion.jpg',
                        'sort_order' => 1,
                        'meta_title' => 'Men\'s Fashion - Clothing & Accessories',
                        'meta_description' => 'Shop men\'s clothing, shoes, accessories. T-shirts, shirts, jeans, formal wear and more.',
                        'meta_keywords' => 'mens fashion, mens clothing, mens shoes, mens accessories',
                        'children' => [
                            ['name' => 'T-Shirts & Polos', 'slug' => 'mens-tshirts-polos', 'icon' => 'fas fa-tshirt'],
                            ['name' => 'Shirts', 'slug' => 'mens-shirts', 'icon' => 'fas fa-user-tie'],
                            ['name' => 'Jeans & Trousers', 'slug' => 'mens-jeans-trousers', 'icon' => 'fas fa-user'],
                            ['name' => 'Shorts & 3/4ths', 'slug' => 'mens-shorts', 'icon' => 'fas fa-running'],
                            ['name' => 'Ethnic Wear', 'slug' => 'mens-ethnic-wear', 'icon' => 'fas fa-pray'],
                            ['name' => 'Innerwear', 'slug' => 'mens-innerwear', 'icon' => 'fas fa-user-secret'],
                            ['name' => 'Sleepwear', 'slug' => 'mens-sleepwear', 'icon' => 'fas fa-bed'],
                            ['name' => 'Footwear', 'slug' => 'mens-footwear', 'icon' => 'fas fa-shoe-prints'],
                            ['name' => 'Sports & Activewear', 'slug' => 'mens-sports-activewear', 'icon' => 'fas fa-running'],
                            ['name' => 'Accessories', 'slug' => 'mens-accessories', 'icon' => 'fas fa-glasses'],
                        ]
                    ],
                    [
                        'name' => 'Women',
                        'slug' => 'women-fashion',
                        'icon' => 'fas fa-female',
                        'image' => 'categories/women-fashion.jpg',
                        'sort_order' => 2,
                        'meta_title' => 'Women\'s Fashion - Clothing & Accessories',
                        'meta_description' => 'Shop women\'s clothing, shoes, accessories. Dresses, tops, ethnic wear, western wear and more.',
                        'meta_keywords' => 'womens fashion, womens clothing, womens shoes, womens accessories',
                        'children' => [
                            ['name' => 'Western Wear', 'slug' => 'womens-western-wear', 'icon' => 'fas fa-female'],
                            ['name' => 'Ethnic Wear', 'slug' => 'womens-ethnic-wear', 'icon' => 'fas fa-pray'],
                            ['name' => 'Lingerie & Sleepwear', 'slug' => 'womens-lingerie-sleepwear', 'icon' => 'fas fa-bed'],
                            ['name' => 'Footwear', 'slug' => 'womens-footwear', 'icon' => 'fas fa-shoe-prints'],
                            ['name' => 'Sports & Activewear', 'slug' => 'womens-sports-activewear', 'icon' => 'fas fa-running'],
                            ['name' => 'Handbags & Clutches', 'slug' => 'womens-handbags-clutches', 'icon' => 'fas fa-briefcase'],
                            ['name' => 'Jewellery', 'slug' => 'womens-jewellery', 'icon' => 'fas fa-gem'],
                            ['name' => 'Accessories', 'slug' => 'womens-accessories', 'icon' => 'fas fa-glasses'],
                        ]
                    ],
                    [
                        'name' => 'Kids',
                        'slug' => 'kids-fashion',
                        'icon' => 'fas fa-child',
                        'image' => 'categories/kids-fashion.jpg',
                        'sort_order' => 3,
                        'meta_title' => 'Kids Fashion - Boys & Girls Clothing',
                        'meta_description' => 'Shop kids clothing for boys and girls. T-shirts, dresses, ethnic wear, footwear and more.',
                        'meta_keywords' => 'kids fashion, kids clothing, boys clothing, girls clothing',
                        'children' => [
                            ['name' => 'Boys Clothing', 'slug' => 'boys-clothing', 'icon' => 'fas fa-male'],
                            ['name' => 'Girls Clothing', 'slug' => 'girls-clothing', 'icon' => 'fas fa-female'],
                            ['name' => 'Baby Clothing (0-24M)', 'slug' => 'baby-clothing', 'icon' => 'fas fa-baby'],
                            ['name' => 'Kids Footwear', 'slug' => 'kids-footwear', 'icon' => 'fas fa-shoe-prints'],
                            ['name' => 'Kids Accessories', 'slug' => 'kids-accessories', 'icon' => 'fas fa-child'],
                        ]
                    ]
                ]
            ],

            // 2. ELECTRONICS
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'parent_id' => null,
                'status' => true,
                'icon' => 'fas fa-laptop',
                'image' => 'categories/electronics.jpg',
                'sort_order' => 2,
                'meta_title' => 'Electronics - Mobiles, Laptops, TV, Audio',
                'meta_description' => 'Shop latest electronics. Mobiles, laptops, TV, audio, cameras, gaming and more.',
                'meta_keywords' => 'electronics, mobiles, laptops, tv, audio, cameras',
                'children' => [
                    [
                        'name' => 'Mobiles & Accessories',
                        'slug' => 'mobiles-accessories',
                        'icon' => 'fas fa-mobile-alt',
                        'sort_order' => 1,
                        'children' => [
                            ['name' => 'Smartphones', 'slug' => 'smartphones', 'icon' => 'fas fa-mobile-alt'],
                            ['name' => 'Feature Phones', 'slug' => 'feature-phones', 'icon' => 'fas fa-phone'],
                            ['name' => 'Cases & Covers', 'slug' => 'mobile-cases-covers', 'icon' => 'fas fa-shield-alt'],
                            ['name' => 'Screen Guards', 'slug' => 'screen-guards', 'icon' => 'fas fa-shield-alt'],
                            ['name' => 'Power Banks', 'slug' => 'power-banks', 'icon' => 'fas fa-battery-full'],
                            ['name' => 'Chargers', 'slug' => 'mobile-chargers', 'icon' => 'fas fa-plug'],
                            ['name' => 'Headphones', 'slug' => 'mobile-headphones', 'icon' => 'fas fa-headphones'],
                        ]
                    ],
                    [
                        'name' => 'Laptops & Computers',
                        'slug' => 'laptops-computers',
                        'icon' => 'fas fa-laptop',
                        'sort_order' => 2,
                        'children' => [
                            ['name' => 'Laptops', 'slug' => 'laptops', 'icon' => 'fas fa-laptop'],
                            ['name' => 'Desktop PCs', 'slug' => 'desktop-pcs', 'icon' => 'fas fa-desktop'],
                            ['name' => 'Tablets', 'slug' => 'tablets', 'icon' => 'fas fa-tablet-alt'],
                            ['name' => 'Monitors', 'slug' => 'monitors', 'icon' => 'fas fa-tv'],
                            ['name' => 'Keyboards', 'slug' => 'keyboards', 'icon' => 'fas fa-keyboard'],
                            ['name' => 'Mouse', 'slug' => 'computer-mouse', 'icon' => 'fas fa-mouse'],
                            ['name' => 'Printers', 'slug' => 'printers', 'icon' => 'fas fa-print'],
                            ['name' => 'Storage', 'slug' => 'computer-storage', 'icon' => 'fas fa-hdd'],
                        ]
                    ],
                    [
                        'name' => 'TV & Audio',
                        'slug' => 'tv-audio',
                        'icon' => 'fas fa-tv',
                        'sort_order' => 3,
                        'children' => [
                            ['name' => 'Televisions', 'slug' => 'televisions', 'icon' => 'fas fa-tv'],
                            ['name' => 'Speakers', 'slug' => 'speakers', 'icon' => 'fas fa-volume-up'],
                            ['name' => 'Soundbars', 'slug' => 'soundbars', 'icon' => 'fas fa-music'],
                            ['name' => 'Home Theatre', 'slug' => 'home-theatre', 'icon' => 'fas fa-home'],
                            ['name' => 'Headphones', 'slug' => 'headphones', 'icon' => 'fas fa-headphones'],
                            ['name' => 'Earphones', 'slug' => 'earphones', 'icon' => 'fas fa-headphones'],
                        ]
                    ],
                    [
                        'name' => 'Cameras & Photography',
                        'slug' => 'cameras-photography',
                        'icon' => 'fas fa-camera',
                        'sort_order' => 4,
                        'children' => [
                            ['name' => 'Digital Cameras', 'slug' => 'digital-cameras', 'icon' => 'fas fa-camera'],
                            ['name' => 'DSLR Cameras', 'slug' => 'dslr-cameras', 'icon' => 'fas fa-camera-retro'],
                            ['name' => 'Action Cameras', 'slug' => 'action-cameras', 'icon' => 'fas fa-video'],
                            ['name' => 'Lenses', 'slug' => 'camera-lenses', 'icon' => 'fas fa-circle'],
                            ['name' => 'Tripods', 'slug' => 'tripods', 'icon' => 'fas fa-camera'],
                            ['name' => 'Camera Accessories', 'slug' => 'camera-accessories', 'icon' => 'fas fa-cog'],
                        ]
                    ]
                ]
            ],

            // 3. HOME & FURNITURE
            [
                'name' => 'Home & Furniture',
                'slug' => 'home-furniture',
                'parent_id' => null,
                'status' => true,
                'icon' => 'fas fa-home',
                'image' => 'categories/home-furniture.jpg',
                'sort_order' => 3,
                'meta_title' => 'Home & Furniture - Decor, Kitchen, Bath',
                'meta_description' => 'Shop home furniture, decor, kitchen appliances, bath, bedding and more.',
                'meta_keywords' => 'home, furniture, decor, kitchen, appliances, bedding',
                'children' => [
                    [
                        'name' => 'Furniture',
                        'slug' => 'furniture',
                        'icon' => 'fas fa-couch',
                        'sort_order' => 1,
                        'children' => [
                            ['name' => 'Sofas & Seating', 'slug' => 'sofas-seating', 'icon' => 'fas fa-couch'],
                            ['name' => 'Beds & Mattresses', 'slug' => 'beds-mattresses', 'icon' => 'fas fa-bed'],
                            ['name' => 'Dining Tables', 'slug' => 'dining-tables', 'icon' => 'fas fa-utensils'],
                            ['name' => 'Chairs', 'slug' => 'chairs', 'icon' => 'fas fa-chair'],
                            ['name' => 'Storage & Wardrobes', 'slug' => 'storage-wardrobes', 'icon' => 'fas fa-archive'],
                            ['name' => 'TV Units', 'slug' => 'tv-units', 'icon' => 'fas fa-tv'],
                            ['name' => 'Study Tables', 'slug' => 'study-tables', 'icon' => 'fas fa-desk'],
                        ]
                    ],
                    [
                        'name' => 'Home Decor',
                        'slug' => 'home-decor',
                        'icon' => 'fas fa-palette',
                        'sort_order' => 2,
                        'children' => [
                            ['name' => 'Wall Decor', 'slug' => 'wall-decor', 'icon' => 'fas fa-image'],
                            ['name' => 'Lighting', 'slug' => 'lighting', 'icon' => 'fas fa-lightbulb'],
                            ['name' => 'Curtains', 'slug' => 'curtains', 'icon' => 'fas fa-window-maximize'],
                            ['name' => 'Rugs & Carpets', 'slug' => 'rugs-carpets', 'icon' => 'fas fa-square'],
                            ['name' => 'Plants & Planters', 'slug' => 'plants-planters', 'icon' => 'fas fa-leaf'],
                            ['name' => 'Clocks', 'slug' => 'clocks', 'icon' => 'fas fa-clock'],
                        ]
                    ],
                    [
                        'name' => 'Kitchen & Dining',
                        'slug' => 'kitchen-dining',
                        'icon' => 'fas fa-utensils',
                        'sort_order' => 3,
                        'children' => [
                            ['name' => 'Cookware', 'slug' => 'cookware', 'icon' => 'fas fa-fire'],
                            ['name' => 'Kitchen Appliances', 'slug' => 'kitchen-appliances', 'icon' => 'fas fa-blender'],
                            ['name' => 'Dinnerware', 'slug' => 'dinnerware', 'icon' => 'fas fa-utensils'],
                            ['name' => 'Storage Containers', 'slug' => 'storage-containers', 'icon' => 'fas fa-box'],
                            ['name' => 'Kitchen Tools', 'slug' => 'kitchen-tools', 'icon' => 'fas fa-tools'],
                        ]
                    ]
                ]
            ],

            // 4. BEAUTY & PERSONAL CARE
            [
                'name' => 'Beauty & Personal Care',
                'slug' => 'beauty-personal-care',
                'parent_id' => null,
                'status' => true,
                'icon' => 'fas fa-spa',
                'image' => 'categories/beauty.jpg',
                'sort_order' => 4,
                'meta_title' => 'Beauty & Personal Care - Makeup, Skincare',
                'meta_description' => 'Shop beauty products, makeup, skincare, haircare, fragrances and personal care items.',
                'meta_keywords' => 'beauty, makeup, skincare, haircare, cosmetics, personal care',
                'children' => [
                    [
                        'name' => 'Makeup',
                        'slug' => 'makeup',
                        'icon' => 'fas fa-lipstick',
                        'sort_order' => 1,
                        'children' => [
                            ['name' => 'Face Makeup', 'slug' => 'face-makeup', 'icon' => 'fas fa-palette'],
                            ['name' => 'Eye Makeup', 'slug' => 'eye-makeup', 'icon' => 'fas fa-eye'],
                            ['name' => 'Lip Makeup', 'slug' => 'lip-makeup', 'icon' => 'fas fa-lipstick'],
                            ['name' => 'Nail Care', 'slug' => 'nail-care', 'icon' => 'fas fa-hand-paper'],
                            ['name' => 'Makeup Tools', 'slug' => 'makeup-tools', 'icon' => 'fas fa-brush'],
                        ]
                    ],
                    [
                        'name' => 'Skincare',
                        'slug' => 'skincare',
                        'icon' => 'fas fa-leaf',
                        'sort_order' => 2,
                        'children' => [
                            ['name' => 'Face Care', 'slug' => 'face-care', 'icon' => 'fas fa-smile'],
                            ['name' => 'Body Care', 'slug' => 'body-care', 'icon' => 'fas fa-user'],
                            ['name' => 'Sun Care', 'slug' => 'sun-care', 'icon' => 'fas fa-sun'],
                            ['name' => 'Anti-Aging', 'slug' => 'anti-aging', 'icon' => 'fas fa-clock'],
                        ]
                    ],
                    [
                        'name' => 'Hair Care',
                        'slug' => 'hair-care',
                        'icon' => 'fas fa-cut',
                        'sort_order' => 3,
                        'children' => [
                            ['name' => 'Shampoo', 'slug' => 'shampoo', 'icon' => 'fas fa-tint'],
                            ['name' => 'Conditioner', 'slug' => 'conditioner', 'icon' => 'fas fa-spray-can'],
                            ['name' => 'Hair Oil', 'slug' => 'hair-oil', 'icon' => 'fas fa-oil-can'],
                            ['name' => 'Hair Styling', 'slug' => 'hair-styling', 'icon' => 'fas fa-cut'],
                            ['name' => 'Hair Tools', 'slug' => 'hair-tools', 'icon' => 'fas fa-fire'],
                        ]
                    ]
                ]
            ],

            // 5. SPORTS & FITNESS
            [
                'name' => 'Sports & Fitness',
                'slug' => 'sports-fitness',
                'parent_id' => null,
                'status' => true,
                'icon' => 'fas fa-dumbbell',
                'image' => 'categories/sports.jpg',
                'sort_order' => 5,
                'meta_title' => 'Sports & Fitness - Equipment, Clothing',
                'meta_description' => 'Shop sports equipment, fitness gear, activewear, outdoor sports and more.',
                'meta_keywords' => 'sports, fitness, gym, workout, activewear, sports equipment',
                'children' => [
                    [
                        'name' => 'Fitness Equipment',
                        'slug' => 'fitness-equipment',
                        'icon' => 'fas fa-dumbbell',
                        'sort_order' => 1,
                        'children' => [
                            ['name' => 'Gym Equipment', 'slug' => 'gym-equipment', 'icon' => 'fas fa-dumbbell'],
                            ['name' => 'Cardio Equipment', 'slug' => 'cardio-equipment', 'icon' => 'fas fa-heartbeat'],
                            ['name' => 'Yoga Equipment', 'slug' => 'yoga-equipment', 'icon' => 'fas fa-spa'],
                            ['name' => 'Home Gym', 'slug' => 'home-gym', 'icon' => 'fas fa-home'],
                        ]
                    ],
                    [
                        'name' => 'Sports',
                        'slug' => 'sports',
                        'icon' => 'fas fa-football-ball',
                        'sort_order' => 2,
                        'children' => [
                            ['name' => 'Cricket', 'slug' => 'cricket', 'icon' => 'fas fa-baseball-ball'],
                            ['name' => 'Football', 'slug' => 'football', 'icon' => 'fas fa-football-ball'],
                            ['name' => 'Badminton', 'slug' => 'badminton', 'icon' => 'fas fa-shuttlecock'],
                            ['name' => 'Tennis', 'slug' => 'tennis', 'icon' => 'fas fa-tennis-ball'],
                            ['name' => 'Basketball', 'slug' => 'basketball', 'icon' => 'fas fa-basketball-ball'],
                            ['name' => 'Swimming', 'slug' => 'swimming', 'icon' => 'fas fa-swimmer'],
                        ]
                    ]
                ]
            ],

            // 6. BOOKS & EDUCATION
            [
                'name' => 'Books & Education',
                'slug' => 'books-education',
                'parent_id' => null,
                'status' => true,
                'icon' => 'fas fa-book',
                'image' => 'categories/books.jpg',
                'sort_order' => 6,
                'meta_title' => 'Books & Education - Academic, Fiction, Non-fiction',
                'meta_description' => 'Shop books, educational materials, stationery, academic books and more.',
                'meta_keywords' => 'books, education, academic, fiction, textbooks, stationery',
                'children' => [
                    [
                        'name' => 'Books',
                        'slug' => 'books',
                        'icon' => 'fas fa-book-open',
                        'sort_order' => 1,
                        'children' => [
                            ['name' => 'Fiction', 'slug' => 'fiction-books', 'icon' => 'fas fa-book'],
                            ['name' => 'Non-Fiction', 'slug' => 'non-fiction-books', 'icon' => 'fas fa-book-open'],
                            ['name' => 'Academic Books', 'slug' => 'academic-books', 'icon' => 'fas fa-graduation-cap'],
                            ['name' => 'Children Books', 'slug' => 'children-books', 'icon' => 'fas fa-child'],
                            ['name' => 'Comics', 'slug' => 'comics', 'icon' => 'fas fa-laugh'],
                        ]
                    ],
                    [
                        'name' => 'Stationery',
                        'slug' => 'stationery',
                        'icon' => 'fas fa-pen',
                        'sort_order' => 2,
                        'children' => [
                            ['name' => 'Pens & Pencils', 'slug' => 'pens-pencils', 'icon' => 'fas fa-pen'],
                            ['name' => 'Notebooks', 'slug' => 'notebooks', 'icon' => 'fas fa-book'],
                            ['name' => 'Art Supplies', 'slug' => 'art-supplies', 'icon' => 'fas fa-palette'],
                            ['name' => 'Office Supplies', 'slug' => 'office-supplies', 'icon' => 'fas fa-briefcase'],
                        ]
                    ]
                ]
            ],

            // 7. AUTOMOTIVE
            [
                'name' => 'Automotive',
                'slug' => 'automotive',
                'parent_id' => null,
                'status' => true,
                'icon' => 'fas fa-car',
                'image' => 'categories/automotive.jpg',
                'sort_order' => 7,
                'meta_title' => 'Automotive - Car Accessories, Parts, Tools',
                'meta_description' => 'Shop car accessories, automotive parts, tools, bike accessories and more.',
                'meta_keywords' => 'automotive, car accessories, bike accessories, car parts',
                'children' => [
                    [
                        'name' => 'Car Accessories',
                        'slug' => 'car-accessories',
                        'icon' => 'fas fa-car',
                        'sort_order' => 1,
                        'children' => [
                            ['name' => 'Car Electronics', 'slug' => 'car-electronics', 'icon' => 'fas fa-radio'],
                            ['name' => 'Interior Accessories', 'slug' => 'car-interior', 'icon' => 'fas fa-couch'],
                            ['name' => 'Exterior Accessories', 'slug' => 'car-exterior', 'icon' => 'fas fa-car-side'],
                            ['name' => 'Car Care', 'slug' => 'car-care', 'icon' => 'fas fa-spray-can'],
                        ]
                    ],
                    [
                        'name' => 'Bike Accessories',
                        'slug' => 'bike-accessories',
                        'icon' => 'fas fa-motorcycle',
                        'sort_order' => 2,
                        'children' => [
                            ['name' => 'Helmets', 'slug' => 'bike-helmets', 'icon' => 'fas fa-hard-hat'],
                            ['name' => 'Bike Parts', 'slug' => 'bike-parts', 'icon' => 'fas fa-cog'],
                            ['name' => 'Bike Care', 'slug' => 'bike-care', 'icon' => 'fas fa-tools'],
                        ]
                    ]
                ]
            ]
        ];

        $this->insertCategories($categories);
    }

    /**
     * Recursively insert categories
     */
    private function insertCategories($categories, $parentId = null)
    {
        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $categoryData['parent_id'] = $parentId;
            $categoryData['created_at'] = now();
            $categoryData['updated_at'] = now();

            // Set default values if not provided
            $categoryData['status'] = $categoryData['status'] ?? true;
            $categoryData['sort_order'] = $categoryData['sort_order'] ?? 0;
            $categoryData['icon'] = $categoryData['icon'] ?? 'fas fa-tag';
            $categoryData['image'] = $categoryData['image'] ?? null;
            $categoryData['meta_title'] = $categoryData['meta_title'] ?? $categoryData['name'];
            $categoryData['meta_description'] = $categoryData['meta_description'] ?? "Shop {$categoryData['name']} online";
            $categoryData['meta_keywords'] = $categoryData['meta_keywords'] ?? strtolower($categoryData['name']);

            $categoryId = DB::table('categories')->insertGetId($categoryData);

            if (!empty($children)) {
                $this->insertCategories($children, $categoryId);
            }
        }
    }
}