<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user or create a test user
        $user = User::first();
        
        if (!$user) {
            // Create a test user if none exists
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        // Sample notifications data
        $notifications = [
            [
                'type' => Notification::TYPE_ORDER_PLACED,
                'title' => 'Order Placed Successfully!',
                'message' => 'Your order #ORD-2024-001 has been placed and is being processed. You will receive updates as your order progresses.',
                'data' => ['order_id' => 'ORD-2024-001'],
                'action_url' => '/order/ORD-2024-001/track',
                'is_important' => false,
                'created_at' => now()->subMinutes(5),
            ],
            [
                'type' => Notification::TYPE_ORDER_SHIPPED,
                'title' => 'Great News! Your Order is Shipped',
                'message' => 'Your order #ORD-2024-002 containing iPhone 15 Pro has been shipped and is on its way to you. Expected delivery: Tomorrow',
                'data' => ['order_id' => 'ORD-2024-002', 'product_name' => 'iPhone 15 Pro'],
                'action_url' => '/order/ORD-2024-002/track',
                'is_important' => true,
                'created_at' => now()->subHours(2),
            ],
            [
                'type' => Notification::TYPE_ORDER_DELIVERED,
                'title' => 'Order Delivered Successfully!',
                'message' => 'Your order #ORD-2024-003 has been delivered. We hope you love your new purchase! Please consider leaving a review.',
                'data' => ['order_id' => 'ORD-2024-003'],
                'action_url' => '/order/ORD-2024-003',
                'is_important' => false,
                'created_at' => now()->subHours(6),
                'read_at' => now()->subHours(5),
            ],
            [
                'type' => Notification::TYPE_PAYMENT_SUCCESS,
                'title' => 'Payment Successful',
                'message' => 'Your payment of $299.99 for order #ORD-2024-004 has been processed successfully. Thank you for your purchase!',
                'data' => ['order_id' => 'ORD-2024-004', 'amount' => '$299.99'],
                'action_url' => '/order/ORD-2024-004',
                'is_important' => false,
                'created_at' => now()->subHours(8),
                'read_at' => now()->subHours(7),
            ],
            [
                'type' => Notification::TYPE_REVIEW_REQUEST,
                'title' => 'How was your recent purchase?',
                'message' => 'We\'d love to hear about your experience with the MacBook Pro you purchased. Your review helps other customers make informed decisions.',
                'data' => ['product_name' => 'MacBook Pro 16"', 'order_id' => 'ORD-2024-005'],
                'action_url' => '/product/macbook-pro-16/review',
                'is_important' => false,
                'created_at' => now()->subDays(1),
            ],
            [
                'type' => Notification::TYPE_WISHLIST_SALE,
                'title' => '🔥 Item on Sale!',
                'message' => 'Great news! The Samsung Galaxy Watch you added to your wishlist is now 25% off. Limited time offer!',
                'data' => ['product_name' => 'Samsung Galaxy Watch', 'discount' => '25%'],
                'action_url' => '/product/samsung-galaxy-watch',
                'is_important' => true,
                'created_at' => now()->subDays(1),
            ],
            [
                'type' => Notification::TYPE_PROMOTION,
                'title' => 'Weekend Flash Sale - Up to 50% Off!',
                'message' => 'Don\'t miss our biggest sale of the month! Get up to 50% off on electronics, fashion, and home appliances. Sale ends Sunday!',
                'data' => ['sale_type' => 'weekend_flash', 'max_discount' => '50%'],
                'action_url' => '/shop?sale=weekend-flash',
                'is_important' => false,
                'created_at' => now()->subDays(2),
                'read_at' => now()->subDays(2),
            ],
            [
                'type' => Notification::TYPE_WELCOME,
                'title' => 'Welcome to Laravel Cart! 🎉',
                'message' => 'Thank you for joining our community! Enjoy free shipping on your first order and explore our amazing collection of products.',
                'data' => ['welcome_offer' => 'free_shipping'],
                'action_url' => '/shop',
                'is_important' => false,
                'created_at' => now()->subDays(3),
                'read_at' => now()->subDays(3),
            ],
            [
                'type' => Notification::TYPE_SYSTEM,
                'title' => 'Security Alert: Password Changed',
                'message' => 'Your account password was successfully changed. If this wasn\'t you, please contact our support team immediately.',
                'data' => ['security_event' => 'password_change'],
                'action_url' => '/profile/security',
                'is_important' => true,
                'created_at' => now()->subDays(5),
                'read_at' => now()->subDays(4),
            ],
            [
                'type' => Notification::TYPE_ORDER_CANCELLED,
                'title' => 'Order Cancelled',
                'message' => 'Your order #ORD-2024-006 has been cancelled as requested. Refund will be processed within 3-5 business days.',
                'data' => ['order_id' => 'ORD-2024-006', 'refund_time' => '3-5 business days'],
                'action_url' => '/order/ORD-2024-006',
                'is_important' => true,
                'created_at' => now()->subDays(7),
                'read_at' => now()->subDays(6),
            ],
        ];

        // Create notifications for the user
        foreach ($notifications as $notificationData) {
            Notification::create([
                'user_id' => $user->id,
                'type' => $notificationData['type'],
                'title' => $notificationData['title'],
                'message' => $notificationData['message'],
                'data' => $notificationData['data'],
                'action_url' => $notificationData['action_url'],
                'is_important' => $notificationData['is_important'],
                'read_at' => $notificationData['read_at'] ?? null,
                'channel' => 'web',
                'created_at' => $notificationData['created_at'],
                'updated_at' => $notificationData['created_at'],
            ]);
        }

        $this->command->info('Created ' . count($notifications) . ' sample notifications for user: ' . $user->email);
    }
}
