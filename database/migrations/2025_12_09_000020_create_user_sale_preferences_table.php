<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_sale_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Notification Preferences
            $table->boolean('email_sale_notifications')->default(true);
            $table->boolean('push_sale_notifications')->default(true);
            $table->boolean('sms_sale_notifications')->default(false);
            
            // Interest Categories
            $table->json('preferred_sale_categories')->nullable();
            $table->json('preferred_brands')->nullable();
            
            // Price Alert Settings
            $table->decimal('price_drop_threshold', 5, 2)->default(10.00);
            $table->integer('max_price_notifications_per_day')->default(5);
            
            // Sale Timing Preferences
            $table->time('preferred_notification_time')->default('09:00:00');
            $table->string('timezone', 50)->default('Asia/Kolkata');
            
            // VIP Settings
            $table->boolean('early_access_enabled')->default(true);
            $table->boolean('exclusive_deals_enabled')->default(true);
            
            $table->timestamps();
            
            // Indexes
            $table->unique('user_id', 'unique_user_preferences');
            $table->index('email_sale_notifications');
            $table->index('push_sale_notifications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sale_preferences');
    }
};