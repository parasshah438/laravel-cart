<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing coupons
        Coupon::truncate();
        $coupons = [
            [
                'code' => 'WELCOME10',
                'type' => 'percent',
                'discount' => 10,
                'min_cart_value' => 50.00,
                'starts_at' => Carbon::now()->subDays(5),
                'expires_at' => Carbon::now()->addDays(30),
            ],
            [
                'code' => 'FLAT50',
                'type' => 'fixed',
                'discount' => 50,
                'min_cart_value' => 100.00,
                'starts_at' => Carbon::now()->subDays(2),
                'expires_at' => Carbon::now()->addDays(15),
            ],
            [
                'code' => 'SUMMER15',
                'type' => 'percent',
                'discount' => 15,
                'min_cart_value' => 75.00,
                'starts_at' => Carbon::now()->subDays(10),
                'expires_at' => Carbon::now()->addDays(10),
            ],
            [
                'code' => 'SAVE100',
                'type' => 'fixed',
                'discount' => 100,
                'min_cart_value' => 200.00,
                'starts_at' => Carbon::now()->subDays(20),
                'expires_at' => Carbon::now()->addDays(45),
            ],
            [
                'code' => 'NEWUSER25',
                'type' => 'percent',
                'discount' => 25,
                'min_cart_value' => 30.00,
                'starts_at' => Carbon::now()->subDays(3),
                'expires_at' => Carbon::now()->addDays(60),
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::create($coupon);
        }
    }
}
