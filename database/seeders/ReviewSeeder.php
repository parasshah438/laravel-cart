<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\Product;
use App\Models\ReviewHelpfulness;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample review data for testing
        $reviewData = [
            [
                'rating' => 5,
                'title' => 'Excellent product! Highly recommend',
                'comment' => 'This product exceeded my expectations in every way. The quality is outstanding, delivery was fast, and the customer service was helpful. I would definitely purchase again and recommend to friends and family.',
                'would_recommend' => true,
                'product_variant' => 'Size M, Blue Color',
                'verified_purchase' => true
            ],
            [
                'rating' => 4,
                'title' => 'Good value for money',
                'comment' => 'Overall satisfied with this purchase. The product works as described and the price point is reasonable. Minor issues with packaging but the product itself is solid.',
                'would_recommend' => true,
                'product_variant' => 'Size L, Red Color',
                'verified_purchase' => true
            ],
            [
                'rating' => 3,
                'title' => 'Average product, meets basic needs',
                'comment' => 'The product is okay for the price. It does what it\'s supposed to do but nothing exceptional. Build quality could be better. Suitable for basic use.',
                'would_recommend' => null,
                'product_variant' => 'Size S, Black Color',
                'verified_purchase' => false
            ],
            [
                'rating' => 5,
                'title' => 'Amazing quality and fast shipping!',
                'comment' => 'I am thoroughly impressed with this purchase. The attention to detail is remarkable, and it arrived much faster than expected. The product photos don\'t do it justice - it looks even better in person!',
                'would_recommend' => true,
                'product_variant' => 'Size XL, Green Color',
                'verified_purchase' => true
            ],
            [
                'rating' => 2,
                'title' => 'Not what I expected',
                'comment' => 'Unfortunately, this product didn\'t meet my expectations. The quality feels cheap and it doesn\'t work as advertised. Customer service was responsive but couldn\'t resolve my issues.',
                'would_recommend' => false,
                'product_variant' => 'Size M, White Color',
                'verified_purchase' => true
            ],
            [
                'rating' => 4,
                'title' => 'Great product with minor flaws',
                'comment' => 'This is a solid product overall. The main functionality works perfectly and I\'m happy with the performance. There are a few minor design issues but nothing that affects daily use.',
                'would_recommend' => true,
                'product_variant' => 'Size L, Gray Color',
                'verified_purchase' => true
            ],
            [
                'rating' => 5,
                'title' => 'Perfect for my needs!',
                'comment' => 'Exactly what I was looking for! The product description was accurate and it arrived quickly. Great build quality and excellent functionality. Very pleased with this purchase.',
                'would_recommend' => true,
                'product_variant' => 'Size S, Pink Color',
                'verified_purchase' => true
            ],
            [
                'rating' => 1,
                'title' => 'Poor quality, waste of money',
                'comment' => 'This product broke within a week of use. Very disappointed with the quality. The materials feel cheap and the construction is poor. Would not recommend to anyone.',
                'would_recommend' => false,
                'product_variant' => 'Size XL, Yellow Color',
                'verified_purchase' => true
            ]
        ];

        // Get some users and products for the reviews
        $users = User::limit(10)->get();
        $products = Product::limit(5)->get();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No users or products found. Please seed users and products first.');
            return;
        }

        $this->command->info('Creating sample reviews...');

        foreach ($products as $product) {
            // Create 3-6 reviews per product
            $reviewCount = rand(3, 6);
            
            for ($i = 0; $i < $reviewCount; $i++) {
                if ($i >= count($reviewData)) break;
                
                $userData = $reviewData[$i];
                $user = $users->random();
                
                // Check if user already reviewed this product
                if (Review::where('user_id', $user->id)->where('product_id', $product->id)->exists()) {
                    continue;
                }

                $review = Review::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'rating' => $userData['rating'],
                    'title' => $userData['title'],
                    'comment' => $userData['comment'],
                    'would_recommend' => $userData['would_recommend'],
                    'product_variant' => $userData['product_variant'],
                    'verified_purchase' => $userData['verified_purchase'],
                    'status' => 'approved',
                    'approved_at' => now(),
                    'helpful_count' => rand(0, 15),
                    'not_helpful_count' => rand(0, 3),
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Mozilla/5.0 (Sample Browser)',
                    'created_at' => now()->subDays(rand(1, 30)),
                    'last_updated_by_user' => now()->subDays(rand(1, 30))
                ]);

                // Create some helpfulness votes
                $voteCount = rand(0, 8);
                $availableUsers = $users->where('id', '!=', $user->id)->shuffle();
                
                for ($j = 0; $j < $voteCount && $j < $availableUsers->count(); $j++) {
                    ReviewHelpfulness::create([
                        'review_id' => $review->id,
                        'user_id' => $availableUsers[$j]->id,
                        'is_helpful' => rand(0, 1) === 1,
                        'ip_address' => '127.0.0.1'
                    ]);
                }

                // Update helpfulness counts
                $helpfulCount = ReviewHelpfulness::where('review_id', $review->id)
                                                ->where('is_helpful', true)
                                                ->count();
                $notHelpfulCount = ReviewHelpfulness::where('review_id', $review->id)
                                                   ->where('is_helpful', false)
                                                   ->count();
                
                $review->update([
                    'helpful_count' => $helpfulCount,
                    'not_helpful_count' => $notHelpfulCount
                ]);
            }

            // Update product rating cache
            $avgRating = $product->reviews()->approved()->avg('rating');
            $reviewCount = $product->reviews()->approved()->count();

            $product->update([
                'average_rating' => $avgRating ? round($avgRating, 1) : null,
                'review_count' => $reviewCount
            ]);

            $this->command->info("Created {$reviewCount} reviews for product: {$product->name}");
        }

        $this->command->info('Sample reviews created successfully!');
    }
}
