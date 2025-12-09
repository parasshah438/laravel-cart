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
        Schema::create('sale_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Challenge Type
            $table->enum('type', ['spend_amount', 'buy_quantity', 'visit_days', 'share_social', 'invite_friends', 'complete_profile']);
            
            // Challenge Conditions
            $table->decimal('target_value', 10, 2);
            $table->integer('target_days')->default(1);
            
            // Rewards
            $table->enum('reward_type', ['cashback', 'discount_coupon', 'free_product', 'points', 'badge']);
            $table->decimal('reward_value', 10, 2);
            $table->foreignId('reward_product_id')->nullable()->constrained('products')->onDelete('set null');
            
            // Challenge Settings
            $table->integer('max_participants')->default(0);
            $table->integer('current_participants')->default(0);
            $table->integer('per_user_attempts')->default(1);
            
            // Sale Integration
            $table->foreignId('sale_event_id')->nullable()->constrained()->onDelete('set null');
            
            // Display
            $table->string('banner_image', 500)->nullable();
            $table->string('icon', 500)->nullable();
            $table->string('badge_image', 500)->nullable();
            
            // Timing
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active', 'type']);
            $table->index(['starts_at', 'ends_at']);
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_challenges');
    }
};