<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Slider;

class SliderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sliders = [
            [
                'title' => 'Summer Fashion Sale',
                'subtitle' => 'Up to 70% off on trending fashion items. Limited time offer!',
                'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=600&h=400&fit=crop',
                'button_text' => 'Shop Fashion',
                'button_link' => '#products',
                'bg_color' => '#667eea',
                'bg_color_secondary' => '#764ba2',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Electronics Mega Sale',
                'subtitle' => 'Latest gadgets and electronics at unbeatable prices. Free shipping included!',
                'image' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=600&h=400&fit=crop',
                'button_text' => 'Shop Electronics',
                'button_link' => '#products',
                'bg_color' => '#f093fb',
                'bg_color_secondary' => '#f5576c',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Home & Living Collection',
                'subtitle' => 'Transform your space with our beautiful home decor collection. New arrivals weekly!',
                'image' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=600&h=400&fit=crop',
                'button_text' => 'Explore Home',
                'button_link' => '#products',
                'bg_color' => '#4facfe',
                'bg_color_secondary' => '#00f2fe',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Sports & Fitness Gear',
                'subtitle' => 'Get fit with premium sports equipment and activewear. Quality guaranteed!',
                'image' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&h=400&fit=crop',
                'button_text' => 'Shop Sports',
                'button_link' => '#products',
                'bg_color' => '#fa709a',
                'bg_color_secondary' => '#fee140',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'title' => 'Books & Learning',
                'subtitle' => 'Expand your knowledge with our vast collection of books and educational materials.',
                'image' => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=600&h=400&fit=crop',
                'button_text' => 'Browse Books',
                'button_link' => '#products',
                'bg_color' => '#a8edea',
                'bg_color_secondary' => '#fed6e3',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($sliders as $slider) {
            Slider::create($slider);
        }
    }
}
