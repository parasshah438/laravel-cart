<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProfessionalCouponsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME20',
                'title' => 'Welcome Offer',
                'description' => 'Get 20% off on your first order',
                'terms' => 'Valid for new users only. Cannot be combined with other offers.',
                'type' => 'percent',
                'discount' => 20.00,
                'max_discount' => 500.00,
                'min_cart_value' => 999.00,
                'usage_limit' => 1000,
                'used_count' => 245,
                'category' => 'first_order',
                'banner_color' => '#e74c3c',
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addDays(30),
            ],
            [
                'code' => 'SAVE100',
                'title' => 'Flat ₹100 Off',
                'description' => 'Save ₹100 on orders above ₹500',
                'terms' => 'Valid on all products. Free shipping included.',
                'type' => 'fixed',
                'discount' => 100.00,
                'max_discount' => null,
                'min_cart_value' => 500.00,
                'usage_limit' => null,
                'used_count' => 1205,
                'category' => 'general',
                'banner_color' => '#3498db',
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addDays(15),
            ],
            [
                'code' => 'MEGA50',
                'title' => 'Mega Sale Special',
                'description' => 'Flat 50% off on electronics',
                'terms' => 'Valid on electronics category only. Limited period offer.',
                'type' => 'percent',
                'discount' => 50.00,
                'max_discount' => 2000.00,
                'min_cart_value' => 1500.00,
                'usage_limit' => 500,
                'used_count' => 387,
                'category' => 'festival',
                'banner_color' => '#f39c12',
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addDays(7),
            ],
            [
                'code' => 'FREESHIP',
                'title' => 'Free Shipping',
                'description' => 'Get free shipping on any order',
                'terms' => 'Valid on all orders. No minimum purchase required.',
                'type' => 'fixed',
                'discount' => 49.00,
                'max_discount' => null,
                'min_cart_value' => null,
                'usage_limit' => null,
                'used_count' => 5672,
                'category' => 'general',
                'banner_color' => '#27ae60',
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addDays(90),
            ],
            [
                'code' => 'LOYALTY15',
                'title' => 'Loyalty Reward',
                'description' => '15% off for valued customers',
                'terms' => 'Valid for customers with 5+ orders. Premium members only.',
                'type' => 'percent',
                'discount' => 15.00,
                'max_discount' => 750.00,
                'min_cart_value' => 750.00,
                'usage_limit' => 200,
                'used_count' => 89,
                'category' => 'loyalty',
                'banner_color' => '#9b59b6',
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addDays(60),
            ],
        ];

        foreach ($coupons as $coupon) {
            \App\Models\Coupon::create($coupon);
        }
    }
}
