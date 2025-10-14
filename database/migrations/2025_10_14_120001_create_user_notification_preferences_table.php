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
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Notification delivery preferences
            $table->boolean('email_notifications')->default(true);
            $table->boolean('push_notifications')->default(false);
            
            // Notification type preferences
            $table->boolean('order_updates')->default(true);
            $table->boolean('payment_alerts')->default(true);
            $table->boolean('review_reminders')->default(true);
            $table->boolean('review_responses')->default(true);
            $table->boolean('promotional_emails')->default(false);
            $table->boolean('wishlist_sales')->default(true);
            
            // Frequency and timing preferences
            $table->enum('email_frequency', ['instant', 'daily', 'weekly', 'never'])->default('daily');
            $table->enum('quiet_hours', ['none', 'evening', 'night', 'weekend'])->default('night');
            
            // Metadata
            $table->timestamps();
            
            // Indexes
            $table->unique('user_id'); // One preference record per user
            $table->index(['user_id', 'email_notifications']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');
    }
};